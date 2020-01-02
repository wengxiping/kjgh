<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/cluster/cluster');

class SocialEvent extends SocialCluster
{
	/**
	 * Defines the cluster type.
	 * @var string
	 */
	public $cluster_type = SOCIAL_TYPE_EVENT;
	public $cluster_var = SOCIAL_TYPE_EVENTS;

	/**
	 * Stores the instances of events.
	 * @var array
	 */
	static $instances = array();

	/**
	 * Stores the guest states key that exists as property within this class.
	 * @var array
	 */
	static $guestStates = array('invited', 'going', 'pending', 'maybe', 'notgoing');

	/**
	 * Stores the guest state of invited.
	 * @var array
	 */
	public $invited = array();

	/**
	 * Stores the guest state of going.
	 * @var array
	 */
	public $going = array();

	/**
	 * Stores the guest state of pending.
	 * @var array
	 */
	public $pending = array();

	/**
	 * Stores the guest state of maybe.
	 * @var array
	 */
	public $maybe = array();

	/**
	 * Stores the guest state of notgoing.
	 * @var array
	 */
	public $notgoing = array();

	/**
	 * Stores all the guests of this event in SocialTableEventGuest class.
	 * @var array
	 */
	public $guests = array();

	/**
	 * Stores all the admin id (mapped to $this->guests) of this event.
	 * @var array
	 */
	public $admins = array();

	/**
	 * Stores the meta table of this event.
	 * @var SocialTableEventMeta
	 */
	public $meta = null;

	/**
	 * Construct and initialise this event class per single event class.
	 *
	 * @since   1.3
	 * @access  public
	 * @param   array     $params The parameters to init.
	 */
	public function __construct($params = array())
	{
		// Create the user parameters object
		$this->_params = ES::registry();

		// Initialize user's property locally.
		$this->initParams($params);

		$this->table = ES::table('Event');
		$this->table->bind($this);

		$this->meta = ES::table('EventMeta');
		$this->meta->load(array('cluster_id' => $this->id));

		parent::__construct();
	}

	public function initParams(&$params)
	{
		// We want to map the members data
		$this->members = isset($params->guests) ? $params->guests : array();
		$this->admins = isset($params->admins) ? $params->admins : array();
		$this->pending = isset($params->pending) ? $params->pending : array();

		return parent::initParams($params);
	}

	/**
	 * Core function to initialise this class.
	 *
	 * @since   1.3
	 * @access  public
	 * @param   Mixed        $ids The ids to load.
	 * @return  Mixed        The event class or array of event classes.
	 */
	public static function factory($ids = null)
	{
		$items = self::loadEvents($ids);

		return $items;
	}

	/**
	 * Custom save function to handle additional meta to save into events_meta table.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function save()
	{
		$isNew = $this->isNew();

		if ($isNew && !$this->hasPointToCreate()) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_EVENTS_INSUFFICIENT_POINTS', $this->getPointToCreate()));
			return false;
		}

		// Let parent save first to ensure there is a cluster id
		$state = parent::save();

		if (!$state) {
			return $state;
		}

		// Then now we store the meta.
		$this->meta->cluster_id = $this->id;
		$this->meta->store();

		return $state;
	}

	/**
	 * Method to get extended event meta data.
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string $key     The key of the meta.
	 * @param   Mixed  $default The default value of the meta.
	 * @return  Mixed           The data of the meta.
	 */
	public function getMeta($key, $default = null)
	{
		return $this->meta->get($key, $default);
	}

	/**
	 * Method to set extended event meta data.
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string $key   The key of the meta.
	 * @param   Mixed  $value The value of the meta.
	 * @return  Boolean True if successful.
	 */
	public function setMeta($key, $value)
	{
		return $this->meta->set($key, $value);
	}

	/**
	 * Event has a different method of deleting itself
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		$parentId = $this->id;

		// Check if this is a recurring event
		if ($this->isRecurringEvent()) {
			$parentId = $this->parent_id;
		}

		// Delete the recurring events
		$model = ES::model("Events");
		$model->deleteRecurringEvents($parentId);

		// Delete from calendar as well
		$this->deleteFromCalendar();

		// Now we delete ourselves
		$state = parent::delete();

		return $state;
	}

	/**
	 * Remove deleted user's stream
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function deleteMemberStream($userId)
	{
		$model = ES::model('Clusters');
		$model->deleteUserStreams($this->id, $this->cluster_type, $userId);
	}

	/**
	 * Deletes an event entry from the calendar
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function deleteFromCalendar()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_apps_calendar');
		$sql->where('uid', $this->id);
		$sql->where('type', SOCIAL_TYPE_EVENT);

		$db->setQuery($sql);
		return $db->Query();
	}

	/**
	 * Loads and prepares the event classes.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public static function loadEvents($ids = null)
	{
		if (is_object($ids)) {
			$obj = new self;
			$obj->bind($ids);

			self::$instances[$ids->id] = $obj;

			return self::$instances[$ids->id];
		}

		$argumentIsArray = is_array($ids);

		if (!is_array($ids)) {
			$ids = array($ids);
		}

		if (empty($ids)) {
			return false;
		}

		$model = ES::model('Events');

		$events = $model->getMeta($ids);

		if (empty($events)) {
			return false;
		}

		$result = array();

		foreach ($events as $event) {
			if (!$event) {
				continue;
			}

			if (isset(self::$instances[$event->id])) {
				$result[] = self::$instances[$event->id];
				continue;
			}

			$event->cover = self::getCoverObject($event);

			$guests = $model->getGuests($event->id);

			$event->guests = array();

			$loaded = array();

			foreach ($guests as $guest) {

				if (isset($loaded[$guest->uid])) {
					continue;
				}

				$event->guests[$guest->uid] = $guest;
				$loaded[$guest->uid] = $guest->uid;

				if ($guest->isAdmin()) {
					$event->admins[] = $guest->uid;
				}

				if (!isset($event->{self::$guestStates[$guest->state]}) || !is_array($event->{self::$guestStates[$guest->state]})){
					$event->{self::$guestStates[$guest->state]} = array();
				}

				// Guests states array only stores the id, and this needs to be mapped to the instance->guests property to get the guest table object.
				$event->{self::$guestStates[$guest->state]}[] = $guest->uid;
			}

			$obj = new SocialEvent($event);

			self::$instances[$event->id] = $obj;

			$result[] = self::$instances[$event->id];
		}

		if (empty($result)) {
			return false;
		}

		if (!$argumentIsArray && count($result) === 1) {
			return $result[0];
		}

		return $result;
	}

	public function bind($data)
	{
		$this->table->bind($data);

		$keyToArray = array_merge(array('avatars', 'admins'), self::$guestStates);

		foreach ($data as $key => $value) {
			if (property_exists($this, $key)) {
				if (in_array($key, $keyToArray) && is_object($value)) {
					$value = ES::makeArray($value);
				}

				$this->$key = $value;
			}
		}
	}

	/**
	 * Returns the total number of seats available
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalSeats()
	{
		$total = $this->getParams()->get('guestlimit', 0);

		return $total;
	}

	/**
	 * Returns the total number of guests attending
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalMembers($options = array())
	{
		return $this->getTotalGoing();
	}

	/**
	 * Returns the total number of pending guests
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalPendingGuests()
	{
		return count($this->pending);
	}

	/**
	 * Returns the total guests in this event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getTotalGuests()
	{
		// Pending and undecided is not consider a guest
		return count($this->guests) - count($this->pending) - count($this->invited);
	}

	/**
	 * Returns the total admins in this event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getTotalAdmins()
	{
		return count($this->admins);
	}

	/**
	 * Returns the total guests that is going to this event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getTotalGoing()
	{
		return count($this->going);
	}

	/**
	 * Returns the total guests that might be going to this event.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  integer  The number of guests that might be going to this event.
	 */
	public function getTotalMaybe()
	{
		return count($this->maybe);
	}

	/**
	 * Returns the total guests that is not going to this event.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  integer  The number of guests that is not going to this event.
	 */
	public function getTotalNotGoing()
	{
		return count($this->notgoing);
	}

	/**
	 * Returns the total guests that is invited but haven't make a decision in this event.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  integer  The number of guests that is invited but haven't make a decision in this event.
	 */
	public function getTotalUndecided()
	{
		return count($this->invited);
	}

	/**
	 * Returns the permalink to the page of this event, depending on the layout.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getPermalink($xhtml = true, $external = false, $layout = 'item', $sef = true, $params = array(), $adminSef = false)
	{
		$options = array('id' => $this->getAlias(), 'layout' => $layout, 'external' => $external, 'sef' => $sef, 'adminSef' => $adminSef);

		// we dont include cluster into apps to create less complications.
		$hasApp = isset($params['appId']) && $params['appId'] ? true : false;
		$includeCluster = ($layout == 'item' || $layout == 'edit') ? true : false;

		if ($includeCluster) {
			// further test if this is filter form or not.
			$includeCluster = ((isset($params['page']) && $params['page'] == 'filterForm') || isset($params['filterId'])) ? false : $includeCluster;
		}

		if (!$hasApp && $includeCluster && $this->isClusterEvent()) {
			$cluster = $this->getCluster();

			$options['uid'] = $cluster->getAlias();
			$options['type'] = $cluster->getType();
		}

		// if event is in draft state, the permalink should always point to edit page.
		if ($this->isDraft()) {
			$options['layout'] = 'edit';
		}

		$options = array_merge($options, $params);

		return FRoute::events($options, $xhtml);
	}

	/**
	 * Centralized method to retrieve a page's edit link.
	 *
	 * @since 2.1
	 * @access	public
	 */
	public function getEditPermalink($xhtml = true, $external = false, $layout = 'edit')
	{
		$url = $this->getPermalink($xhtml, $external, $layout);

		return $url;
	}

	/**
	 * Determines if the event is an open event.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  bool    True if is an open event.
	 */
	public function isOpen()
	{
		return $this->type == SOCIAL_EVENT_TYPE_PUBLIC;
	}

	/**
	 * Determines if the event is a close event.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  bool    True if is a closed event.
	 */
	public function isClosed()
	{
		return $this->type == SOCIAL_EVENT_TYPE_PRIVATE;
	}

	/**
	 * Determines if the user is attending
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function isAttending($id = null)
	{
		$user = ES::user($id);

		if (!isset($this->guests[$user->id])) {
			return false;
		}

		$obj = $this->guests[$user->id];

		return $obj->isGoing();
	}

	/**
	 * Determines if the user is attending
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function isNotAttending($id = null)
	{
		$user = ES::user($id);

		if (!isset($this->guests[$user->id])) {
			return false;
		}

		$obj = $this->guests[$user->id];

		return $obj->isNotGoing();
	}

	/**
	 * Alias method for isClosed
	 *
	 * @since   1.3
	 * @access  public
	 * @return  bool    True if is a private event.
	 */
	public function isPrivate()
	{
		return $this->type == SOCIAL_EVENT_TYPE_PRIVATE;
	}

	/**
	 * Determines if the event is an invite-only event.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  bool    True if is invite-only event.
	 */
	public function isInviteOnly()
	{
		return $this->type == SOCIAL_EVENT_TYPE_INVITE;
	}

	/**
	 * Returns the SocialDate object of the event start datetime.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getEventStart()
	{
		return $this->meta->getStart();
	}

	/**
	 * Returns the SocialDate object of the event end datetime.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  SocialDate The SocialDate object of the event end datetime.
	 */
	public function getEventEnd()
	{
		return $this->meta->getEnd();
	}

	/**
	 * Get number of days this event being held
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getNumberOfDays($currentStart = null, $currentEnd = null)
	{
		$end = $this->getEventEnd()->format('Y-m-d');
		$start = $this->getEventStart()->format('Y-m-d');

		// Only get number of days within specified date
		if ($currentStart && $currentEnd) {
			$currentStart = new DateTime($currentStart);
			$currentEnd = new DateTime($currentEnd);

			$currentStart = $currentStart->format('Y-m-d');
			$currentEnd = $currentEnd->format('Y-m-d');

			if (strtotime($start) < strtotime($currentStart)) {
				$start = $currentStart;
			}

			if (strtotime($end) > strtotime($currentEnd)) {
				$end = $currentEnd;
			}
		}

		$endDate = new DateTime($end);
		$startDate = new DateTime($start);
		$interval = $endDate->diff($startDate);

		return $interval->format('%a') + 1;
	}

	/**
	 * Returns the SocialDate object of the event timezone.
	 *
	 * @since   1.4
	 * @access  public
	 * @return  SocialDate The SocialDate object of the event timezone.
	 */
	public function getEventTimezone()
	{
		return $this->meta->getTimezone();
	}

	/**
	 * Retrieves the iCal link for the event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getCalendarLink()
	{
		$link = $this->getPermalink();

		if (strpos($link, '?') !== false) {
			$link .= '&format=ical';
		} else {
			$link .= '?format=ical';
		}

		return $link;
	}

	/**
	 * Check if this event has an end date.
	 *
	 * @since  1.3
	 * @access public
	 * @return boolean   True if event has an end date.
	 */
	public function hasEventEnd()
	{
		return $this->meta->hasEnd();
	}

	/**
	 * Determines if the user has points to create the event under a particular category
	 *
	 * @since   2.0.15
	 * @access  public
	 */
	public function hasPointToCreate($userId = null)
	{
		$user = ES::user($userId);

		// check if this user has enough oints to create group in the selected category or not.
		$category = ES::table('EventCategory');
		$category->load($this->category_id);

		if (!$category->hasPointsToCreate($user->id)) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve points needed to create event in category
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPointToCreate($userId = null)
	{
		$user = ES::user($userId);

		$category = ES::table('EventCategory');
		$category->load($this->category_id);

		return $category->getPointsToCreate($user->id);
	}

	/**
	 * Determines if the event is over.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  boolean   True if the event is over.
	 */
	public function isOver()
	{
		// Get the event end date
		$end = $this->getEventEnd();

		// Get the current date
		$now = ES::date();

		// If now > end, means it is over.
		$over = $now->toUnix() > $end->toUnix();

		return $over;
	}

	/**
	 * Determines if this is past event.
	 *
	 * @since   2.0
	 * @access  public
	 * @return  boolean   True if the event is passed.
	 */
	public function isPassed()
	{
		// Get the event start date
		$start = $this->getEventStart();
		$end = $this->getEventEnd();

		// Get the current date
		// since the start and end we are going to test against are stored in db and its a local datetime
		$today = ES::date();
		$now = ES::date($today->format('Y-m-d H:i:s'), false);

		// If now > start, means it is passed.
		$passed = ($now->toUnix() > $start->toUnix() && $now->toUnix() > $end->toUnix());

		return $passed;
	}

	/**
	 * Determines if the event is an upcoming event. Optionally check by days.
	 *
	 * @since   1.3
	 * @access  public
	 * @param   integer    $days Days to check. Optional.
	 * @return  boolean          True if it is an upcoming event.
	 */
	public function isUpcoming($daysToCheck = null)
	{
		$start = $this->getEventStart();

		$now = ES::date();

		$upcoming = $now->toUnix() < $start->toUnix();

		// If not upcoming, then no point checking whether it is within the days or not.
		if (!$upcoming || is_null($daysToCheck)) {
			return $upcoming;
		}

		$daysToEvent = $this->timeToEvent() / (60*60*24);

		return $daysToEvent < $daysToCheck;
	}

	/**
	 * Determines if the event is currently ongoing.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  boolean True if the event is currently ongoing.
	 */
	public function isOngoing()
	{
		// Regardless of eventstart or eventend, as long as it is not upcoming and not over, then it is ongoing.
		return !$this->isUpcoming() && !$this->isOver();
	}

	/**
	 * Return the amount of time to event from now.
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string  $format The format of the time to return.
	 * @return  integer         The time based on the format to the event.
	 */
	public function timeToEvent($format = 'seconds')
	{
		$start = $this->getEventStart();

		$now = ES::date();

		// Get the total seconds first.
		$seconds = $start->toFormat('U') - $now->toFormat('U');

		$units = array(
			'seconds' => 1,
			'minutes' => 60,
			'hours' => 60 * 60,
			'days' => 60 * 60 * 24,
			'weeks' => 60 * 60 * 24 * 7,
			'months' => 60 * 60 * 24 * 30,
			'years' => 60 * 60 * 24 * 365
	   );

		return floor($seconds / (isset($units[$format]) ? $units[$format] : 1));
	}

	/**
	 * Returns the table object of the category of this event.
	 *
	 * @since   1.3
	 * @access  public
	 * @return  SocialTableEventCategory    The event category table object.
	 */
	public function getCategory()
	{
		static $_cache = array();

		if (!isset($_cache[$this->category_id])) {
			$table = ES::table('EventCategory');
			$table->load($this->category_id);

			$_cache[$this->category_id] = $table;
		}

		return $_cache[$this->category_id];
	}

	/**
	 * Alias method for getCreator
	 *
	 * @since  1.3
	 * @access public
	 * @return  SocialTableEventGuest    The event guest table object of the creator of this event.
	 */
	public function getOwner()
	{
		return $this->getCreator();
	}

	/**
	 * Returns an array of SocialTableEventGuest object who are the admins.
	 * @since   1.3
	 * @access  public
	 * @return  array   Array of admins in SocialTableEventGuest object.
	 */
	public function getAdmins()
	{
		$admins = array();

		foreach ($this->admins as $uid) {
			if (isset($this->guests[$uid])) {
				$admins[$uid] = $this->guests[$uid];
			}
		}

		return $admins;
	}

	/**
	 * Returns the EventGuest object with the given user id.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getGuest($uid = null)
	{
		if (empty($uid)) {
			$uid = ES::user()->id;
		}

		if (!isset($this->guests[$uid])) {
			$guest = ES::table('EventGuest');
			$guest->uid = $uid;
			$guest->type = SOCIAL_TYPE_USER;

			return $guest;
		}

		return $this->guests[$uid];
	}

	/**
	 * Approves the event.
	 * @since   1.3
	 * @access  public
	 * @return  boolean True if successfull.
	 */
	public function approve()
	{
		$previousState = $this->state;

		$this->state = SOCIAL_CLUSTER_PUBLISHED;

		$state = $this->save();

		if (!$state) {
			return false;
		}

		$dispatcher = ES::dispatcher();

		// Set the arguments
		$args = array(&$this);

		// @trigger onEventAfterApproved
		$dispatcher->trigger(SOCIAL_TYPE_EVENT, 'onAfterApproved', $args);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventAfterApproved', $args);

		// Send email.
		ES::language()->loadSite();

		$adminSef = false;
		if (JFactory::getApplication()->isAdmin()) {
			$adminSef = true;
		}

		$params = array(
			'title' => $this->getName(),
			'name' => $this->getCreator()->getName(),
			'avatar' => $this->getAvatar(),
			'url' => $this->getPermalink(false, true, 'item', true, array(), $adminSef),
			'editUrl' => $this->getPermalink(false, true, 'edit', true, array(), $adminSef),
			'discussion' => $this->getParams()->get('discussions', true)
		);

		// Get the email title.
		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_EVENT_APPROVED', $this->getName());
		$namespace = 'site/event/approved';

		if ($previousState == SOCIAL_CLUSTER_UPDATE_PENDING) {
			$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_EVENT_UPDATED_APPROVED', $this->getName());
			$namespace = 'site/event/update.approved';
		}

		$mailer = ES::mailer();

		$tpl = $mailer->getTemplate();

		$recipient = $this->getCreator();

		$tpl->setRecipient($recipient->getName(), $recipient->email);

		$tpl->setTitle($title);

		$tpl->setTemplate($namespace, $params);

		$tpl->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

		$mailer->create($tpl);

		// Create stream.
		$stream = ES::table('Stream');
		$state = $stream->load(array('context_type' => 'events', 'verb' => 'create', 'cluster_id' => $this->id));

		// If no stream found then only we create the stream item
		if (!$state || empty($stream->id)) {
			$this->createStream($this->creator_uid, 'create');
		}

		// The event is updated
		if ($previousState == SOCIAL_CLUSTER_UPDATE_PENDING) {
			$this->createStream($this->getCreator()->id, 'update');
		}

		return true;
	}

	/**
	 * Removes event stream item for event item related action.
	 * For guest response stream item, see SocialTableEventGuest::removeStream();
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string  $action     The action associated with the stream.
	 * @return  boolean             True if successful.
	 */
	public function removeStream($action)
	{
		// To prevent unexpected callees deleting stream.
		$allowed = array('feature');

		if (!in_array($action, $allowed)) {
			return false;
		}

		$stream = ES::table('Stream');
		$state = $stream->load(array(
			'cluster_id' => $this->id,
			'cluster_type' => $this->cluster_type,
			'context_type' => 'events',
			'verb' => $action
	   ));

		if (!$state) {
			return false;
		}

		return $stream->delete();
	}

	/**
	 * Allow caller to notify event admins
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function notifyAdmins($action, $data = array())
	{
		$model = ES::model('Events');
		$targets = $model->getMembers($this->id, array('admin' => true, 'users' => true));

		if ($action == 'moderate.review') {

			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->clusterName = $this->getName();
			$params->clusterLink = $this->getPermalink(false, true);
			$params->message = $data['message'];
			$params->title = $data['title'];
			$params->approve = ESR::controller('reviews', array('external' => true, 'task' => 'approve', 'id' => $data['reviewId']));
			$params->reject = ESR::controller('reviews', array('external' => true, 'task' => 'reject', 'id' => $data['reviewId']));
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_ES_EMAILS_REVIEW_PENDING_MODERATION_SUBJECT';
			$options->template = 'site/reviews/moderate.review';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'events';
			$system->type = SOCIAL_TYPE_EVENT;
			$system->url = $data['permalink'];

			ES::notify('events.moderate.review', $targets, $options, $system);
		}
	}

	/**
	 * Notify members of the event
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function notifyMembers($action, $data = array())
	{
		$model = ES::model('Events');

		$rule = false;

		// Determines if the targets has been provided
		$targets = isset($data['targets']) ? $data['targets'] : false;
		$exclude = isset($data['userId']) ? $data['userId'] : '';
		$sendAsBatch = false;

		if ($targets === false) {
			// $exclude = isset($data['userId']) ? $data['userId'] : '';
			// $options = array('exclude' => $exclude, 'state' => SOCIAL_EVENT_GUEST_GOING, 'users' => true);
			// $targets = $model->getGuests($this->id, $options);

			$sendAsBatch = true;
		}

		// If there is nothing to send, just skip this altogether
		if (!$targets && !$sendAsBatch) {
			return;
		}

		if ($action == 'polls.create') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->pollTitle = $data['title'];
			$params->pollLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_EMAILS_EVENT_POLL_CREATED_SUBJECT';
			$options->template = 'site/event/polls.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('APP_EVENT_STORY_POLLS_CREATED_IN_EVENT', $actor->getName(), $this->getName());
			$system->content = $params->pollTitle;
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'events';
			$system->type = SOCIAL_TYPE_EVENT;
			$system->url = $params->pollLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.polls.create';

		}

		if ($action == 'story.updates') {

			$actor = ES::user($data['userId']);

			// Prepare for email data
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->posterAvatar = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
			$params->posterLink = $actor->getPermalink(true,true);
			$params->message = nl2br($data['content']);
			$params->event = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(true,true);
			$params->permalink = $data['permalink'];

			$options = new stdClass();
			$options->title = $data['title'];
			$options->template = $data['template'];
			$options->params = $params;

			// Now prepare the system notification
			$system = new stdClass();
			$system->uid = $data['uid'];
			$system->context_type = $data['context_type'];
			$system->url = ESR::stream(array('id' => $data['uid'], 'layout' => 'item', 'sef' => false));
			$system->actor_id = $actor->id;
			$system->context_ids = $this->id;
			$system->content = $data['system_content'];

			$rule = 'events.updates';
		}

		if ($action == 'video.create') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->videoTitle = $data['title'];
			$params->videoDescription = $data['description'];
			$params->videoLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_VIDEO_CREATED_SUBJECT';
			$options->template = 'site/event/video.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'events';
			$system->type = SOCIAL_TYPE_EVENT;
			$system->url = $params->videoLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.video.create';
		}

		if ($action == 'audio.create') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->audioTitle = $data['title'];
			$params->audioDescription = $data['description'];
			$params->audioLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_AUDIO_EMAILS_EVENT_AUDIO_CREATED_SUBJECT';
			$options->template = 'site/event/audio.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'events';
			$system->type = SOCIAL_TYPE_EVENT;
			$system->url = $params->audioLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.audio.create';
		}

		if ($action == 'task.completed') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->milestoneName = $data['milestone'];
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_TASK_COMPLETED_SUBJECT';
			$options->template = 'site/event/task.completed';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'event';
			$system->type = 'events';
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.task.completed';
		}

		if ($action == 'task.uncompleted')
		{
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->milestoneName = $data['milestone'];
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_TASK_UNCOMPLETED_SUBJECT';
			$options->template = 'site/event/task.uncompleted';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'event';
			$system->type = 'events';
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.task.uncompleted';
		}

		if ($action == 'task.create') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->milestoneName = $data['milestone'];
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_NEW_TASK_SUBJECT';
			$options->template = 'site/event/task.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'event';
			$system->type = 'events';
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.task.created';
		}

		if ($action == 'milestone.create') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_NEW_MILESTONE_SUBJECT';
			$options->template = 'site/event/milestone.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'event';
			$system->type = 'events';
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.milestone.created';
		}

		if ($action == 'discussion.reply') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_NEW_REPLY_SUBJECT';
			$options->template = 'site/event/discussion.reply';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'event';
			$system->type = 'events';
			$system->url = $params->permalink;
			$system->context_ids = $data['discussionId'];

			$rule = 'events.discussion.reply';
		}

		if ($action == 'discussion.answered') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_DISCUSSION_ANSWERED_SUBJECT';
			$options->template = 'site/event/discussion.answered';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'event';
			$system->type = 'events';
			$system->url = $params->permalink;
			$system->context_ids = $data['discussionId'];

			$rule = 'events.discussion.answered';
		}

		if ($action == 'discussion.create') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->title = $data['discussionTitle'];
			$params->content = $data['discussionContent'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_NEW_DISCUSSION_SUBJECT';
			$options->template = 'site/event/discussion.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'event';
			$system->type = 'events';
			$system->url = $params->permalink;
			$system->context_ids = $data['discussionId'];

			$rule = 'events.discussion.create';
		}

		if ($action == 'file.uploaded') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();

			// Set the actor
			$params->actor = $actor->getName();
			$params->actorLink = $actor->getPermalink(false, true);
			$params->actorAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);

			// Set the event attributes.
			$params->event = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);

			// Set the file attributes
			$params->fileTitle = $data['fileName'];
			$params->fileSize = $data['fileSize'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_NEW_FILE_SUBJECT';
			$options->template = 'site/event/file.uploaded';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'file.event.uploaded';
			$system->context_ids = $data['fileId'];
			$system->type = 'events';
			$system->url = $params->permalink;

			$rule = 'events.updates';
		}

		if ($action == 'news.create') {

			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->event = $this->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->newsTitle = $data['newsTitle'];
			$params->newsContent = $data['newsContent'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_EVENT_NEW_ANNOUNCEMENT_SUBJECT';
			$options->template = 'site/event/news';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'events';
			$system->context_ids = $data['newsId'];
			$system->type = 'events';
			$system->url = $params->permalink;

			$rule = 'events.news';
		}

		if ($action == 'album.create') {

			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->eventName = $this->getName();
			$params->eventAvatar = $this->getAvatar();
			$params->eventLink = $this->getPermalink(false, true);
			$params->albumTitle = $data['title'];
			$params->albumDescription = $data['description'];
			$params->albumLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_EMAILS_EVENT_ALBUM_CREATED_SUBJECT';
			$options->template = 'site/event/album.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_ES_NOTIFICATION_EVENT_ALBUM_CREATED_SUBJECT', $actor->getName(), $this->getName());
			$system->content = $params->albumTitle;
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'events';
			$system->type = SOCIAL_TYPE_EVENT;
			$system->url = $params->albumLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'events.album.create';
		}

		// If no rule assigned, we skip the notification
		if (!$rule) {
			return;
		}

		if (!$targets && $sendAsBatch) {
			ES::notifyClusterMembers($rule, $this->id, $options, $system, $exclude);
		} else {
			ES::notify($rule, $targets, $options, $system);
		}

	}

	/**
	 * Gets event guest filter.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getFilters()
	{
		$model = ES::model('Clusters');
		$filters = $model->getFilters($this->id, $this->cluster_type);
		$defaultDisplay = $this->config->get('pages.item.display', 'timeline');

		// Update the permalink of the filters
		if ($filters) {
			foreach ($filters as &$filter) {
				$filterOptions = array('layout' => 'item', 'id' => $this->getAlias(), 'filterId' => $filter->getAlias());

				if ($defaultDisplay == 'info') {
					$filterOptions['type'] = 'timeline';
				}

				$filter->permalink = ESR::events($filterOptions);
			}
		}

		return $filters;
	}

	/**
	 * Override parent's behavior to determine if the current user is allowed to post discussions
	 *
	 * @since   2.0.13
	 * @access  public
	 */
	public function canCreateDiscussion($userId = null)
	{
		$user = ES::user($userId);
		$access = $this->getAccess();

		if ($access->get('discussions.access') == 'admins' && (!$this->isAdmin($user->id) && !$user->isSiteAdmin())) {
			return false;
		}

		return parent::canCreateDiscussion($userId);
	}

	/**
	 * Override parent's behavior to determine if the current user is allowed to post tasks
	 *
	 * @since   2.0.13
	 * @access  public
	 */
	public function canCreateTasks($userId = null)
	{
		$user = ES::user($userId);

		// Super admin can do anything
		if ($user->isSiteAdmin()) {
			return true;
		}

		$access = $this->getAccess();

		// If the access is only for the admins
		if ($access->get('tasks.access') == 'admins' && !$this->isAdmin($user->id)) {
			return false;
		}

		return parent::canCreateTasks($userId);
	}

	/**
	 * Override parent's behavior to determine if the current user is allowed to edit tasks
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function canEditTasks($userId = null)
	{
		$user = ES::user($userId);
		$access = $this->getAccess();

		if ($access->get('tasks.edit') == 'admins' && (!$this->isAdmin($user->id) || !$user->isSiteAdmin())) {
			return false;
		}

		return parent::canEditTasks($userId);
	}


	/**
	 * Invites a user to the event and does the appropriate follow actions.
	 *
	 * @since  1.3
	 * @access public
	 * @param  integer    $target The invited user id.
	 * @param  integer    $actor  The actor user id.
	 * @return boolean            True if successful.
	 */
	public function invite($target, $actor = null)
	{
		$guest = $this->getGuest($target);

		if ($guest->isGuest()) {
			$this->setError('Already a guest of the event');
			return false;
		}

		$actor = ES::user($actor);
		$target = ES::user($target);

		$guest = ES::table('EventGuest');

		// lets check if the records already exists or not.
		$guest->load(array('cluster_id' => $this->id, 'uid' => $target->id, 'type' => SOCIAL_TYPE_USER, 'state' => SOCIAL_EVENT_GUEST_INVITED));

		if ($guest->id) {
			$guest->invited_by = $actor->id;
			$guest->created = ES::date()->toSql();

		} else {
			$guest->cluster_id = $this->id;
			$guest->uid = $target->id;
			$guest->type = SOCIAL_TYPE_USER;
			$guest->state = SOCIAL_EVENT_GUEST_INVITED;
			$guest->invited_by = $actor->id;
		}

		$guest->store();

		ES::points()->assign('events.guest.invite', 'com_easysocial', $actor->id);

		$emailOptions = (object) array(
			'title' => 'COM_EASYSOCIAL_EMAILS_EVENT_GUEST_INVITED_SUBJECT',
			'template' => 'site/event/guest.invited',
			'event' => $this->getName(),
			'eventName' => $this->getName(),
			'eventAvatar' => $this->getAvatar(),
			'eventLink' => $this->getPermalink(false, true),
			'invitorName' => $actor->getName(),
			'invitorLink' => $actor->getPermalink(false, true),
			'invitorAvatar' => $actor->getAvatar()
	   );

		$systemOptions = (object) array(
			'uid' => $this->id,
			'actor_id' => $actor->id,
			'target_id' => $target->id,
			'context_type' => 'events',
			'type' => 'events',
			'url' => $this->getPermalink(true, false, 'item', false),
			'eventId' => $this->id
	   );

		ES::notify('events.guest.invited', array($target->id), $emailOptions, $systemOptions);

		return true;
	}

	/**
	 * Returns the available seats left based on the guestLimit param - total guest.
	 * @return integer  If guest limit is not unlimited, then returns the number of seats left. If guest limit is unlimited, then return -1;
	 */
	public function seatsLeft()
	{
		$max = $this->getParams()->get('guestlimit', 0);

		if (empty($max)) {
			return -1;
		}

		// We do not want to count 'notgoing'
		$total = $this->getTotalGuests() - $this->getTotalNotGoing();

		return $max - $total;
	}

	/**
	 * Determines if the user is an admin of the event
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function isAdmin($userid = null)
	{
		if (empty($userid)) {
			$userid = ES::user()->id;
		}

		$guest = $this->getGuest($userid);

		return $guest->isAdmin();
	}

	/**
	 * Determines if the user is an owner of the event
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function isOwner($id = null)
	{
		if (is_null($id)) {
			$id = ES::user()->id;
		}

		$guest = $this->getGuest($id);

		return $guest->isOwner();
	}

	/**
	 * Standard implementation to determine if the user is a member of the event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isMember($userid = null)
	{
		if (empty($userid)) {
			$userid = ES::user()->id;
		}

		$guest = $this->getGuest($userid);

		return $guest->isGuest();
	}

	/**
	 * Determines if the user can invite other friends to the event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canInvite($userId = null)
	{
		$user = ES::user($userId);
		$guest = $this->getGuest($user->id);

		if (!$this->config->get('friends.enabled') && !$this->config->get('events.invite.nonfriends')) {
			return false;
		}

		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($guest->isAdmin()) {
			return true;
		}

		if ($guest->isGuest() && $this->config->get('events.invite.allowmembers')) {
			return true;
		}

		return false;
	}

	/**
	 * Creates a new member for the event
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function createMember($userId, $onRegister = false, $registrationType = null)
	{
		$table = ES::table('EventGuest');

		// Try to load the user record if it exists
		$table->load(array('uid' => $userId, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $this->id));

		$table->cluster_id = $this->id;
		$table->uid = $userId;
		$table->type = SOCIAL_TYPE_USER;
		$table->owner = false;
		$table->admin = false;

		// If this is public event, just add the attendee
		if ($this->isOpen()) {
			$table->state = SOCIAL_EVENTS_MEMBER_PUBLISHED;
		}

		if ($this->isClosed()) {
			$table->state = SOCIAL_EVENTS_MEMBER_PENDING;
		}

		// if the user is invited and after registration, they can join the private event immediatly
		if ($onRegister) {
			$table->state = SOCIAL_EVENTS_MEMBER_PUBLISHED;
		}

		// if ($onRegister && ($registrationType == 'auto' || $registrationType == 'login')) {
		// 	$table->state = SOCIAL_EVENTS_MEMBER_PUBLISHED;
		// }

		// if ($onRegister && ($registrationType == 'verify' || $registrationType == 'approvals' || $registrationType == 'confirmation_approval')) {
		// 	$table->state = SOCIAL_EVENTS_MEMBER_BEING_JOINED;
		// }

		$state = $table->store();

		if ($state) {
			if ($table->state == SOCIAL_EVENTS_MEMBER_PUBLISHED) {
				// Add the user to the cache now
				$this->members[$userId] = ES::user($userId);

				// Additional triggers to be processed when the page starts.
				$table->triggerResponse('going');

				ES::points()->assign('events.going', 'com_easysocial', $userId);

				$table->removeNotification('notgoing');
				$table->removeNotification('maybe');

				$table->createNotification('going');
				$table->createStream('going');

				$this->members[$userId]->updateGoals('joincluster');
			}

			if ($table->state == SOCIAL_EVENTS_MEMBER_PENDING) {
				// Add the user to the cache now
				$this->pending[$userId] = ES::user($userId);

				//Notify event's admins
				$table->createNotification('request');
			}
		}

		return $table;
	}

	/**
	 * Determines if the user can invite non-friends to the event
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function canInviteNonFriends($userId = null)
	{
		$user = ES::user($userId);

		if (!$this->config->get('events.invite.nonfriends')) {
			return false;
		}

		if ($user->isSiteAdmin() || $this->isAdmin() || $this->isMember()) {
			return true;
		}

		return false;
	}


	/**
	 * Determines if the user is allowed to remove another user from an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canRemoveGuest($targetId, $userId = null)
	{
		$user = ES::user($userId);
		$guest = $this->getGuest($user->id);
		$targetGuest = $this->getGuest($targetId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		if (!$targetGuest->isOwner() && ($guest->isOwner() || $guest->isAdmin())) {
			return true;
		}

		return fasle;
	}

	/**
	 * Overrides the parent's canDelete behavior
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canDelete($userId = null)
	{
		$canDelete = parent::canDelete($userId);

		// If the logics from the parent already allows the user to delete, skip the rest of the checking
		if ($canDelete) {
			return $canDelete;
		}

		// If this is a group event, we should check if they are allowed to delete here
		if ($this->isGroupEvent() && $this->getGroup()->isOwner()) {
			return true;
		}

		return false;
	}

	/**
	 * This determines who can post status updates on an event
	 *
	 * @since   1.4.10
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function canPostUpdates($userId = null)
	{
		$guest = $this->getGuest($userId);
		$my = ES::user($userId);

		if ($this->isAdmin() || $guest->isGuest() || $my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can view the event altogether
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canViewEvent($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		// Get the guest object to test against
		$guest = $this->getGuest($user->id);

		if ($this->isInviteOnly() && !$guest->isParticipant()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user can view the event items
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canViewItem($id = null)
	{
		$user = ES::user($id);

		if ($this->isClusterEvent()) {
			$cluster = $this->getCluster();

			if (!$cluster->canViewEvent()) {
				return false;
			}

			return true;
		}

		if (!parent::canViewItem($user->id)) {
			return false;
		}

		return true;
	}

	public function isPendingMember($userid = null)
	{
		if (is_null($userid)) {
			$userid = ES::user()->id;
		}

		$guest = $this->getGuest($userid);

		return $guest->isPending();
	}

	/**
	 * Retrieves a list of apps for an event
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getApps()
	{
		static $apps = null;

		if (!$apps) {
			$model = ES::model('Apps');
			$data = $model->getEventApps($this->id);

			$apps = $data;
		}

		return $apps;
	}

	/**
	 * Centralized method to retrieve a person's profile link.
	 * This is where all the magic happens.
	 *
	 * @access	public
	 * @param	null
	 *
	 * @return	string	The url for the person
	 */
	public function getAppsPermalink($appId, $xhtml = true, $external = false, $layout = 'item', $sef = true)
	{
		$options = array('id' => $this->getAlias(), 'layout' => $layout, 'appId' => $appId);

		if ($external) {
			$options['external'] = true;
		}

		$options['sef'] = $sef;

		$url = ESR::events($options, $xhtml);

		return $url;
	}

	/**
	 * Creates the owner node. This is an override on the parent class createOwner method to use EventGuest table object instead, and assign it into the guest property by default.
	 *
	 * @since  1.2
	 * @access public
	 * @param  int  $userId The owner id.
	 * @return bool         True if successful.
	 */
	public function createOwner($userId = null)
	{
		if (empty($userId)) {
			$userId = ES::user()->id;
		}

		$guest = ES::table('EventGuest');

		$state = $guest->load(array('cluster_id' => $this->id, 'uid' => $userId, 'type' => SOCIAL_TYPE_USER));

		$guest->cluster_id = $this->id;
		$guest->uid = $userId;
		$guest->type = SOCIAL_TYPE_USER;
		$guest->state = SOCIAL_STATE_PUBLISHED;
		$guest->admin = true;
		$guest->owner = true;

		$guest->store();

		$this->guests[$userId] = $guest;

		$this->admins[] = $userId;

		$this->going[] = $userId;

		return $guest;
	}

	/**
	 * Checks if this event is an all day event.
	 *
	 * @since  1.3.7
	 * @access public
	 * @return boolean   True if this event is an all day event.
	 */
	public function isAllDay()
	{
		return $this->meta->isAllDay();
	}

	/**
	 * Checks if this event is an all day event.
	 *
	 * @since  1.3.7
	 * @access public
	 * @return boolean   True if this event is an all day event.
	 */
	public function getReminder()
	{
		return $this->meta->getReminder();
	}

	/**
	 * As the logic is getting more complicated, we move it here so that it does not cloud the theme files.
	 *
	 * @since  1.3.7
	 * @access public
	 * @return string    The output of the start end of the event.
	 */
	public function getStartEndDisplay($options = array())
	{
		// Get the 12h/24h settings
		$timeformat = ES::config()->get('events.timeformat', '12h');

		$start = $this->getEventStart();
		$end = $this->getEventEnd();
		$timezone = $this->getEventTimezone();

		$startString = $start->toSql(true);
		$endString = $end->toSql(true);

		list($startYMD, $startHMS) = explode(' ', $startString);
		list($endYMD, $endHMS) = explode(' ', $endString);

		// Available options
		// start = true/false (force show/hide start)
		// end = true/false (force show/hide end)
		// startdate = true/false (force show/hide startdate)
		// starttime = true/false (force show/hide starttime)
		// enddate = true/false (force show/hide enddate)
		// endtime = true/false (force show/hide endtime)

		// Each checking blocks has its own "default"

		$default = array(
			'start' => true,
			'end' => true,
			'startdate' => true,
			'starttime' => true,
			'enddate' => true,
			'endtime' => true
		);

		if (!$this->config->get('events.layout.eventtime')) {
			$default['starttime'] = false;
			$default['endtime'] = false;
		}

		// If there is a timezone set for this event, display it
		if ($timezone && $this->config->get('events.layout.timezone')) {
			$default['timezone'] = true;
		}

		// If start and end is the same, means there is no end, then we do not want to show end by default
		if ($startString == $endString) {

			$default['end'] = false;
		}

		// If start and end is on the same day, then we do not want to show the end date
		if ($startYMD == $endYMD) {
			$default['enddate'] = false;
		}

		if ($this->isAllDay()) {
			// If it is an all day event, then we do not want to show time by default
			$default['starttime'] = false;
			$default['endtime'] = false;

			// If it is all day then we only check the date part
			if ($startYMD == $endYMD) {
				// If it is on the same day then we do not want to show end by default

				$default['end'] = false;
			}
		}

		$options = array_merge($default, $options);

		// If startdate/starttime or enddate/endtime are both explicitly false, then we switch off that particular display
		if (!$options['startdate'] && !$options['starttime']) {
			$options['start'] = false;
		}
		if (!$options['enddate'] && !$options['endtime']) {
			$options['end'] = false;
		}

		// If start/end are both explicitly false, means there is nothing to display, then it is the callee's fault
		if ((!$options['start'] && !$options['end'])) {
			return;
		}

		// Determine the format
		$startFormat = JText::_('COM_EASYSOCIAL_DATE_' . ($options['startdate'] ? 'DMY' : '') . ($options['starttime'] ? ($timeformat == '12h' ? '12H' : '24H') : ''));
		$endFormat = JText::_('COM_EASYSOCIAL_DATE_' . ($options['enddate'] ? 'DMY' : '') . ($options['endtime'] ? ($timeformat == '12h' ? '12H' : '24H') : ''));

		$output = '';

		if ($options['start']) {
			$output .= $start->format($startFormat, true);
		}

		if ($options['end'] && $this->config->get('events.showenddate')) {
			if (!empty($output)) {
				$output .= ' - ';
			}

			$output .= $end->format($endFormat, true);
		}

		if (isset($options['timezone']) && $options['timezone']) {
			$output .= ' - ' . $timezone;
		}

		return $output;
	}

	/**
	 * Determines if this event belongs to a cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isClusterEvent()
	{
		if ($this->meta->isGroupEvent() || $this->meta->isPageEvent()) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if this event is a group event.
	 *
	 * @since  1.3.9
	 * @access public
	 */
	public function isGroupEvent()
	{
		return $this->meta->isGroupEvent();
	}

	/**
	 * Checks if this event is a page event.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function isPageEvent()
	{
		return $this->meta->isPageEvent();
	}

	/**
	 * Gets the cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getCluster()
	{
		if ($this->isGroupEvent()) {
			$id = $this->getMeta('group_id');
			$group = ES::group($id);

			return $group;
		}

		if ($this->isPageEvent()) {
			$id = $this->getMeta('page_id');
			$page = ES::page($id);

			return $page;
		}
	}

	/**
	 * Returns the group that this event belongs to if it is a group event.
	 *
	 * @since  1.3.9
	 * @access public
	 */
	public function getGroup()
	{
		if (!$this->isGroupEvent()) {
			return false;
		}

		return ES::group($this->getMeta('group_id'));
	}

	/**
	 * Return a page that this event belongs to
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getPage()
	{
		if (!$this->isPageEvent()) {
			return false;
		}

		return ES::page($this->getMeta('page_id'));
	}

	/**
	 * Determines if this is recurring event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isRecurringEvent()
	{
		return !empty($this->parent_id) && $this->parent_type == SOCIAL_TYPE_EVENT;
	}

	/**
	 * Determines if this event has recurring event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function hasRecurringEvents()
	{
		static $data = array();

		if (!isset($data[$this->id])) {
			$data[$this->id] = ES::model('Events')->getTotalEvents(array(
				'state' => SOCIAL_STATE_PUBLISHED,
				'parent_id' => $this->id
		   )) > 0;
		}

		return $data[$this->id];
	}

	/**
	 * Get recurring events for this particular event
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getRecurringEvents()
	{
		return ES::model('Events')->getEvents(array(
			'state' => SOCIAL_STATE_PUBLISHED,
			'parent_id' => $this->id
	   ));
	}

	/**
	 * Allows caller to rsvp to an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function rsvp($task, $userId = null)
	{
		// The event should be available
		if (!$this->isPublished()) {
			$this->setError('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE');
			return false;
		}

		if (is_null($userId)) {
			$userId = ES::user()->id;
		}

		// Determine the guest object
		$guest = $this->getGuest($userId);

		// If the event is closed, and the user is not a participant yet, ensure that they can only perform a "request" task
		if ($this->isClosed() && !$guest->isParticipant() && $task !== 'request' && ($this->isGroupEvent() && !$this->getGroup()->isMember())) {
			$this->setError('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
			return false;
		}

		// If the guest is still pending, we need to ensure that they can only withdraw their requests
		if ($guest->isPending() && $task !== 'withdraw') {
			$this->setError('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
			return false;
		}

		// User's without an invitation should not be able to respond to an invite only event
		if ($this->isInviteOnly() && !$guest->isParticipant()) {
			$this->setError('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
			return false;
		}

		// Set the guest properties
		$guest->cluster_id = $this->id;

		// Get the current user properties
		$my = ES::user($userId);
		$access = $my->getAccess();
		$total = $my->getTotalEvents();

		if (!$access->get('events.allow.join')) {
			$this->setError('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED');
			return false;
		}

		// Ensure that the user does not exceed their limit
		if (in_array($task, array('going', 'maybe', 'request')) && $access->exceeded('events.join', $total)) {
			$limit = $access->get('events.join');

			$this->setError(JText::sprintf('COM_EASYSOCIAL_EVENTS_EXCEEDED_JOIN_EVENT_LIMIT', $limit));
			return false;
		}

		// We do not want other unknown tasks to be submitted
		$allowedTasks = array('going', 'notgoing', 'maybe', 'request', 'withdraw');

		if (!in_array($task, $allowedTasks)) {
			$this->setError('COM_EASYSOCIAL_EVENTS_INVALID_GUEST_STATE');
			return false;
		}

		// Determine whether current logged in user have attend this event before
		$hasAttendBefore = $this->isAttending();

		if ($task == 'going') {
			$guest->going();

			// If the guest is joining, update the social goals
			$user = ES::user($guest->uid);
			$user->updateGoals('joincluster');
		}

		// Depending on the event settings
		// It is possible that if user is not going, then admin doesn't want the user to continue be in the group.

		// If guest is owner, admin or siteadmin, or this event allows not going guest then allow notgoing state
		// If guest is just a normal user, then we return state as 'notgoingdialog' so that the JS part can show a dialog to warn user about it.
		if ($task == 'notgoing') {
			$allowNotGoing = $this->getParams()->get('allownotgoingguest', true) || $guest->isOwner();

			if ($allowNotGoing) {
				$guest->notGoing($hasAttendBefore);
			} else {
				$guest->withdraw();

				// Reload the cache
				$this->guests[$my->id] = ES::table('EventGuest');
			}
		}

		if ($task == 'maybe') {
			$guest->maybe();
		}

		if ($task == 'request') {
			$guest->request();

			// Reload the cache
			$this->guests[$my->id] = $guest;
		}

		if ($task == 'withdraw') {
			$guest->withdraw();

			// Reload the cache
			$this->guests[$my->id] = ES::table('EventGuest');
		}

		return $guest;
	}

	/**
	 * Determines if the user is the page/group owner
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isClusterOwner()
	{
		if ($this->isGroupEvent() && !$this->getGroup()->isOwner()) {
			return false;
		}

		if ($this->isPageEvent() && !$this->getPage()->isOwner()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is the page/group member
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isClusterMember()
	{
		if ($this->isGroupEvent() && $this->getGroup()->isMember()) {
			return true;
		}

		if ($this->isPageEvent() && $this->getPage()->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve filter permalink for event listing
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getFilterPermalink($options = array())
	{
		if (isset($options['cluster']) && $options['cluster']) {
			$cluster = $options['cluster'];
			$options['type'] = $cluster->getType();
			$options['uid'] = $cluster->getAlias();
		}

		unset($options['cluster']);

		$filterLink = ESR::events($options);

		return $filterLink;
	}

	/**
	 * Render start time opengraph tag
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function renderStartTimeHeader()
	{
		// Get start and end datetime
		$start = $this->getEventStart();
		$end = $this->getEventEnd();

		$startString = $start->toSql(true);
		$endString = $end->toSql(true);

		$startString = str_replace(' ', 'T', $startString);
		$endString = str_replace(' ', 'T', $endString);

		// Assign the meta
		ES::meta()->setMeta('start_time', $startString);
		ES::meta()->setMeta('end_time', $endString);
	}

	/**
	 * Retrieves the date object
	 *
	 * @since   2.1.9
	 * @access  public
	 */
	public function getDateObject($timestamp = '')
	{
		//This gets today's date
		if (!$timestamp) {
			// Get the current date
			$date = ES::date();

			// Get the timestamp
			$timestamp = $date->toUnix();
		}

		//This puts the day, month, and year in seperate variables
		$result = new stdClass();

		$result->day = date('d', $timestamp);
		$result->month = date('m', $timestamp);
		$result->year = date('Y', $timestamp);
		$result->unix = $timestamp;

		return $result;
	}

	/**
	 * determine if the date object is a current day.
	 *
	 * @since   2.1.9
	 * @access  public
	 */
	public function isCurrentDay($dateObject)
	{
		// Get the current date
		$date = ES::date();
		$timestamp = $date->toUnix();

		$current = date('Y-m-d', $timestamp);

		$check = $dateObject->year . '-' . $dateObject->month . '-' . $dateObject->day;

		if ($current == $check) {
			return true;
		}

		return false;
	}

	/**
	 * Converts a group object into an array that can be exported
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $includeFields = false)
	{
		static $cache = array();

		$key = $this->id . $viewer->id . (int) $includeFields;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$result = parent::toExportData($viewer, $includeFields);

		$result->period = array(
			'start' => $this->getEventStart()->toSql(),
			'end' => $this->getEventEnd()->toSql()
		);

		if ($includeFields) {
			// Prepare DISPLAY custom fields
			ES::language()->loadAdmin();

			$model = ES::model('Events');
			$steps = $model->getAbout($this);

			// Get the step mapping first
			$stepTitles = array();

			foreach ($steps as $step) {
				$stepsData = new stdClass();
				$stepsData->id = $step->id;
				$stepsData->title = JText::_($step->title);
				$stepsData->fields = array();

				foreach ($step->fields as $field) {
					$value = (string) $field->value;

					$data = new stdClass();
					$data->id = $field->id;
					$data->type = $field->element;
					$data->name = JText::_($field->title);
					$data->value = (string) $field->value;
					$data->params = $field->getParams()->toObject();

					$stepsData->fields[] = $data;
				}

				$result['fields'][] = $stepsData;
			}
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}
}
