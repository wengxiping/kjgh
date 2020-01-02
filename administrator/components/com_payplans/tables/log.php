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

class PayplansTableLog extends PayplansTable
{
	public $log_id = null;
	public $level = null;
	public $owner_id = null;
	public $user_id = null;
	public $class = null;
	public $object_id = null;
	public $message = null;
	public $user_ip = null;
	public $created_date = null;
	public $content = null;
	public $read = null;
	public $position = null;
	public $previous_token = null;
	public $current_token = null;
	public $legacy = null;
	
	public function __construct($db)
	{
		parent::__construct('#__payplans_log', 'log_id', $db);
	}
}

