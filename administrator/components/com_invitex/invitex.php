<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

/*
 * this is the file which exicute first
 * and call to controll.pho file
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
$document = JFactory::getDocument();

if (JVERSION >= '1.6.0')
{
	$core_js = JURI::root() . 'media/system/js/core.js';
	$flg = 0;

	foreach ($document->_scripts as $name => $ar)
	{
		if ($name == $core_js)
		{
			$flg = 1;
		}
	}

	if ($flg == 0)
	{
		$document->addScript($core_js);
	}
}

if (JVERSION < '3.0')
{
	// Icon constants.
	define('INVITEX_ICON_CHECKMARK', " icon-ok-sign");
	define('INVITEX_ICON_MINUS', " icon-minus");
	define('INVITEX_ICON_PLUS', " icon-plus-sign");
	define('INVITEX_ICON_EDIT', " icon-apply ");
	define('INVITEX_ICON_CART', " icon-shopping-cart");
	define('INVITEX_ICON_BACK', " icon-arrow-left");
	define('INVITEX_ICON_REMOVE', " icon-remove");

	// Define wrapper class
	if (!defined('INVITEX_WRAPPER_CLASS'))
	{
		define('INVITEX_WRAPPER_CLASS', "invitex-wrapper techjoomla-bootstrap");
	}

	// Other
	JHtml::_('behavior.tooltip');
}
else
{
	// Icon constants.
	define('INVITEX_ICON_CHECKMARK', " icon-checkmark");
	define('INVITEX_ICON_MINUS', " icon-minus-2");
	define('INVITEX_ICON_PLUS', " icon-plus-2");
	define('INVITEX_ICON_EDIT', " icon-pencil-2");
	define('INVITEX_ICON_CART', " icon-cart");
	define('INVITEX_ICON_BACK', " icon-arrow-left-2");
	define('INVITEX_ICON_REMOVE', " icon-cancel-2");

	jimport('joomla.html.html.bootstrap');

	// Define wrapper class
	if (!defined('INVITEX_WRAPPER_CLASS'))
	{
		define('INVITEX_WRAPPER_CLASS', "invitex-wrapper");
	}

	// Tabstate
	JHtml::_('behavior.tabstate');

	// Other
	JHtml::_('behavior.tooltip');

	// Bootstrap tooltip and chosen js
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_invitex');
}

$document->addStyleSheet(JURI::base() . 'components/com_invitex/assets/css/invitex.css');
$document->addScript(JURI::base() . 'components/com_invitex/assets/js/invitex.js');

$helperPath = JPATH_SITE . '/components/com_invitex/helper.php';

if (!class_exists('cominvitexHelper'))
{
	// Require_once $path;
	JLoader::register('cominvitexHelper', $helperPath);
	JLoader::load('cominvitexHelper');
}

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';

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

// Redirect if set by the controller
$controller	= JControllerLegacy::getInstance('Invitex');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
