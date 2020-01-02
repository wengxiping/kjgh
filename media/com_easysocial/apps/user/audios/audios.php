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

class SocialUserAppAudios extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_AUDIOS) {
			return;
		}

		$audio = ES::table('Audio');
		$audio->load($uid);

		$lib = ES::audio($audio);

		if (!$lib->isViewable()) {
			return false;
		}

		return true;
	}


	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = false;

		if (! $params->get('uploadAudios', true)) {
			$excludeVerb[] = 'create';
		}

		if (! $params->get('featuredAudios', true)) {
			$excludeVerb[] = 'featured';
		}

		if ($excludeVerb !== false) {
			$exclude['audios'] = $excludeVerb;
		}
	}


	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'audios') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			$streamModel = ES::model('Stream');
			$activityItem = $streamModel->getActivityItem($item->id, 'uid');

			$uid = $activityItem[0]->context_id;
			$rule = 'audios.view';
			$context = 'audios';

			if (!$privacy->validate($rule, $uid, $context, $item->actor_id)) {
				$item->cnt = 0;
			}
		}

		return true;
	}

	/**
	 * Responsible to generate the activity logs.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'audios') {
			return;
		}

		// we only process audio upload activity log since 'featured' can only be done by admin.
		if ($item->verb != 'create') {
			return;
		}

		// Get the context id.
		$id = $item->contextId;

		// Load the audio
		$audio = ES::audio($this->my->id, SOCIAL_TYPE_USER);
		$audio->load($id);

		// Get the actor
		$actor = $item->actor;
		$target = false;

		// Determines if the audio is shared on another person's timeline
		if ($item->targets) {
			$target = $item->targets[0];
		}


		$term = $this->getGender($item->actor);

		$this->set('term', $term);
		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('audio', $audio);
		$this->set('stream', $item);

		$count = count($item->contextIds);
		$this->set('count', $count);

		$file = 'user/';

		if ($item->cluster_id && $item->cluster_type == SOCIAL_TYPE_GROUP) {
			$file = 'group/';

			$group = ES::group($item->cluster_id);
			$this->set('cluster', $group);

		} else if ($item->cluster_id && $item->cluster_type == SOCIAL_TYPE_EVENT) {
			$file = 'event/';

			$event = ES::event($item->cluster_id);
			$this->set('cluster', $event);
		} else if ($item->cluster_id && $item->cluster_type == SOCIAL_TYPE_PAGE) {
			$file = 'page/';

			$page = ES::page($item->cluster_id);
			$this->set('cluster', $page);
		}

		$file .= 'title.create';

		$item->display  = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('themes:/site/audios/stream/' . $file);
		$item->content = parent::display('streams/activity_content');

		$privacyRule = 'audios.view';

		if ($includePrivacy) {
			$my = ES::user();

			$streamModel = ES::model('Stream');
			$activityItem = $streamModel->getActivityItem($item->aggregatedItems[0]->uid, 'uid');

			$streamId = count($activityItem) > 1 ? '' : $item->aggregatedItems[0]->uid;
			$item->privacy = ES::privacy($my->id)->form($audio->id, 'audios', $item->actor->id, $privacyRule, false, $streamId);
		}
	}


	/**
	 * Retrieves the Gender representation of the language string
	 *
	 * @since   2.1
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
	 * Generates the stream item for audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != SOCIAL_TYPE_AUDIOS) {
			return;
		}

		// Check for privacy
		$privacy = $this->my->getPrivacy();

		if ($includePrivacy && !$privacy->validate('audios.view', $stream->contextId, SOCIAL_TYPE_AUDIOS, $stream->actor->id)) {
			return;
		}

		// Get the actor
		$actor = $stream->getActor();

		$audio = ES::audio();
		$audio->load($stream->contextId);

		// Ensure that the audio is really published
		if (!$audio->isPublished()) {
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
		$this->set('audio', $audio);
		$this->set('actor', $actor);
		$this->set('cluster', $cluster);

		// Update the stream title
		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;
		$stream->title = parent::display('themes:/site/streams/audios/user/title.' . $stream->verb);
		$stream->preview = parent::display('themes:/site/streams/audios/preview');

		// Assign the comments library
		$stream->comments = $audio->getComments($stream->verb, $stream->uid);

		// Assign the likes library
		$stream->likes = $audio->getLikes($stream->verb, $stream->uid);

		if ($includePrivacy) {;
			$stream->privacy = $privacy->form($stream->contextId, $stream->context, $stream->actor->id, 'audios.view', false, $stream->uid, array(), array('iconOnly' => true));
		}

		// If the audio has an album art, add the opengraph tags
		$albumArt = $audio->getAlbumArt();

		if ($albumArt) {
			$stream->addOgImage($albumArt);
		}

		$stream->addOgDescription($audio->getDescription(false));

		return true;
	}

	/**
	 * Prepares the audio in the story edit form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStoryEditForm(&$story, &$stream)
	{
		// preparing data for story edit.
		$data = array();

		// get audio from this stream uid.
		$model = ES::model('Audios');
		$audio = $model->getStreamAudio($stream->id);

		if ($audio) {
			$data['audio'] = $audio;
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
		// Only process audios
		if ($stream->context_type != 'audios') {
			return;
		}

		// Determine the type of the audio
		$data = array();
		$data['id'] = $this->input->get('audios_id', 0, 'int');
		$data['artist'] = $this->input->get('audios_artist', '', 'default');
		$data['album'] = $this->input->get('audios_album', '', 'default');
		$data['genre_id'] = $this->input->get('audios_genre', 0, 'int');
		$data['description'] = $this->input->get('audios_description', '', 'default');
		$data['iEncoding'] = $this->input->get('audios_isEncoding', false, 'bool');
		$data['link'] = $this->input->get('audios_link', '', 'default');
		$data['title'] = $this->input->get('audios_title', '', 'default');
		$data['source'] = $this->input->get('audios_type', '', 'default');

		$model = ES::model('audios');
		$state = $model->updateStreamAudio($stream->id, $data);

		return true;
	}

	/**
	 * Generates the story form for audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareStoryPanel(SocialStory $story, $isEdit = false, $data = array())
	{
		// Ensure that the user really has access to create audio
		$audio = ES::audio();

		if ($isEdit && isset($data['audio']) && $data['audio']) {
			$audio = $data['audio'];
		}

		if (!$audio->canUpload() && !$audio->canEmbed()) {
			return;
		}

		// Determines if the user can really utilize the audio app
		if (!$this->getApp()->hasAccess($this->my->getProfile()->id)) {
			return;
		}

		// Get a list of audio genre
		$model = ES::model('Audios');
		$options = array('pagination' => false, 'ordering' => 'ordering');

		if (!$this->my->isSiteAdmin()) {
			$options['respectAccess'] = true;
			$options['profileId'] = $this->my->getProfile()->id;
		}

		$genres = $model->getGenres($options);

		// Create a new plugin for this audio
		$plugin = $story->createPlugin('audios', 'panel');

		// Get the maximum file size allowed for audio uploads
		$uploadLimit = $audio->getUploadLimit();

		$title = JText::_('COM_ES_AUDIO');
		$plugin->title = $title;

		$supportedProviders = $audio->getSupportedProviders();
		$supportedProviders = implode(', ', $supportedProviders);

		$theme = ES::themes();
		$theme->set('genres', $genres);
		$theme->set('uploadLimit', $uploadLimit);
		$theme->set('audio', $audio);
		$theme->set('isEdit', $isEdit);
		$theme->set('title', $title);
		$theme->set('supportedProviders', $supportedProviders);
		$button = $theme->output('site/story/audios/button');
		$form = $theme->output('site/story/audios/form');

		$script = ES::script();
		$script->set('uploadLimit', $uploadLimit);
		$script->set('type', SOCIAL_TYPE_USER);
		$script->set('uid', $this->my->id);
		$script->set('audio', $audio);
		$script->set('isEdit', $isEdit);

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/audios/plugin'));

		return $plugin;
	}

	/**
	 * Processes before a story is saved on the site. Before story is stored, we need to save the audio object
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onBeforeStorySave(SocialStreamTemplate &$template, SocialStream &$stream, $content)
	{
		// Only process audios
		if ($template->context_type != 'audios') {
			return;
		}

		// Determine the type of the audio
		$data = array();
		$data['source'] = $this->input->get('audios_type', '', 'word');
		$data['title'] = $this->input->get('audios_title', '', 'default');
		$data['artist'] = $this->input->get('audios_artist', '', 'default');
		$data['album'] = $this->input->get('audios_album', '', 'default');
		$data['description'] = $this->input->get('audios_description', '', 'default');
		$data['genre_id'] = $this->input->get('audios_genre', 0, 'int');

		// We need to format the link first.
		$link = $this->input->get('audios_link', '', 'default');

		// Save options for the audio library
		$saveOptions = array();

		// If this is a link source, we just load up a new audio library
		if ($data['source'] == 'link') {
			$audio = ES::audio();
			$data['link'] = $audio->format($link);
		}

		// If this is an audio upload, the id should be provided because audio are created first.
		if ($data['source'] == 'upload') {
			$id = $this->input->get('audios_id', 0, 'int');

			$audio = ES::audio();
			$audio->load($id);

			// Audio library needs to know that we're storing this from the story
			$saveOptions['story'] = true;

			// We cannot publish the audio if auto encoding is disabled
			if ($this->config->get('audio.autoencode')) {
				$data['state'] = SOCIAL_AUDIO_PUBLISHED;
			}
		}

		$data['uid'] = $this->my->id;
		$data['type'] = SOCIAL_TYPE_USER;

		// Try to save the audio
		$state = $audio->save($data, array(), $saveOptions);

		// We need to update the context
		$template->context_type = SOCIAL_TYPE_AUDIOS;
		$template->context_id = $audio->id;
	}
	/**
	 * Processes after a story is saved on the site. When the story is stored, we need to create the necessary audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		// Only process audios
		if ($template->context_type != 'audios') {
			return;
		}

		// Change the isNew to false
		$table = ES::table('Audio');
		$table->load($streamItem->context_id);
		$table->isnew = 0;
		$table->store();

		// Determine the type of the audio
		$data = array();
		$data['source'] = $this->input->get('audios_type', '', 'word');

		// If this is an audio upload, the id should be provided because audio are created first.
		if ($data['source'] == 'upload') {

			if (!$this->config->get('audio.autoencode')) {
				$streamItem->notice = JText::_('COM_ES_AUDIO_UPLOAD_SUCCESS_AWAIT_PROCESSING_STORY');
			} else {
				// Load the audio
				$audio = ES::audio($table->uid, $table->type, $table->id);

				// Get the status of the audio
				$status = $audio->status();

				// Published the audio
				if ($status === true && $audio->isNew()) {
					$audio->publish(array('createStream' => true));
				}
			}
		}
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		if ($likes->type == 'audios.user.create') {

			$audioTable = ES::table('Audio');
			$audioTable->load($likes->uid);

			$audio = ES::audio($audioTable);

			// since when liking own audio no longer get points,
			// unlike own audio should not deduct point too. #3471
			if ($likes->created_by != $audio->user_id) {
				ES::points()->assign('audio.unlike', 'com_easysocial', $this->my->id);
			}
		}
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('audios.user.create', 'audios.user.featured');

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

			$table = ES::table('Audio');
			$table->load($likes->uid);

			$audio = ES::audio($table);

			$systemOptions['context_ids'] = $audio->id;
			$systemOptions['url'] = $audio->getPermalink(false);

			$element = 'audios';

			if ($likes->type == 'audios.user.create') {
				$verb = 'create';
			}

			if ($likes->type == 'audios.user.featured') {
				$verb = 'featured';
			}

			ES::badges()->log('com_easysocial', 'audios.react', $likes->created_by, '');

			if ($likes->created_by != $audio->user_id) {
				// assign points when the liker is not the audio owner. #3471
				ES::points()->assign('audio.like', 'com_easysocial', $likes->created_by);

				// Notify the owner of the audio
				ES::notify('likes.item', array($audio->user_id), false, $systemOptions);
			}

			$additionalRecipients = array();
			$this->getTagRecipients($additionalRecipients, $audio);

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'user', $verb, $additionalRecipients, array($audio->user_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

	}

	/**
	 * Retrieves a list of tag recipients on an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	private function getTagRecipients(&$recipients, SocialAudio &$audio, $exclusion = array())
	{
		return array();
	}

	/**
	 * Renders the notification item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('comments.item', 'comments.involved', 'likes.item', 'likes.involved', 'audios.tagged', 'likes.likes', 'comments.comment.add');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		$allowedContexts = array('audios.user.create', 'audios.user.featured');

		if (($item->cmd == 'comments.item' || $item->cmd == 'comments.involved') && in_array($item->context_type, $allowedContexts)) {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// When user likes a single audio
		$allowedContexts    = array('audios.user.create', 'audios.user.featured');
		if (($item->cmd == 'likes.item' || $item->cmd == 'likes.involved') && in_array($item->context_type, $allowedContexts)) {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When user is tagged in an audio
		if ($item->cmd == 'audios.tagged' && $item->context_type == 'tagging') {

			$hook = $this->getHook('notification', 'tagging');
			$hook->execute($item);
		}


		return;
	}

	/**
	 * Triggered after a comment is deleted
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterDeleteComment(SocialTableComments &$comment)
	{
		$allowed = array('audios.user.create', 'audios.user.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Assign points when a comment is deleted for an audio
		ES::points()->assign('audio.comment.remove', 'com_easysocial', $comment->created_by);
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('audios.user.create', 'audios.user.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

		$commentContent = ES::string()->parseEmoticons($comment->comment);

		// Set the email options
		$emailOptions = array(
			'template' => 'site/audios/comment.audio.item',
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
		$ownerTitle = 'APP_USER_AUDIO_EMAILS_COMMENT_AUDIO_ITEM_SUBJECT';
		$involvedTitle = 'APP_USER_AUDIO_EMAILS_COMMENT_AUDIO_INVOLVED_SUBJECT';

		// For single audio item on the stream
		$table = ES::table('Audio');
		$table->load($comment->uid);

		$audio = ES::audio($table);

		// Get external permalink for email purpose
		$emailOptions['permalink'] = $audio->getExternalPermalink();

		// Get normal permalink
		$systemOptions['url'] = $audio->getPermalink(false);

		$element = 'audios';

		if ($comment->element == 'audios.user.create') {
			$verb = 'create';
		}

		if ($comment->element == 'audios.user.featured') {
			$verb = 'featured';
		}

		$emailOptions['title'] = $ownerTitle;

		// Assign points for the author for liking this item
		ES::points()->assign('audio.comment.add', 'com_easysocial', $comment->created_by);
		ES::badges()->log('com_easysocial', 'audios.comment', $comment->created_by, '');

		// Notify the owner of the audio first
		if ($audio->user_id != $comment->created_by) {
			ES::notify('comments.item', array($audio->user_id), $emailOptions, $systemOptions);
		}

		// Get additional recipients since audios has tag
		$additionalRecipients = array();
		$this->getTagRecipients($additionalRecipients, $audio);

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'user', $verb, $additionalRecipients, array($audio->user_id, $comment->created_by));

		$emailOptions['title'] = $involvedTitle;
		$emailOptions['template'] = 'site/audios/comment.audio.involved';

		// Notify other participating users
		ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);

		return;
	}

}
