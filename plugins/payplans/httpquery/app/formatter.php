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

class PayplansAppHttpqueryFormatter extends PayplansAppFormatter
{
	public $template	= 'view_log';
	
	// get Ignore data 
	function getIgnoredata()
	{
		$ignore = array('_trigger', '_tplVars', '_mailer', '_location', '_errors', '_component');
		return $ignore;
	}
	
	// get rules
	function getVarFormatter()
	{
		$rules = array('_appplans'       => array('formatter'=> 'PayplansAppFormatter',
											   'function' => 'getAppPlans'),
					   'app_params'      => array('formatter'=> 'PayplansAppHttpqueryFormatter',
											   'function' => 'getFormattedContent'));
		return $rules;
	}

	// format email app content,status, expiration time 
	function getFormattedContent($key, $value, $data)
	{	
		$this->template = 'view';
	}
}
