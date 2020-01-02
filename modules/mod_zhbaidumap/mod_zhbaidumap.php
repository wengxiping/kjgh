<?php
/*------------------------------------------------------------------------
# mod_zhbaidumap - Zh BaiduMap Module
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JLoader::register('modZhBaiduMapHelper', JPATH_SITE.'/modules/mod_zhbaidumap/helpers/mod_zhbaidumap.php'); 
JLoader::register('comZhBaiduMapData', JPATH_SITE.'/components/com_zhbaidumap/helpers/map_data.php'); 

require JModuleHelper::getLayoutPath('mod_zhbaidumap', $params->get('layout', 'default'));