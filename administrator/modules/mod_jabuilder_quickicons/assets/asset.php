<?php
defined('_JEXEC') or die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$basepath = JURI::root(true).'/administrator/modules/' . $module->module . '/assets/';

$doc->addStyleSheet($basepath.'css/style.css');

//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
if(file_exists(JPATH_ADMINISTRATOR . '/' . $templatepath)) {
	$doc->addStyleSheet(JURI::root(true).'/'.$templatepath);
}