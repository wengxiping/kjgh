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

class EasySocialViewStory extends EasySocialSiteView
{
	/**
	 * Generates the story meta language string
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function buildStoryMeta()
	{
		$ids = $this->input->get('ids', 0, 'default');

		if (!is_array($ids)) {
			return;
		}

		$users = ES::user($ids);

		$caption = ES::themes()->html('string.with', $users);
		$caption = JString::trim($caption);

		return $this->ajax->resolve($caption);
	}

	/**
	 * Renders the story form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderForm()
	{
		ES::requireLogin();

		// Determine if we should render form from specific type
		$type = $this->input->get('type', '', 'default');

		$story = ES::story(SOCIAL_TYPE_USER);
		$story->setTarget($this->my->id);

		$contents = $story->html(true, $type);

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processes after a user submits a story.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function create($streamItemTable = '', $clusterId = '', $clusterType = '')
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$stream = ES::stream();
		$stream->getItem($streamItemTable->uid, $clusterId, $clusterType, true);

		$output = $stream->html();

		// If app explicitly wants to hide the stream item, do not display anything here.
		if (isset($streamItemTable->hidden) && $streamItemTable->hidden) {
			$output = '';
		}

		// If app explicitly wants to display notice, do it here.
		if (isset($streamItemTable->notice) && $streamItemTable->notice) {
			$theme = ES::themes();
			$theme->set('notice', $streamItemTable->notice);
			$output = $theme->output('site/stream/default/notice');
		}

		// Success Message
		$message = 'COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED';
		$type = SOCIAL_MSG_SUCCESS;

		if (!$streamItemTable) {
			$message = 'COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED_FAILED';
			$type = SOCIAL_MSG_ERROR;
		}

		$this->setMessage($message, $type);

		return $this->ajax->resolve($output, $streamItemTable->uid, $this->getMessage());
	}

	/**
	 * Post processes after a user submits a simple story.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function createFromModule($streamItemTable = '')
	{
		// Default message
		$message = JText::_('COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED');

		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		// If we know that there is no argument, the process failed because they are not logged in.
		if (!$streamItemTable) {
			$message = JText::_('COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED_FAILED');

			return $this->ajax->resolve(false, $message);
		}

		$stream = ES::stream();
		$stream->getItem($streamItemTable->uid, '', '', true);

		$output = $stream->html();

		// If app explicitly wants to hide the stream item, do not display anything here.
		if (isset($streamItemTable->hidden) && $streamItemTable->hidden) {
			$output = '';
		}

		return $this->ajax->resolve(true, $message, $output, $streamItemTable->uid);
	}

	/**
	 * Post processes after a user updates a stream.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function update($streamTable = '', $clusterId = null, $clusterType = null, $moderated = null)
	{
		ES::requireLogin();

		// If this coming from cluster, we need to check for stream moderation state
		if ($clusterId && $moderated == SOCIAL_STREAM_STATE_MODERATE) {
			$moderated = true;
		}

		$stream = ES::stream();
		$streamItem = $stream->getItem($streamTable->uid, $clusterId, $clusterType, $moderated);
		$streamItem = $streamItem[0];

		$output = $stream->html(false, '', array('contentOnly' => true));
		$preview = '';
		$locationPreview = '';

		if ($streamItem && $streamItem->hasPreview()) {
			$preview = $streamItem->preview;
		}

		if ($streamItem->location && $this->config->get('stream.location.style') === 'inline') {
			$theme = ES::themes();
			$theme->set('stream', $streamItem);
			$theme->set('isEdit', true);
			$theme->set('provider', $this->config->get('location.provider'));
			$locationPreview = $theme->output('site/stream/default/location');
		}

		$backgroundId = '';

		if ($streamItem && $streamItem->background_id) {
			$backgroundId = $streamItem->background_id;
		}

		return $this->ajax->resolve($output, $streamTable->uid, '', $preview, $backgroundId, $locationPreview);
	}

	/**
	 * Display flood protection warning
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function showFloodWarning()
	{
		$theme	= ES::themes();
		$output	= $theme->output('site/story/dialogs/flood.protection');

		return $this->ajax->resolve($output);
	}
}
