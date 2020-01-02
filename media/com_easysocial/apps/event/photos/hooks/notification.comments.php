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

class SocialEventAppPhotosHookNotificationComments
{
	/**
	 * Processes comments notifications
	 *
	 * @since   1.2
	 * @access  public
	 */
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

		// Load the comment object since we have the context_ids
		$comment = ES::table('Comments');
		$comment->load($item->uid);

		// When user likes on an album or a event of photos from an album on the stream
		if ($item->context_type == 'albums.event.create') {

			$album = ES::table('Album');
			$album->load($item->context_ids);

			$item->content = ES::string()->processEmoWithTruncate($comment->comment);
			$item->image = $album->getCover();

			// We need to determine if the user is the owner
			if ($album->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$string = ES::string()->computeNoun('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PHOTO_ALBUM', count($users));
				$item->title = JText::sprintf($string, $names, $album->title);

				return;
			}

			// For other users, we just post a generic message
			$string = ES::string()->computeNoun('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PHOTO_ALBUM', count($users));
			$item->title = JText::sprintf($string, $names, ES::user($album->user_id)->getName());

			return;
		}

		// If user uploads multiple photos on the stream
		if ($item->context_type == 'stream.event.upload') {

			//Get the stream item object
			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('uid' => $item->context_ids));

			// Get the photo object
			$photo  = ES::table('Photo');
			$photo->load($streamItem->context_id);

			$item->content = '';

			// We could also set an image preview
			$item->image = $photo->getSource();

			// Because we know that this is coming from a stream, we can display a nicer message
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_YOUR_PHOTO_SHARED_ON_THE_STREAM', $names);

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_USERS_PHOTO_SHARED_ON_THE_STREAM', $names, ES::user($photo->user_id)->getName());

			return;
		}

	   if ($item->context_type == 'photos.event.updateCover') {

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->context_ids);

			// Set the photo image
			$item->image = $photo->getSource();
			$item->content = '';

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PROFILE_COVER', $names);

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PROFILE_COVER', $names, ES::user($photo->user_id)->getName());

			return;
		}

	   if ($item->context_type == 'photos.event.uploadAvatar') {

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->context_ids);

			// Set the photo image
			$item->image = $photo->getSource();
			$item->content = '';

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PROFILE_PHOTO', $names);

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PROFILE_PHOTO', $names, ES::user($photo->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.event.upload' || $item->context_type == 'photos.event.add') {

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->context_ids);

			// Set the photo image
			$item->image = $photo->getSource();

			// Set the comment message
			$item->content = ES::string()->processEmoWithTruncate($comment->comment);

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PHOTO', $names);

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_EVENT_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PHOTO', $names, ES::user($photo->user_id)->getName());

			return;
		}

		return;
	}

}
