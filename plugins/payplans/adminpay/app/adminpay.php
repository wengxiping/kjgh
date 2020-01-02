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

class PPAppAdminPay extends PPAppPayment
{
	/**
	 * Only render this on admin only
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		$app = JFactory::getApplication();

		if (!$app->isAdmin()) {
			return false;
		}

		return parent::isApplicable($refObject, $eventName);
	}
	
	public function onPayplansPaymentForm(PPPayment $payment, $data = NULL)
	{
	}

	/**
	 * This used to be a method from adminpay app but since we moved this into the core,
	 * we will just capture the triggers here
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansTransactionBeforeSave($prev = null, $new)
	{
		// Perform the task only once when a new transaction has been created
		if ($prev != null) {
			return true;
		}

		$id = $new->gateway_txn_id;
		$message = $new->message;

		if (!$message) {
			$new->message = JText::_('COM_PAYPLANS_APP_OFFLINE_TRANSACTION_CREATED');
		}

		return true;
	}
}
