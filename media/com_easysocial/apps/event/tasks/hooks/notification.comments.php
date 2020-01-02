<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialEventAppTasksHookNotificationComments
{
	public function execute($item)
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

		// By default content is always empty;
		$content = '';

		// Only show the content when there is only 1 item
		if (count($users) == 1 && !empty($item->content)) {
			$content = ES::string()->processEmoWithTruncate($item->content);
		}

		$item->content = $content;

		list($element, $group, $verb) = explode('.', $item->context_type);

		if ($verb == 'createMilestone') {

			// Load the milestone
			$milestone = ES::table('Milestone');
			$milestone->load($item->uid);

			// We need to generate the notification message differently for the author of the item and the recipients of the item.
			if ($milestone->owner_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf(ES::string()->computeNoun('APP_EVENT_TASKS_USER_COMMENTED_ON_YOUR_MILESTONE', count($users)), $names);
				$item->content = $milestone->get('title');

				return $item;
			}

			// This is for 3rd party viewers
			$item->title = JText::sprintf(ES::string()->computeNoun('APP_EVENT_TASKS_USER_COMMENTED_ON_USERS_MILESTONE', count($users)), $names, ES::user($milestone->owner_id)->getName());
			$item->content = $milestone->get('title');
		}

		if ($verb == 'createTask') {
			// Load the task
			$task = ES::table('Task');
			$task->load($item->uid);

			// We need to generate the notification message differently for the author of the item and the recipients of the item.
			if ($task->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf(ES::string()->computeNoun('APP_EVENT_TASKS_USER_COMMENTED_ON_YOUR_TASK', count($users)), $names);

				return $item;
			}

			// This is for 3rd party viewers
			$item->title = JText::sprintf(ES::string()->computeNoun('APP_EVENT_TASKS_USER_COMMENTED_ON_USERS_TASK', count($users)), $names, ES::user($task->user_id)->getName());
		}

		return $item;
	}

}
