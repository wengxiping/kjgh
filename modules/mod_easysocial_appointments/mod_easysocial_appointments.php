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

jimport('joomla.filesystem.file');

// Include main engine
$engine = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
$exists = JFile::exists($engine);

if (!$exists) {
	return;
}

// Include the engine file.
require_once($engine);
require_once(__DIR__ . '/helper.php');

$lib = ES::modules($module);

if (!$lib->my->id) {
	return;
}

// App is not installed, so we shouldn't do anything here
$calendarApp = EasySocialModAppointmentsHelper::getCalendarApp();

if (!$calendarApp) {
	return;
}

$model = ES::model('Calendar');
$days = (int) $params->get('interval', 14);
$total = (int) $params->get('total', 5);

$result = $model->getUpcomingSchedules($lib->my->id, $days, $total);

if (!$result) {
	return;
}

$appointments = EasySocialModAppointmentsHelper::format($result);

require($lib->getLayout());
