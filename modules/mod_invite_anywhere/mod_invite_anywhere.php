<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

$open_module_in = $params->get('open_module_in');
$invite_type = (INT) $params->get('invite_type');
$invite_url = (!empty($params->get('invite_url')))?$params->get('invite_url'):JUri::current();
$catch_act = $params->get('catch_action');
$button_text = $params->get('button_text');
$custom_code = $params->get('custom_code');

$helperPath = JPATH_SITE . '/components/com_invitex/helper.php';

if (!class_exists('cominvitexHelper'))
{
	//  require_once $path;
	JLoader::register('cominvitexHelper', $helperPath);
	JLoader::load('cominvitexHelper');
}

$cominvitexHelper = new cominvitexHelper;
$itemid	= $cominvitexHelper->getitemid('index.php?option=com_invitex&view=invites');

require JModuleHelper::getLayoutPath('mod_invite_anywhere', $params->get('layout', 'default'));
