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

class PayplansOrderFormatter extends PayplansFormatter
{
	public function getIgnoredata()
	{
		$ignore = array('_trigger', '_component', '_errors', '_name', '_invoices');
		return $ignore;
	}

	public function getVarFormatter()
	{
		$rules = array('buyer_id' => array('formatter'=> 'PayplansUserFormatter', 'function' => 'getBuyerName'),
					   '_subscription' => array('formatter'=> 'PayplansSubscriptionFormatter', 'function' => 'getSubscriptionDetails'),
					   'params' => array('formatter'=> 'PayplansFormatter', 'function' => 'getFormattedParams'));
		return $rules;
	}

	/**
	 * get name of order status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOrderStatusName($key, $value, $data)
	{
		$status = array();
		
		foreach ($value as $v) {
			$status[] = PayplansStatus::getName($v);
		}

		$value = $status;
	}
}