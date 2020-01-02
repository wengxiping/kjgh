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

class PayplansAppAssignplanFormatter extends PayplansAppFormatter
{
	/**
	 * Retrieve the rules
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function getVarFormatter()
	{
		$rules = array('_appplans' => array('formatter' => 'PayplansAppFormatter', 'function' => 'getAppPlans'),
					   'app_params' => array('formatter' => 'PayplansAppAssignplanFormatter', 'function' => 'getFormattedParams'));
		
		return $rules;
	}

	/**
	 * Format this app params data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormattedParams($key, $value, $data)
	{
		$value['assignPlan'] = $this->getListName($value['assignPlan']);
		$value['setPlanOnHold'] = $this->getListName($value['setPlanOnHold']);
		$value['setPlanOnExpire'] = $this->getListName($value['setPlanOnExpire']);
	}

	/**
	 * Get Acymailing list names
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function getListName($values)
	{
		foreach ($values as $value) {
			$listNames[] = PPHelperPlan::getName($value);
		}
		
		$result = implode(',', $listNames);

		return $result;
	}
}