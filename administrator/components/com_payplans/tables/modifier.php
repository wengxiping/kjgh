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

class PayplansTableModifier extends PayplansTable
{
	public $modifier_id = null;
	public $user_id = null;
	public $invoice_id = null;
	public $amount = null;
	public $type = null;
	public $reference = null;
	public $message = null;
	public $percentage = null;
	public $serial = null;
	public $frequency = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_modifier', 'modifier_id', $db);
	}
}