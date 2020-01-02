<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);

//  if ($dosef == false) return;

//  ------------------  standard plugin initialize function - don't change ---------------------------

//  ------------------  load language file - adjust as needed ----------------------------------------

//  $shLangIso = shLoadPluginLanguage( 'com_XXXXX', $shLangIso, '_SEF_SAMPLE_TEXT_STRING');

//  ------------------  load language file - adjust as needed ----------------------------------------

$view = isset($view) ? @$view : null;
$layout = isset($layout) ? @$layout : null;
$controller = isset($controller) ? @$controller : null;

$Itemid = isset($Itemid) ? @$Itemid : null;

//  $shWeblinksName = $swln
$swln = shGetComponentPrefix($option);

$swln = empty($swln) ? getMenuTitle($option, isset($view) ? $view:null, isset($Itemid) ? $Itemid:null, null, $shLangName) : $swln;

$swln = (empty($swln) || $swln == '/') ? 'invitex' : $swln;

if (!empty($swln))
{
	$title[] = $swln;
}

//  $title[]=$view;

//  if (isset($layout))

//  $title[]=$layout;

if (isset($task))
{
	if ($task == 'maito' || $task = 'get_access_token')
	{
		$dosef = false;
	}
}

if ($view == 'invites')
{
	switch ($layout)
	{
		case 'default':
		break;

		case 'apis':
		case 'captcha':
		case 'registered_users':
		case 'send_invites':
		$title[] = $layout;
		break;

		case 'send':
		$title[] = $rout;
		break;
	}
}

if ($view == 'stats')
{
	$title[] = 'stats';
}

if ($view == 'urlstats')
{
	$title[] = 'urlstats';
}

if ($view == 'resend')
{
	$title[] = 'resend';
}

if ($controller == 'invites')
{
	if (isset($task))
	{
		$title[] = $task;
	}
}

shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('view');
shRemoveFromGETVarsList('controller');
shRemoveFromGETVarsList('task');
shRemoveFromGETVarsList('layout');
shRemoveFromGETVarsList('rout');
shRemoveFromGETVarsList('lang');

if (!empty($Itemid))
{
	shRemoveFromGETVarsList('Itemid');
}

if (!empty($limit))
{
	shRemoveFromGETVarsList('limit');
}

if (isset($limitstart))
{
	// Limitstart can be zero
	shRemoveFromGETVarsList('limitstart');
}

if (isset($id))
{
	shRemoveFromGETVarsList('id');
}

if (isset($controller))
{
	shRemoveFromGETVarsList('controller');
}

//   $title[] = $cid."-".videoseriesGetShortcode( $cid).".html";

// ------------------  standard plugin finalize function - don't change ---------------------------

if ($dosef)
{
	$string = shFinalizePlugin(
				$string, $title, $shAppendString, $shItemidString,
				(isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
				(isset($shLangName) ? @$shLangName : null)
			);
}

//  ------------------  standard plugin finalize function - don't change ---------------------------
