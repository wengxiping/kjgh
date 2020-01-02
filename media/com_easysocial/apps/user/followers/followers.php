<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/apps/apps');

class SocialUserAppFollowers extends SocialAppItem
{
	public function hasActivityLog()
	{
		if (!$this->config->get('followers.enabled')) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the notification item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('profile.followed');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		// Process notifications for followers
		if ($item->cmd == 'profile.followed') {
			$actor = ES::user($item->actor_id);
			$item->title = JText::sprintf('APP_USER_FOLLOWERS_NOTIFICATIONS_FOLLOWED_YOU', $actor->getName());
		}

		return $item;
	}

	/**
	 * Responsible to generate the activity logs.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_FOLLOWERS) {
			return;
		}

		$privacy = $this->my->getPrivacy();

		$table = ES::table('Subscription');
		$table->load($item->contextId);

		$actor = $item->actor;
		$target = ES::user($table->uid);

		$this->set('target', $target);
		$item->title = parent::display('logs/' . $item->verb);

		if ($includePrivacy) {
			$item->privacy = $privacy->form($item->contextId, SOCIAL_TYPE_FOLLOWERS, $item->actor->id, 'followers.view', false, $item->aggregatedItems[0]->uid);
		}

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation( &$item, $includePrivacy = true )
	{
		// If this is not it's context, we don't want to do anything here.
		if( $item->context_type != SOCIAL_TYPE_FOLLOWERS )
		{
			return false;
		}

		$item->cnt = 1;

		if( $includePrivacy )
		{
			$my         = FD::user();
			$privacy	= FD::privacy( $my->id );

			$sModel = FD::model( 'Stream' );
			$aItem 	= $sModel->getActivityItem( $item->id, 'uid' );

			$contextId = $aItem[0]->context_id;

			if( !$privacy->validate( 'followers.view', $contextId, SOCIAL_TYPE_FOLLOWERS, $item->actor_id ) )
			{
				$item->cnt = 0;
			}

		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude( &$exclude )
	{
		// Get app params
		$params		= $this->getParams();

		$excludeVerb = false;

		if(! $params->get('stream_follow', true)) {
			$exclude[SOCIAL_TYPE_FOLLOWERS] = true;
		}
	}


	/**
	 * Responsible to generate the stream contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true )
	{
		if ($item->context != SOCIAL_TYPE_FOLLOWERS) {
			return;
		}

		// Check if the app should be able to generate the stream.
		$params = $this->getParams();

		if (!$params->get('stream_follow', true)) {
			return;
		}

		if ($includePrivacy) {
			$privacy = $this->my->getPrivacy();

			if (!$privacy->validate('followers.view', $item->contextId, SOCIAL_TYPE_FOLLOWERS, $item->actor->id)) {
				return;
			}
		}

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		// Get the target.
		$table = ES::table('Subscription');
		$table->load((int) $item->contextId);

		$actor = $item->actor;
		$target = ES::user($table->uid);

		$this->set('actor', $actor);
		$this->set('target', $target);

		// User A following user B
		if ($item->verb == 'follow') {

			if (($actor->id == $this->my->id && $this->my->isBlockedBy($target->id, true))) {
				return true;
			}

			$item->title = parent::display('streams/follow.title');
		}

		// Append the opengraph tags
		$item->addOgDescription($item->title);

		return true;
	}
}
