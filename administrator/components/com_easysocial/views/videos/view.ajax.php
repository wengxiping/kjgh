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

class EasySocialViewVideos extends EasySocialAdminView
{
	/**
	 * Confirmation before deleting a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmDelete()
	{
		$ids = $this->input->get('ids', array(), 'array');

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/videos/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to browse for a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/videos/dialogs/browse');

		return $this->ajax->resolve($content);
	}

	/**
	 * Returns the status of the processing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function status($video, $progress)
	{
		$permalink = '';

		// this method is being called from both backend and frontend. we need to redirect to proper page. #597
		if (JFactory::getApplication()->isAdmin()) {
			$permalink = 'index.php?option=com_easysocial&view=videos&layout=form&id=' . $video->id;
		} else {
			$permalink = $video->getPermalink(false);
		}

		// Once the progress is complete, we need to send the url to the video
		if ($progress === true) {

			if (JFactory::getApplication()->isAdmin()) {
				$this->setMessage('COM_EASYSOCIAL_VIDEOS_UPDATED_SUCCESS', SOCIAL_MSG_SUCCESS);
				$this->info->set($this->getMessage());
			}

			return $this->ajax->resolve($permalink, 'done', $video->export(), $video->getThumbnail());
		}

		return $this->ajax->resolve($permalink, $progress);
	}
}
