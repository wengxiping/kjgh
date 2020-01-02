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

class SocialUserAppPollsHookNotificationComments extends SocialAppHooks
{
	public function execute(&$item)
	{
		// Get comment participants
		$model = ES::model('Comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// Include the actor of the stream item as the recipient
		$users = array_merge($users, array($item->actor_id));

		// Ensure that the values are unique
		$users = array_unique($users);
		$users = array_values($users);

		// Exclude myself from the list of users.
		$index = array_search(ES::user()->id, $users);

		// If the skipExcludeUser is true, we don't unset myself from the list
		if (isset($item->skipExcludeUser) && $item->skipExcludeUser) {
			$index = false;
		}

		if ($index !== false) {
			unset($users[$index]);
			$users = array_values($users);
		}

		// Convert the names to stream-ish
		$names = ES::string()->namesToNotifications($users);

		// When user reacted on the polls
		if ($item->context_type == 'polls.user.create') {

			$stream = ES::table('Stream');
			$stream->load($item->uid);

			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('uid' => $stream->id));

			$poll = ES::table('Polls');
			$poll->load($streamItem->uid);

			$pollTitle = '';

			if ($poll->id) {
				$pollTitle = $poll->title;
			}

			$content = '';

			// Only show the content when there is only 1 user
			if (count($users) == 1 && !empty($item->content)) {
				$content = ES::string()->processEmoWithTruncate($item->content);
			}

			$item->content = $content;

			// We need to determine if the user is the owner
			if ($poll->created_by == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_POLLS_USERS_COMMENT_ON_YOUR_POLL', $users), $names, $pollTitle);
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $stream->actor_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_POLLS_OWNER_COMMENT_ON_OWN_POLL', $item->actor_id), ES::user($stream->actor_id)->getName(), $pollTitle);
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_POLLS_USERS_COMMENT_ON_USERS_POLL', $users), $names, ES::user($poll->created_by)->getName(), $pollTitle);

			return;
		}

		return;
	}
}
