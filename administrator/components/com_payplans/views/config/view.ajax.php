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

class PayPlansViewConfig extends PayPlansAdminView
{
	/**
	 * Renders confirmation before allowing users to edit the encryption key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function editKeyDialog()
	{
		$theme = PP::themes();
		$contents = $theme->output('admin/settings/dialogs/edit.key');

		return $this->resolve($contents);
	}
}

