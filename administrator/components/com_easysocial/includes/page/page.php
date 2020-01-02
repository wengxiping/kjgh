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
ES::import('admin:/includes/indexer/indexer');

class SocialPage extends SocialCluster
{
	public $cluster_type = SOCIAL_TYPE_PAGE;
	public $cluster_var = SOCIAL_TYPE_PAGES;

	/**
	 * Keeps a list of pages that are already loaded so we
	 * don't have to always reload the page again.
	 * @var Array
	 */
	static $instances = array();

	public function __construct($params = array() , $debug = false)
	{
		// Create the page parameters object
		$this->_params = ES::registry();

		// Initialize page's property locally.
		$this->initParams($params);

		$this->table = ES::table('Page');
		$this->table->bind($this);

		parent::__construct();
	}

	public function initParams(&$params)
	{
		// We want to map the followers data
		$this->members = isset($params->members) ? $params->members : array();
		$this->admins = isset($params->admins) ? $params->admins : array();
		$this->pending = isset($params->pending) ? $params->pending : array();

		return parent::initParams($params);
	}

	/**
	 * Object initialisation for the class to fetch the appropriate page
	 * object.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public static function factory($ids = null , $debug = false)
	{
		$items = self::loadPages($ids, $debug);

		return $items;
	}

	/**
	 * Loads a given page id or an array of id's.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public static function loadPages($ids = null, $debug = false)
	{
		if (is_object($ids)) {
			$obj = new self;
			$obj->bind($ids);

			self::$instances[$ids->id] = $obj;

			return self::$instances[$ids->id];
		}

		// Determine if the argument is an array.
		$argumentIsArray = is_array($ids);

		// Ensure that id's are always an array
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		// Reset the index of ids so we don't load multiple times from the same page.
		$ids = array_values($ids);

		if (empty($ids)) {
			return false;
		}

		// Get the metadata of all pages
		$model = ES::model('Pages');
		$pages = $model->getMeta($ids);

		if (!$pages) {
			return false;
		}

		// Format the return data
		$result = array();

		foreach ($pages as $page) {
			if ($page === false) {
				continue;
			}

			// Set the cover for the page
			$page->cover = self::getCoverObject($page);

			// Pre-load list of followers for the page
			$page->members = array();
			$page->admins = array();
			$page->pending = array();

			// admin
			$members = $model->getMembers($page->id, array('users' => false, 'state' => SOCIAL_PAGES_MEMBER_PUBLISHED, 'admin' => SOCIAL_STATE_PUBLISHED));
			if ($members) {
				foreach ($members as $member) {
					$page->admins[$member->uid] = $member->uid;
				}
			}

			// pending
			$members = $model->getMembers($page->id , array('users' => false, 'state' => SOCIAL_PAGES_MEMBER_PENDING));
			if ($members) {
				foreach ($members as $member) {
					$page->pending[$member->uid] = $member->uid;
				}
			}

			// Create an object
			$obj = new SocialPage($page);

			self::$instances[$page->id] = $obj;

			$result[] = self::$instances[$page->id];
		}

		if (!$result) {
			return false;
		}

		if (!$argumentIsArray && count($result) == 1) {
			return $result[0];
		}

		return $result;
	}

	/**
	 * Retrieves a list of apps for a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getApps()
	{
		static $apps = null;

		if (!$apps) {
			$model = ES::model('Apps');
			$apps = $model->getPageApps($this->id);
		}

		return $apps;
	}

	/**
	 * Retrieves a list of admins for this page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAdmins($exclude = null)
	{
		$admins = $this->admins;

		// Exclude user
		if ($exclude) {
			if (is_array($exclude)) {
				foreach ($exclude as $id) {
					unset($admins[$id]);
				}
			} else {
				unset($admins[$exclude]);
			}
		}

		return $admins;
	}

	/**
	 * Centralized method to retrieve a page's link.
	 *
	 * @access	public
	 * @param	null
	 */
	public function getPermalink($xhtml = true, $external = false, $layout = 'item', $sef = true, $adminSef = false)
	{
		// if this page under draft state, the link should always points to edit page.
		if ($this->isDraft()) {
			$layout = 'edit';
		}

		$options = array('id' => $this->getAlias(), 'layout' => $layout);

		if ($external) {
			$options['external'] = true;
		}

		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$url = ESR::pages($options, $xhtml);

		return $url;
	}

	/**
	 * Centralized method to retrieve a app's profile link.
	 * This is where all the magic happens.
	 *
	 * @access	public
	 * @param	null
	 */
	public function getAppsPermalink($appId, $xhtml = true, $external = false, $layout = 'item', $sef = true)
	{
		$options = array('id' => $this->getAlias(), 'layout' => $layout, 'appId' => $appId);

		if ($external) {
			$options['external'] = true;
		}

		$options['sef'] = $sef;

		$url = ESR::pages($options, $xhtml);

		return $url;
	}

	/**
	 * Centralized method to retrieve a page's edit link.
	 *
	 * @access	public
	 * @param	null
	 *
	 * @return	string	The url for the person
	 */
	public function getEditPermalink($xhtml = true , $external = false , $layout = 'edit')
	{
		$url = $this->getPermalink($xhtml , $external , $layout);

		return $url;
	}

	/**
	 * Create bind method
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bind($data)
	{
		// Bind the table data first.
		$this->table->bind($data);

		$keyToArray = array('avatars', 'members', 'admins', 'pending');

		foreach($data as $key => $value)
		{
			if (property_exists($this, $key)) {
				if (in_array($key, $keyToArray) && is_object($value)) {
					$value = ES::makeArray($value);
				}

				$this->$key = $value;
			}
		}
	}

	/**
	 * Retrieve the invitor
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getInvitor($userId)
	{
		static $invites = array();

		if (!isset($invites[$userId])) {
			$member = ES::table('PageMember');
			$member->load(array('uid' => $userId, 'cluster_id' => $this->id));

			// Get the invitor for this user
			$invitor = ES::user($member->invited_by);

			$invites[$userId] = $invitor;
		}

		return $invites[$userId];
	}

	/**
	 * Delete a stream item of a follower
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteMemberStream($userId)
	{
		$model = ES::model('Clusters');
		$model->deleteUserStreams($this->id, $this->cluster_type, $userId);
	}

	/**
	 * Allows caller to depart the user from the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unlike($id = null)
	{
		// Delete the user from the cluster members relation
		$state = $this->deleteNode($this->my->id);

		if (!$state) {
			return $state;
		}

		// Delete stream from this user.
		$this->deleteMemberStream($this->my->id);

		// Additional triggers to be processed when the page starts.
		ES::apps()->load(SOCIAL_TYPE_PAGE);
		$dispatcher = ES::dispatcher();

		// Trigger: onUnlikePage
		$dispatcher->trigger('user', 'onUnlikePage', array($this->my->id, $this));

		// @points: page.unlike
		// Deduct points when user unlike the page
		ES::points()->assign('pages.unlike', 'com_easysocial', $this->my->id);

		return $state;
	}

	/**
	 * Creates a new follower for the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function createMember($userId, $onRegister = false, $registrationType = null)
	{
		$member = ES::table('PageMember');

		// Try to load the user record if it exists
		$member->load(array('uid' => $userId, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $this->id));

		$member->cluster_id = $this->id;
		$member->uid = $userId;
		$member->type = SOCIAL_TYPE_USER;
		$member->admin = false;
		$member->owner = false;

		// If the page type is open page, just add the follower
		if ($this->isOpen()) {
			$member->state = SOCIAL_PAGES_MEMBER_PUBLISHED;
		}

		// If the page type is closed page, we need the page admins to approve the application.
		// Unless if the user is invited, then the user can just like directly
		if ($this->isClosed() || $this->isInviteOnly()) {
			if ($member->state == SOCIAL_PAGES_MEMBER_INVITED) {
				$member->state = SOCIAL_PAGES_MEMBER_PUBLISHED;
			} else {
				$member->state = SOCIAL_PAGES_MEMBER_PENDING;
			}
		}

		// If the user is set to like the page after user registration the user state should be publish immediately.
		if ($onRegister) {
			$member->state = SOCIAL_PAGES_MEMBER_PUBLISHED;
		}

		// // If the user is set to like the page after user registration the user state should be publish immediately.
		// // Check profile type as well
		// if ($onRegister && ($registrationType == 'auto' || $registrationType == 'login')) {
		// 	$member->state = SOCIAL_PAGES_MEMBER_PUBLISHED;
		// }

		// if ($onRegister && ($registrationType == 'verify' || $registrationType == 'approvals' || $registrationType == 'confirmation_approval')) {
		// 	$member->state = SOCIAL_PAGES_MEMBER_BEING_LIKED;
		// }

		$state = $member->store();

		if ($state) {
			if ($member->state == SOCIAL_PAGES_MEMBER_PUBLISHED) {

				// Add the user to the cache now
				$this->members[$userId] = ES::user($userId);

				// Additional triggers to be processed when the page starts.
				ES::apps()->load(SOCIAL_TYPE_PAGE);
				$dispatcher = ES::dispatcher();

				// Trigger: onLikePage
				$dispatcher->trigger('user', 'onLikePage', array($userId, $this));

				// @points: pages.like
				// Add points when user likes a page
				ES::points()->assign('pages.like', 'com_easysocial', $userId);

				// If it is an open page, notify page admin only
				$this->notifyPageAdmins('like', array('userId' => $userId));

				// Create a stream for the user
				$this->createStream($userId, 'like');

				// Update goals
				$this->members[$userId]->updateGoals('joincluster');
			}

			// Send notification e-mail to the admin
			if ($member->state == SOCIAL_PAGES_MEMBER_PENDING) {
				// add the user to the cache
				$this->pending[$userId] = ES::user($userId);

				$this->notifyPageAdmins('request', array('userId' => $userId));
			}
		}

		return $member;
	}

	/**
	 * Approves the user via email/user management which is use auto like page feature
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function createMemberViaAutoLikePages($userId)
	{
		$member = ES::table('PageMember');
		$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

		$member->state = SOCIAL_PAGES_MEMBER_PUBLISHED;
		$state = $member->store();

		// Additional triggers to be processed when the page starts.
		ES::apps()->load(SOCIAL_TYPE_PAGE);
		$dispatcher = ES::dispatcher();

		// Trigger: onComponentStart
		$dispatcher->trigger('user', 'onLikePage', array($userId, $this));

		// @points: pages.like
		// Add points when user likes a page
		$points = ES::points();
		$points->assign('pages.like', 'com_easysocial', $userId);

		// Publish on the stream
		if ($state) {
			// Add stream item so the world knows that the user liked the page
			$this->createStream($userId, 'like');
		}

		return $state;
	}

	/**
	 * Invites another user to like this page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function invite($targetId, $actorId)
	{
		// Ensure that the target is not a follower or has been invited already
		if ($this->isMember($targetId) || $this->isInvited($targetId)) {
			return false;
		}

		// Get the actor's user object
		$actor = ES::user($actorId);

		// Get the target user's object
		$target = ES::user($targetId);

		$node = ES::table('ClusterNode');

		$node->cluster_id = $this->id;
		$node->uid = $targetId;
		$node->type = SOCIAL_TYPE_USER;
		$node->state = SOCIAL_PAGES_MEMBER_INVITED;
		$node->invited_by = $actorId;

		$node->store();

		$params = new stdClass();
		$params->invitorName = $actor->getName();
		$params->invitorLink = $actor->getPermalink(false, true);
		$params->pageName = $this->getName();
		$params->pageAvatar = $this->getAvatar();
		$params->pageLink = $this->getPermalink(false, true);
		$params->acceptLink = ESR::controller('pages', array('external' => true, 'task' => 'respondInvitation', 'id' => $this->id, 'email' => 1, 'action' => 'accept', 'userId' => $targetId, 'key' => $this->key));
		$params->page = $this->getName();

		// Send notification e-mail to the target
		$options = new stdClass();
		$options->title = 'COM_EASYSOCIAL_EMAILS_USER_INVITED_YOU_TO_LIKE_PAGE_SUBJECT';
		$options->template = 'site/page/invited';
		$options->params = $params;

		// Set the system alerts
		$system = new stdClass();
		$system->uid = $this->id;
		$system->actor_id = $actor->id;
		$system->target_id = $target->id;
		$system->context_type = 'pages';
		$system->type = SOCIAL_TYPE_PAGE;
		$system->url = $this->getPermalink(true, false, 'item', false);

		// @points: pages.invite
		// Assign points when user invites another user to join the page
		ES::points()->assign('pages.invite', 'com_easysocial', $actorId);

		ES::notify('pages.invited', array($target->id), $options, $system);

		return $node;
	}

	/**
	 * Determines if the provided user id is a follower of this page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isOpen()
	{
		return $this->type == SOCIAL_PAGES_PUBLIC_TYPE;
	}

	/**
	 * Determines if the provided user id is a follower of this page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isClosed()
	{
		return $this->type == SOCIAL_PAGES_PRIVATE_TYPE;
	}

	/**
	 * Determines if the page is invite only
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isInviteOnly()
	{
		return $this->type == SOCIAL_PAGES_INVITE_TYPE;
	}

	/**
	 * Determines if the user is pending invitation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isPendingInvitationApproval($uid = null)
	{
		static $pending	= array();

		if (!isset($pending[$uid])) {
			$user = ES::user($uid);

			$node = ES::table('ClusterNode');
			$node->load(array('uid' => $user->id, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $this->id));

			$pending[$uid] = false;

			if ($node->invited_by && $node->state == SOCIAL_PAGES_MEMBER_INVITED) {
				$pending[$uid] = true;
			}
		}

		return $pending[$uid];
	}

	/**
	 * Determines if the provided user id is an admin of this page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isAdmin($userId = null, $checkSiteAdmin = true)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		// it look like currently do not have any place calling this second parameter
		// This is the ticket #2324 I can found but now no longer use
		if (isset($this->admins[$userId]) || $this->isOwner($userId)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the users array has page admin
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function hasPageAdmin(&$users, $removeFromList = false)
	{
		foreach ($users as $key => $value) {
			if (isset($this->admins[$value->id]) || $this->isOwner($value->id)) {
				if ($removeFromList) {
					unset($users[$key]);
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Approves a page's creation application
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approve($email = true)
	{
		$previousState = $this->state;

		// Update the page's state first.
		$this->state = SOCIAL_CLUSTER_PUBLISHED;

		$state = $this->save();

		$dispatcher = ES::dispatcher();

		// Set the arguments
		$args = array(&$this);

		// @trigger onPageAfterApproved
		$dispatcher->trigger(SOCIAL_TYPE_PAGE, 'onAfterApproved', $args);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onPageAfterApproved', $args);

		// Activity logging.
		// Announce to the world when a new user registered on the site.
		$config = ES::config();

		// If we need to send email to the user, we need to process this here.
		if ($email) {
			ES::language()->loadSite();

			$adminSef = false;
			if (JFactory::getApplication()->isAdmin()) {
				$adminSef = true;
			}

			// Push arguments to template variables so users can use these arguments
			$params = array(
							'title' => $this->getName(),
							'name' => $this->getCreator()->getName(),
							'avatar' => $this->getAvatar(SOCIAL_AVATAR_LARGE),
							'pageUrl' => $this->getPermalink(false, true, 'item', true, $adminSef),
							'editUrl' => ESR::pages(array('external' => true, 'layout' => 'edit', 'id' => $this->getAlias()), false)
							);

			// Get the email title.
			$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_PAGE_APPLICATION_APPROVED', $this->getName());
			$namespace = 'site/page/approved';

			if ($previousState == SOCIAL_CLUSTER_UPDATE_PENDING) {
				$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_PAGE_UPDATED_APPROVED', $this->getName());
				$namespace = 'site/page/update.approved';
			}

			// Immediately send out emails
			$mailer = ES::mailer();

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($this->getCreator()->getName(), $this->getCreator()->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the contents
			$mailTemplate->setTemplate($namespace, $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email now.
			$mailer->create($mailTemplate);
		}

		// Once a page is approved, generate a stream item for it.
		$stream = ES::table('Stream');
		$state = $stream->load(array('context_type' => 'pages', 'verb' => 'create', 'cluster_id' => $this->id));

		// If no stream found then only we create the stream item
		if (!$state || empty($stream->id)) {
			$this->createStream($this->creator_uid, 'create', array('postActor' => SOCIAL_TYPE_PAGE));
		}

		// The group is updated
		if ($previousState == SOCIAL_CLUSTER_UPDATE_PENDING) {
			$points = ES::points();
			$points->assign('pages.update', 'com_easysocial', $this->getCreator()->id);

			$this->createStream($this->getCreator()->id, 'update', array('postActor' => SOCIAL_TYPE_PAGE));
		}

		return true;
	}

	/**
	 * Approves the user application
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approveUser($userId)
	{
		$member = ES::table('PageMember');
		$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

		$member->state = SOCIAL_PAGES_MEMBER_PUBLISHED;

		$state = $member->store();

		// Additional triggers to be processed when the page starts.
		ES::apps()->load(SOCIAL_TYPE_PAGE);
		$dispatcher = ES::dispatcher();

		// Trigger: onLikePage
		$dispatcher->trigger('user', 'onLikePage', array($userId, $this));

		// @points: pages.like
		// Add points when user like a page
		ES::points()->assign('pages.like', 'com_easysocial', $userId);

		// Publish on the stream
		if ($state) {
			// Add stream item so the world knows that the user liked the page
			$this->createStream($userId, 'like');
		}

		// Notify the user that his request to like the page has been approved
		$this->notifyMembers('approved', array('targets' => array($userId)));

		// Send notifications to page followers when a new follower liked the page
		//$this->notifyMembers('like', array('userId' => $userId));

		return $state;
	}

	/**
	 * Notify admins of the post moderation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function notifyAdminsModeration($data = array())
	{
		$model = ES::model('Pages');
		$targets = $model->getMembers($this->id, array('admin' => true));

		$actor = ES::user($data['userId']);

		// Prepare for email data
		$params = new stdClass();
		$params->actor = $actor->getName();
		$params->posterAvatar = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
		$params->posterLink = $actor->getPermalink(true,true);
		$params->message = nl2br($data['content']);
		$params->item = $this->getName();
		$params->pageLink = $this->getPermalink(true,true);
		$params->permalink = $params->pageLink;

		$options = new stdClass();
		$options->title = $data['title'];
		$options->template = $data['template'];
		$options->params = $params;

		$rule = 'pages.updates';

		ES::notify($rule, $targets, $options);
	}

	/**
	 * Mirror function for notifyPageAdmins
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function notifyAdmins($action, $data = array())
	{
		$this->notifyPageAdmins($action, $data);
	}

	/**
	 * Notify admins of the page
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function notifyPageAdmins($action, $data = array())
	{
		$model = ES::model('Pages');
		$targets = $model->getMembers($this->id, array('admin' => true));

		// if actor is page owner, we dont have to notify page owner.
		if ($targets) {
			for ($i = 0; $i < count($targets); $i++ ) {
				$admin = $targets[$i];

				if ($admin->id == $data['userId']) {
					unset($targets[$i]);
				}
			}
		}

		// no more admins, do nothing.
		if (! $targets) {
			return;
		}

		$rule = false;

		if ($action == 'request') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->approve = ESR::controller('pages', array('external' => true, 'task' => 'approve', 'userId' => $actor->id, 'id' => $this->id, 'key' => $this->key));
			$params->reject = ESR::controller('pages', array('external' => true, 'task' => 'reject', 'userId' => $actor->id, 'id' => $this->id, 'key' => $this->key));
			$params->page = $this->getName();

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_USER_REQUESTED_TO_LIKE_PAGE_SUBJECT';
			$options->template = 'site/page/moderate.follower';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $this->getPermalink(false, true, 'item', false);

			$rule = 'pages.requested';
		}

		if ($action == 'like') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->page = $this->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_PAGE_LIKED_PAGE_SUBJECT';
			$options->template = 'site/page/liked';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_EASYSOCIAL_PAGES_NOTIFICATION_LIKE_PAGE', $actor->getName(), $this->getName());
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $this->getPermalink();

			$rule = 'pages.liked';
		}

		if ($action == 'story.updates') {

			$actor = ES::user($data['userId']);

			// Prepare for email data
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->posterAvatar = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
			$params->posterLink = $actor->getPermalink(true,true);
			$params->message = nl2br($data['content']);
			$params->page = $this->getName();
			$params->pageLink = $this->getPermalink(true,true);
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

			$rule = 'pages.updates';
		}

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
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $data['permalink'];

			$rule = 'pages.moderate.review';
		}

		if (!$rule) {
			return;
		}

		ES::notify($rule, $targets, $options, $system);
	}

	/**
	 * Notify followers of the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function notifyMembers($action, $data = array())
	{
		// Default rule
		$rule = false;

		$sendAsBatch = false;
		$model = ES::model('Pages');
		$targets = isset($data['targets']) ? $data['targets'] : false;
		$exclude = '';

		if ($targets === false) {
			$exclude = isset($data['userId']) ? $data['userId'] : '';
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
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->pollTitle = $data['title'];
			$params->pollLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_EMAILS_PAGE_POLL_CREATED_SUBJECT';
			$options->template = 'site/page/polls.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('APP_PAGE_STORY_POLLS_CREATED_IN_PAGE', $this->getName());
			$system->content = $params->pollTitle;
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $params->pollLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'pages.polls.create';

		}

		if ($action == 'story.updates') {

			$actor = ES::user($data['userId']);

			// Only admin post will have notification
			if (!$this->isAdmin($actor->id)) {
				return;
			}

			// Prepare for email data
			$params = new stdClass();
			$params->message = nl2br($data['content']);
			$params->page = $this->getName();
			$params->pageLink = $this->getPermalink(true,true);
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

			$rule = 'pages.updates';
		}

		if ($action == 'video.create') {
			$actor = ES::user($data['userId']);

			// Only admin post will have notification
			if (!$this->isAdmin($actor->id)) {
				return;
			}

			$params = new stdClass();
			$params->page = $this->getName();
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->videoTitle = $data['title'];
			$params->videoDescription = $data['description'];
			$params->videoLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_PAGE_VIDEO_CREATED_SUBJECT';
			$options->template = 'site/page/video.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $params->videoLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'pages.video.create';
		}

		if ($action == 'audio.create') {
			$actor = ES::user($data['userId']);

			// Only admin post will have notification
			if (!$this->isAdmin($actor->id)) {
				return;
			}

			$params = new stdClass();
			$params->page = $this->getName();
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->audioTitle = $data['title'];
			$params->audioDescription = $data['description'];
			$params->audioLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_AUDIO_EMAILS_PAGE_AUDIO_CREATED_SUBJECT';
			$options->template = 'site/page/audio.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $params->audioLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'pages.audio.create';
		}

		if ($action == 'discussion.reply') {

			$actor = ES::user($data['userId']);

			// If the replier is the Page itself, we need to change the actor
			if ($this->isAdmin($actor->id)) {
				$actor = $this;
			}

			$params = new stdClass();

			$params->actor = $actor->getName();

			// We need to add page admins to the target
			$targets = array_merge($targets, $this->admins);

			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_PAGE_REPLIED_TO_DISCUSSION_SUBJECT';
			$options->template = 'site/page/discussion.reply';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_EASYSOCIAL_PAGES_NOTIFICATION_REPLY_DISCUSSION', $actor->getName());
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $params->permalink;
			$system->context_ids = $data['discussionId'];

			$rule = 'pages.discussion.reply';
		}

		if ($action == 'discussion.create') {
			$actor = ES::user($data['userId']);

			$emailSubject = 'COM_EASYSOCIAL_EMAILS_PAGE_NEW_DISCUSSION_SUBJECT';

			if ($this->isAdmin($actor->id)) {
				$actor = $this;
				$emailSubject = 'COM_EASYSOCIAL_EMAILS_PAGE_NEW_DISCUSSION_SUBJECT_ADMIN';
			}

			$params = new stdClass();

			$params->actor = $actor->getName();
			$params->page = $this->getName();
			$params->userName = $actor->getName();
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->userLink = $actor->getPermalink(false, true);
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->title = $data['discussionTitle'];
			$params->content = $data['discussionContent'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = $emailSubject;
			$options->template = 'site/page/discussion.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_EASYSOCIAL_PAGES_NOTIFICATION_NEW_DISCUSSION', $actor->getName());
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $params->permalink;
			$system->context_ids = $data['discussionId'];

			$rule = 'pages.discussion.create';
		}

		if ($action == 'file.uploaded') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->page = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->fileTitle = $data['fileName'];
			$params->fileSize = $data['fileSize'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_PAGE_NEW_FILE_SUBJECT';
			$options->template = 'site/page/file.uploaded';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'file.page.uploaded';
			$system->context_ids = $data['fileId'];
			$system->type = 'pages';
			$system->url = $params->permalink;

			$rule = 'pages.updates';
		}

		if ($action == 'news.create') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->page = $this->getName();
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->newsTitle = $data['newsTitle'];
			$params->newsContent = $data['newsContent'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_PAGE_NEW_ANNOUNCEMENT_SUBJECT';
			$options->template = 'site/page/news';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'pages';
			$system->context_ids = $data['newsId'];
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $params->permalink;

			$rule = 'pages.news';
		}

		if ($action == 'user.remove') {
			$actor = ES::user($data['userId']);

			// targets should be the user being removed.
			$targets = array($actor->id);

			$params = new stdClass();
			$params->page = $this->getName();
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_SUBJECT_PAGES_YOU_REMOVED_FROM_PAGE';
			$options->template = 'site/page/user.removed';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->cmd = 'pages.user.removed';
			$system->url = $this->getPermalink();

			$rule = 'pages.user.removed';
		}


		// Admin approves the user
		if ($action == 'approved') {

			// The actor is always the current user.
			$actor = ES::user();

			// There is a situation where action approved been made via email,
			// and the admin did not logged in to the site (frontend).
			// So, if actor for this action is a Guest,
			// we get the page creator to be the actor.
			if (!$actor->id) {
				$actor = ES::user($this->creator_uid);
			}

			$params = new stdClass();
			$params->page = $this->getName();
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_SUBJECT_PAGES_APPROVED_LIKE_PAGE';
			$options->template = 'site/page/user.approved';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $this->getPermalink();

			$rule = 'pages.approved';
		}

		if ($action == 'album.create') {

			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->pageName = $this->getName();
			$params->pageAvatar = $this->getAvatar();
			$params->pageLink = $this->getPermalink(false, true);
			$params->albumTitle = $data['title'];
			$params->albumDescription = $data['description'];
			$params->albumLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_EMAILS_PAGE_ALBUM_CREATED_SUBJECT';
			$options->template = 'site/page/album.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_ES_NOTIFICATION_PAGE_ALBUM_CREATED_SUBJECT', $this->getName());
			$system->content = $params->albumTitle;
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'pages';
			$system->type = SOCIAL_TYPE_PAGE;
			$system->url = $params->albumLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'pages.album.create';
		}

		// If no rule assigned, we skip the notification
		if (!$rule) {
			return;
		}

		if (!$targets && $sendAsBatch) {
			ES::notifyClusterMembers($rule, $this->id, $options, $system, $exclude, $this->notification);
		} else {
			ES::notify($rule, $targets, $options, $system, $this->notification);
		}
	}

	/**
	 * Rejects the user application
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function rejectUser($userId)
	{
		$member = ES::table('PageMember');
		$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

		$state = $member->delete();

		// Notify the user that they have been rejected :(
		$emailOptions = array();
		$emailOptions['title'] = 'COM_EASYSOCIAL_PAGES_APPLICATION_REJECTED';
		$emailOptions['template'] = 'site/page/user.rejected';
		$emailOptions['pageName'] = $this->getName();
		$emailOptions['pageLink'] = $this->getPermalink(false, true);

		$systemOptions 	= array();
		$systemOptions['context_type'] = 'pages';
		$systemOptions['cmd'] = 'pages.user.rejected';
		$systemOptions['url'] = $this->getPermalink(true, false, 'item', false);
		$systemOptions['actor_id'] = ES::user()->id;
		$systemOptions['uid'] = $this->id;

		ES::notify('pages.user.rejected', array($userId), $emailOptions, $systemOptions);

		return $state;
	}

	/**
	 * Cancel user invitation from the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cancelInvitation($userId)
	{
		$member = ES::table('PageMember');
		$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

		$state = $member->delete();

		return $state;
	}

	/**
	 * Gets page followers's filter.
	 *
	 * @since	2.0
	 * @access	public
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

				$filter->permalink = ESR::pages($filterOptions);
			}
		}

		return $filters;
	}

	public function hasPointToCreate($userId = null)
	{
		$user = ES::user($userId);

		// check if this user has enough oints to create group in the selected category or not.
		$category = ES::table('PageCategory');
		$category->load($this->category_id);

		if (! $category->hasPointsToCreate($user->id)) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve points needed to create page in category
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPointToCreate($userId = null)
	{
		$user = ES::user($userId);

		$category = ES::table('PageCategory');
		$category->load($this->category_id);

		return $category->getPointsToCreate($user->id);
	}

	/**
	 * Preprocess before storing data into the table object.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function save()
	{
		$isNew = $this->isNew();

		if ($isNew && !$this->hasPointToCreate()) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_PAGES_INSUFFICIENT_POINTS', $this->getPointToCreate()));
			return false;
		}

		$state = parent::save();
		return $state;
	}

	/**
	 * Determines if the viewer can access the page
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccess()
	{
		// We only need to check if this is an invite only page
		if (!$this->isInviteOnly()) {
			return true;
		}

		if (!$this->isMember() && !$this->isInvited() && !$this->my->isSiteAdmin()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user can invite other friends to the group
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function canInvite($userId = null)
	{
		$user = ES::user($userId);

		if (!$this->config->get('friends.enabled') && !$this->config->get('pages.invite.nonfriends')) {
			return false;
		}

		if ($user->isSiteAdmin() || $this->isAdmin() || $this->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can invite non-friends to the page
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function canInviteNonFriends($userId = null)
	{
		$user = ES::user($userId);

		if (!$this->config->get('pages.invite.nonfriends')) {
			return false;
		}

		if ($user->isSiteAdmin() || $this->isAdmin() || $this->isMember()) {
			return true;
		}

		return false;
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

		if ($access->get('discussions.access') == 'admins' && (!$this->isAdmin($user->id) || !$user->isSiteAdmin())) {
			return false;
		}

		return parent::canCreateDiscussion($userId);
	}

	/**
	 * Determine whether the user can create event in this page or not
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateEvent($userId = null)
	{
		if (!$this->allowEvents()) {
			return false;
		}

		if (is_null($userId)) {
			$user = ES::user();
		}

		if ($user->guest) {
			return false;
		}

		// Check access
		if (!$this->getAccess()->allowed('events.pageevent', true)) {
			return false;
		}

		// Check for profile type access
		if (!ES::user()->getAccess()->get('events.create')) {
			return false;
		}

		$model = ES::model('EventCategories');
		$categories = $model->getCreatableCategories($user->getProfile()->id);

		if (empty($categories)) {
			return false;
		}

		if ($this->isOwner($user->id) || $user->isSiteAdmin()) {
			return true;
		}

		$allowed = ES::makeArray($this->getParams()->get('eventcreate', '[]'));

		if (in_array('admin', $allowed) && $this->isAdmin($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Return the number of total pending followers in this page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalPendingFollowers()
	{
		$model = ES::model('Pages');
		$total = $model->getTotalPendingFollowers($this->id);

		return $total;
	}

	/**
	 * Demotes a page follower back to normal user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function demoteUser($userId)
	{
		$member = ES::table('PageMember');
		$member->load(array('uid' => $userId, 'cluster_id' => $this->id));

		// Revoke admin access
		$state = $member->revokeAdmin();

		return $state;
	}

	/**
	 * Promote a user to be a page admin
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function promoteUser($userId)
	{
		$member = ES::table('PageMember');
		$member->load(array('uid' => $userId, 'cluster_id' => $this->id));

		// Make the user as admin
		$member->makeAdmin();

		// Create a stream for this
		$this->createStream($userId, 'makeAdmin');

		// Notify the person that are promoted
		$emailOptions   = array(
			'title' => 'COM_EASYSOCIAL_PAGES_EMAILS_PROMOTED_AS_PAGE_ADMIN_SUBJECT',
			'template' => 'site/page/promoted',
			'permalink' => $this->getPermalink(true, true),
			'actor' => $this->my->getName(),
			'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $this->my->getPermalink(true, true),
			'page' => $this->getName(),
			'pageLink' => $this->getPermalink(true, true)
		);

		$systemOptions  = array(
			'context_type' => 'pages.page.promoted',
			'url' => $this->getPermalink(false, false),
			'actor_id' => $this->my->id,
			'uid' => $this->id
		);

		$state = ES::notify('pages.promoted', array($userId), $emailOptions, $systemOptions);

		return $state;
	}

	/**
	 * Determines if the user is allowed to moderate like request
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canModerateLikeRequests($userId = null)
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->isAdmin($userId)) {
			return true;
		}

		return false;
	}


	/**
	 * Determines if the user can view this group event or not
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canViewEvent()
	{
		// We need to check if the page's event app is published
		if (!$this->isAppPublished('events')) {
			return false;
		}

		if (!$this->canAccessEvents() || !$this->allowEvents()) {
			return false;
		}

		if (!ES::user()->isSiteAdmin() && !$this->isOpen() && !$this->isMember()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines whether use able to see the like button
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function showLikeButton()
	{
		if (!$this->my->getAccess()->get('pages.allow.like')) {
			return false;
		}

		if ($this->isInviteOnly() || $this->isMember() || $this->isPendingMember()) {
			return false;
		}

		return true;
	}

	/**
	 * To cater for $user->isVerified
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function isVerified()
	{
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

		if ($includeFields) {
			// Prepare DISPLAY custom fields
			ES::language()->loadAdmin();

			$model = ES::model('Pages');
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
