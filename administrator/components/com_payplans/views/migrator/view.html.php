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

class PayPlansViewMigrator extends PayPlansAdminView
{
	public function display($tpl = null)
	{
	}

	public function importsample($tpl = null)
	{
		$this->heading('Import Sample Data');

		$types = array('basic' => 'Basic', 'advanced' => 'Advanced', 'expert' => 'Expert');
		$importTypes = array();
		
		foreach($types as $key => $val) {
			$obj = new stdClass();
			$obj->title = $val;
			$obj->value = $key;

			$importTypes[] = $obj;
		}
		
		$this->set('importTypes', $importTypes);

		parent::display('migrator/importsample');
	}

}
