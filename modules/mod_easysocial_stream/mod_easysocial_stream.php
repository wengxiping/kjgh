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

$lib = ES::modules($module);
$lib->renderComponentScripts();

// Determine the filter type
$filter = $params->get('filter_type', 'public');
$total = $params->get('total', 10);
$category = '';

$stream = ES::stream();

// Filter stream items by public stream items
if ($filter == 'public') {
	$stream->getPublicStream($total, 0, null, 'dashboard');
}

// Filter by group categories
if ($filter == 'groupcategory') {
	$category = (int) $params->get('group_category', '');

	if (!$category) {
		return;
	}

	$options = array('customlimit' => $total, 'clusterCategory' => $category, 'clusterType' => SOCIAL_TYPE_GROUP, 'ignoreUser' => true);
	$stream->get($options, array('perspective' => 'dashboard'));
}

// Filter by event categories
if ($filter == 'eventcategory') {
	$category = (int) $params->get('event_category', '');

	if (!$category) {
		return;
	}

	$options = array('customlimit' => $total, 'clusterCategory' => $category, 'clusterType' => SOCIAL_TYPE_EVENT, 'ignoreUser' => true);
	$stream->get($options, array('perspective' => 'dashboard'));
}

// Filter by page categories
if ($filter == 'pagecategory') {
	$category = (int) $params->get('page_category', '');

	if (!$category) {
		return;
	}

	$options = array('customlimit' => $total, 'clusterCategory' => $category, 'clusterType' => SOCIAL_TYPE_PAGE, 'ignoreUser' => true);

	$stream->get($options, array('perspective' => 'dashboard'));
}

if ($my->id && $params->get('story_form', true)) {
	$story = ES::story(SOCIAL_TYPE_USER);
	$story->setTarget($my->id);
	$stream->story = $story;
}

$readmoreURL = '';
$readmoreText = '';

if ($my->id) {
	$readmoreURL = ESR::dashboard(array(), false);
	$readmoreText = 'MOD_EASYSOCIAL_STREAM_GOTO_DASHBOARD';
} else {
	$readmoreURL = ESR::login(array(), false);
	$readmoreText = 'MOD_EASYSOCIAL_STREAM_LOGIN';
}

require($lib->getLayout());
