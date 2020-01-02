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

class EasySocialViewSettings extends EasySocialAdminView
{
	/**
	 * Displays dialog to confirm reset settings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmReset()
	{
		$section = JRequest::getVar( 'section' );

		$theme = ES::themes();
		$theme->set('section', $section);
		$contents = $theme->output('admin/settings/dialogs/reset');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display confirmation box to purge text based avatars
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmPurgeTextAvatars()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/settings/dialogs/purge.textavatar');

		return $this->ajax->resolve($output);
	}

	/**
	 * Display confirmation box to remove email logo
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmRestoreImage()
	{
		$type = $this->input->get('type');

		$default = explode('_', $type);
		$defaults = array('avatar', 'cover');

		if (in_array($default[1], $defaults)) {
			$type = 'default_' . $default[1];
		}

		$theme = ES::themes();
		$theme->set('type', $type);
		$output = $theme->output('admin/settings/dialogs/restore.image');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows user to import a .json file
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function import()
	{
		$theme = ES::themes();
		$page = JRequest::getVar('page');

		$theme->set('page', $page);
		$contents = $theme->output('admin/settings/dialogs/import');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display confirmation box to remove login image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmRemoveImage()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/settings/dialogs/delete.login.image');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display confirmation box to check for valid api key
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function validateApiKey()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/settings/dialogs/verify.api');

		return $this->ajax->resolve($contents);
	}
}