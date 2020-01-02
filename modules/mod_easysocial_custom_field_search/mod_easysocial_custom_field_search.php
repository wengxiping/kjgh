<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
jimport('joomla.filesystem.file');

// Include main engine
$engine = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
$exists = JFile::exists($engine);

if (!$exists) {
	return;
}

// Include the engine file.
require_once($engine);

$jinput = JFactory::getApplication()->input;
$type = $params->get('searchtype', 'user');
$view = $jinput->get('view', false, 'default');
$viewType = $jinput->get('type', '', 'default');
$searchType = $type . 's';

if ($view == 'search' && $viewType != $type) {
	return;
}

if ($view != 'search' && $view != $searchType) {
	return;
}

$lib = ES::modules($module);

// add module js script
$lib->addScript('script.js');

// Get the current logged in user object
$my = ES::user();
$config = ES::config();

$filterMode = $params->get('filtermode', 'equal');
$submitOnClick = $params->get('submitonclick', false);

// Load up helper file
require_once(dirname(__FILE__) . '/helper.php');

// Get fields available
$fields = EasySocialModCustomFieldSearchHelper::getFields($params);

if (!$fields) {
	return;
}

// Get values from posted data
$values = array();
$values['criterias'] = $jinput->get('criterias', null, 'default');
$values['datakeys'] = $jinput->get('datakeys', null, 'default');
$values['operators'] = $jinput->get('operators', null, 'default');
$values['conditions'] = $jinput->get('conditions', null, 'default');

$userData = array();

if ($values['criterias']) {
	for ($i = 0; $i < count($values['criterias']); $i++) {
		$criteria = $values['criterias'][$i];
		$condition = $values['conditions'][$i];
		$datakey = $values['datakeys'][$i];

		$field  = explode('|', $criteria);

		$fieldCode = $field[0];
		$fieldType = $field[1];

		$userData[$fieldType]['condition'] = $condition;
	}
}

foreach ($fields as $field) {
	$field->data = (isset($userData[$field->element]['condition'])) ? $userData[$field->element]['condition'] : '';

	$field->checkedItems = explode('|', $field->data);

	$fieldParams = json_decode($field->params);
}

require($lib->getLayout());



