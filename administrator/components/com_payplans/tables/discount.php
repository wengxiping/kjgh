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

class PayplansTableDiscount extends PayplansTable
{
	public $prodiscount_id = null;
	public $title = null;
	public $coupon_code = null;
	public $coupon_type = null;
	public $core_discount = null;
	public $coupon_amount = null;
	public $plans = null;
	public $start_date = null;
	public $end_date = null;
	public $published = null;
	public $params = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_prodiscount', 'prodiscount_id', $db);
	}
 }