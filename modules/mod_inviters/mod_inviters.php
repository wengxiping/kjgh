<?php
/**
 * @version     SVN: <svn_id>
 * @package     Invitex
 * @subpackage  mod_inviters
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

// Include module helper.
require_once __DIR__ . '/helper.php';
$document = JFactory::getDocument();
$root_url = Juri::root(true);
$document->addStyleSheet($root_url . '/modules/mod_inviters/mod_inviters.css');

// Get Inviters
$inviters = ModInvitexhelper::getInviters($params);
require JModuleHelper::getLayoutPath('mod_inviters', $params->get('layout', 'default'));
