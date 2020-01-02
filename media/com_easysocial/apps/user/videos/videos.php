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

class SocialUserAppVideos extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'videos') {
			return;
		}

		$video = ES::table('Video');
		$video->load($uid);

		if (!$video->id) {
			return false;
		}

		$lib = ES::video($video);
		if (!$lib->isViewable()) {
			return false;
		}

		return true;
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

		$item->cnt = 1;

		if ($includePrivacy) {
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			$sModel = ES::model('Stream');
			$aItem = $sModel->getActivityItem($item->id, 'uid');

			$uid = $aItem[0]->context_id;
			$rule = 'videos.view';
			$context = 'videos';

			if (!$privacy->validate($rule, $uid, $context, $item->actor_id)) {
				$item->cnt = 0;
			}
		}

		return true;
	}

	/**
	 * Responsible to generate the activity logs.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'videos') {
			return;
		}

		// we only process video upload activity log since 'featured' can only be done by admin.
		if ($item->verb != 'create') {
			return;
		}

		// Get the context id.
		$id = $item->contextId;

		// Load the videos
		$video = ES::video($this->my->id, SOCIAL_TYPE_USER);
		$video->load($id);

		// Get the actor
		$actor = $item->actor;
		$target = false;

		// Determines if the photo is shared on another person's timeline
		if ($item->targets) {
			$target = $item->targets[0];
		}

		$term = $this->getGender($item->actor);

		$this->set('term'  , $term);
		$this->set('actor' , $actor);
		$this->set('target', $target);
		$this->set('video' , $video);
		$this->set('stream' , $item);

		$count = count($item->contextIds);
		$this->set('count' , $count);

		$file = 'user/';

		if ($item->cluster_id && $item->cluster_type == SOCIAL_TYPE_GROUP) {
			$file = 'group/';

			$group = ES::group($item->cluster_id);
			$this->set('cluster' , $group);

		} else if ($item->cluster_id && $item->cluster_type == SOCIAL_TYPE_EVENT) {
			$file = 'event/';

			$event = ES::event($item->cluster_id);
			$this->set('cluster' , $event);
		}

		$file .= 'title.create';

		$item->display  = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('themes:/site/videos/stream/' . $file);
		$item->content  = parent::display('streams/activity_content');

		$privacyRule = 'videos.view';

		if ($includePrivacy) {
			$my = ES::user();

			$sModel = ES::model('Stream');
			$aItem  = $sModel->getActivityItem($item->aggregatedItems[0]->uid, 'uid');

			$streamId = count($aItem) > 1 ? '' : $item->aggregatedItems[0]->uid;
			$item->privacy = ES::privacy($my->id)->form($video->id, 'videos', $item->actor->id, $privacyRule, false, $streamId);
		}
	}


	/**
	 * Retrieves the Gender representation of the language string
	 *
	 * @since   1.4
	 * @access  public
	 */
	private function getGender(SocialUser $user)
	{
		// Get the term to be displayed
		$value = $user->getFieldData('GENDER');

		$term = 'NOGENDER';

		if ($value == 1) {
			$term = 'MALE';
		}

		if ($value == 2) {
			$term = 'FEMALE';
		}

		return $term;
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

		// Check for privacy
		$privacy = $this->my->getPrivacy();

		if ($includePrivacy && !$privacy->validate('videos.view', $stream->contextId, SOCIAL_TYPE_VIDEOS, $stream->actor->id)) {
			return;
		}

		// Get the actor
		$actor = $stream->getActor();

		$video = ES::video();
		$video->load($stream->contextId);

		// Ensure that the video is really published
		if (!$video->isPublished()) {
			return;
		}

		if ($this->my->isSiteAdmin() || $this->my->id == $stream->actor->id) {
			$stream->editable = true;
			$stream->appid = $this->getApp()->id;
		}

		$target = count($stream->targets) > 0 ? $stream->targets[0] : '';

		// Get the cluster
		$cluster = $stream->getCluster();

		if ($cluster) {
			$target = $cluster;
		}

		$this->set('target', $target);
		$this->set('stream', $stream);
		$this->set('video', $video);
		$this->set('actor', $actor);
		$this->set('cluster', $cluster);

		// handle for the video category permalink for the cluster as well
		$this->set('uid', '');
		$this->set('utype', '');

		// Update the stream title
		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;
		$stream->title = parent::display('themes:/site/streams/videos/user/title.' . $stream->verb);
		$stream->preview = parent::display('themes:/site/streams/videos/preview');

		// Assign the comments library
		$stream->comments = $video->getComments($stream->verb, $stream->uid);

		// Assign the likes library
		$stream->likes = $video->getLikes($stream->verb, $stream->uid);

		if ($includePrivacy) {;
			$stream->privacy = $privacy->form($stream->contextId, $stream->context, $stream->actor->id, 'videos.view', false, $stream->uid, array(), array('iconOnly' => true));
		}

		// If the video has a thumbnail, add the opengraph tags
		$thumbnail = $video->getThumbnail();

		if ($thumbnail) {
			$stream->addOgImage($thumbnail);
		}

		$stream->addOgDescription($video->getDescription(false));

		return true;
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
	 * @since   1.4
	 * @access  public
	 */
	public function onPrepareStoryPanel(SocialStory $story, $isEdit = false, $data = array())
	{
		// Ensure that the user really has access to create videos
		$video = ES::video();

		if ($isEdit && isset($data['video']) && $data['video']) {
			$video = $data['video'];
		}

		if (!$video->canUpload() && !$video->canEmbed()) {
			return;
		}

		// Determines if the user can really utilize the videos app
		if (!$this->getApp()->hasAccess($this->my->getProfile()->id)) {
			return;
		}

		// Get a list of video categories
		$model = ES::model('Videos');

		// Get a list of video categories
		$options = array('pagination' => false, 'ordering' => 'ordering');

		if (!$this->my->isSiteAdmin()) {
			$options['respectAccess'] = true;
			$options['profileId'] = $this->my->getProfile()->id;
		}

		$categories = $model->getCategories($options);

		// Create a new plugin for this video
		$plugin = $story->createPlugin('videos', 'panel');

		$title = JText::_('COM_EASYSOCIAL_STORY_VIDEO');
		$plugin->title = $title;

		// Get the maximum file size allowed for video uploads
		$uploadLimit = $video->getUploadLimit();

		$theme = ES::themes();
		$theme->set('categories', $categories);
		$theme->set('uploadLimit', $uploadLimit);
		$theme->set('video', $video);
		$theme->set('isEdit', $isEdit);
		$theme->set('title', $plugin->title);
		$button = $theme->output('site/story/videos/button');
		$form = $theme->output('site/story/videos/form');

		$script = ES::script();
		$script->set('uploadLimit', $uploadLimit);
		$script->set('type', SOCIAL_TYPE_USER);
		$script->set('uid', $this->my->id);
		$script->set('video', $video);
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
		// Only process videos
		if ($template->context_type != 'videos') {
			return;
		}

		// Determine the type of the video
		$data = array();
		$data['source'] = $this->input->get('videos_type', '', 'word');
		$data['title'] = $this->input->get('videos_title', '', 'default');
		$data['description'] = $this->input->get('videos_description', '', 'default');
		$data['category_id'] = $this->input->get('videos_category', 0, 'int');

		// We need to format the link first.
		$link = $this->input->get('videos_link', '', 'default');

		// Save options for the video library
		$saveOptions = array();

		// If this is a link source, we just load up a new video library
		if ($data['source'] == 'link') {
			$video = ES::video();
			$data['link'] = $video->format($link);
		}

		// If this is a video upload, the id should be provided because videos are created first.
		if ($data['source'] == 'upload') {
			$id = $this->input->get('videos_id', 0, 'int');

			$video = ES::video();
			$video->load($id);

			// Video library needs to know that we're storing this from the story
			$saveOptions['story'] = true;

			// We cannot publish the video if auto encoding is disabled
			if ($this->config->get('video.autoencode')) {
				$data['state'] = SOCIAL_VIDEO_PUBLISHED;
			}
		}

		$data['uid'] = $this->my->id;
		$data['type'] = SOCIAL_TYPE_USER;

		// Check if user is really allowed to upload videos
		if ($video->id && !$video->canEdit()) {
			return JError::raiseError(500, JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING'));
		}

		// Try to save the video
		$state = $video->save($data, array(), $saveOptions);

		// We need to update the context
		$template->context_type = SOCIAL_TYPE_VIDEOS;
		$template->context_id = $video->id;
	}

	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		// Only process videos
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
		if ($data['source'] == 'upload') {

			if (!$this->config->get('video.autoencode')) {
				$streamItem->notice = JText::_('COM_ES_VIDEOS_UPLOAD_SUCCESS_AWAIT_PROCESSING_STORY');
			} else {
				// Load the video
				$video = ES::video($table->uid, $table->type, $table->id);

				// Get the status of the video
				$status = $video->status();

				// Assign points to the video creator
				if ($status === true) {
					ES::points()->assign('video.upload', 'com_easysocial', $video->getAuthor()->id);
				}

				// Published the video
				if ($status === true && $video->isNew()) {
					$video->publish(array('createStream' => true));
				}
			}
		}
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		if ($likes->type == 'videos.user.create') {

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
		// @legacy
		// photos.user.add should just be photos.user.upload since they are pretty much the same
		$allowed = array('videos.user.create', 'videos.user.featured');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		if (in_array($likes->type, $allowed)) {
			$actor = ES::user($likes->created_by);

			$systemOptions  = array(
				'context_type' => $likes->type,
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			$table = ES::table("Video");
			$table->load($likes->uid);

			$video = ES::video($table);

			$systemOptions['context_ids'] = $video->id;
			$systemOptions['url'] = $video->getPermalink(false);

			$element = 'videos';

			// For single photo items on the stream
			if ($likes->type == 'videos.user.create') {
				$verb = 'create';
			}

			if ($likes->type == 'videos.user.featured') {
				$verb = 'featured';
			}

			ES::badges()->log('com_easysocial', 'videos.react', $likes->created_by, '');

			if ($likes->created_by != $video->user_id) {
				// assign points when the liker is not the video owner. #3471
				ES::points()->assign('video.like', 'com_easysocial', $likes->created_by);

				// Notify the owner of the video
				ES::notify('likes.item', array($video->user_id), false, $systemOptions);
			}

			// Get additional recipients since photos has tag
			$additionalRecipients = array();
			$this->getTagRecipients($additionalRecipients, $video);

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'user', $verb, $additionalRecipients, array($video->user_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

	}


	/**
	 * Retrieves a list of tag recipients on a video
	 *
	 * @since   1.2
	 * @access  public
	 */
	private function getTagRecipients(&$recipients, SocialVideo &$video, $exclusion = array())
	{
		return array();
	}

	/**
	 * Renders the notification item
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('comments.item', 'comments.involved', 'likes.item', 'likes.involved', 'videos.tagged',
							'likes.likes' , 'comments.comment.add');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		// When user likes a single photo
		$allowedContexts = array('videos.user.create', 'videos.user.featured');

		if (($item->cmd == 'comments.item' || $item->cmd == 'comments.involved') && in_array($item->context_type, $allowedContexts)) {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// When user likes a single photo
		$allowedContexts = array('videos.user.create', 'videos.user.featured');
		if (($item->cmd == 'likes.item' || $item->cmd == 'likes.involved') && in_array($item->context_type, $allowedContexts)) {

			$hook   = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When user is tagged in a photo
		if ($item->cmd == 'videos.tagged' && $item->context_type == 'tagging') {

			$hook   = $this->getHook('notification', 'tagging');
			$hook->execute($item);
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
		$allowed = array('videos.user.create', 'videos.user.featured');

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
		$allowed = array('videos.user.create', 'videos.user.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

		$commentContent = ES::string()->parseEmoticons($comment->comment);

		// Set the email options
		$emailOptions = array(
			'template' => 'apps/user/videos/comment.video.item',
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'comment' => $commentContent
		);

		$systemOptions = array(
			'context_type' => $comment->element,
			'context_ids' => $comment->id,
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true,
			'content' => $commentContent
		);

		// Standard email subject
		$ownerTitle = 'APP_USER_VIDEOS_EMAILS_COMMENT_VIDEO_ITEM_SUBJECT';
		$involvedTitle = 'APP_USER_VIDEOS_EMAILS_COMMENT_VIDEO_INVOLVED_SUBJECT';

		// For single video item on the stream
		$table = ES::table("Video");
		$table->load($comment->uid);

		$video = ES::video($table);

		// Get external permalink for email purpose
		$emailOptions['permalink'] = $video->getExternalPermalink();

		// Get normal permalink
		$systemOptions['url'] = $video->getPermalink(false);

		$element = 'videos';

		if ($comment->element == 'videos.user.create') {
			$verb = 'create';
		}

		if ($comment->element == 'videos.user.featured') {
			$verb = 'featured';
		}

		$emailOptions['title'] = $ownerTitle;

		// Assign points for the author for liking this item
		ES::points()->assign('video.comment.add', 'com_easysocial', $comment->created_by);
		ES::badges()->log('com_easysocial', 'videos.comment', $comment->created_by, '');


		// Notify the owner of the photo first
		if ($video->user_id != $comment->created_by) {
			ES::notify('comments.item', array($video->user_id), $emailOptions, $systemOptions);
		}

		// Get additional recipients since videos has tag
		$additionalRecipients   = array();
		$this->getTagRecipients($additionalRecipients, $video);

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'user', $verb, $additionalRecipients, array($video->user_id, $comment->created_by));

		$emailOptions['title'] = $involvedTitle;
		$emailOptions['template'] = 'apps/user/videos/comment.video.involved';

		// Notify other participating users
		ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);

		return;
	}

}
