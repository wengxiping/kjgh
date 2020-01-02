<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
if (class_exists('shRouter')) {
	$sefConfig = shRouter::shGetConfig();
} 
else {
	$sefConfig = Sh404sefFactory::getConfig();
}
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------


// load JS language strings. If we are creating urls on the
// fly, after an automatic redirection, they may not be loaded yet
$lang = JFactory::getLanguage();
$lang->load('com_payplans');

// real start
$Itemid = isset($Itemid) ? $Itemid : null;
$limit = isset($limit) ? $limit : null;
$limitstart = isset($limitstart) ? $limitstart : null;

// main vars
$option = isset($option) ? $option : null;
$view = isset($view) ? $view : 'plan';
$task = isset($task) ? $task : null;
$layout = isset($layout) ? $layout : null;

$group_id = isset($group_id) ? $group_id : null;
$plan_id = isset($plan_id) ? $plan_id : null;


// item id work
if (!function_exists('_getPayplansMenus')) {
	function _getPayplansMenus()
	{
		static $menus = null;

		if ($menus ===null) {
			$menu = JFactory::getApplication()->getMenu();
			$menus = $menu->getItems('component_id',JComponentHelper::getComponent('com_payplans')->id);
		}
	
		return $menus;
	}
}

if (!function_exists('_getPayplansUrlVars')) {
	function _getPayplansUrlVars()
	{
		return array('view', 'task', 'plan_id', 'order_id', 'payment_id', 'app_id', 'subscription_id', 'user_id');
	}	
}

if (!function_exists('_findPayplansMatchCount')) {
	function _findPayplansMatchCount($menu, $query)
	{
		$vars = _getPayplansUrlVars();
		$count = 0;
		foreach($vars as $var)
		{
			//variable not requested
			if(!isset($query[$var])) {
				continue;
			}
	
			//variable not exist in menu
			if(!isset($menu[$var])) {
				continue;
			}
	
			//exist but do not match
			if($menu[$var] !== $query[$var]){
				/* 
				 * return 0, because if some variables are in conflict
				 * then variable appended in query will be desolved during parsing 
				 * e.g.
				 * 
				 * index.php?option=com_payplans&view=plan
				 * index.php/subscribe
				 * 
				 * index.php?option=com_payplans&view=plan&task=subscribe&plan_id=1
				 * index.php/subscribe1
				 * 
				 * index.php?option=com_payplans&view=plan&task=subscribe&plan_id=2
				 * index.php/subscribe1?plan_id=2   <== *** WRONG ***
				 * index.php/subscribe?task=subscribe&plan_id=2   <== *** RIGHT ***
				 */ 
				return 0;
			}
	
			$count++;
		}
		return $count;
	}
}

$ppmenus = _getPayplansMenus();

//If item id is not set then we need to extract those
$selMenu = null;

$query = array();
$query['task'] = $task;
$query['view'] = $view;
$query['option'] = $option;

if (empty($Itemid) && $ppmenus) {
	$count = 0;
	$selMenu = $ppmenus[0];

	foreach($ppmenus as $menu) {
		//count matching
		$matching = _findPayplansMatchCount($menu->query,$query);

		if ($count >= $matching) {
			continue;
		}

		//current menu matches more
		$count = $matching;
		$selMenu = $menu;
	}

	//assig ItemID of selected menu
	$Itemid = $selMenu->id;
}

// prepare the menu item view for later reference.
$menuView = '';
$xMenu = '';

if ($Itemid) {
	$xMenu = JFactory::getApplication()->getMenu()->getItem($Itemid);
	$menuView = (isset($xMenu->query['view']) && $xMenu->query['view']) ? $xMenu->query['view'] : '';
}

if (!empty($Itemid)){
	// $shAppendString = '&Itemid='.$Itemid;  // append current Itemid
	shAddToGETVarsList('Itemid', $Itemid);
}

// insert component name from menu
$shPPName = shGetComponentPrefix($option); 
$shPPName = empty($shSampleName) ? getMenuTitle($option, $task, $Itemid, null, $shLangName) : $shPPName;
$shPPName = (empty($shPPName) || $shPPName == '/') ? 'PP':$shPPName;

$title[] = $shPPName;

// build url first based on view, but make use of other vars ($task,..) as needed
if (!empty($view)) {
	if ($menuView != $view) {
		$title[] = $view;
	}
}

// add more details based on $task
if (!empty($layout)) {
	$title[] = $layout;
	shRemoveFromGETVarsList('layout');
}

// add more details based on $task
if (!empty($task)) {
	$title[] = $task;
}

if (isset($print) && $print) {
	$title[] = 'print';

	shRemoveFromGETVarsList('print');
}

if (isset($invoice_key) && $invoice_key) {
	$title[] = $invoice_key;
	shRemoveFromGETVarsList('invoice_key');
}

if (isset($subscription_key) && $subscription_key) {
	$title[] = $subscription_key;
	shRemoveFromGETVarsList('subscription_key');
}

if (isset($payment_key) && $payment_key) {
	$title[] = $payment_key;
	shRemoveFromGETVarsList('payment_key');
}

// process group_id
if (isset($group_id) && $group_id) {
	$title[] = $group_id;
}
shRemoveFromGETVarsList('group_id');

// process plan_id
if (isset($plan_id) && $plan_id) {
	$title[] = $plan_id;
}
shRemoveFromGETVarsList('plan_id');

if (isset($tmpl) && $tmpl && $tmpl == 'component') {
	shRemoveFromGETVarsList('tmpl');
}

shRemoveFromGETVarsList('view');
shRemoveFromGETVarsList('task');

shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');

if (!empty($Itemid)) {
	shRemoveFromGETVarsList('Itemid');
}

if (!empty($limit)) {
	shRemoveFromGETVarsList('limit');
}

if (isset($limitstart)) {
	shRemoveFromGETVarsList('limitstart');
}

// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef) {
	$string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString,
	(isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
	(isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change ---------------------------