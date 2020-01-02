<?php
/**
 * ------------------------------------------------------------------------
 * JA Quick Contact Module for J3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$basepath = JURI::root(true).'/modules/' . $module->module . '/assets/';

$doc->addStyleSheet($basepath.'css/style.css');
//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
if(file_exists(JPATH_SITE . '/' . $templatepath)) {
	$doc->addStyleSheet(JURI::root(true).'/'.$templatepath);
}

//script
//$doc->addScript($basepath.'script.js');

// load font awesome.
$found=false;
foreach ($doc->_styleSheets AS $k => $d) {
	if (preg_match('/font\-awesome/', $k) && preg_match('/\.css/', $k)) { // check if font awesome if loaded by another source.
		$found=true;
	}
}
if ($found==false) $doc->addStyleSheet("https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css",'text/css');