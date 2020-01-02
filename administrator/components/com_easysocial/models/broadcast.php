<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

class EasySocialModelBroadcast extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('broadcast', $config);
	}

	/**
	 * Retrieves a list of broadcasts created on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getBroadcasts($userId)
	{
		$db = ES::db();
		$sql = $db->sql();
		$now = ES::date()->toSql(true);

		$sql->select('#__social_broadcasts');
		$sql->where('target_id', $userId);
		$sql->where('target_type', SOCIAL_TYPE_USER);
		$sql->where('state', 1);
		$sql->where('(');
		$sql->where('expiry_date', $now, '>=');
		$sql->where('expiry_date', '0000-00-00 00:00:00', '=', 'OR');
		$sql->where(')');
		$sql->order('created', 'DESC');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		$broadcasts = array();

		foreach ($result as $row) {

			$broadcast = ES::table('Broadcast');
			$broadcast->bind($row);

			// When the broadcasts are alredy retrieved from the system, it should be marked as read.
			// Otherwise it would keep on spam the user's screen.
			$broadcast->markAsRead();

			$broadcasts[] = $broadcast;
		}

		return $broadcasts;
	}

	/**
	 * Broadcast a message to a set of profiles on the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function broadcast($ids, $content, $createdBy, $title = '', $link = '', $expiryDate = '', $context)
	{
		$db  = FD::db();
		$sql = $db->sql();

		$query  = array();

		$query[] = 'INSERT INTO ' . $db->quoteName('#__social_broadcasts');
		$query[] = '(`target_id`,`target_type`,`title`,`content`,`link`,`state`,`created`,`created_by`, `expiry_date`)';

		// Get the creation date
		$date = FD::date();

		if ($context == 'profile') {
			// get the users
			$query[] = 'SELECT';
			$query[] = '`user_id`,' . $db->Quote(SOCIAL_TYPE_USER) . ',' . $db->Quote($title) . ',' . $db->Quote($content) . ',' . $db->Quote($link) . ',1,' . $db->Quote($date->toSql()) . ',' . $db->Quote($createdBy) . ',' . $db->Quote($expiryDate);
			$query[] = 'FROM ' . $db->quoteName('#__social_profiles_maps');
			$query[] = 'WHERE 1';

			// If this is not an array, make it as an array.
			if (!is_array($ids)){
				$ids = array($ids);
			}

			$ids = implode(',', $ids);

			// If the id is empty, send to all
			if (!empty($ids)) {
				$query[] = 'AND ' . $db->quoteName('profile_id') . ' IN (' . $ids . ')';
			}

			// Exclude the broadcaster because it would be pretty insane if I am spamming myself
			$my = ES::user();
			$query[] = 'AND `user_id` !=' . $db->Quote($my->id);
		}

		// If the context is group, we will get the group members
		if ($context == 'group') {

			$query[] = 'VALUES';

			// get all group members
			$userIds = $this->getGroupMembers($ids);

			if (empty($userIds)) {
				return;
			}

			$count = 1;

			// generate the SQL query
			foreach ($userIds as $userId) {
				$query[] = '(' . $db->Quote($userId) . ',' . $db->Quote(SOCIAL_TYPE_USER) . ',' . $db->Quote($title) . ',' . $db->Quote($content) . ',' . $db->Quote($link) . ',1,' . $db->Quote($date->toSql()) . ',' . $db->Quote($createdBy) . ',' . $db->Quote($expiryDate) . ')';

				if ($count < count($userIds)) {
					$query[] = ',';
				}

				$count++;
			}
		}

		$query = implode(' ', $query);

		$sql->raw($query);

		$db->setQuery($sql);

		$state = $db->Query();

		if (!$state) {
			return $state;
		}

		// Get the id of the new broadcasted item
		$id = $db->insertid();

		return $id;
	}

	/**
	 * Notify a broadcast a message to a set of profiles on the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function notifyBroadcast($ids, $title, $content, $link, $createdBy, $streamItem, $context)
	{
		$db  = FD::db();
		$sql = $db->sql();
		$my = ES::user();

		$systemOptions = array('uid' => $my->id,
						 'actor_id' => $my->id,
						 'title' => $title,
						 'content' => $content,
						 'type' => 'broadcast',
						 'url' => FRoute::stream(array('layout' => 'item', 'id' => $streamItem->uid)));

		$emailParams = array('content' => $content, 'title' => $title);

		$emailOptions = array(
				'title' => 'APP_USER_BROADCAST_EMAILS_NEW_BROADCAST_TITLE',
				'template' => 'apps/user/broadcast/new.broadcast',
				'permalink' => FRoute::stream(array('layout' => 'item', 'id' => $streamItem->uid)),
				'params' => $emailParams
			);

		$state = false;

		if ($context == 'profile') {
			$state = ES::notifyProfileMembers('broadcast.notify', $ids, $emailOptions, $systemOptions);
		}

		if ($context == 'group') {
			$state = ES::notifyClusterMembers('broadcast.notify', $ids, $emailOptions, $systemOptions);
		}

		if ($state) {

			// Create an empty broadcast record for stream item
			$query  = array();

			// Get the creation date
			$date = FD::date();

			$query[] = 'INSERT INTO ' . $db->quoteName('#__social_broadcasts');
			$query[] = '(`target_id`,`target_type`,`title`,`content`,`link`,`state`,`created`,`created_by`) VALUES';
			$query[] = '(' . $db->Quote('') . ','. $db->Quote('') .',' . $db->Quote($title) . ',' . $db->Quote($content) . ',' . $db->Quote($link) . ',1,' . $db->Quote($date->toSql()) . ',' . $db->Quote($createdBy) . ')';

			$query = implode(' ', $query);

			$sql->raw($query);

			$db->setQuery($sql);

			$state = $db->Query();

			if (!$state) {
				return $state;
			}

			// Get the id of the new broadcasted item
			$id = $db->insertid();

			return $id;
		}

		return $state;
	}

	/**
	 * Retrieve group members
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getGroupMembers($ids)
	{
		$model = ES::model('Groups');
		$my = ES::user();

		// If this is not an array, make it as an array.
		if (!is_array($ids)){
			$ids = array($ids);
		}

		// if empty ids, just get all members from all groups
		if (empty($ids)) {
			// get all groups with published state
			$groups = $model->getGroups(array('state' => SOCIAL_CLUSTER_PUBLISHED));

			foreach ($groups as $group) {
				$ids[] = $group->id;
			}
		}

		$users = array();

		foreach ($ids as $id) {

			// get the active members exclude myself
			$members = $model->getMembers($id, array('state' => SOCIAL_STATE_PUBLISHED, 'exclude' => $my->id));

			// Merge members from all groups
			$users = array_merge($users, $members);
		}

		$result = array();

		foreach ($users as $user) {
			$result[] = $user->id;
		}

		// We have to make it unique so that there will be no duplicate user
		return array_unique($result);
	}

	/**
	 * Retrieve the users
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getUsers($ids)
	{
		$db  = FD::db();
		$sql = $db->sql();

		$query  = array();

		$query[] = 'SELECT';
		$query[] = '`user_id`';
		$query[] = 'FROM ' . $db->quoteName('#__social_profiles_maps');
		$query[] = 'WHERE 1';

		// If this is not an array, make it as an array.
		if (!is_array($ids)){
			$ids = array($ids);
		}

		$ids = implode(',', $ids);

		// If the id is empty, send to all
		if (!empty($ids)) {
			$query[] = 'AND ' . $db->quoteName('profile_id') . ' IN (' . $ids . ')';
		}

		// Exclude the broadcaster because it would be pretty insane if I am spamming myself
		$my = ES::user();
		$query[] = 'AND `user_id` !=' . $db->Quote($my->id);

		$query = implode(' ', $query);

		$sql->raw($query);

		$db->setQuery($sql);

		$users = $db->loadObjectList();

		$result = array();

		foreach ($users as $user) {
			$result[] = $user->user_id;
		}

		return $result;
	}
}
