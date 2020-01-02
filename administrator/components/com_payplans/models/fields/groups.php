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

require_once(__DIR__ . '/abstract.php');

class JFormFieldGroups extends JFormFieldPayPlans
{
	protected $type = 'Groups';

	protected function getInput()
	{
		$label = (string) $this->element['label'];
		$name = (string) $this->name;
		$value = $this->value;
		$multiple = $this->multiple;

		$this->set('multiple', $multiple);
		$this->set('value', $value);
		$this->set('label', $label);
		$this->set('name', $name);

		return $this->output('groups');
	}
}
