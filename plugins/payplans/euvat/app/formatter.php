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


class PayplansAppEuvatFormatter extends PayplansAppFormatter
{
	// get rules to apply
	public function getVarFormatter()
	{
		$rules = array(
						'_appplans' => array('formatter'=> 'PayplansAppFormatter', 'function' => 'getAppPlans'),
						'app_params' => array('formatter'=> 'PayplansAppEuvatFormatter', 'function' => 'getFormattedParams')
					);

		return $rules;
	}

	public function getFormattedParams($key, $value, $data)
	{
		$model = PP::model('Country');
		$items = $model->loadRecords();

		if (is_array($value['tax_country'])) {
			foreach ($value['tax_country'] as $param) {
				$country[] = $items[$param]->title;
			}

			$value['tax_country'] = $country;

		} else {
			$value['tax_country'] = $items[$value['tax_country']]->title;

		}
	}
}
