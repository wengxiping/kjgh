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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
$input = $app->input;

// Installation temp file
$file = JPATH_ROOT . '/tmp/payplans.installation';

// check if user initiate to cancel installation
$cancelSetup = $input->get('cancelSetup', false, 'bool');

if ($cancelSetup && JFile::exists($file)) {
	// delete installer tmp file
	JFile::delete($file);

	return $app->redirect('index.php?option=com_payplans');
}

// If manual installation is invoked, we need to create the installer file
$install = $input->get('setup', false, 'bool');

if ($install) {
	// Determines if the installation is a new installation or old installation.
	$obj = new stdClass();
	$obj->new = false;
	$obj->step = 1;
	$obj->status = 'installing';

	$contents = json_encode($obj);

	if (!JFile::exists($file)) {
		JFile::write($file, $contents);
	}
}

// Check if there's a file initiated for installation
$installCompleted = $input->get('active') == 'complete';

if (JFile::exists($file) || $installCompleted) {
	require_once(dirname(__FILE__) . '/setup/bootstrap.php');
	exit;
}

// Include main engine file
require_once(JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php');

PP::import('admin:/controllers/controller');
PP::ajax()->listen();

$legacyFiles = PP::log()->getLegacyLFiles();
$layout = $input->get('layout', '', 'default');

if ($legacyFiles && $layout != 'fixLegacy') {
	return $app->redirect('index.php?option=com_payplans&view=log&layout=fixLegacy');
}

// Check for environment changes
PP::checkEnvironment();

// For admin acl
if (!JFactory::getUser()->authorise('core.manage', 'com_payplans')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller = JControllerLegacy::getInstance('payplans');
$task = $input->get('task');

$controller->execute($task);
$controller->redirect();
