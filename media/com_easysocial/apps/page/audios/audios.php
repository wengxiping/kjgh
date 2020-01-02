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

class SocialPageAppAudios extends SocialAppItem
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
	 * @since   2.1
	 * @access  public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = false;

		if (!$params->get('uploadAudios', true)) {
			$excludeVerb[] = 'create';
		}

		if (!$params->get('featuredAudios', true)) {
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

		$params = ES::registry($item->params);
		$page = ES::page($params->get('page'));

		if (!$page) {
			return;
		}

		$item->cnt = 1;

		if (!$page->isOpen() && !$page->isMember($this->my->id)) {
			$item->cnt = 0;
		}

		return true;
	}

	/**
	 * Generates the stream item for audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != SOCIAL_TYPE_AUDIOS) {
			return;
		}

		// Determines if the viewer can view the stream item from this page
		$page = $stream->getCluster();

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		// Decorate the stream item with the neccessary design
		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get the audio
		$audio = ES::audio($stream->cluster_id, SOCIAL_TYPE_PAGE, $stream->contextId);

		// Ensure that the audio is really published
		if (!$audio->isPublished()) {
			return;
		}

		// Set the actor alias
		$actor = $stream->getPostActor($page);

		$perspective = $stream->getPerspective() == 'PAGES' ? 'CLUSTERS' : 'STREAM';

		$this->set('stream', $stream);
		$this->set('audio', $audio);
		$this->set('actor', $actor);
		$this->set('page', $page);
		$this->set('perspective', $perspective);

		$access = $page->getAccess();
		if ($this->my->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $stream->actor->id == $this->my->id)) {
			$stream->editable = true;
			$stream->appid = $this->getApp()->id;
		}

		// Update the stream title
		$stream->title = parent::display('themes:/site/streams/audios/page/title.' . $stream->verb);
		$stream->preview = parent::display('themes:/site/streams/audios/preview');

		// For Page, we need to pass the page id in order to use custom author for comment
		$stream->comments = $audio->getComments($stream->verb, $stream->uid);
		$stream->likes = $audio->getLikes($stream->verb, $stream->uid);

		// If the audio has a album Art, add the opengraph tags
		$albumArt = $audio->getAlbumArt();

		if ($albumArt) {
			$stream->addOgImage($albumArt);
		}

		// Append the opengraph tags
		$stream->addOgDescription($audio->getDescription(false));
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
	 * Generates the story form for audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareStoryPanel(SocialStory $story, $isEdit = false, $data = array())
	{
		// Get the page id
		$pageId = $story->cluster;

		// Get the audio adapter
		$adapter = ES::audio($pageId, SOCIAL_TYPE_PAGE);

		$page = ES::page($pageId);

		if (!$adapter->allowUpload() && !$adapter->allowEmbed()) {
			return;
		}

		// Ensure that audio creation is allowed
		if (!$page->canAccessAudios() || !$adapter->allowCreation() || !$page->canCreateAudios()) {
			return;
		}

		// In story panel, we only allow page admin and page members to share audio
		if (!$page->isAdmin() && !$page->isMember()) {
			return;
		}

		// Get a list of audio genres
		$model = ES::model('Audios');
		$options = array('pagination' => false, 'ordering' => 'ordering');

		if (!$this->my->isSiteAdmin()) {
			$options['respectAccess'] = true;
			$options['profileId'] = $this->my->getProfile()->id;
		}

		$genres = $model->getGenres($options);

		if ($isEdit && isset($data['audio']) && $data['audio']) {
			$adapter = $data['audio'];
		}

		// Create a new plugin for this audio
		$plugin = $story->createPlugin('audios', 'panel');

		$title = JText::_('COM_ES_AUDIO');
		$plugin->title = $title;

		// Get the maximum upload filesize allowed
		$uploadLimit = $adapter->getUploadLimit();

		$supportedProviders = $adapter->getSupportedProviders();
		$supportedProviders = implode(', ', $supportedProviders);

		$theme = ES::themes();
		$theme->set('genres', $genres);
		$theme->set('uploadLimit', $uploadLimit);
		$theme->set('audio', $adapter);
		$theme->set('isEdit', $isEdit);
		$theme->set('title', $plugin->title);
		$theme->set('supportedProviders', $supportedProviders);

		$button = $theme->output('site/story/audios/button');
		$form = $theme->output('site/story/audios/form');

		$script = ES::script();
		$script->set('uploadLimit', $uploadLimit);
		$script->set('type', SOCIAL_TYPE_PAGE);
		$script->set('uid', $pageId);
		$script->set('audio', $adapter);
		$script->set('isEdit', $isEdit);

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/audios/plugin'));

		return $plugin;
	}

	/**
	 * Processes after a story is saved on the site. When the story is stored, we need to create the necessary audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onBeforeStorySave(SocialStreamTemplate &$template, SocialStream &$stream, $content)
	{
		if ($template->context_type != 'audios') {
			return;
		}

		// Check if user is really allowed to do this?
		$cluster = ES::cluster($template->cluster_type, $template->cluster_id);

		if (!$cluster->canCreateAudios()) {
			JError::raiseError(500, JText::_('COM_EASYSOCIAL_CLUSTER_NOT_ALLOWED_TO_POST_UPDATE'));
			return;
		}

		// Determine the type of the audio
		$data = array();
		$data['source'] = $this->input->get('audios_type', '', 'word');
		$data['title'] = $this->input->get('audios_title', '', 'default');
		$data['description'] = $this->input->get('audios_description', '', 'default');
		$data['link'] = $this->input->get('audios_link', '', 'default');
		$data['genre_id'] = $this->input->get('audios_genre', 0, 'int');
		$data['uid'] = $template->cluster_id;
		$data['type'] = $template->cluster_type;

		// The audio author will follow the stream Post As value
		$data['post_as'] = $template->post_as ? $template->post_as : SOCIAL_TYPE_USER;

		// Save options for the audio library
		$saveOptions = array();

		// If this is a link source, we just load up a new audio library
		if ($data['source'] == 'link') {
			$audio = ES::audio($template->cluster_id, SOCIAL_TYPE_PAGE);
		}

		// If this is an audio upload, the id should be provided because audios are created first.
		if ($data['source'] == 'upload') {
			$id = $this->input->get('audios_id', 0, 'int');

			$audio = ES::audio($template->cluster_id, SOCIAL_TYPE_PAGE);
			$audio->load($id);

			// Audio library needs to know that we're storing this from the story
			$saveOptions['story'] = true;

			// We cannot publish the audio if auto encoding is disabled
			if ($this->config->get('audio.autoencode')) {
				$data['state'] = SOCIAL_AUDIO_PUBLISHED;
			}
		}

		// Check if user is really allowed to upload audios
		if ($audio->id && !$audio->canEdit()) {
			return JError::raiseError(500, JText::_('COM_ES_AUDIO_NOT_ALLOWED_EDITING'));
		}

		// Try to save the audio
		$state = $audio->save($data, array(), $saveOptions);

		// We should set this to hide the stream from being displayed.
		$stream->hidden = true;

		// We need to update the context
		$template->context_type = SOCIAL_TYPE_AUDIOS;
		$template->context_id = $audio->id;

		$options = array();
		$options['userId'] = $this->my->id;
		$options['title'] = $audio->title;
		$options['description'] = $audio->getDescription();
		$options['permalink'] = $audio->getPermalink();
		$options['id'] = $audio->id;

		// Only notify followers if the updates is came from Page
		if ($template->post_as == SOCIAL_TYPE_PAGE) {
			$cluster->notifyMembers('audio.create', $options);
		}
	}

	public function onAfterStorySave(&$stream, &$streamItem)
	{
		// Determine the type of the audio
		$data = array();
		$data['source'] = $this->input->get('audios_type', '', 'word');

		// If this is an audio upload, the id should be provided because audio are created first.
		if ($data['source'] == 'upload' && !$this->config->get('audio.autoencode')) {
			$streamItem->hidden = true;
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

		// Deduct points when the user unliked an audio
		if ($likes->type == 'audios.page.create' || $likes->type == 'audios.page.featured') {

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
		$allowed = array('audios.page.create', 'audios.page.featured');

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

		$audioTable = ES::table('Audio');
		$audioTable->load($likes->uid);

		$audio = ES::audio($audioTable->uid, $audioTable->type, $audioTable);

		// Get the page
		$page = ES::page($audio->uid);

		// Get the permalink to the audio
		$systemOptions['context_ids'] = $audio->id;
		$systemOptions['url'] = $audio->getPermalink(false);
		$verb = 'create';

		// For single audio items on the stream
		if ($likes->type == 'audios.user.create') {
			$verb = 'create';
		}

		if ($likes->type == 'audios.user.featured') {
			$verb = 'featured';
		}

		ES::badges()->log('com_easysocial', 'audios.react', $likes->created_by, '');

		// assign points when the liker is not the audio owner. #3471
		if ($likes->created_by != $audio->user_id) {
			ES::points()->assign('audio.like', 'com_easysocial', $likes->created_by);
		}

		// Notify the owner of the audio first
		if ($likes->created_by != $audio->user_id && $audio->post_as != SOCIAL_TYPE_PAGE) {
			ES::notify('likes.item', array($audio->user_id), false, $systemOptions, $page->notification);
		}

		// If this audio is post as Page, notify all the page admin
		if ($audio->post_as == SOCIAL_TYPE_PAGE) {
			ES::notify('likes.item', $page->getAdmins($likes->created_by), false, $systemOptions, $page->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, 'audios', 'page', $verb, array(), array($audio->user_id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions, $page->notification);

		return;
	}

	/**
	 * Renders the notification item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('page.audio.create', 'comments.item', 'comments.involved', 'likes.item');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		if ($item->cmd == 'page.audio.create') {
			$hook = $this->getHook('notification', 'updates');
			$hook->execute($item);

			return;
		}

		// Someone posted a comment on the audio
		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.involved') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// Someone likes an audio
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
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterDeleteComment(SocialTableComments &$comment)
	{
		$allowed = array('audios.page.create', 'audios.page.featured');

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
		$allowed = array('audios.page.create', 'audios.page.featured');

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

		$systemOptions  = array(
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

		$audioTable = ES::table('Audio');
		$audioTable->load($comment->uid);

		$audio = ES::audio($audioTable->uid, $audioTable->type, $audioTable);

		// Get the page
		$page = ES::page($audio->uid);

		$emailOptions['permalink'] = $audio->getPermalink(true, true);
		$systemOptions['url'] = $audio->getPermalink(false, false, 'item', false);

		$verb = 'create';

		// Default email title should be for the owner
		$emailOptions['title'] = $ownerTitle;

		// Assign points for the author for posting a comment
		ES::points()->assign('audios.comment.add', 'com_easysocial', $comment->created_by);
		ES::badges()->log('com_easysocial', 'audios.comment', $comment->created_by, '');

		// Notify the owner of the audio first
		if ($audio->user_id != $comment->created_by && $audio->post_as != SOCIAL_TYPE_PAGE) {
			ES::notify('comments.item', array($audio->user_id), $emailOptions, $systemOptions, $page->notification);
		}

		// If this audio is post as Page, notify all the page admin
		if ($audio->post_as == SOCIAL_TYPE_PAGE) {
			ES::notify('comments.item', $page->getAdmins($comment->created_by), $emailOptions, $systemOptions, $page->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'audios', 'page', $verb, array(), array($audio->user_id, $comment->created_by));

		$emailOptions['title'] = $involvedTitle;
		$emailOptions['template'] = 'site/audios/comment.audio.involved';

		// Notify other participating users
		ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $page->notification);

		return;
	}

}
