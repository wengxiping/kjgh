<?php
/**
* @package   com_zoo
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

printf('<select %s multiple="true">', $this->app->field->attributes(array('name' => "{$control_name}[{$name}][]")));
$plans = PayplansApi::getPlans();
$value = !is_array($value) ? array($value) : $value;

$plans[0]->plan_id = -1;
$plans[0]->title = JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_PLANS_NONE');

asort($plans);
foreach ($plans as $option) {
	$attrib = '';
	if (in_array($option->plan_id, $value)) {
		$attrib = 'selected="selected"';
	}

	printf('<option %s %s>%s</option>', 'value="'.$option->plan_id.'"', $attrib, $option->title);
}

printf('</select>');