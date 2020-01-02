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

class SocialEventAppStory extends SocialAppItem
{
	/**
	 * Handles onAfterLikeSave event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onAfterLikeSave(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		$data = explode('.', $likes->type);

		$element = array_shift($data);

		if ($element != 'story') {
			return;
		}

		$event = array_shift($data);
		$verb = array_shift($data);

		$uid = $likes->uid;

		$stream = ES::table('Stream');
		$stream->load($uid);

		$actor = ES::user($likes->created_by);

		$systemOptions = array(
			'context_type' => $likes->type,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		// Notify the owner first
		ES::notify('likes.item', array($stream->actor_id), false, $systemOptions);

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the item and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $event, $verb, array(), array($stream->actor_id, $likes->created_by));

		// Notify other participating users
		ES::notify('likes.involved', $recipients, false, $systemOptions);
	}

	/**
	 * Handles onAfterCommentSave event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('story.event.create', 'links.event.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		list($element, $event, $verb) = explode('.', $comment->element);

		$stream = ES::table('Stream');
		$stream->load($comment->uid);

		$actor = ES::user($comment->created_by);

		$commentContent = ES::string()->parseEmoticons($comment->comment);

		$emailOptions = array(
			'title' => 'APP_EVENT_STORY_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/event/story/comment.item',
			'comment' => $commentContent,
			'permalink' => $stream->getPermalink(true, true),
			'posterName' => $actor->getName(),
			'posterAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'posterLink' => $actor->getPermalink(true, true)
		);

		$systemOptions  = array(
			'content' => $comment->comment,
			'context_type' => $comment->element,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		// Notify the note owner
		ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions);

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the item and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $event, $verb, array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title'] = 'APP_EVENT_STORY_EMAILS_COMMENT_ITEM_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/event/story/comment.involved';

		// Notify participating users
		ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
	}

	/**
	 * Processes notifications.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('story.event.create', 'links.create', 'photos.event.share');
		if ($item->type == 'likes' && in_array($item->context_type, $allowed)) {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		$allowed = array('story.event.create', 'links.event.create', 'photos.event.share');
		if ($item->type == 'comments' && in_array($item->context_type, $allowed)) {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		$allowed = array('story.event.create', 'links.event.create', 'photos.event.share', 'file.event.uploaded');

		if ($item->cmd == 'events.updates' && (in_array($item->context_type, $allowed))) {

			$hook = $this->getHook('notification', 'updates');
			$hook->execute($item);

			return;
		}
	}

	/**
	 * Handles onAfterStorySave trigger.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onAfterStorySave(SocialStream &$stream, SocialTableStreamItem &$streamItem, SocialStreamTemplate &$template)
	{
		// Determine if this is for a event
		if (!$template->cluster_id) {
			return;
		}

		// Now we only want to allow specific context
		$context = $template->context_type . '.' . $template->verb;
		$allowed = array('story.create', 'links.create', 'photos.share');

		if (!in_array($context, $allowed)) {
			return;
		}

		$event = ES::event($template->cluster_id);

		$actor = ES::user($streamItem->actor_id);

		// Get number of event members
		$targets = $event->getTotalMembers();

		// If there's nothing to send skip this altogether.
		if (!$targets) {
			return;
		}

		// Get the item's permalink
		$permalink = ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true);

		$contents = $template->content;

		// break the text and images
		if (strpos($template->content, '<img') !== false) {
			preg_match('#(<img.*?>)#', $template->content, $results);

			$img = "";
			if ($results) {
				$img = $results[0];
			}

			$segments = explode('<img', $template->content);
			$contents = $segments[0];

			if ($img) {
				$contents = $contents . '<br /><div style="text-align:center;">' . $img . "</div>";
			}
		}

		$data = array(
				'userId' => $actor->id,
				'content' => $contents,
				'permalink' => ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true),
				'title' => 'APP_EVENT_STORY_EMAILS_NEW_POST_IN_EVENT',
				'template' => 'apps/event/story/new.post',
				'uid' => $streamItem->uid,
				'context_type' => $template->context_type . '.event.' . $template->verb,
				'system_content' => $template->content
			);

		$event->notifyMembers('story.updates', $data);
	}

	/**
	 * Trigger to prepare a stream object.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onPrepareStream(SocialStreamItem &$item)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context != 'story') {
			return;
		}

		// Get the event object
		$event = $item->getCluster();

		if (!$event) {
			return;
		}

		if (!$event->canViewItem()) {
			return;
		}

		$access = $event->getAccess();

		// Allow editing of the stream item
		$item->editable = $this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id);

		// Get the actor
		$actor = $item->getActor();

		// Decorate the stream
		$item->fonticon = 'fa fa-pencil-alt';
		$item->color = '#6E9545';
		$item->label = ES::_('APP_EVENT_STORY_STREAM_TOOLTIP', true);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$this->set('event', $event);
		$this->set('actor', $actor);
		$this->set('stream', $item);

		$item->title = parent::display('streams/title.' . $item->verb);

		// Apply likes on the stream
		$likes = ES::likes();
		$likes->get($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, $item->uid);

		$item->likes = $likes;

		// If this update is posted in a event, the comments should be linked to the event item
		$comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $item->cluster_id), $item->uid);
		$item->comments = $comments;

		// Append the opengraph tags
		$item->addOgDescription($item->content);

		return true;
	}
}
