<?php
/**
 * ------------------------------------------------------------------------
 * JA Builder Admin Menu Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

$jinput = JFactory::getApplication()->input;
$edit = $jinput->get('layout', '');
$disabled = false;
if ($edit=='edit') $disabled=true;
require JModuleHelper::getLayoutPath('mod_jabuilder_admin_menu', $params->get('layout', 'default'));
