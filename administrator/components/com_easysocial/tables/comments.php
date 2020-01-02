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

ES::import('admin:/tables/table');

class SocialTableComments extends SocialTable
{
	public $id = null;
	public $element = null;
	public $uid = null;
	public $comment = null;
	public $created_by = null;
	public $post_as = null;
	public $created = null;
	public $depth = null;
	public $parent = null;
	public $child = null;
	public $lft = null;
	public $rgt = null;
	public $params = null;
	public $stream_id = null;

	// flag to tell if store need to trigger onBeforeCommentSave and onAfterCommentSave
	public $_trigger = true;

	// custom author for this comment
	public $alias = null;

	public function __construct($db)
	{
		parent::__construct('#__social_comments', 'id', $db);
	}

	/**
	 * Retrieves the element
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getElement()
	{
		$parts = explode('.', $this->element);

		return $parts[0];
	}

	/**
	 * Retrieves the verb from the element
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getVerb()
	{
		$parts = explode('.', $this->element);

		return $parts[2];
	}

	/**
	 * Retrieves the group from the element
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getGroup()
	{
		$parts = explode('.', $this->element);

		return $parts[1];
	}


	/**
	 * Retrieves the comment author object
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getAuthor()
	{
		$user = FD::user($this->created_by);

		return $user;
	}

	/**
	 * Allow caller to set a custom author alias
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function setAuthorAlias($object)
	{
		$this->alias = $object;
	}

	/**
	 * Retrieve author of the comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAuthorAlias()
	{
		if (!$this->alias) {
			return $this->getAuthor();
		}

		return $this->alias;
	}

	/**
	 * Get the overlay for a comment message (mentions & hashtags)
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getOverlay()
	{
		// Get the tags for the comment
		$model = ES::model('Tags');
		$tags = $model->getTags($this->id, 'comments');

		$overlay = $this->comment;

		$counter = 0;
		$tmp = array();

		foreach ($tags as $tag) {

			if ($tag->type === 'entity' && $tag->item_type === SOCIAL_TYPE_USER) {
				$user = ES::user($tag->item_id);
				$replace = '<span data-value="user:' . $tag->item_id . '" data-type="entity">' . $user->getName() . '</span>';
			}

			if ($tag->type === 'hashtag') {
				$replace = '<span data-value="' . $tag->title . '" data-type="hashtag">' . "#" . $tag->title . '</span>';
			}

			if ($tag->type === 'emoticon') {
				$replace = '<span data-value="' . $tag->title . '" data-type="emoticon">' . ":" . $tag->title . '</span>';
			}

			$tmp[$counter] = $replace;

			$replace = '[si:mentions]' . $counter . '[/si:mentions]';
			$overlay = JString::substr_replace($overlay, $replace, $tag->offset, $tag->length);

			$counter++;
		}

		$overlay = ES::string()->escape($overlay);

		foreach ($tmp as $i => $v) {
			$overlay = str_ireplace('[si:mentions]' . $i . '[/si:mentions]', $v, $overlay);
		}

		return $overlay;
	}

	public function store($updateNulls = false)
	{
		if (!$this->params instanceof SocialRegistry) {
			$this->params = FD::registry($this->params);
		}

		$this->params = $this->params->toString();

		$isNew = false;

		if (empty($this->id)) {
			$isNew = true;
		}

		// Get the necessary group
		$namespace  = explode('.', $this->element);
		$group      = isset($namespace[1]) ? $namespace[1] : SOCIAL_APPS_GROUP_USER;

		FD::apps()->load($group);

		if ($isNew && $this->_trigger) {
			if (!empty($this->parent)) {
				$parent = $this->getParent();

				if ($parent) {
					$this->depth = $parent->depth + 1;

					$parent->addChildCount();
				}
			}

			$this->setBoundary();

			// Get the dispatcher object
			$dispatcher     = FD::dispatcher();
			$args           = array(&$this);

			// @trigger: onValidateSpam
			// This triggers specially created for cleantalk. All comments should be checked for spam.
			$error = $dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onUserValidateCommentSpam', $args);

			if (in_array(true, $error)) {
				$this->setError(JText::_('COM_ES_COMMENT_SPAM'));

				return false;
			}

			// @trigger: onBeforeCommentSave
			$dispatcher->trigger($group, 'onBeforeCommentSave', $args);
		}

		$state = parent::store();

		if (!$state) {
			return false;
		}

		if ($isNew && $this->_trigger) {
			// @trigger: onAfterCommentSave
			$dispatcher->trigger($group, 'onAfterCommentSave', $args);
		}

		return $state;
	}

	/*
	 * tell store function not to trigger onBeforeCommentSave and onAfterCommentSave
	 */
	public function offTrigger()
	{
		$this->_trigger = false;
	}

	// No chainability
	public function update(array $newData)
	{
		// IMPORTANT:
		// No escape is required here as we store the data as is

		// General loop to update the rest of the new data
		foreach($newData as $key => $value)
		{
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}

		$state = $this->store();

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Overwrite of the original delete function to include more hooks
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function delete($pk = null)
	{
		$arguments  = array(&$this);

		// Trigger beforeDelete event
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onBeforeDeleteComment', $arguments);


		$state = parent::delete($pk);

		if ($state) {
			// Clear out all the likes for this comment
			$likesModel = ES::model('likes');
			$likesModel->delete($this->uid, 'comments');

			// Delete reactions made on the comment
			$likesModel->delete($this->id, 'comments.user.like');

			// #3147
			// Delete notifications associated to this comment.
			// We can only delete standard notifications where it matches the following:
			//
			// comment.id is related to notification.uid
			// comment.element is related to notification.context_type
			$notificationsModel = ES::model('Notifications');
			$notificationsModel->deleteNotificationsWithUid($this->id, $this->element);

			// #3420
			// look like some element need to be removed using uid.
			// e.g. videos.user.create | videos.user.featured
			$requiredManualRemoval = array('videos.user.create', 'videos.group.create', 'videos.event.create', 'videos.page.create',
										'videos.user.featured', 'videos.group.featured', 'videos.event.featured', 'videos.page.featured',
										'audios.user.create', 'audios.group.create', 'audios.event.create', 'audios.page.create',
										'audios.user.featured', 'audios.group.featured', 'audios.event.featured', 'audios.page.featured');

			if (in_array($this->element, $requiredManualRemoval)) {
				$notificationsModel->deleteNotificationsWithUid($this->uid, $this->element);
			}

			// Delete files related to this comment
			$filesModel = ES::model('Files');
			$filesModel->deleteFiles($this->id, 'comments');

			// Trigger afterDelete event
			$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onAfterDeleteComment', $arguments);

			// We also need to clear the last action of the stream
			if ($this->stream_id) {
				$model = ES::model('Stream');
				$model->revertLastAction($this->stream_id, $this->created_by, 'comment');
			}
		}

		return $state;
	}

	/**
	 * Renders the output of comments
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function html($options = array())
	{
		$clusterId = $this->getParams()->get('clusterId', 0);
		$clusterType = $this->getParams()->get('clusterType', '');

		// Modify the commentator
		if ($this->post_as == SOCIAL_TYPE_PAGE) {
			$page = ES::page($clusterId);
			$this->setAuthorAlias($page);
		}

		$likeOptions = array();

		if ($clusterType == SOCIAL_TYPE_PAGE && $clusterId) {
			$likeOptions['clusterId'] = $clusterId;
		}

		$author = $this->getAuthorAlias();
		$isAuthor = $this->isAuthor();
		$likes = ES::likes($this->id, 'comments', 'like', SOCIAL_APPS_GROUP_USER, null, $likeOptions);

		$theme = ES::themes();

		// Determines if the viewer can delete the comment
		$deleteable = isset($options['deleteable']) ? $options['deleteable'] : $isAuthor;

		// Get attachments associated with this comment
		$model = ES::model('Files');
		$attachments = $model->getFiles($this->id, SOCIAL_TYPE_COMMENTS);

		$theme->set('attachments', $attachments);
		$theme->set('deleteable', $deleteable);
		$theme->set('comment', $this);
		$theme->set('author', $author);
		$theme->set('isAuthor', $isAuthor);
		$theme->set('likes', $likes);
		$theme->set('identifier', uniqid());

		$html = $theme->output('site/comments/item');

		return $html;
	}

	/**
	 * Deprecated. Use @html instead
	 *
	 * @deprecated	2.1.0
	 */
	public function renderHTML($options = array())
	{
		return $this->html($options);
	}

	public function getPermalink()
	{
		$base = $this->getParams()->get('url');

		if (empty($base)) {
			return false;
		}

		// FRoute it
		$base = ESR::_($base);

		$base .= '#commentid=' . $this->id;

		return $base;
	}

	/**
	 * Retrieve the comment and format it accordingly
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getComment($limit = 150)
	{
		// Set the comment data on a variable
		$comment = $this->comment;

		// Load up the string library
		$stringLib = ES::get('string');

		// 1.2.17 Update
		// We truncate to get a short preview content but in actual, we prepare 2 copies of data here.
		// Instead of separating the comments into Shorten and Balance, we do Shorten and Full instead.
		// Shorten contains first 150 character in raw.
		// Full contains the full comment, untruncated and processed.
		// The display part switches the shorten into the full content with JS.
		// Preview doesn't need to be processed.

		// Generate a unique id.
		$uid = uniqid();

		$model = ES::model('Tags');
		$tags = $model->getTags($this->id, 'comments');

		if ($tags) {
			$comment = $stringLib->processTags($tags, $comment, true, true);
		}

		$comment = $stringLib->escape($comment);

		// Convert the tags
		if ($tags) {
			$comment = $stringLib->afterProcessTags($tags, $comment, true, false);
			$comment = $stringLib->processSimpleTags($comment);
		}

		// Apply bbcode on the comment
		$config = ES::config();
		$comment = $stringLib->parseBBCode($comment, array('escape' => false, 'emoticons' => $config->get('comments.smileys'), 'links' => true));

		// If there's a read more, then we prepare a short preview content
		$preview = '';

		// Determine if read more is needed.
		if ($readmore = JString::strlen(preg_replace('/<.*?>/', '', $comment)) >= $limit) {
			$preview = $stringLib->truncateWithHtml($comment, $limit);
		}

		$html = $comment;

		// #2192
		// When there is truncation, we need to find and replace links to avoid problems with hyperlinks
		if ($readmore) {

			require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/crawler/helpers/simplehtml.php');
			$originalParser = SocialSimpleHTML::str_get_html($comment);
			$previewParser = SocialSimpleHTML::str_get_html($preview);

			$links = $previewParser->find('a');

			if ($links) {
				$originalLinks = $originalParser->find('a');

				for ($i = 0; $i < count($links); $i++) {
					$link =& $links[$i];
					$originalLink = $originalLinks[$i];

					$newLink = (string) $originalLink->getAttribute('href');

					$link->setAttribute('href', $newLink);
					$link = (string) $link;

					$preview = (string) $previewParser;
				}
			}

			$html = $preview;

			$html .= '<span data-es-comment-full style="display: none;">' . $comment . '</span>';
			$html .= '<span data-es-comment-readmore-' . $uid . ' data-es-comment-readmore>&nbsp;';
			$html .= '<a href="javascript:void(0);" data-es-comment-readmore>&nbsp;' . JText::_('COM_EASYSOCIAL_MORE_LINK') . '</a>';
			$html .= '</span>';
		}

		return $html;
	}

	/**
	 * Retrieves the date the comment was posted
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getDate($format = '')
	{
		$config = FD::config();

		$date = FD::date($this->created);

		$elapsed = $config->get('comments_elapsed_time', true);

		// If format is passed in as true or false, this means disregard the elapsed time settings and obey the decision of format
		if ($format === true || $format === false) {
			$elapsed = $format;

			$format = '';
		}

		if ($elapsed && empty($format)) {
			return $date->toLapsed();
		}

		if (empty($format)) {
			return $date->toSql(true);
		}

		return $date->format($format);
	}

	public function getApp()
	{
		static $apps = array();

		if (empty($apps[$this->element])) {
			$app = FD::table('apps');

			$app->loadByElement($this->element, SOCIAL_APPS_GROUP_USER, SOCIAL_APPS_TYPE_APPS);

			$apps[$this->element] = $app;
		}

		return $apps[$this->element];
	}

	/**
	 * Get reports for this comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getReports()
	{
		$model = ES::model('Reports');

		$reports = $model->getReporters('com_easysocial', $this->id, 'comments');

		return $reports;
	}

	/**
	 * Determines if the provided user is the author of the comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isAuthor($userid = null)
	{
		if (is_null($userid)) {
			$userid = FD::user()->id;
		}

		return $this->created_by == $userid;
	}

	public function getParams()
	{
		if (!$this->params instanceof SocialRegistry) {
			$this->params = FD::registry($this->params);
		}

		return $this->params;
	}

	public function setParam($key, $value)
	{
		if (!$this->params instanceof SocialRegistry) {
			$this->params = FD::registry($this->params);
		}

		$this->params->set($key, $value);

		return true;
	}

	/**
	 * Determines if the user can delete this comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canDelete($userId = null)
	{
		$user = ES::user($userId);
		$access = $user->getAccess();

		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($access->allowed('comments.delete')) {
			return true;
		}

		if ($this->isAuthor($user->id) && $access->allowed('comments.deleteown')) {
			return true;
		}

		if ($this->isStreamAuthor($user->id) && $access->allowed('comments.deleteownstream')){
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user is the author of the stream
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function isStreamAuthor($userId = null)
	{
		if (is_null($userId)) {
			$userId = ES::user()->id;
		}

		$model = ES::model('Stream');
		$isStreamAuthor = $model->isStreamAuthor($userId, $this->stream_id);

		if ($isStreamAuthor) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can edit the comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canEdit($userId = null)
	{
		$user = ES::user($userId);
		$access = $user->getAccess();

		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($access->allowed('comments.edit')) {
			return true;
		}

		if ($this->isAuthor($user->id) && $access->allowed('comments.editown')) {
			return true;
		}

		return false;
	}

	public function getParticipants($options = array())
	{
		$model = FD::model('Comments');

		$recipients = $model->getParticipants($this->uid, $this->element);

		if (!empty($options['excludeSelf'])) {
			$total = count($recipients);
			for($i = 0; $i < $total; $i++)
			{
				if ($recipients[$i] == $this->created_by) {
					unset($recipients[$i]);
					break;
				}
			}
		}

		$recipients = array_values($recipients);

		return $recipients;
	}

	public function addChildCount()
	{
		$this->child = $this->child + 1;

		return $this->store();
	}

	public function getParent()
	{
		if (empty($this->parent)) {
			return false;
		}

		$parent = FD::table('Comments');
		$state = $parent->load($this->parent);

		if (!$state) {
			return false;
		}

		return $parent;
	}

	public function setBoundary()
	{
		$model = FD::model('Comments');
		$lastSibling = $model->getLastSibling($this->parent);

		$node = 0;

		if (empty($lastSibling)) {
			$parent = $this->getParent();

			if ($parent) {
				$node = $parent->lft;
			}
		}
		else {
			$node = $lastSibling->rgt;
		}

		if ($node > 0) {
			$model->updateBoundary($node);
		}

		$this->lft = $node + 1;
		$this->rgt = $node + 2;

		return true;
	}

	public function hasChild()
	{
		return $this->child > 0;
	}

	/**
	 * Exports necessary data from this table
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer)
	{
		$data = new stdClass();
		$data->id = $this->id;
		$data->author = $this->getAuthor()->toExportData($viewer);
		$data->content = $this->getComment();
		$data->date = $this->created;

		return $data;
	}
}
