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

PP::import('admin:/tables/table');

class PayplansTableInvoice extends PayplansTable
{
	public $invoice_id = null;
	public $serial = null;
	public $object_id = null;
	public $object_type = null;
	public $user_id = null;
	public $subtotal = null;
	public $total = null;
	public $currency = null;
	public $counter = null;
	public $status = null;
	public $params = null;
	public $created_date = null;
	public $modified_date = null;
	public $paid_date = null;
	public $checked_out = null;
	public $checked_out_time = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_invoice', 'invoice_id', $db);
	}
}

