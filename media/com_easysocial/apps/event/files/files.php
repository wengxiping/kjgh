<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialEventAppFiles extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_FILES) {
			return;
		}

		// Since the data is being tempered by unwanted guest,
		// we can assume that anything beyond here is no longer accessible.
		return false;
	}

	/**
	 * Determines if the app should be displayed in the list
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function appListing($view, $id, $type)
	{
		$event = ES::event($id);

		if (!$event->canAccessFiles()) {
			return false;
		}

		if (!$event->canViewItem()) {
			return false;
		}

		return true;
	}

	/**
	 * Processes notifications for files
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('files.event.uploaded');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		$hook = $this->getHook('notification', $item->type);
		$hook->execute($item);
		return;
	}

	/**
	 * Processes when user likes a file
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('files.event.uploaded');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Set the default element.
		$uid = $likes->uid;
		$data = explode('.', $likes->type);
		$element = $data[0];
		$verb = $data[2];

		// Get the owner of the post.
		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		// Since we have the stream, we can get the event id
		$event = ES::event($stream->cluster_id);

		// Get the actor
		$actor = ES::user($likes->created_by);

		$systemOptions = array(
			'context_type' => $likes->type,
			'context_ids' => $stream->cluster_id,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
	   );

		// Notify the owner first
		if ($likes->created_by != $stream->actor_id) {
			ES::notify('likes.item', array($stream->actor_id), false, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'event', $verb, array(), array($stream->actor_id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions);

		return;
	}

	/**
	 * Processes when user comments on a file
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('files.event.uploaded');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the stream object
		$stream = ES::table('Stream');
		$stream->load($comment->uid);

		$segments = explode('.', $comment->element);
		$element = $segments[0];
		$verb = $segments[2];

		// Load up the stream object
		$stream = ES::table('Stream');
		$stream->load($comment->stream_id);

		// Get the event object
		$event = ES::event($stream->cluster_id);

		// Get the comment actor
		$actor = ES::user($comment->created_by);

		$commentContent = ES::string()->parseEmoticons($comment->comment);

		$emailOptions = array(
			'title' => 'APP_EVENT_FILES_EMAILS_COMMENT_ITEM_SUBJECT',
			'template' => 'apps/event/files/comment.file.item',
			'comment' => $commentContent,
			'event' => $event->getName(),
			'permalink' => $stream->getPermalink(true, true),
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true)
		);

		$systemOptions = array(
			'content' => $comment->comment,
			'context_type' => $comment->element,
			'context_ids' => $stream->cluster_id,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		// Notify the note owner
		if ($comment->created_by != $stream->actor_id) {
			ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'event', $verb, array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title'] = 'APP_EVENT_FILES_EMAILS_COMMENT_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/event/files/comment.file.involved';

		// Notify participating users
		ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);

		return;
	}

	/**
	 * Prepares the files stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_FILES) {
			return;
		}

		// Event access checking
		$event = ES::event($item->cluster_id);

		if (!$event || !$event->canViewItem()) {
			return;
		}

		$access = $event->getAccess();
		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if ($item->verb == 'uploaded') {
			$this->prepareUploadedStream($item);
		}

		// Append the opengraph tags
		$item->addOgDescription();
	}

	/**
	 * Prepares the stream item for new file uploads
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function prepareUploadedStream(SocialStreamItem &$item)
	{
		// Get the params from the stream item
		$params = $item->getParams();

		// Get the event
		$event = $item->getCluster();

		// Get the file object from the context params
		$params = ES::registry($item->contextParams[0]);
		$items = $params->get('file');

		if (!$items) {
			return;
		}

		$files = array();

		foreach ($items as $id) {
			$file = ES::table('File');
			$file->load((int) $id);

			if ($file->id) {
				$files[] = $file;
			}
		}

		// Only proceed if the file still exist
		if (!$files) {
			return;
		}

		// Get the actor
		$actor = $item->actor;

		// actions.
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, $item->uid);
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => $item->getPermalink(false, false, false), 'clusterId' => $item->cluster_id), $item->uid);
		$this->set('content', $item->content);
		$this->set('actor', $item->actor);
		$this->set('files', $files);
		$this->set('cluster', $event);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/files/title.cluster');
		$item->preview = parent::display('themes:/site/streams/files/preview');
	}

	/**
	 * Prepares the photos in the story edit form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStoryEditForm(&$story, &$stream)
	{
		// preparing data for story edit.
		$data = array();

		// get all photos from this stream uid.
		$model = ES::model('Files');
		$files = $model->getStreamFiles($stream->id, true);

		if ($files) {
			$data['files'] = $files;
		}

		$plugin = $this->onPrepareStoryPanel($story, true, $data);

		$story->panelsMain = array($plugin);
		$story->panels = array($plugin);
		$story->plugins = array($plugin);

		$contents = $story->editForm(false, $stream->id);

		return $contents;
	}

	/**
	 * Processes a story edit save.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onAfterStoryEditSave(SocialTableStream &$stream)
	{
		if ($stream->context_type != SOCIAL_TYPE_FILES) {
			return;
		}

		$files = $this->input->get('files', array(), 'array');

		if (!$files) {
			return;
		}

		$model = ES::model('Files');
		$state = $model->updateStreamFiles($stream->id, $files, true);

		return true;
	}

	/**
	 * Prepares what should appear in the story form.
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function onPrepareStoryPanel($story, $isEdit = false, $data = array())
	{
		$params = $this->getParams();

		// Determine if the user can use this feature
		if (!$params->get('enable_uploads', true)) {
			return;
		}

		// Get the event object
		$event = ES::event($story->cluster);

		if (!$event->canAccessFiles() || !$this->getApp()->hasAccess($event->category_id)) {
			return;
		}

		// Ensure that the user really has access to upload files in an event
		if (!$event->canCreateFiles()) {
			return;
		}

		// Get the guest
		$guest = $event->getGuest($this->my->id);

		if (!$guest->isGuest() && !$guest->isAdmin()) {
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('files', 'panel');

		// Get the allowed extensions
		$allowedExtensions = $params->get('allowed_extensions', 'zip,txt,pdf,gz,php,doc,docx,ppt,xls');
		$maxFileSize = $params->get('max_upload_size', 8) . 'M';

		// We need to attach the button to the story panel
		$theme  = ES::themes();
		$theme->set('title', $plugin->title);

		$plugin->button->html = $theme->output('site/story/files/button');
		$plugin->content->html = $theme->output('site/story/files/form', array('data' => $data, 'isEdit' => $isEdit));

		// Attachment script
		$script = ES::script();
		$script->set('allowedExtensions', $allowedExtensions);
		$script->set('maxFileSize', $maxFileSize);
		$script->set('type', SOCIAL_TYPE_EVENT);
		$script->set('uid', $story->cluster);

		$plugin->script = $script->output('site/story/files/plugin');

		return $plugin;
	}

	/**
	 * Processes after the story is saved so that we can generate a stream item for this
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onAfterStorySave(SocialStream &$stream , SocialTableStreamItem $streamItem, &$template)
	{
		$files = $this->input->get('files', array(), 'array');

		if (!$files) {
			return;
		}

		// We need to set the context id's for the files shared in this stream.
		$params = ES::registry();
		$params->set('file', $files);

		$streamItem->verb = 'uploaded';
		$streamItem->params = $params->toString();
		$streamItem->store();
	}
}
