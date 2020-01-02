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

class SocialUserAppStory extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'story') {
			return;
		}

		// the only place that user can submit coments / react on this app is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}


	/**
	 * When someone likes the status update
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		// Set the default element.
		$element = $likes->type;
		$verb = '';
		$uid = $likes->uid;

		if (strpos($element, '.') !== false) {
			$data = explode('.', $element);
			$group = $data[1];
			$element = $data[0];
			$verb = isset($data[2]) ? $data[2] : '';
		}

		// When a user likes a comment
		if ($element == 'comments') {

			// For this, we only have 1 recipient since the comment itself was posted by a user
			$comment = ES::table('Comments');
			$comment->load($uid);

			// Get the actor that likes the comment
			$actor = ES::user($likes->created_by);

			// Get the comment url
			$permalink = $comment->getPermalink();

			$systemOptions  = array(
									'context_type' => $likes->type,
									'url' => $permalink,
									'actor_id' => $likes->created_by,
									'uid' => $likes->uid,
									'aggregate' => true
								);


			// Notify the owner of the comment first
			if ($comment->created_by != $likes->created_by) {
				ES::notify('comments.like', array($comment->created_by), false, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'comments', 'user', 'like', array(), array($comment->created_by, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

		// When a user likes a story
		if ($element == 'story') {

			// Since the uid is tied to the album we can get the album object
			$stream = ES::table('Stream');
			$stream->load($likes->uid);

			// Get the actor of the likes
			$actor = ES::user($likes->created_by);

			$systemOptions  = array(
				'context_type' => $likes->type,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// Notify the owner of the photo first
			if ($likes->created_by != $stream->actor_id) {
				ES::notify('likes.item', array($stream->actor_id), false, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'user', $verb, array(), array($stream->actor_id, $likes->created_by));

			// Notify other participating users
			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}
	}

	/**
	 * Triggered before comments notify subscribers
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('story.user.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// For likes on albums when user uploads multiple photos within an album
		if ($comment->element == 'story.user.create') {

			// Since the uid is tied to the album we can get the album object
			$stream = ES::table('Stream');
			$stream->load($comment->uid);

			// Get the actor of the likes
			$actor = ES::user($comment->created_by);

			$owner = ES::user($stream->actor_id);

			$commentContent = ES::string()->parseEmoticons($comment->comment);

			// Set the email options
			$emailOptions = array(
				'title' => 'APP_USER_STORY_EMAILS_COMMENT_STATUS_ITEM_SUBJECT',
				'template' => 'apps/user/story/comment.status.item',
				'permalink' => $stream->getPermalink(true, true),
				'comment' => $commentContent,
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true),
				'target' => $owner->getName(),
				'targetLink' => $owner->getPermalink(true, true)
			);

			$systemOptions  = array(
				'context_type' => $comment->element,
				'context_ids' => $comment->id,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $comment->created_by,
				'uid' => $comment->uid,
				'aggregate' => true,
				'content' => $commentContent
			);

			// Notify the owner of the photo first
			if ($stream->actor_id != $comment->created_by) {
				ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($comment->uid, 'story', 'user', 'create', array(), array($stream->actor_id, $comment->created_by));

			$emailOptions['title'] = 'APP_USER_STORY_EMAILS_COMMENT_STATUS_INVOLVED_SUBJECT';
			$emailOptions['template'] = 'apps/user/story/comment.status.involved';

			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);

			return;
		}
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		if ($comment->element != 'story.user.create') {
			return;
		}

		// Here we know that comments associated with story is always
		// comment.id = notification.context_id
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithContextId($comment->id, $comment->element);
	}

	/**
	 * Renders the notification item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if ($item->type == 'comments' && $item->cmd == 'comments.like') {
			$item->context_type = 'comments.user.like';

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		$allowed = array('likes.item', 'likes.involved', 'comments.item', 'comments.involved', 'comments.like', 'story.tagged', 'stream.tagged', 'story.story', 'comments.tagged');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		// When someone likes on a status update
		$allowedContexts = array('story.user.create', 'story');
		if ($item->type == 'likes' && ($item->cmd == 'likes.item' || $item->cmd == 'likes.involved') && in_array($item->context_type, $allowedContexts)) {

			// @legacy
			$item->context_type = 'story.user.create';

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When someone comments on a status update
		$allowedContexts = array('story.user.create', 'story');
		if ($item->type == 'comments' && ($item->cmd == 'comments.item' || $item->cmd == 'comments.involved') && in_array($item->context_type, $allowedContexts)) {

			// @legacy
			$item->context_type = 'story.user.create';

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// When someone likes a comment
		$allowedContexts = array('comments.user.like');

		if ($item->type == 'likes' && ($item->cmd == 'likes.item' || $item->cmd == 'likes.involved') && in_array($item->context_type, $allowedContexts)) {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When a user is mentioned using @
		if ($item->cmd == 'story.tagged') {

			$hook = $this->getHook('notification', 'mentions');
			$hook->execute($item);

			return;
		}

		// When a user is tagged using the "with" method
		if ($item->cmd == 'stream.tagged') {

			$hook = $this->getHook('notification', 'tagged');
			$hook->execute($item);

			return;
		}

		// When user posts on another person's timeline
		if ($item->cmd == 'story.story' && $item->context_type == 'post.user.timeline') {

			$hook = $this->getHook('notification', 'story');
			$hook->execute($item);

			return;
		}

		if ($item->cmd == 'comments.tagged' && $item->context_type == 'comments.user.tagged') {

			$hook = $this->getHook('notification', 'tagged');
			$hook->execute($item);

			return;
		}
	}

	/**
	 * Prepares the activity log for user's actions
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function onPrepareActivityLog(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != 'story') {
			return;
		}

		// Stories wouldn't be aggregated
		$actor = $stream->actor;
		$target = count($stream->targets) > 0 ? $stream->targets[0] : '';

		$stream->display = SOCIAL_STREAM_DISPLAY_MINI;

		// @triggers: onPrepareStoryContent
		// Processes any apps to process the content.
		ES::apps()->load(SOCIAL_TYPE_USER);

		$args = array(&$story , &$stream);
		$dispatcher = ES::dispatcher();

		$result = $dispatcher->trigger(SOCIAL_TYPE_USER , 'onPrepareStoryContent' , $args);

		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('stream', $stream);
		$this->set('result', $result);

		$stream->title = parent::display('logs/title.' . $stream->verb);
		$stream->content = parent::display('logs/content.' . $stream->verb);

		if ($includePrivacy) {
			$privacy = $this->my->getPrivacy();
			$stream->privacy = $privacy->form($stream->uid, SOCIAL_TYPE_STORY, $stream->actor->id, 'story.view', false, $stream->aggregatedItems[0]->uid, array(), array('iconOnly' => true));
		}

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	jos_social_stream, boolean
	 * @return  0 or 1
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'story') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			if (!$privacy->validate('story.view', $item->id , SOCIAL_TYPE_STORY , $item->actor_id)) {
				$item->cnt = 0;
			}
		}

		return true;
	}


	/**
	 * Triggered to prepare the stream item
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($stream->context != 'story') {
			return;
		}

		// Get the privacy object
		$privacy = $this->my->getPrivacy();

		// Allow editing of the stream item
		$stream->editable = $this->my->isSiteAdmin() || $stream->actor->id == $this->my->id;

		// we stil need to check for the privacy because the context might come from the 'repost'
		if ($includePrivacy && !$privacy->validate('story.view', $stream->uid, SOCIAL_TYPE_STORY, $stream->actor->id)) {
			return;
		}

		// Actor of this stream
		$actor = $stream->actor;
		$target = count($stream->targets) > 0 ? $stream->targets[0] : '';

		// Get stream params
		$streamParams = $stream->getParams();
		$articleStream = false;
		$articleStreamLink = '';
		$articleStreamTitle = '';

		if ($streamParams->get('articlestream', false)) {
			$articleStream = true;
			$articleStreamLink = $streamParams->get('permalink', '');
			$articleStreamTitle = $streamParams->get('title', '');
		}

		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Apply actions on the stream
		$stream->likes = ES::likes($stream->uid, $stream->context, $stream->verb, SOCIAL_APPS_GROUP_USER, $stream->uid);
		$stream->comments = ES::comments($stream->uid, $stream->context, $stream->verb, SOCIAL_APPS_GROUP_USER, array('url' => $stream->getPermalink(false, false, false)), $stream->uid);
		$stream->repost = ES::repost($stream->uid , SOCIAL_TYPE_STREAM, SOCIAL_APPS_GROUP_USER);

		// Get application params
		$params = $this->getParams();

		$this->set('articleStreamTitle', $articleStreamTitle);
		$this->set('articleStreamLink', $articleStreamLink);
		$this->set('articleStream', $articleStream);
		$this->set('params', $params);
		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('stream', $stream);

		$stream->title = parent::display('themes:/site/streams/story/user/title');
		$stream->addOgDescription($stream->content);

		if ($includePrivacy) {
			$stream->privacy = $privacy->form($stream->uid, SOCIAL_TYPE_STORY, $stream->actor->id, 'story.view', false, $stream->uid, array(), array('iconOnly' => true));
		}

		return true;
	}
}
