<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

$path = JPATH_SITE . '/components/com_invitex/helper.php';

if (!class_exists('CominvitexHelper'))
{
	JLoader::register('CominvitexHelper', $path);
	JLoader::load('CominvitexHelper');
}

$invitexHelper = new CominvitexHelper;

$invitexHelper->loadInvitexAssetFiles();

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_invitex');
}

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base(true) . '/media/com_invitex/css/invitex.css');

$helperPath = JPATH_SITE . '/components/com_invitex/helper.php';

if (!class_exists('cominvitexHelper'))
{
	JLoader::register('cominvitexHelper', $helperPath);
	JLoader::load('cominvitexHelper');
}

if (!class_exists('techjoomlaHelperLogs'))
{
	JLoader::register('techjoomlaHelperLogs', $path);
	JLoader::load('techjoomlaHelperLogs');
}

// Require the base controller
require_once  JPATH_COMPONENT . '/controller.php';

// Require specific controller if requested
if ($controller = JFactory::getApplication()->input->getWord('controller'))
{
	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';

	if (file_exists($path))
	{
		require_once $path;
	}
	else
	{
		$controller = '';
	}
}

// Create the controller
$classname	= 'InvitexController' . ucfirst($controller);
$controller = new $classname;

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
