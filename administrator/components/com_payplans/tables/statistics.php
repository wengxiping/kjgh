<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Payplans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/tables/table');

class PayplansTableStatistics extends PayplansTable
{
	public $statistics_id = null;
	public $statistics_type = null;
	public $purpose_id_1 = null;
	public $purpose_id_2 = null; 
	public $count_1 = null;
	public $count_2 = null;
	public $count_3 = null;
	public $count_4 = null;
	public $count_5 = null;
	public $count_6 = null;
	public $count_7 = null;
	public $count_8 = null;
	public $count_9 = null;
	public $count_10 = null;
	public $details_1 = null;
	public $details_2 = null;
	public $message = null;
	public $statistics_date = null;
	public $modified_date = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_statistics', 'statistics_id', $db);
	}
}
