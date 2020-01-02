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

$engine = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
$exists = JFile::exists($engine);

if (!$exists) {
	return;
}

require_once($engine);

$lib = ES::modules($module);

// Module settings
$avatar = (int) $params->get('avatar', 1);
$cover = (int) $params->get('cover', 1);
$options = array('ordering' => $params->get('ordering', 'created'), 'limit' => $params->get('limit', 20));

$userId = $lib->my->id;

if (!$userId) {
	return;
}

$options['uid'] = $lib->my->id;

if (!$avatar) {
	$options['noavatar'] = true;
}

if (!$cover) {
	$options['nocover'] = true;
}

$model = ES::model('Photos');
$photos = $model->getModulePhotos($options);

if (!$photos) {
	return;
}

$ids = array();

foreach($photos as $photo) {
	$ids[] = $photo->id;
}

ES::cache()->cachePhotos($ids);

require($lib->getLayout());