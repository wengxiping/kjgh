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

class PPAppMenuAccess extends PPApp
{
	/**
	 * Triggered when saving the app from the back end
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function collectAppParams($data)
	{
		if (!isset($data['app_params']['allowedMenus'])) {
			$data['app_params']['allowedMenus'] = array();
		}
		
		return parent::collectAppParams($data);
	}
}
