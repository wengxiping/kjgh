<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');

// Include the helper functions only once
require_once dirname(__FILE__) . '/helper.php';
$no_of_friends = $params->get('no_of_friends', '', 'INT');
require JModuleHelper::getLayoutPath('mod_invite_friends', $params->get('layout', 'default'));
