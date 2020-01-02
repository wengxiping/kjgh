<?php
/*------------------------------------------------------------------------
# com_zhbaidumap - Zh BaiduMap
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
		$this->MapXsuffix = "ZhBDMCOM";

		$this->markercluster = 0;
                $this->area_restriction = 0;
                

		$this->main_lang = "";

        $this->featureMarkerWithLabel = 0;
		$this->use_object_manager = 0;

		$this->current_custom_js_path = JURI::root() .'components/com_zhbaidumap/assets/js/';	
		$current_custom_js_path = $this->current_custom_js_path;

		
		$this->useObjectStructure = 1;
		$useObjectStructure = $this->useObjectStructure;
		
		
// ***** Init Section End *************************************


// ***** Settings Begin *************************************

$map = $this->item;
$markers = $this->markers;
$paths = $this->paths;
$routers = $this->routers;
$maptypes = $this->maptypes;

$mgrgrouplist = $this->mgrgrouplist;
$markergroups = $this->markergroups;


$centerplacemarkid = $this->centerplacemarkid;
$centerplacemarkactionid = $this->centerplacemarkid;
$centerplacemarkaction = $this->centerplacemarkaction;
$externalmarkerlink = (int)$this->externalmarkerlink;

$placemarklistid = $this->placemarklistid;
$explacemarklistid = $this->explacemarklistid;
$grouplistid = $this->grouplistid;
$categorylistid = $this->categorylistid;
//
// Pass it but not use there (only in query)
$routelistid = "";//$this->routelistid;
$exroutelistid = "";//$this->exroutelistid;
$routegrouplistid = "";
$routecategorylistid = "";//$this->routecategorylistid;

// Pass, used in query
$pathlistid = $this->pathlistid;
$expathlistid = $this->expathlistid;
$pathgrouplistid = $this->pathgrouplistid;
$pathcategorylistid = $this->pathcategorylistid;
//

$mapzoom = $this->mapzoom;

// addition parameters
if ($this->usermarkersfilter == "")
{
	$usermarkersfilter = (int)$map->usermarkersfilter;
}
else
{
	$usermarkersfilter = (int)$this->usermarkersfilter;
}

$mapMapWidth = $this->mapwidth;
$mapMapHeight = $this->mapheight;


// -- -- extending ------------------------------------------
// class suffix, for example for module use
$cssClassSuffix = "";


// -- -- -- component options - begin -----------------------
$compatiblemode = $this->mapcompatiblemode;
$compatiblemodersf = $this->mapcompatiblemodersf;

$licenseinfo = $this->licenseinfo;

$apikey4map = $this->mapapikey4map;
$loadtype = $this->loadtype;
$apiversion = $this->mapapiversion;

$main_lang = $this->main_lang;

$this->urlProtocol = "http";
if ($this->httpsprotocol != "")
{
	if ((int)$this->httpsprotocol == 0)
	{
		$this->urlProtocol = 'https';
	}
}	

$urlProtocol = $this->urlProtocol;

$placemarkTitleTag = $this->placemarktitletag;

// Fix Global Scope Variable names
$this->apikey4map = $apikey4map;
$this->apiversion = $apiversion;

// -- -- -- component options - end -------------------------

// ***** Settings End ***************************************



require_once (JPATH_SITE . '/components/com_zhbaidumap/views/zhbaidumap/tmpl/display_map_data.php');


// add local variables for common script
//   because module doesn't use object model

$main_lang = $this->main_lang;

$markercluster = $this->markercluster;
$area_restriction = $this->area_restriction;
$featureMarkerWithLabel = $this->featureMarkerWithLabel;


$use_object_manager = $this->use_object_manager;

$useObjectStructure = $this->useObjectStructure;

$current_custom_js_path = $this->current_custom_js_path;	


require_once (JPATH_SITE . '/components/com_zhbaidumap/views/zhbaidumap/tmpl/display_script.php');

