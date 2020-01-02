<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayPlansViewUser extends PayPlansAdminView
{
	/**
	 * Renders a browser user dialog
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'word');

		$theme = PP::themes();
		$theme->set('callback' , $callback);

		$output = $theme->output('admin/user/dialogs/browse');

		return $this->resolve($output);
	}
}

