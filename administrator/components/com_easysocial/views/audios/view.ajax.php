<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewAudios extends EasySocialAdminView
{
	/**
	 * Confirmation before deleting an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmDelete()
	{
		$ids = $this->input->get('ids', array(), 'array');

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/audios/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to browse for an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/audios/dialogs/browse');

		return $this->ajax->resolve($content);
	}
}
