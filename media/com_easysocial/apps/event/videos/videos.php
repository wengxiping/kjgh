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

class SocialEventAppVideos extends SocialAppItem
{
	public $appListing = false;

	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_VIDEOS) {
			return;
		}

		$video = ES::table('Video');
		$video->load($uid);

		$lib = ES::video($video);

		if (!$lib->isViewable()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if videos should be enabled for a given event
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function enabled(SocialEvent &$event)
	{
		$params = $event->getParams();

		if (!$params->get('videos', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the app has stream filter
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function hasStreamFilter()
	{
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return parent::hasStreamFilter();
		}

		$event = ES::event($id);

		if (!$this->enabled($event)) {
			return false;
		}

		return parent::hasStreamFilter();
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = false;

		if (!$params->get('uploadVideos', true)) {
			$excludeVerb[] = 'create';
		}

		if (!$params->get('featuredVideos', true)) {
			$excludeVerb[] = 'featured';
		}

		if ($excludeVerb !== false) {
			$exclude['videos'] = $excludeVerb;
		}
	}


	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'videos') {
			return false;
		}

		$params = ES::registry($item->params);
		$event = ES::event($params->get('event'));

		if (!$event) {
			return;
		}

		$item->cnt = 1;

		if (!$event->isOpen() && !$event->isMember($this->my->id)) {
			$item->cnt = 0;
		}

		return true;
	}

	/**
	 * Generates the stream item for videos
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != SOCIAL_TYPE_VIDEOS) {
			return;
		}

		// Determines if the viewer can view the stream item from this group
		$event = $stream->getCluster();

		if (!$event) {
			return;
		}

		if (!$event->canViewItem()) {
			return;
		}

		// Decorate the stream item with the neccessary design
		$stream->color = '#5580BE';
		$stream->fonticon = 'ies-play';
		$stream->label = JText::_('COM_EASYSOCIAL_STREAM_TITLE_VIDEOS', true);
		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;

		if (!$this->enabled($event)) {
			return;
		}

		// Get the video
		$video = ES::video($stream->cluster_id, SOCIAL_TYPE_EVENT, $stream->contextId);

		// Ensure that the video is really published
		if (!$video->isPublished()) {
			return;
		}

		$access = $event->getAccess();
		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $stream->actor->id == $this->my->id)) {
			$stream->editable = true;
			$stream->appid = $this->getApp()->id;
		}

		// Get the actor
		$actor = $stream->getActor();

		// Retrieve event alias
		$alias = $event->getAlias();

		$this->set('stream', $stream);
		$this->set('video', $video);
		$this->set('actor', $actor);
		$this->set('event', $event);
		$this->set('uid', $alias);
		$this->set('utype', SOCIAL_TYPE_EVENT);

		// Update the stream title
		$stream->title = parent::display('themes:/site/streams/videos/event/title.' . $stream->verb);
		$stream->preview = parent::display('themes:/site/streams/videos/preview');


		// Assign the comments library
		$stream->comments = $video->getComments($stream->verb, $stream->uid);

		// Assign the likes library
		$stream->likes = $video->getLikes($stream->verb, $stream->uid);


		// If the video has a thumbnail, add the opengraph tags
		$thumbnail = $video->getThumbnail();

		if ($thumbnail) {
			$stream->addOgImage($thumbnail);
		}

		// Append the opengraph tags
		$stream->addOgDescription($video->getDescription(false));
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

		// get video from this stream uid.
		$model = ES::model('Videos');
		$video = $model->getStreamVideo($stream->id);

		if ($video) {
			$data['video'] = $video;
		}

		$plugin = $this->onPrepareStoryPanel($story, true, $data);

		$story->panelsMain = array($plugin);
		$story->panels = array($plugin);
		$story->plugins = array($plugin);

		$contents = $story->editForm(false, $stream->id);

		// dump($contents);

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
		// Only process videos
		if ($stream->context_type != 'videos') {
			return;
		}

		// Determine the type of the video
		$data = array();
		$data['id'] = $this->input->get('videos_id', 0, 'int');
		$data['category_id'] = $this->input->get('videos_category', 0, 'int');
		$data['description'] = $this->input->get('videos_description', '', 'default');
		$data['iEncoding'] = $this->input->get('videos_isEncoding', false, 'bool');
		$data['link'] = $this->input->get('videos_link', '', 'default');
		$data['title'] = $this->input->get('videos_title', '', 'default');
		$data['source'] = $this->input->get('videos_type', '', 'default');

		$model = ES::model('videos');
		$state = $model->updateStreamVideo($stream->id, $data);

		return true;
	}


	/**
	 * Generates the story form for videos
	 *
	 * @since   2.0.14
	 * @access  public
	 */
	public function onPrepareStoryPanel(SocialStory $story, $isEdit = false, $data = array())
	{
		// Get the event id
		$eventId = $story->cluster;

		// Ensure that the event allows users to upload videos
		$event = ES::event($eventId);

		if (!$event->canAccessVideos() || !$event->canCreateVideos()) {
			return;
		}

		// Get the video adapter
		$adapter = ES::video($eventId, SOCIAL_TYPE_EVENT);

		if ($isEdit && isset($data['video']) && $data['video']) {
			$adapter = $data['video'];
		}

		// Ensure that video creation is allowed
		if (!$adapter->canUpload() && !$adapter->canEmbed()) {
			return;
		}

		// Get a list of video categories
		$options = array('pagination' => false, 'ordering' => 'ordering');

		$model = ES::model('Videos');
		$categories = $model->getCategories($options);

		// Create a new plugin for this video
		$plugin = $story->createPlugin('videos', 'panel');

		$title = JText::_('COM_EASYSOCIAL_STORY_VIDEO');
		$plugin->title = $title;

		// Get the maximum upload filesize allowed
		$uploadLimit = $adapter->getUploadLimit();

		$theme = ES::themes();
		$theme->set('categories', $categories);
		$theme->set('uploadLimit', $uploadLimit);
		$theme->set('video', $adapter);
		$theme->set('isEdit', $isEdit);
		$theme->set('title', $plugin->title);

		$button = $theme->output('site/story/videos/button');
		$form = $theme->output('site/story/videos/form');

		$script = ES::script();
		$script->set('uploadLimit', $uploadLimit);
		$script->set('type', SOCIAL_TYPE_EVENT);
		$script->set('uid', $adapter->id);
		$script->set('video', $adapter);
		$script->set('isEdit', $isEdit);

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/videos/plugin'));

		return $plugin;
	}

	/**
	 * Processes after a story is saved on the site. When the story is stored, we need to create the necessary video
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onBeforeStorySave(SocialStreamTemplate &$template, SocialStream &$stream, $content)
	{
		if ($template->context_type != 'videos') {
			return;
		}

		// Check if user is really allowed to do this?
		$cluster = ES::cluster($template->cluster_type, $template->cluster_id);

		if (!$cluster->canCreateVideos()) {
			JError::raiseError(500, JText::_('COM_EASYSOCIAL_CLUSTER_NOT_ALLOWED_TO_POST_UPDATE'));
			return;
		}

		// Determine the type of the video
		$data = array();
		$data['source'] = $this->input->get('videos_type', '', 'word');
		$data['title'] = $this->input->get('videos_title', '', 'default');
		$data['description'] = $this->input->get('videos_description', '', 'default');
		$data['link'] = $this->input->get('videos_link', '', 'default');
		$data['category_id'] = $this->input->get('videos_category', 0, 'int');
		$data['uid'] = $template->cluster_id;
		$data['type'] = $template->cluster_type;

		// Save options for the video library
		$saveOptions = array();

		// If this is a link source, we just load up a new video library
		if ($data['source'] == 'link') {
			$video = ES::video($template->cluster_id, SOCIAL_TYPE_EVENT);
		}

		// If this is a video upload, the id should be provided because videos are created first.
		if ($data['source'] == 'upload') {
			$id = $this->input->get('videos_id', 0, 'int');

			$video = ES::video($template->cluster_id, SOCIAL_TYPE_EVENT, $id);

			// Video library needs to know that we're storing this from the story
			$saveOptions['story'] = true;

			// We cannot publish the video if auto encoding is disabled
			if ($this->config->get('video.autoencode')) {
				$data['state'] = SOCIAL_VIDEO_PUBLISHED;
			}
		}

		// Check if user is really allowed to upload videos
		if ($video->id && !$video->canEdit()) {
			return JError::raiseError(500, JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING'));
		}

		// Try to save the video
		$state = $video->save($data, array(), $saveOptions);

		// We need to update the context
		$template->context_type = SOCIAL_TYPE_VIDEOS;
		$template->context_id = $video->id;

		$options = array();
		$options['userId'] = $this->my->id;
		$options['title'] = $video->title;
		$options['description'] = $video->getDescription();
		$options['permalink'] = $video->getPermalink();
		$options['id'] = $video->id;

		// Notify group members when a video is uploaded on the site
		$cluster->notifyMembers('video.create', $options);
	}

	public function onAfterStorySave(&$stream, &$streamItem)
	{
		if ($streamItem->context_type != 'videos') {
			return;
		}

		// Change the isNew to false
		$table = ES::table("Video");
		$table->load($streamItem->context_id);
		$table->isnew = 0;
		$table->store();

		// Determine the type of the video
		$data = array();
		$data['source'] = $this->input->get('videos_type', '', 'word');

		// If this is a video upload, the id should be provided because videos are created first.
		if ($data['source'] == 'upload' && !$this->config->get('video.autoencode')) {
			$streamItem->hidden = true;
		}
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		// Deduct points when the user unliked a video
		if ($likes->type == 'videos.event.create' || $likes->type == 'videos.event.featured') {

			$table = ES::table("Video");
			$table->load($likes->uid);

			$video = ES::video($table);

			// since when liking own video no longer get points,
			// unlike own video should not deduct point too. #3471
			if ($likes->created_by != $video->user_id) {
				ES::points()->assign('video.unlike', 'com_easysocial', $this->my->id);
			}
		}
	}


	/**
	 * Triggers after a like is saved
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('videos.event.create', 'videos.event.featured');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($likes->created_by);

		$systemOptions = array(
			'context_type' => $likes->type,
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		$videoTable = ES::table('Video');
		$videoTable->load($likes->uid);

		$video = ES::video($videoTable);

		// Get the permalink to the photo
		$systemOptions['context_ids'] = $video->id;
		$systemOptions['url'] = $video->getPermalink(false);

		// For single photo items on the stream
		if ($likes->type == 'videos.event.create') {
			$verb = 'create';
		}

		if ($likes->type == 'videos.event.featured') {
			$verb = 'featured';
		}

		ES::badges()->log('com_easysocial', 'videos.react', $likes->created_by, '');

		if ($likes->created_by != $video->user_id) {
			// assign points when the liker is not the video owner. #3471
			ES::points()->assign('video.like', 'com_easysocial', $likes->created_by);

			// Notify the owner of the photo first
			ES::notify('likes.item', array($video->user_id), false, $systemOptions);
		}

		$element = 'videos';
		$verb = 'create';

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'group', $verb, array(), array($video->user_id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions);

		return;
	}

	/**
	 * Renders the notification item
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('event.video.create', 'comments.item', 'comments.involved', 'likes.item');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		if ($item->cmd == 'event.video.create') {
			$hook = $this->getHook('notification', 'updates');
			$hook->execute($item);

			return;
		}

		// Someone posted a comment on the video
		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.involved') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// Someone likes a video
		if ($item->cmd == 'likes.item') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		return;
	}

	/**
	 * Triggered after a comment is deleted
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onAfterDeleteComment(SocialTableComments &$comment)
	{
		$allowed = array('videos.event.create', 'videos.event.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Assign points when a comment is deleted for a video
		ES::points()->assign('video.comment.remove', 'com_easysocial', $comment->created_by);
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('videos.event.create', 'videos.event.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

		$commentContent = ES::string()->parseEmoticons($comment->comment);

		// Set the email options
		$emailOptions   = array(
			'template' => 'apps/event/videos/comment.video.item',
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'comment' => $commentContent
		);

		$systemOptions  = array(
			'context_type' => $comment->element,
			'context_ids' => $comment->id,
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true,
			'content' => $commentContent
		);

		// Standard email subject
		$ownerTitle = 'COM_EASYSOCIAL_VIDEOS_CLUSTER_COMMENT_VIDEO_OWNER_SUBJECT';
		$involvedTitle = 'COM_EASYSOCIAL_VIDEOS_CLUSTER_COMMENT_VIDEO_INVOLVED_SUBJECT';

		$videoTable = ES::table('Video');
		$videoTable->load($comment->uid);

		$video = ES::video($videoTable);

		$emailOptions['permalink'] = $video->getPermalink(true, true);
		$systemOptions['url'] = $video->getPermalink(false, false, 'item', false);

		$element = 'videos';
		$verb = 'create';

		// Default email title should be for the owner
		$emailOptions['title'] = $ownerTitle;

		// Assign points for the author for posting a comment
		ES::points()->assign('videos.comment.add', 'com_easysocial', $comment->created_by);
		ES::badges()->log('com_easysocial', 'videos.comment', $comment->created_by, '');

		// Notify the owner of the photo first
		if ($video->user_id != $comment->created_by) {
			ES::notify('comments.item', array($video->user_id), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'event', $verb, array(), array($video->user_id, $comment->created_by));

		$emailOptions['title'] = $involvedTitle;
		$emailOptions['template'] = 'apps/event/videos/comment.video.involved';

		// Notify other participating users
		ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);

		return;
	}

}
