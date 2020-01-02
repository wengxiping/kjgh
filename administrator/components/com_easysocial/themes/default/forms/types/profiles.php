<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$inputName = $field->inputName;

if (isset($field->multiple) && $field->multiple) {
	$inputName .= '[]';
}

$value = $params->get($field->name, $field->default);

if (isset($field->multiple) && $field->multiple) {

	if (is_object($value)) {
		$value = (array) $value;
	}
}
?>
<?php echo $this->html('form.profiles', $inputName, $inputName, $value, array('multiple' => isset($field->multiple) ? $field->multiple : true)); ?>