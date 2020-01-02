<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/helper.php');

class SocialGroupAppPolls extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_POLLS) {
			return;
		}

		return false;
	}

	/**
	 * Determines if polls should appear on the sidebar of the group
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function appListing($context, $id)
	{
		$group = ES::group($id);

		if (!$group->canAccessPolls()) {
			return false;
		}

		return true;
	}

	/**
	 * Prepares the notifications for poll items
	 *
	 * @since	3.1.5
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('group.polls.create', 'groups.polls.vote.item', 'comments.item', 'comments.involved', 'likes.item');
		$group = ES::group($item->uid);

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		if ($item->cmd == 'group.polls.create') {
			$poll = ES::table('Polls');
			$poll->load(array('uid' => $item->uid));

			$item->title = JText::sprintf('APP_GROUP_POLLS_USER_CREATED_A_POLL', $item->getActor(), $group->title);
			$item->content = $poll->title;

			return $item;
		}

		if ($item->cmd == 'likes.item') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// Someone voted on the group poll
		if ($item->cmd == 'groups.polls.vote.item') {
			$hook = $this->getHook('notification', 'vote');
			$hook->execute($item);

			return;
		}

		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.involved') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		return;
	}

	/**
	 * Processes a before saved story.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onBeforeStorySave(&$template, &$stream, &$content)
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_polls', true)) {
			return;
		}

		// Check if the user is really allowed to create polls in a group
		$cluster = ES::cluster($template->cluster_type, $template->cluster_id);
		$access = $cluster->getAccess();

		if (!$cluster->canAccessPolls() || !$cluster->canCreatePolls()) {
			return false;
		}

		$in = ES::input();

		$title = $in->getString('polls_title', '');
		$multiple = $in->getInt('polls_multiple', 0);
		$expiry = $in->getString('polls_expirydate', '');
		$items = $in->get('polls_items', array(), 'array');
		$element = $in->getString('polls_element', 'stream');
		$uid = $in->getInt('polls_uid', 0);
		$sourceid = $in->getInt('polls_sourceid', 0);

		if (empty($title) || empty($items)) {
			return;
		}

		$my = ES::user();

		$poll = ES::get('Polls');
		$polltmpl = $poll->getTemplate();

		$polltmpl->setTitle($title);
		$polltmpl->setCreator($my->id);
		$polltmpl->setContext($uid, $element);
		$polltmpl->setMultiple($multiple);
		$polltmpl->setCluster($sourceid);

		if ($expiry) {
			$polltmpl->setExpiry($expiry);
		}

		if ($items) {
			foreach($items as $itemOption) {
				$polltmpl->addOption($itemOption);
			}
		}

		// polls creation option
		$saveOptions = array('createStream' => false);

		$pollTbl = $poll->create($polltmpl, $saveOptions);

		$template->context_type = SOCIAL_TYPE_POLLS;
		$template->context_id = $pollTbl->id;

		$params = array(
			'poll' => $pollTbl
		);

		$template->setParams(ES::json()->encode($params));
	}

	/**
	 * Processes a saved story.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_polls', true)) {
			return;
		}

		if ( ($streamItem->context_type != SOCIAL_TYPE_POLLS) || (! $streamItem->context_id)) {
			return;
		}

		//load poll item and assign uid.
		$poll = ES::table('Polls');
		$state = $poll->load($streamItem->context_id);

		if ($state) {
			$poll->uid = $streamItem->uid;
			$poll->store();

			// We need to notify the group members
			$this->notify($stream, $streamItem, $template);

			// reset the stream privacy to use polls.view privacy instead of story.view
			// $poll->updateStreamPrivacy($streamItem->uid);
		}

		return true;
	}

	/**
	 * Trigger for onPrepareDigest
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareDigest(SocialStreamItem &$item)
	{
		if ($item->context != SOCIAL_TYPE_POLLS) {
			return;
		}

		$pollId = $item->contextId;
		$actor = $item->actor;

		$poll = ES::table('Polls');
		$exists = $poll->load($pollId);

		if (!$exists) {
			return;
		}

		$item->title = '';
		$item->link = $poll->getPermalink(true, true);

		// for now we only process member join feed.
		if ($item->verb == 'create') {
			$item->title = JText::sprintf('COM_ES_APP_POLLS_DIGEST_NEW_POLLS_TITLE', $actor->getName(), $poll->title);
		}
	}


	/**
	 * Prepares the stream item.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_POLLS) {
			return;
		}

		// Determines if the stream should be generated
		$params = $this->getParams();

		if ( !$params->get( 'stream_' . $item->verb, true)) {
			return;
		}

		$my = ES::user();
		$privacy = $my->getPrivacy();

		// Get the group
		$group = ES::group($item->cluster_id);

		// privacy validation
		if ($includePrivacy && !$privacy->validate( 'polls.view', $item->contextId , SOCIAL_TYPE_POLLS, $item->actor->id)) {
			return;
		}

		$permalink  = FRoute::stream(array('layout' => 'item', 'id' => $item->uid));
		$pollId = $item->contextId;
		$actor = $item->actor;

		$poll = ES::get('Polls');
		$contents = $poll->getDisplay($pollId);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->label = JText::_('APP_POLLS_STREAM', true);

		$pollTbl = ES::table('Polls');
		$pollTbl->load($pollId);

		$this->set('cluster', $group);
		$this->set('actor', $actor);
		$this->set('poll', $pollTbl);
		$this->set('contents', $contents);
		$this->set('permalink', $permalink);

		$item->comments = ES::comments($item->contextId, SOCIAL_TYPE_POLLS, $item->verb, SOCIAL_APPS_GROUP_GROUP, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)),'clusterId' => $item->cluster_id), $item->uid);
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_TYPE_GROUP, $item->uid);

		$item->title = parent::display('themes:/site/streams/polls/cluster/title.' . $item->verb);
		$item->preview  = parent::display('themes:/site/streams/polls/preview');

		// we need to determine if current user can edit this poll or not.
		$item->editablepoll = ($my->id == $pollTbl->created_by || $my->isSiteAdmin()) ? true : false;

		if ($includePrivacy) {
			$item->privacy  = $privacy->form( $item->contextId, $item->context, $item->actor->id, 'polls.view', false, $item->uid );
		}

		return true;
	}

	/**
	 * Prepares the story panel
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStoryPanel($story)
	{
		if (!$this->config->get('polls.enabled') || !$this->my->canCreatePolls()) {
			return;
		}

		$params = $this->getParams();

		// Load the group
		$group = ES::group($story->cluster);
		$access = $group->getAccess();

		// Polls has been disabled explicitly
		if (!$access->get('polls.enabled', true)) {
			return;
		}

		if (!$group->canCreatePolls() || !$this->getApp()->hasAccess($group->category_id)) {
			return;
		}

		// We only allow polls creation on dashboard, which means if the story target and current logged in user is different, then we don't show this
		// Empty target is also allowed because it means no target.
		if (!empty($story->target) && $story->target != $this->my->id) {
			return;
		}

		// Determine if we should attach ourselves here.
		if( !$params->get( 'story_polls' , true ) )
		{
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('polls', 'panel');

		// We need to attach the button to the story panel
		$theme = ES::themes();

		// content. need to get the form from poll lib.
		$poll = ES::get('Polls');
		$form = $poll->getForm(SOCIAL_TYPE_STREAM, 0, '', $group->id);
		$theme->set('form', $form);
		$theme->set('group', $group);
		$theme->set('title', $plugin->title);

		// Attachment script
		$script = ES::script();

		$button = $theme->output('site/story/polls/button');
		$form = $theme->output('site/story/polls/form');

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/polls/plugin'));

		return $plugin;
	}

	public function notify($stream, $streamItem, $template)
	{
		// When a user posts a new story in a group, we need to notify the group members
		$group = ES::group($template->cluster_id);

		$table = ES::table('Polls');
		$table->load($template->context_id);

		$options = array(
			'userId' => $this->my->id,
			'title' => $table->title,
			'permalink' => ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true),
			'id' => $table->id,
		);

		$group->notifyMembers('polls.create', $options);
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('polls.group.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($likes->created_by);

		// Load the stream item
		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		// We need to get the context id retrieve the correct polls id
		$streamItem = ES::table('StreamItem');
		$streamItem->load(array('uid' => $likes->stream_id));

		// Load the current group.
		$polls = ES::table('Polls');
		$polls->load($streamItem->context_id);

		$group = ES::group($polls->cluster_id);

		$systemParams = array();
		$systemParams['context_type'] = $likes->type;
		$systemParams['title'] = JText::sprintf('APP_GROUP_STORY_SYSTEM_LIKES_YOUR_POLLS_INSIDE_GROUP', $actor->getName(), $group->getName(), $polls->title);
		$systemParams['url'] = ESR::stream(array('id' => $stream->id, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $stream->id;
		$systemParams['context_ids'] = $group->id;
		$systemParams['aggregate'] = true;

		// Notify other participating users
		if ($actor->id != $stream->actor_id) {
			ES::notify('likes.item', array($stream->actor_id), false, $systemParams, $group->notification);
		}

		return;
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('polls.group.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

		// Load the stream item
		$stream = ES::table('Stream');
		$stream->load($comment->stream_id);

		// Load the current group.
		$polls = ES::table('Polls');
		$polls->load($comment->uid);

		$group = ES::group($polls->cluster_id);

		$commentContent = ES::string()->parseEmoticons($comment->comment);

		// Prepare the email params
		$mailParams = array();
		$mailParams['comment'] = $commentContent;
		$mailParams['actor'] = $actor->getName();
		$mailParams['posterAvatar'] = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['posterLink'] = $actor->getPermalink(true, true);
		$mailParams['actorAvatar'] = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['actorLink'] = $actor->getPermalink(true, true);

		$mailParams['group'] = $group->getName();
		$mailParams['groupLink'] = $group->getPermalink(true, true);
		$mailParams['permalink'] = $stream->getPermalink();
		$mailParams['title'] = 'APP_GROUP_STORY_EMAILS_COMMENT_YOUR_POLLS_INSIDE_GROUP';
		$mailParams['template'] = 'apps/group/polls/comment.polls.item';

		// Prepare the system notification params
		$systemParams = array();
		$systemParams['context_type'] = $comment->element;
		$systemParams['title'] = JText::sprintf('APP_GROUP_STORY_SYSTEM_COMMENT_YOUR_POLLS_INSIDE_GROUP', $actor->getName(), $group->getName(), $polls->title);
		$systemParams['url'] = ESR::stream(array('id' => $stream->id, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $comment->uid;
		$systemParams['content'] = $comment->comment;
		$systemParmas['context_ids'] = $group->id;
		$systemParams['aggregate'] = true;

		// Notify the owner of the photo first
		if ($comment->created_by != $polls->created_by) {
			ES::notify('comments.item', array($polls->created_by), $mailParams, $systemParams, $group->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'polls', 'group', 'create', array(), array($polls->created_by, $comment->created_by));

		$mailParams['title'] = 'APP_GROUP_POLLS_EMAILS_COMMENT_INVOLVED_SUBJECT';
		$mailParams['template'] = 'apps/group/polls/comment.polls.involved';

		$systemParams['title'] = JText::sprintf('APP_GROUP_STORY_SYSTEM_COMMENT_POLLS_INVOLVED_TITLE', $actor->getName(), $group->getName());

		// Notify other participating users
		ES::notify('comments.involved', $recipients, $mailParams, $systemParams, $group->notification);

		return;
	}
}
