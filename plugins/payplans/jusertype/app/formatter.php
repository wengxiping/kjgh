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

class PayplansAppJusertypeFormatter extends PayplansAppFormatter
{
	public  $template = 'view_log';

	public function getVarFormatter()
	{
		$rules = array(
					'_appplans' => array(
										'formatter'=> 'PayplansAppFormatter',
										'function' => 'getAppPlans'
									),
					'app_params' => array(
										'formatter'=> 'PayplansAppJusertypeFormatter',
										'function' => 'getFormattedContent'
									)
				);

		return $rules;
	}

	public function getFormattedContent($key,$value,$data)
	{
		$value['jusertypeOnActive'] = $this->getGroupNames($value['jusertypeOnActive']);
		$value['jusertypeOnHold'] = $this->getGroupNames($value['jusertypeOnHold']);
		$value['jusertypeOnExpire'] = $this->getGroupNames($value['jusertypeOnExpire']);
		$value['removeFromDefault'] = $value['removeFromDefault'];

		$this->template = 'view';
	}

	public function getGroupNames($values)
	{
		$model = PP::model('User');
		$groups = $model->getJoomlaGroups();

		foreach ($values as $value) {
			$group[] = $groups[$value]->name;
		}

		return implode(',', $group);
	}
}