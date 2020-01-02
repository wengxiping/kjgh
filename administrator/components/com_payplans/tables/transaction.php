<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/tables/table');

class PayplansTableTransaction extends PayplansTable
{
	public $transaction_id = null;
	public $user_id = null;
	public $invoice_id = null;
	public $current_invoice_id = null;
	public $payment_id = null;
	public $gateway_txn_id = null;
	public $gateway_parent_txn = null;
	public $gateway_subscr_id = null;
	public $amount = null;
	public $reference = null;
	public $message = null;
	public $created_date = null;
	public $params = null;

	/**
	 * Class Constructor
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function __construct($db)
	{
		parent::__construct('#__payplans_transaction', 'transaction_id', $db);
	}
}

