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

require_once(JPATH_ROOT . '/administrator/components/com_payplans/includes/payplans.php');

// If Payplans System Plugin disabled then do nothing
$state = JPluginHelper::isEnabled('system','payplans');
$app = JFactory::getApplication();

if (!$state) {
	$app->redirect(JURI::root(), JText::_('Please enable the plugin "<b>System - PayPlans</b>"'), 'error');
	return true;
}

$input = $app->input;

// trigger apps, so that they can override the behaviour
// if somebody overrided it, then they must overwrite $args['controllerClass']
// in this case they must include the file, where class is defined
$args = array(
	'view' => strtolower($input->get('view', 'plan', 'cmd')),
	'controller' => strtolower($input->get('view', 'plan', 'cmd')),
	'task' => strtolower($input->get('task', '', 'cmd')),
	'format' => strtolower($input->get('format', 'html', 'cmd'))
);

$results = PP::event()->trigger('onPayplansControllerCreation', $args);

PP::import('site:/controllers/controller');

// Ajax calls
PP::ajax()->listen();

// Check for environment changes
PP::checkEnvironment();

$controller = JControllerLegacy::getInstance('payplans');
$task = $input->get('task');

$controller->execute($task);


// A simple way, by which we can exit after controller request.
if (defined('PAYPLANS_EXIT')) {
	exit(PAYPLANS_EXIT);
}

$controller->redirect();