<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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

// Get the current logged in user object
$my = ES::user();
$option = JRequest::getVar('option');

if ($option == 'com_easysocial' && !$params->get('show_on_easysocial', false)) {
	return;
}

// load ES frontend language.
FD::language()->loadSite();

$lib = ES::modules($module);
$toolbar = ES::toolbar();

// Load required js for notification
$lib->renderComponentScripts();

$esconfig = ES::config();
$showFriends = $params->get('show_friends', true) && $esconfig->get('friends.enabled') ? true : false;

$options = array(
					'forceoption' => true,
					'toolbar' => true,
					'dashboard' => $params->get('show_dashboard', true),
					'friends' => $showFriends,
					'conversations' => $params->get('show_conversations', true),
					'notifications'	=> $params->get('show_notifications', true),
					'search' => $params->get('show_search', true),
					'login'	=> $params->get('show_login', true),
					'profile' => $params->get('show_profile', true),
					'responsive' => $params->get('responsive', true),
					'modulePopboxPosition' => $params->get('module_popbox_position', 'bottom'),
					'modulePopboxCollision' => $params->get('module_popbox_collision', 'none')
				);

require($lib->getLayout());
