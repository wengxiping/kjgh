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

class EasySocialControllerDashboard extends EasySocialController
{
	/**
	 * Retrieves public stream contents
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getPublicStream()
	{
		$hashtags = array();
		$hashtag = $this->input->get('hashtag', '', 'default');

		if ($hashtag) {
			$hashtags = array($hashtag);
		}

		// Get the layout to use.
		$stream = ES::stream();
		$stream->getPublicStream($this->config->get('stream.pagination.pagelimit', 10), 0, $hashtags);

		return $this->view->call(__FUNCTION__, $stream);
	}

	/**
	 * Retrieves the stream contents.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getStream()
	{
		ES::requireLogin();
		ES::checkToken();

		$this->input->set('view', 'dashboard');

		$hashtags = array();

		// Get the type of the stream to load.
		$filter = $this->input->get('type', '', 'word');

		// Get the stream
		$stream = ES::stream();

		if (!$filter) {
			$this->view->setMessage('COM_EASYSOCIAL_STREAM_INVALID_FEED_TYPE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $stream, $filter);
		}

		// Default stream options
		$streamOptions = array();
		$stickyIds = array();

		$stickyOptions = array('userId' => $this->my->id, 'type' => 'sticky', 'adminOnly' => true);
		$stickies = $stream->getStickies($stickyOptions);

		// Only assign stickies if the result is an array
		if (is_array($stickies)) {
			foreach ($stickies as $stick) {
				$stickyIds[] = $stick->uid;
			}

			$streamOptions['excludeStreamIds'] = $stickyIds;
			$stream->stickies = $stickies;
		}

		$streamOptions['nosticky'] = false;

		if ($filter == 'hashtag') {
			$tag = $this->input->get('id', '', 'default');

			$hashtags = array($tag);
			$streamOptions['tag'] = array($tag);
		}

		if ($filter == 'following') {
			$streamOptions['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;
			$streamOptions['type'] = 'follow';
			$stream->stickies = false;
		}

		// Filter by bookmarks
		if ($filter == 'bookmarks' && $this->config->get('stream.bookmarks.enabled')) {
			$streamOptions['guest'] = true;
			$streamOptions['type'] = 'bookmarks';
			$stream->stickies = false;
			$streamOptions['nosticky'] = false;
		}

		// Get feeds from everyone
		if ($filter == 'everyone') {
			$streamOptions['guest'] = true;

			// temporary comment out this because do not want to render banner user stream item #3446
			// $streamOptions['ignoreUser'] = true;
		}

		$postTypes = $this->input->get('postTypes', array(), 'word');
		if ($postTypes) {
			$streamOptions['context'] = $postTypes;
		}

		if ($filter == 'sticky' && $this->config->get('stream.pin.enabled')) {

			$stream->stickies = false;

			$streamOptions['userId'] = $this->my->id;
			$streamOptions['type'] = 'sticky';
			$streamOptions['includeClusterSticky'] = true;
			$streamOptions['excludeStreamIds'] = false;
		}

		$streamFilter = '';

		// Custom stream filters
		if ($filter == 'custom') {
			$id = $this->input->get('id', 0, 'int');

			$streamFilter = ES::table('StreamFilter');
			$streamFilter->load($id);

			$stream->filter = 'custom';

			if ($streamFilter->id) {
				$hashtags = $streamFilter->getHashTag();
				$hashtags = explode(',', $hashtags);

				if ($hashtags) {
					// $streamOptions = array('context' => SOCIAL_STREAM_CONTEXT_TYPE_ALL , 'tag' => $hashtags, 'nosticky' => true);

					$streamOptions['tag'] = $hashtags;
					$streamOptions['nosticky'] = true;

					$hashtagRule = $this->config->get('stream.filter.hashtag', '');
					if ($hashtagRule == 'and') {
						$streamOptions['matchAllTags'] = true;
					}
				}
			}
		}

		$stream->get($streamOptions);

		return $this->view->call(__FUNCTION__, $stream, $filter, $hashtags, $streamFilter);
	}

	/**
	 * Retrieves the dashboard contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAppContents()
	{
		ES::requireLogin();
		ES::checkToken();

		$appId = $this->input->get('appId', 0, 'int');

		$app = ES::table('App');
		$state = $app->load($appId);

		if (!$appId || !$state) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_INVALID_APP_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__ , $app);
		}

		// Check if the user has access to this app or not.
		if (!$app->accessible($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_PLEASE_INSTALL_APP_FIRST', ES_ERROR);
			return $this->view->call(__FUNCTION__ , $app);
		}

		return $this->view->call(__FUNCTION__ , $app);
	}
}
