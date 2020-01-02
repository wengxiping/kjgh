<?php
defined('_JEXEC') or die();

include_once(dirname(__FILE__).'/assets/asset.php');

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));