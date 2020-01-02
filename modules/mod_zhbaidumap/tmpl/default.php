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


$document	= JFactory::getDocument();

// ***** Init Section Begin ***********************************

		$MapXsuffix = "ZhBDMMOD";

		$markercluster = 0;
		$markermanager = 0;
		
		$main_lang = "";
		$infobubble = 0;
		$featureMarkerWithLabel = 0;
		$use_object_manager = 0;

		$current_custom_js_path = JURI::root() .'components/com_zhbaidumap/assets/js/';

		$useObjectStructure = 0;
		
		
// ***** Init Section End *************************************


$id = $params->get('mapid', '');

$map = comZhBaiduMapData::getMap((int)$id);

// Change translation language and load translation
$currentLanguage = JFactory::getLanguage();
$currentLangTag = $currentLanguage->getTag();
if (isset($map->lang) && $map->lang != "")
{

	$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap', $map->lang, true);	

	$currentLanguage->load('mod_zhbaidumap', JPATH_SITE, $map->lang, true);	

}
else
{

	$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap', $currentLangTag, true);	

	$currentLanguage->load('mod_zhbaidumap', JPATH_SITE, $currentLangTag, true);	
	
}

if (isset($map) && (int)$map->id != 0)
{

// ***** Settings Begin *************************************
    
$centerplacemarkid = $params->get('centerplacemarkid', '');
$centerplacemarkaction = $params->get('centerplacemarkaction', '');
$centerplacemarkactionid = $params->get('centerplacemarkid', '');

$externalmarkerlink = (int)$params->get('externalmarkerlink', '');

$placemarklistid = $params->get('placemarklistid', '');
$explacemarklistid = $params->get('explacemarklistid', '');
$grouplistid = $params->get('grouplistid', '');
$categorylistid = $params->get('categorylistid', '');

// Pass it but not use there (only in query)
$routelistid = $params->get('routelistid', '');
$exroutelistid = $params->get('exroutelistid', '');
$routegrouplistid = "";
$routecategorylistid = $params->get('routecategorylistid', '');

// Pass, used in query
$pathlistid = $params->get('pathlistid', '');
$expathlistid = $params->get('expathlistid', '');
$pathgrouplistid = $params->get('pathgrouplistid', '');
$pathcategorylistid = $params->get('pathcategorylistid', '');
//

$usermarkersfilter = "";

// addition parameters
if ($usermarkersfilter == "")
{
	$usermarkersfilter = (int)$map->usermarkersfilter;
}
else
{
	$usermarkersfilter = (int)$usermarkersfilter;
}

if ($map->useajaxobject == 0)
{
	$markers = comZhBaiduMapData::getMarkers($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, 
											  $map->usermarkers, $usermarkersfilter, $map->usercontact, $map->markerorder);

        $paths = comZhBaiduMapData::getPaths($map->id, $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid);

}
else
{
	unset($markers);
        unset($paths);
}
$routers = comZhBaiduMapData::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
$maptypes = comZhBaiduMapData::getMapTypes();

$markergroups = comZhBaiduMapData::getMarkerGroups($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $map->markergrouporder);
$mgrgrouplist = comZhBaiduMapData::getMarkerGroupsManage($map->id, 
                                                            $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, 
                                                            $map->markergrouporder, $map->markergroupctlmarker, $map->markergroupctlpath, 
                                                            $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid);

$mapzoom = "";

$mapMapWidth = "";
$mapMapHeight = "";



// -- -- extending ------------------------------------------
// class suffix, for example for module use

$cssClassSuffix = $params->get('moduleclass_sfx', '');

// -- -- -- component options - begin -----------------------

$compatiblemode = comZhBaiduMapData::getCompatibleMode();
$compatiblemodersf = comZhBaiduMapData::getCompatibleModeRSF();

$licenseinfo = comZhBaiduMapData::getMapLicenseInfo();

$apikey4map = comZhBaiduMapData::getMapAPIKey();
$loadtype = comZhBaiduMapData::getLoadType();
$apiversion = comZhBaiduMapData::getMapAPIVersion();

$apitype = comZhBaiduMapData::getMapAPIType();

$httpsprotocol = comZhBaiduMapData::getHttpsProtocol();

$urlProtocol = 'http';
if ($httpsprotocol != "")
{
	if ((int)$httpsprotocol == 0)
	{
		$urlProtocol = 'https';
	}
}

$placemarkTitleTag = comZhBaiduMapData::getPlacemarkTitleTag();

// -- -- -- component options - end -------------------------

// ***** Settings End ***************************************




require_once (JPATH_SITE . '/components/com_zhbaidumap/views/zhbaidumap/tmpl/display_map_data.php');

require_once (JPATH_SITE . '/components/com_zhbaidumap/views/zhbaidumap/tmpl/display_script.php');

}
else
{
  echo JText::_( 'MOD_ZHBAIDUMAP_MAP_NOTFIND_ID' ).' '. $id;
}
