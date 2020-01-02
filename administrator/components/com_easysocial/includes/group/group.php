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

class SocialGroup extends SocialCluster
{
	public $cluster_type = SOCIAL_TYPE_GROUP;
	public $cluster_var = SOCIAL_TYPE_GROUPS;
	/**
	 * Keeps a list of groups that are already loaded so we
	 * don't have to always reload the user again.
	 * @var Array
	 */
	static $instances = array();

	public function __construct($params = array(), $debug = false)
	{
		// Create the user parameters object
		$this->_params = ES::registry();

		// Initialize user's property locally.
		$this->initParams($params);

		$this->table = ES::table('Group');
		$this->table->bind($this);

		parent::__construct();
	}

	public function initParams(&$params)
	{
		// We want to map the members data
		$this->members = isset($params->members) ? $params->members : array();
		$this->admins = isset($params->admins) ? $params->admins : array();
		$this->pending = isset($params->pending) ? $params->pending : array();

		return parent::initParams($params);
	}

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * @since	1.0
	 * @access	public
	 * @param   $id     int/Array     Optional parameter
	 * @return  SocialUser   The person object.
	 */
	public static function factory($ids = null, $debug = false)
	{
		$items	= self::loadGroups($ids, $debug);

		return $items;
	}

	/**
	 * Loads a given group id or an array of id's.
	 *
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int|Array	Either an int or an array of id's in integer.
	 * @return	SocialUser	The user object.
	 *
	 */
	public static function loadGroups($ids = null, $debug = false)
	{
		if (is_object($ids)) {
			$obj = new self;
			$obj->bind($ids);

			self::$instances[$ids->id]	= $obj;

			return self::$instances[$ids->id];
		}

		// Determine if the argument is an array.
		$argumentIsArray = is_array($ids);

		// Ensure that id's are always an array
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		// Reset the index of ids so we don't load multiple times from the same user.
		$ids = array_values($ids);

		if (empty($ids)) {
			return false;
		}

		// Get the metadata of all groups
		$model = ES::model('Groups');
		$groups	= $model->getMeta($ids);

		if (!$groups) {
			return false;
		}

		// preload members
		// $model->getMembers($ids, array('users' => false));

		// Format the return data
		$result = array();

		foreach ($groups as $group) {
			if ($group === false) {
				continue;
			}

			// Set the cover for the group
			$group->cover = self::getCoverObject($group);

			// Pre-load list of members for the group
			// $members = $model->getMembers($group->id, array('users' => false));
			// $members = array();
			$group->members	= array();
			$group->admins = array();
			$group->pending = array();

			$members = $model->getMembers( $group->id , array( 'users' => false, 'state' => SOCIAL_GROUPS_MEMBER_PUBLISHED, 'admin' => SOCIAL_STATE_PUBLISHED));
			if ($members) {
				foreach ($members as $member) {
					$group->admins[$member->uid] = $member->uid;
				}
			}

			$members = $model->getMembers( $group->id , array( 'users' => false, 'state' => SOCIAL_GROUPS_MEMBER_PENDING));
			if ($members) {
				foreach ($members as $member) {
					$group->pending[$member->uid] = $member->uid;
				}
			}

			// Create an object
			$obj = new SocialGroup($group);

			self::$instances[$group->id] = $obj;

			$result[] = self::$instances[$group->id];
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
	 * Return the total number of members in this group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPendingMembers()
	{
		$model = ES::model('Groups');
		$total = $model->getTotalPendingMembers($this->id);

		return $total;
	}

	/**
	 * Retrieve total invited members
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTotalInvitedMembers()
	{
		$model = ES::model('Groups');
		$total = $model->getTotalInvitedMembers($this->id);

		return $total;
	}

	/**
	 * Retrieves a list of apps for a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getApps()
	{
		static $apps = null;

		if (!$apps) {
			$model = ES::model('Apps');
			$apps = $model->getGroupApps($this->id);
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
	public function getPermalink($xhtml = true, $external = false, $layout = 'item', $sef = true, $adminSef = false)
	{
		// if this group under draft state, the link should always points to edit page.
		if ($this->isDraft()) {
			$layout = 'edit';
		}

		$options = array('id' => $this->getAlias(), 'layout' => $layout);

		if ($external) {
			$options['external'] = true;
		}

		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$url = ESR::groups($options, $xhtml);

		// Ensure URL do not have include /administrator/
		$url = str_replace('/administrator/', '/', $url);

		return $url;
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

		$url = ESR::groups($options, $xhtml);

		return $url;
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
	public function getEditPermalink($xhtml = true, $external = false, $layout = 'edit')
	{
		$url = $this->getPermalink($xhtml, $external, $layout);

		return $url;
	}

	/**
	 * Create bind method
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function bind($data)
	{
		// Bind the table data first.
		$this->table->bind($data);
		$keyToArray = array('avatars', 'members', 'admins', 'pending');

		foreach ($data as $key => $value) {
			if (property_exists($this, $key)) {
				if (in_array($key, $keyToArray) && is_object($value)) {
					$value = ES:: makeArray($value);
				}

				$this->$key = $value;
			}
		}
	}

	/**
	 * Retrieve the creator of this group
	 *
	 * @since	1.2
	 * @access	public
	 * @return	SocialUser
	 */
	public function getInvitor($userId)
	{
		static $invites = array();

		if (!isset($invites[$userId])) {
			$member = ES::table('GroupMember');
			$member->load(array('uid' => $userId, 'cluster_id' => $this->id));

			// Get the invitor
			$invitor = ES::user($member->invited_by);
			$invites[$userId] = $invitor;
		}

		return $invites[$userId];
	}

	/**
	 * Remove member stream
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
	 * Allows caller to depart the user from the group
	 *
	 * @since	1.2
	 * @access	public
	 * @param	int		The user's id.
	 * @return
	 */
	public function leave($id = null)
	{
		// Delete the user from the cluster members relation
		$state = $this->deleteNode($this->my->id);

		if (!$state) {
			return $state;
		}

		// Delete the stream for this user.
		$this->deleteMemberStream($this->my->id);

		// Additional triggers to be processed when the page starts.
		ES::apps()->load(SOCIAL_TYPE_GROUP);
		$dispatcher = ES::dispatcher();

		// Trigger: onComponentStart
		$dispatcher->trigger('user', 'onLeaveGroup', array($this->my->id, $this));

		// @points: groups.leave
		// Deduct points when user leaves the group
		ES::points()->assign('groups.leave', 'com_easysocial', $this->my->id);

		// Add activity stream
		$this->createStream($this->my->id, 'leave');

		return $state;
	}

	/**
	 * Demotes a group member back to a normal user
	 *
	 * @since	2.0
	 * @access	public
	 * @param	int
	 * @return
	 */
	public function demoteUser($userId)
	{
		$member	= ES::table('GroupMember');
		$member->load(array('uid' => $userId, 'cluster_id' => $this->id));

		// Revoke admin access
		$state = $member->revokeAdmin();

		return $state;
	}

	/**
	 * Creates a new member for the group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createMember($userId, $onRegister = false, $registrationType = null)
	{
		$member = ES::table('GroupMember');

		// Try to load the user record if it exists
		$member->load(array('uid' => $userId, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $this->id));

		$member->cluster_id = $this->id;
		$member->uid = $userId;
		$member->type = SOCIAL_TYPE_USER;
		$member->admin = false;
		$member->owner = false;

		// If the group type is open group, just add the member
		if ($this->isOpen()) {
			$member->state = SOCIAL_GROUPS_MEMBER_PUBLISHED;
		}

		// If the group type is closed or semi open group, we need the group admins to approve the application.
		// Unless if the user is invited, then the user can just join directly
		if ($this->isClosed() || $this->isSemiOpen()) {
			if ($member->state == SOCIAL_GROUPS_MEMBER_INVITED) {
				$member->state = SOCIAL_GROUPS_MEMBER_PUBLISHED;
			} else {
				$member->state = SOCIAL_GROUPS_MEMBER_PENDING;
			}
		}

		// If the user is set to join the group after user registration the user state should be publish immediately.
		if ($onRegister) {
			$member->state = SOCIAL_GROUPS_MEMBER_PUBLISHED;
		}

		// // If the user is set to join the group after user registration the user state should be publish immediately.
		// // Check profile type as well
		// if ($onRegister && ($registrationType == 'auto' || $registrationType == 'login')) {
		// 	$member->state = SOCIAL_GROUPS_MEMBER_PUBLISHED;
		// }

		// if ($onRegister && ($registrationType == 'verify' || $registrationType == 'approvals' || $registrationType == 'confirmation_approval')) {
		// 	$member->state = SOCIAL_GROUPS_MEMBER_BEING_JOINED;
		// }

		$state = $member->store();

		if ($state) {
			if ($member->state == SOCIAL_GROUPS_MEMBER_PUBLISHED) {
				// Add the user to the cache now
				$this->members[$userId] = ES::user($userId);

				// Additional triggers to be processed when the page starts.
				ES::apps()->load(SOCIAL_TYPE_GROUP);
				$dispatcher = ES::dispatcher();

				// Trigger: onComponentStart
				$dispatcher->trigger('user', 'onJoinGroup', array($userId, $this));

				// @points: groups.join
				// Add points when user joins a group
				ES::points()->assign('groups.join', 'com_easysocial', $userId);

				// If it is an open group, notify members
				$this->notifyMembers('join', array('userId' => $userId));

				// Create a stream for the user
				$this->createStream($userId, 'join');

				// Update goals
				$this->members[$userId]->updateGoals('joincluster');
			}

			// Send notification e-mail to the admin
			if ($member->state == SOCIAL_GROUPS_MEMBER_PENDING) {
				// Add the user to the cache now
				$this->pending[$userId] = ES::user($userId);

				$this->notifyGroupAdmins('request', array('userId' => $userId));
			}
		}

		return $member;
	}

	/**
	 * Invites another user to join this group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function invite($targetId, $actorId)
	{
		// Ensure that the target is not a member or has been invited already
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
		$node->state = SOCIAL_GROUPS_MEMBER_INVITED;
		$node->invited_by = $actorId;
		$node->store();

		$params = new stdClass();
		$params->invitorName = $actor->getName();
		$params->invitorLink = $actor->getPermalink(false, true);
		$params->groupName = $this->getName();
		$params->groupAvatar = $this->getAvatar();
		$params->groupLink  = $this->getPermalink(false, true);
		$params->acceptLink = ESR::controller('groups', array('external' => true, 'task' => 'respondInvitation', 'id' => $this->id, 'email' => 1, 'action' => 'accept', 'userId' => $targetId, 'key' => $this->key));
		$params->group = $this->getName();

		// Send notification e-mail to the target
		$options = new stdClass();
		$options->title = 'COM_EASYSOCIAL_EMAILS_USER_INVITED_YOU_TO_JOIN_GROUP_SUBJECT';
		$options->template = 'site/group/invited';
		$options->params = $params;

		// Set the system alerts
		$system = new stdClass();
		$system->uid = $this->id;
		$system->actor_id = $actor->id;
		$system->target_id = $target->id;
		$system->context_type = 'groups';
		$system->type = SOCIAL_TYPE_GROUP;
		$system->url = $this->getPermalink(true, false, 'item', false);

		// @points: groups.invite
		// Assign points when user invites another user to join the group
		ES::points()->assign('groups.invite', 'com_easysocial', $actorId);

		// Send notifications
		ES::notify('groups.invited', array($target->id), $options, $system);

		return $node;
	}

	/**
	 * Determines if the user is allowed to moderate join requests
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canModerateJoinRequests($userId = null)
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
	 * Determines if this is a semi open group
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function isSemiOpen()
	{
		return $this->type == SOCIAL_GROUPS_SEMI_PUBLIC_TYPE;
	}

	/**
	 * Determines if this is an open group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isOpen()
	{
		// Semi open is consider open group as well
		if ($this->type == SOCIAL_GROUPS_PUBLIC_TYPE || $this->isSemiOpen()) {
			return true;
		}
	}

	/**
	 * Determines if this is a closed group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isClosed()
	{
		return $this->type == SOCIAL_GROUPS_PRIVATE_TYPE;
	}

	/**
	 * Determines if the group is invite only
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isInviteOnly()
	{
		return $this->type == SOCIAL_GROUPS_INVITE_TYPE;
	}

	/**
	 * Determines if the user is pending invitation
	 *
	 * @access	private
	 * @param	null
	 * @return	boolean	True on success false otherwise.
	 */
	public function isPendingInvitationApproval($uid = null)
	{
		static $pending = array();

		if (!isset($pending[$uid])) {
			$user = ES::user($uid);

			$node = ES::table('ClusterNode');
			$node->load(array('uid' => $user->id, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $this->id));

			$pending[$uid] = false;

			if ($node->invited_by && $node->state == SOCIAL_GROUPS_MEMBER_INVITED) {
				$pending[$uid] = true;
			}
		}

		return $pending[$uid];
	}

	/**
	 * Approves a user's registration application
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function approve($email = true)
	{
		$previousState = $this->state;

		// Update the group's state first.
		$this->state = SOCIAL_CLUSTER_PUBLISHED;

		$state = $this->save();

		$dispatcher = ES::dispatcher();

		// Set the arguments
		$args = array(&$this);

		// @trigger onGroupAfterApproved
		$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onAfterApproved', $args);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onGroupAfterApproved', $args);

		// Activity logging.s
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
							'groupUrl' => $this->getPermalink(false, true, 'item', true, $adminSef),
							'editUrl' => ESR::groups(array('external' => true, 'layout' => 'edit', 'id' => $this->getAlias()), false)
							);

			// Get the email title.
			$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_GROUP_APPLICATION_APPROVED', $this->getName());
			$namespace = 'site/group/approved';

			if ($previousState == SOCIAL_CLUSTER_UPDATE_PENDING) {
				$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_GROUP_UPDATED_APPROVED', $this->getName());
				$namespace = 'site/group/update.approved';
			}

			// Send out email immediately
			$mailer = ES::mailer();

			// Get the email template
			$mailTemplate = $mailer->getTemplate();

			// Set the recipient
			$mailTemplate->setRecipient($this->getCreator()->getName(), $this->getCreator()->email);

			// Set the email title.
			$mailTemplate->setTitle($title);

			// Set the email content
			$mailTemplate->setTemplate($namespace, $params);

			// Set the priority.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			$mailer->create($mailTemplate);
		}

		// Once a group is approved, we need to generate stream item
		$stream = ES::table('Stream');
		$state = $stream->load(array('context_type' => 'groups', 'verb' => 'create', 'cluster_id' => $this->id));

		// If no stream found then only we create the stream item
		if (!$state || empty($stream->id)) {
			$this->createStream($this->creator_uid, 'create');
		}

		// The group is updated
		if ($previousState == SOCIAL_CLUSTER_UPDATE_PENDING) {

			// @points: groups.update
			// Add points to the user that updated the group
			$points = ES::points();
			$points->assign('groups.update', 'com_easysocial', $this->getCreator()->id);

			$this->createStream($this->getCreator()->id, 'update');
		}

		return true;
	}

	/**
	 * Approves the user application
	 *
	 * @since	1.2
	 * @access	public
	 * @param	int		The user id
	 * @return
	 */
	public function approveUser($userId)
	{
		$member = ES::table('GroupMember');
		$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

		$member->state = SOCIAL_GROUPS_MEMBER_PUBLISHED;

		// Store the member data
		$state = $member->store();

		// Additional triggers to be processed once the member is saved
		ES::apps()->load(SOCIAL_TYPE_GROUP);
		$dispatcher = ES::dispatcher();

		$dispatcher->trigger('user', 'onJoinGroup', array($userId, $this));

		if ($state) {

			// @points: groups.join
			// Add points when the user is approved
			ES::points()->assign('groups.join', 'com_easysocial', $userId);

			// Create a stream item
			$this->createStream($userId, 'join');

			// Notify the user that his request has been approved
			$this->notifyMembers('approved', array('targets' => array($userId)));

			// Send notification to all the group members that there is a new member
			$this->notifyMembers('join', array('userId' => $userId));
		}

		return $state;
	}

	/**
	 * Mirror function for notifyGroupAdmins
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function notifyAdmins($action, $data = array())
	{
		$this->notifyGroupAdmins($action, $data);
	}

	/**
	 * Notify admins of the group
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function notifyGroupAdmins($action, $data = array())
	{
		$model = ES::model('Groups');
		$targets = $model->getMembers($this->id, array('admin' => true));

		if ($action == 'request') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->approve = ESR::controller('groups', array('external' => true, 'task' => 'approve', 'userId' => $actor->id, 'id' => $this->id, 'key' => $this->key));
			$params->reject = ESR::controller('groups', array('external' => true, 'task' => 'reject', 'userId' => $actor->id, 'id' => $this->id, 'key' => $this->key));
			$params->group = $this->getName();

			// For email notification
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_USER_REQUESTED_TO_JOIN_GROUP_SUBJECT';
			$options->template = 'site/group/moderate.member';
			$options->params = $params;

			// For system notification
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $this->getPermalink(false, true, 'item', false);

			ES::notify('groups.requested', $targets, $options, $system);
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
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $data['permalink'];

			ES::notify('groups.moderate.review', $targets, $options, $system);
		}
	}


	/**
	 * Notify admins of the post moderation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function notifyAdminsModeration($data = array())
	{
		$model = ES::model('Groups');
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

		$rule = 'groups.updates';

		ES::notify($rule, $targets, $options);
	}



	/**
	 * Notify members of the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function notifyMembers($action, $data = array())
	{
		// Default rule
		$rule = false;

		$model = ES::model('Groups');
		$sendAsBatch = false;
		$targets = isset($data['targets']) ? $data['targets'] : false;
		$exclude = '';

		if ($targets === false) {
			$exclude = isset($data['userId']) ? $data['userId'] : '';
			// $options = array('exclude' => $exclude, 'state' => SOCIAL_GROUPS_MEMBER_PUBLISHED);
			// $targets = $model->getMembers($this->id, $options);
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
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->pollTitle = $data['title'];
			$params->pollLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_EMAILS_GROUP_POLL_CREATED_SUBJECT';
			$options->template = 'site/group/polls.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('APP_GROUP_STORY_POLLS_CREATED_IN_GROUP', $actor->getName(), $this->getName());
			$system->content = $params->pollTitle;
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->pollLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.polls.create';

		}

		if ($action == 'album.create') {

			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->albumTitle = $data['title'];
			$params->albumDescription = $data['description'];
			$params->albumLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_ALBUM_CREATED_SUBJECT';
			$options->template = 'site/group/album.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_EASYSOCIAL_NOTIFICATION_GROUP_ALBUM_CREATED_SUBJECT', $actor->getName(), $this->getName());
			$system->content = $params->albumTitle;
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->albumLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.album.create';
		}

		if ($action == 'story.updates') {

			$actor = ES::user($data['userId']);

			// Prepare for email data
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->posterAvatar = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
			$params->posterLink = $actor->getPermalink(true,true);
			$params->message = nl2br($data['content']);
			$params->group = $this->getName();
			$params->groupLink = $this->getPermalink(true,true);
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

			$rule = 'groups.updates';
		}

		if ($action == 'video.create') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->videoTitle = $data['title'];
			$params->videoDescription = $data['description'];
			$params->videoLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_VIDEO_CREATED_SUBJECT';
			$options->template = 'site/group/video.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->videoLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.video.create';
		}

		if ($action == 'audio.create') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->audioTitle = $data['title'];
			$params->audioDescription = $data['description'];
			$params->audioLink = $data['permalink'];

			$options = new stdClass();
			$options->title = 'COM_ES_AUDIO_EMAILS_GROUP_AUDIO_CREATED_SUBJECT';
			$options->template = 'site/group/audio.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->audioLink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.audio.create';
		}

		if ($action == 'task.completed') {

			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->milestoneName = $data['milestone'];
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_TASK_COMPLETED_SUBJECT';
			$options->template = 'site/group/task.completed';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.task.completed';
		}

		if ($action == 'task.uncompleted') {

			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->milestoneName = $data['milestone'];
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_TASK_UNCOMPLETED_SUBJECT';
			$options->template = 'site/group/task.uncompleted';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.task.uncompleted';

			// ES::notify('groups.task.uncompleted', $targets, $options, $system);
		}

		if ($action == 'task.create') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->milestoneName = $data['milestone'];
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_TASK_CREATED_SUBJECT';
			$options->template = 'site/group/task.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.task.create';
		}

		if ($action == 'milestone.create') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_TASK_CREATED_MILESTONE_SUBJECT';
			$options->template = 'site/group/milestone.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = '';
			$system->actor_id = $actor->id;
			$system->context_ids = $data['id'];
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->permalink;
			$system->image = $this->getAvatar(SOCIAL_AVATAR_MEDIUM, true);

			$rule = 'groups.milestone.create';
		}

		if ($action == 'discussion.reply') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->title = $data['title'];
			$params->content = $data['content'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_REPLIED_TO_DISCUSSION_SUBJECT';
			$options->template = 'site/group/discussion.reply';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_EASYSOCIAL_GROUPS_NOTIFICATION_REPLY_DISCUSSION', $actor->getName());
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->permalink;
			$system->context_ids = $data['discussionId'];

			$rule = 'groups.discussion.reply';
		}

		if ($action == 'discussion.create') {
			$actor = ES::user($data['userId']);
			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->title = $data['discussionTitle'];
			$params->content = $data['discussionContent'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_NEW_DISCUSSION_SUBJECT';
			$options->template = 'site/group/discussion.create';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->title = JText::sprintf('COM_EASYSOCIAL_GROUPS_NOTIFICATION_NEW_DISCUSSION', $actor->getName(), $this->getName());
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->permalink;
			$system->context_ids = $data['discussionId'];

			$rule = 'groups.discussion.create';
		}

		if ($action == 'file.uploaded') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->actorLink = $actor->getPermalink(false, true);
			$params->actorAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->group = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->fileTitle = $data['fileName'];
			$params->fileSize = $data['fileSize'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_NEW_FILE_SUBJECT';
			$options->template = 'site/group/file.uploaded';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'file.group.uploaded';
			$system->context_ids = $data['fileId'];
			$system->type = 'groups';
			$system->url = $params->permalink;

			$rule = 'groups.updates';
		}

		if ($action == 'news.create') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->group = $this->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);
			$params->newsTitle = $data['newsTitle'];
			$params->newsContent = $data['newsContent'];
			$params->permalink = $data['permalink'];

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_NEW_ANNOUNCEMENT_SUBJECT';
			$options->template = 'site/group/news';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->context_ids = $data['newsId'];
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $params->permalink;

			$rule = 'groups.news';
		}

		if ($action == 'leave') {
			$actor 	= ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->group = $this->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_SUBJECT_GROUPS_LEFT_GROUP';
			$options->template = 'site/group/leave';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $this->getPermalink();

			$rule = 'groups.leave';
		}

		if ($action == 'user.remove') {
			$actor = ES::user($data['userId']);

			// targets should be the user being removed.
			$targets = array($actor->id);
			$sendAsBatch = false;

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->group = $this->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_SUBJECT_GROUPS_YOU_REMOVED_FROM_GROUP';
			$options->template = 'site/group/user.removed';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->cmd = 'groups.user.removed';
			$system->url = $this->getPermalink();

			$rule = 'groups.user.removed';
		}


		// Admin approves the user
		if ($action == 'approved') {

			// The actor is always the current user.
			$actor = ES::user();

			// There is a situation where action approved been made via email,
			// and the admin did not logged in to the site (frontend).
			// So, if actor for this action is a Guest,
			// we get the group creator to be the actor.
			if (!$actor->id) {
				$actor = ES::user($this->creator_uid);
			}

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->group = $this->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_SUBJECT_GROUPS_APPROVED_JOIN_GROUP';
			$options->template = 'site/group/user.approved';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $this->getPermalink();

			$rule = 'groups.approved';
		}

		if ($action == 'join') {
			$actor = ES::user($data['userId']);

			$params = new stdClass();
			$params->actor = $actor->getName();
			$params->group = $this->getName();
			$params->userName = $actor->getName();
			$params->userLink = $actor->getPermalink(false, true);
			$params->userAvatar = $actor->getAvatar(SOCIAL_AVATAR_LARGE);
			$params->groupName = $this->getName();
			$params->groupAvatar = $this->getAvatar();
			$params->groupLink = $this->getPermalink(false, true);

			// Send notification e-mail to the target
			$options = new stdClass();
			$options->title = 'COM_EASYSOCIAL_EMAILS_GROUP_JOINED_GROUP_SUBJECT';
			$options->template = 'site/group/joined';
			$options->params = $params;

			// Set the system alerts
			$system = new stdClass();
			$system->uid = $this->id;
			$system->actor_id = $actor->id;
			$system->target_id = $this->id;
			$system->context_type = 'groups';
			$system->type = SOCIAL_TYPE_GROUP;
			$system->url = $this->getPermalink();

			$rule = 'groups.joined';
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
	 * Promotes a user from the group as the group admin
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function promoteUser($userId)
	{
		$member = ES::table('GroupMember');
		$member->load(array('uid' => $userId, 'cluster_id' => $this->id));

		// Make the user as the admin
		$member->makeAdmin();

		// Create a stream for this
		$this->createStream($userId, 'makeAdmin');

		$permalink = $this->getPermalink(false, true);

		// Notify the person that they are now a group admin
		$emailOptions = array(
							'title' => 'COM_EASYSOCIAL_GROUPS_EMAILS_PROMOTED_AS_GROUP_ADMIN_SUBJECT',
							'template' => 'site/group/promoted',
							'permalink' => $this->getPermalink(true, true),
							'actor' => $this->my->getName(),
							'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
							'actorLink' => $this->my->getPermalink(true, true),
							'group' => $this->getName(),
							'groupLink' => $this->getPermalink(true, true)
							);

		$systemOptions = array(
							'context_type' => 'groups.group.promoted',
							'url' => $this->getPermalink(false, false),
							'actor_id' => $this->my->id,
							'uid' => $this->id
							);

		$state = ES::notify('groups.promoted', array($userId), $emailOptions, $systemOptions);

		return $state;
	}

	/**
	 * Rejects the user application
	 *
	 * @since	1.2
	 * @access	public
	 * @param	int		The user id
	 * @return
	 */
	public function rejectUser($userId)
	{
		$this->deleteNode($userId);

		// Notify the user that they have been rejected
		$emailOptions = array(
							'title' => 'COM_EASYSOCIAL_GROUPS_APPLICATION_REJECTED',
							'template' => 'site/group/user.rejected',
							'groupName' => $this->getName(),
							'groupLink' => $this->getPermalink(true, true)
							);

		$systemOptions = array(
							'context_type' => 'groups',
							'cmd' => 'groups.user.rejected',
							'url' => $this->getPermalink(true, false, 'item', false),
							'actor_id' => $this->my->id,
							'uid' => $this->id
							);

		ES::notify('groups.user.rejected', array($userId), $emailOptions, $systemOptions);

		return $state;
	}

	/**
	 * Cancel user invitation from the group
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function cancelInvitation($userId)
	{
		$member = ES::table('GroupMember');
		$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

		$state = $member->delete();

		return $state;
	}

	/**
	 * Gets group member's filter.
	 *
	 * @since	1.2
	 * @access	public
	 *
	 */
	public function getFilters()
	{
		$model = ES::model('Clusters');
		$filters = $model->getFilters($this->id, $this->cluster_type);
		$defaultDisplay = $this->config->get('groups.item.display', 'timeline');

		// Update the permalink of the filters
		if ($filters) {
			foreach ($filters as $filter) {
				$filterOptions = array('layout' => 'item', 'id' => $this->getAlias(), 'filterId' => $filter->getAlias());

				if ($defaultDisplay == 'info') {
					$filterOptions['type'] = 'timeline';
				}

				$filter->permalink = ESR::groups($filterOptions);
			}
		}

		return $filters;
	}

	/**
	 * Determines if the viewer can access the group
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccess()
	{
		// We only need to check if this is an invite only group
		if (!$this->isInviteOnly()) {
			return true;
		}

		if (!$this->isMember() && !$this->isInvited() && !$this->my->isSiteAdmin()) {
			return false;
		}

		return true;
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
	 * Determines if the user can invite other friends to the group
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function canInvite($userId = null)
	{
		$user = ES::user($userId);

		if (!$this->config->get('friends.enabled') && !$this->config->get('groups.invite.nonfriends')) {
			return false;
		}

		if ($user->isSiteAdmin() || $this->isAdmin() || $this->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can invite non-friends to the group
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function canInviteNonFriends($userId = null)
	{
		$user = ES::user($userId);

		if (!$this->config->get('groups.invite.nonfriends')) {
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
	public function canCreateTasks($userId = null)
	{
		$user = ES::user($userId);
		$access = $this->getAccess();

		if ($access->get('tasks.access') == 'admins' && (!$this->isAdmin($user->id) || !$user->isSiteAdmin())) {
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

	public function hasPointToCreate($userId = null)
	{
		$user = ES::user($userId);

		// check if this user has enough oints to create group in the selected category or not.
		$category = ES::table('GroupCategory');
		$category->load($this->category_id);

		if (!$category->hasPointsToCreate($user->id)) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve points needed to create group in category
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPointToCreate($userId = null)
	{
		$user = ES::user($userId);

		$category = ES::table('GroupCategory');
		$category->load($this->category_id);

		return $category->getPointsToCreate($user->id);
	}

	/**
	 * Preprocess before storing data into the table object.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  boolean True if successful.
	 */
	public function save()
	{
		$isNew = $this->isNew();

		if ($isNew && !$this->hasPointToCreate()) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_GROUPS_INSUFFICIENT_POINTS', $this->getPointToCreate()));
			return false;
		}

		$state = parent::save();
		return $state;
	}

	/**
	 * Determine if user are allowed to create event in group
	 *
	 * @since	2.0
	 * @access	public
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

		// Check category access
		if (!$this->getAccess()->allowed('events.groupevent', true)) {
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

		// Check for eventcreate custom field settings
		$allowed = ES::makeArray($this->getParams()->get('eventcreate', '[]'));
		$canCreate = false;

		if (in_array('admin', $allowed) && $this->isAdmin($user->id)) {
			$canCreate = true;
		}

		if (in_array('member', $allowed) && $this->isMember($user->id)) {
			$canCreate = true;
		}

		// Group owner and site admin
		if ($this->isOwner($user->id) || $user->isSiteAdmin()) {
			$canCreate = true;
		}

		return $canCreate;
	}

	/**
	 * Approves the user via email/user management which is use auto join group feature
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function createMemberViaAutoJoinGroups($userId)
	{
		$member = ES::table('GroupMember');
		$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

		$member->state = SOCIAL_GROUPS_MEMBER_PUBLISHED;
		$state = $member->store();

		// Additional triggers to be processed when the page starts.
		ES::apps()->load(SOCIAL_TYPE_GROUP);
		$dispatcher = ES::dispatcher();

		// Trigger: onComponentStart
		$dispatcher->trigger('user', 'onJoinGroup', array($userId, $this));

		// @points: groups.join
		// Add points when user joins a group
		$points = ES::points();
		$points->assign('groups.join', 'com_easysocial', $userId);

		// Publish on the stream
		if ($state) {
			// Add stream item so the world knows that the user joined the group
			$this->createStream($userId, 'join');
		}

		// Send notifications to group members when a new member joined the group
		$this->notifyMembers('join', array('userId' => $userId));

		return $state;
	}

	/**
	 * Determines if the user can view this group event or not
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canViewEvent()
	{
		// We need to check if the group's event app is published
		if (!$this->isAppPublished('events')) {
			return false;
		}

		if (!$this->canAccessEvents() || !$this->getCategory()->getAcl()->allowed('events.groupevent')) {
			return false;
		}

		if (ES::user()->isSiteAdmin() || $this->isOpen() || $this->isMember()) {
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

		if ($includeFields) {
			// Prepare DISPLAY custom fields
			ES::language()->loadAdmin();

			$model = ES::model('Groups');
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
