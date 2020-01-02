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

class SocialComments extends EasySocial
{
	static $instance = null;
	static $blocks = array();

	public $config = null;
	public $commentor = null;
	public $commentCount = null;


	public function __construct()
	{
		parent::__construct();

		$this->commentor = array();
	}

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function factory($uid = null, $element = null, $verb = 'null', $group = SOCIAL_APPS_GROUP_USER, $options = array(), $useStreamId = false)
	{
		if ($verb == SOCIAL_APPS_GROUP_USER || $verb == SOCIAL_APPS_GROUP_GROUP) {
			// now we know the caller still using old way of calling the api.
			// we need to manually re-assign the arguments.
			$options = $group;
			$group = $verb;
			$verb = 'null';
		}

		return new self($uid, $element, $verb, $group, $options, $useStreamId);
	}

	public function load( $uid, $element, $verb = 'null', $group = SOCIAL_APPS_GROUP_USER, $options = array(), $useStreamId = false )
	{
		if ($verb == SOCIAL_APPS_GROUP_USER || $verb == SOCIAL_APPS_GROUP_GROUP) {
			// now we know the caller still using old way of calling the api.
			// we need to manually re-assign the arguments.
			$options = $group;
			$group = $verb;
			$verb = 'null';
		}

		if (empty(self::$blocks[$group][$element][$verb][$uid])) {
			$class = new SocialCommentBlock($uid, $element, $verb, $group, $options, $useStreamId);

			self::$blocks[$group][$element][$verb][$uid] = $class;
		}

		self::$blocks[$group][$element][$verb][$uid]->loadOptions($options);

		return self::$blocks[$group][$element][$verb][$uid];
	}
}

class SocialCommentBlock extends EasySocial
{
	public $uid 	= '';
	public $element = '';
	public $group 	= '';
	public $verb 	= '';
	public $stream_id = '';
	public $options = array();

	public function __construct( $uid, $element, $verb = 'null', $group = SOCIAL_APPS_GROUP_USER, $options = array(), $useStreamId = false )
	{
		parent::__construct();

		$this->uid = $uid;
		$this->element = $element;
		$this->group = $group;
		$this->verb = $verb;
		$this->stream_id = ( $useStreamId ) ? $useStreamId : '';

		$this->loadOptions($options);
	}

	/**
	 * Determines if the current viewer is really allowed to post a comment
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function canComment()
	{
		// Site admins should always be allowed to react
		if (ES::isSiteAdmin()) {
			return true;
		}

		// if there is a stream id, lets use it.
		if ($this->stream_id) {

			$streamTable = ES::table('Stream');
			$streamTable->load($this->stream_id);

			if ($streamTable->id && (!$streamTable->isModerated() && !$streamTable->isTrashed())) {
				$items = ES::stream()->getItem($streamTable->id, $streamTable->cluster_id, $streamTable->cluster_type, false, array('perspective' => 'dashboard'));
				if ($items && is_array($items)) {
					return true;
				}

				// if stream lib return non array data, mean this user cannot view the stream
				return false;
			}
		}

		// no stream id. lets fall back to check the item's privacy / access
		$apps = ES::apps();
		$apps->load($this->group);

		$args = array('comment', $this->element, $this->verb, $this->uid);
		$dispatcher = ES::dispatcher();
		$allowed = $dispatcher->trigger($this->group, 'isItemViewable', $args);

		if (in_array(true, $allowed)) {
			return true;
		}

		return false;
	}

	public function loadOptions( $options = array() )
	{
		if (!empty($options['url'])) {
			$this->options['url'] = $options['url'];
		}

		if (!empty($options['clusterId'])) {
			$this->options['clusterId'] = $options['clusterId'];
		}

		if (!empty($options['hideForm'])) {
			$this->options['hideForm'] = $options['hideForm'];
		}

		// This is cluster object
		if (!empty($options['cluster'])) {
			$this->options['cluster'] = $options['cluster'];
		}
	}

	public function setOption( $key, $value )
	{
		$this->options[$key] = $value;
	}

	/**
	 * Generates the unique element
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getElement()
	{
		$compositeKey = $this->element . '.' . $this->group . '.' . $this->verb;
		return $compositeKey;
	}

	/**
	 * Retrieves the comment count given the element and unique id
	 *
	 * @since	1.0
	 * @access	public
	 *
	 * @return	int		The total count of the comment block
	 */
	public function getCount()
	{
		$model = ES::model('Comments');
		$options = array('element' => $this->getElement(), 'uid' => $this->uid);

		if ($this->stream_id) {
			$options['stream_id'] = $this->stream_id;
		}

		$count = $model->getCommentCount($options);

		return $count;
	}

	/**
	 * Retrieves a list of comments
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getComments($options = array())
	{
		// Construct mandatory options
		$options['uid'] = $this->uid;
		$options['element'] = $this->getElement();
		$options['stream_id'] = $this->normalize($this, 'stream_id');

		// Get the model
		$model = ES::model('Comments');

		// Construct bounderies
		if (!isset($options['limit'])) {
			$options['limit'] = $this->config->get('comments.limit', 5);
		}

		// Construct ordering
		$options['order'] = 'created';
		$options['direction'] = 'asc';

		$comments = $model->getComments($options);

		return $comments;
	}

	/**
	 * Generates the html codes for comments
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function html($options = array())
	{
		// Construct mandatory options
		$options['uid'] = $this->uid;
		$options['element'] = $this->getElement();
		$options['hideEmpty'] = $this->normalize($options, 'hideEmpty');
		$options['hideForm'] = $this->normalize($options, 'hideForm');
		$options['deleteable'] = $this->normalize($options, 'deleteable');
		$options['stream_id'] = $this->normalize($this, 'stream_id');

		// Ensure that the site admin can always delete the comments
		if ($this->my->isSiteAdmin()) {
			$options['deleteable'] = true;
		}

		// Check view mode (with childs or not)
		if (empty($options['fullview'])) {
			$options['parentid'] = 0;
		}

		// Get the model
		$model = ES::model('Comments');
		$total = $model->getCommentCount($options);

		// Construct bounderies
		if (!isset($options['limit'])) {
			$options['limit'] = $this->config->get('comments.limit', 5);
		}

		$options['start'] = max($total - $options['limit'], 0);

		// Construct ordering
		$options['order'] = 'created';
		$options['direction'] = 'asc';

		// Check if it is coming from a permalink
		$commentid = $this->input->get('commentid', 0, 'int');

		if ($commentid !== 0) {
			$options['commentid'] = $commentid;

			// If permalink is detected, then no limit is required
			$options['limit'] = 0;
		}

		$comments = array();
		$count = 0;

		if ($total) {
			$comments = $model->getComments($options);
			$count = count($comments);
		}

		// @trigger: onPrepareComments
		$dispatcher = FD::dispatcher();
		$args = array(&$comments);

		$dispatcher->trigger($this->group , 'onPrepareComments', $args);

		// Check for permalink
		if (!empty($options['url'])) {
			$this->options['url'] = $options['url'];
		}

		// Check for stream id
		if (!empty($options['streamid'])) {
			$this->options['streamid'] = $options['streamid'];
		} else if($this->stream_id) {
			$this->options['streamid'] = $this->stream_id;
		}

		// Determines if the js should be rendered
		$ajax = false;

		if ($this->doc->getType() != 'html') {
			$ajax = true;
		}

		static $scriptsLoaded = null;

		$loadScripts = false;

		if (is_null($scriptsLoaded)) {
			$loadScripts = true;
			$scriptsLoaded = true;
		}

		if ($ajax) {
			$loadScripts = true;
		}

		$theme = ES::themes();
		$theme->set('loadScripts', $loadScripts);
		$theme->set('deleteable', $options['deleteable']);
		$theme->set('hideEmpty', $options['hideEmpty'] );
		$theme->set('hideForm', $options['hideForm'] );
		$theme->set('element', $this->element);
		$theme->set('group', $this->group);
		$theme->set('verb', $this->verb);
		$theme->set('uid', $this->uid);
		$theme->set('total', $total);
		$theme->set('count', $count);
		$theme->set('comments', $comments);

		if (!empty($this->options['url'])) {
			$theme->set('url', $this->options['url']);
		}

		if (!empty($this->options['clusterId'])) {
			$theme->set('clusterId', $this->options['clusterId']);
		}

		if (!empty($this->options['streamid'])) {
			$theme->set( 'streamid', $this->options['streamid'] );
		}

		if (isset($this->options['hideForm'])) {
			$theme->set('hideForm', $this->options['hideForm']);
		}

		$model = ES::model('Emoticons');
		$theme->set('emoticons', $model->getJsonEmoticons());

		$html = $theme->output('site/comments/default');

		return $html;
	}

	/**
	 * Function to return HTML of 1 comments block
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getHtml($options = array())
	{
		return $this->html($options);
	}

	/**
	 * Deletes a comment from the site
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function delete()
	{
		$model = ES::model('Comments');

		// Get a list of comments associated with the current comment
		$options = array(
			'element' => $this->getElement(),
			'uid' => $this->uid,
			'limit' => 0
		);

		$comments = $model->getComments($options);

		if (!$comments) {
			return true;
		}

		foreach ($comments as $comment) {
			$comment->delete();
		}

		return true;
	}

	// @TODO: Shift this to comment app
	public function parentItemDeleted()
	{
		$model = FD::model( 'comments' );
		$state = $model->deleteCommentBlock( $this->uid, $this->getElement() );

		return $state;
	}

	public function getParticipants( $options = array() , $userObject = true )
	{
		$model = FD::model( 'comments' );

		$result = $model->getParticipants( $this->uid, $this->getElement(), $options );

		$users = array();

		if( !$result )
		{
			return $users;
		}

		if( !$userObject )
		{
			return $result;
		}

		foreach( $result as $id )
		{
			$users[$id] = FD::user( $id );
		}

		return $users;
	}
}
