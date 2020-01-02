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

jimport('joomla.filesystem.file');

// If Payplans System Plugin disabled then do nothing
$systemPlugin = JPluginHelper::isEnabled('system','payplans');
if (!$systemPlugin){
	return true;
}

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';
if (!JFile::exists($file)) {
	return;
}

require_once($file);

PP::initialize();

$userId = JFactory::getUser();
$userId = $userId->id;

if ($userId == 0) {
	return true;
}

$user = PP::user($userId);
$limit = $params->get('no_subscription');
$renew = $params->get('allow_renewal_link');

$status = $params->get('subscribe_status');
$model = PP::model('Subscription');

$options = array();
$options['status'] = $status;
$options['limit'] = $limit;
$options['userId'] =  $userId;

$subscriptions = $model->getUserSubscription($options);

require_once JModuleHelper::getLayoutPath('mod_payplans_subscription', 'default');
