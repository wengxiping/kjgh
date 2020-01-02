<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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

$lib = ES::modules($module);

// Get the current logged in user object
$my = ES::user();

// This module will only appear on event pages
$view = $lib->input->get('view', '', 'cmd');;
$layout = $lib->input->get('layout', '', 'cmd');
$id = $lib->input->get('id', '', 'int');
$uid = $lib->input->get('uid', '', 'int');
$type = $lib->input->get('type', '', 'string');

$eventView = false;

$allowedViews = array('albums', 'videos', 'audios');

if ($uid && $type == SOCIAL_TYPE_EVENT && in_array($view, $allowedViews)) {
	$eventView = true;
	$id = $uid;
}

if (($view != 'events' || $layout != 'item' || !$id) && !$eventView) {
	return;
}

// Get the current event object
$event = ES::event($id);
$cover = $event->getCoverData();
$apps = $lib->getClusterApps($event);

require($lib->getLayout());