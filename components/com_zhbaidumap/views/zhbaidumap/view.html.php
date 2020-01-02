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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * View class for the ZhBaiduMap Component
 */
class ZhBaiduMapViewZhBaiduMap extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
		// Assign data to the view
		$this->item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}


		// Map API Key
		$mapapikey4map = $this->get('MapAPIKey');
		$this->assignRef( 'mapapikey4map',	$mapapikey4map );
		$mapapiversion = $this->get('MapAPIVersion');
		$this->assignRef( 'mapapiversion',	$mapapiversion );
		
		$placemarktitletag = $this->get('PlacemarkTitleTag');
		$this->assignRef( 'placemarktitletag',	$placemarktitletag );

		
		// Map markers
		$markers = $this->get('Markers');
		$this->assignRef( 'markers',	$markers );

		// Map markergroups
		$markergroups = $this->get('MarkerGroups');
		$this->assignRef( 'markergroups',	$markergroups );
		// Group list manager
		$mgrgrouplist = $this->get('MgrGroupsList');
		$this->assignRef( 'mgrgrouplist',	$mgrgrouplist );

		$licenseinfo = $this->get('LicenseInfo');
		$this->assignRef( 'licenseinfo',	$licenseinfo );
		
		// Map routers
		$routers = $this->get('Routers');
		$this->assignRef( 'routers',	$routers );

		// Map paths
		$paths = $this->get('Paths');
		$this->assignRef( 'paths',	$paths );
				
		$mapcompatiblemode = $this->get('CompatibleMode');
		$this->assignRef( 'mapcompatiblemode',	$mapcompatiblemode );

		$mapcompatiblemodersf = $this->get('CompatibleModeRSF');
		$this->assignRef( 'mapcompatiblemodersf',	$mapcompatiblemodersf );
		
		// Map types
		$maptypes = $this->get('MapTypes');
		$this->assignRef( 'maptypes',	$maptypes );
		
		// Protocol
		$httpsprotocol = $this->get('HttpsProtocol');
		$this->assignRef( 'httpsprotocol',	$httpsprotocol );

		// LoadType
		$loadtype = $this->get('LoadType');
		$this->assignRef( 'loadtype',	$loadtype );

		$centerplacemarkid = $this->get('CenterPlacemarkId');
		$this->assignRef( 'centerplacemarkid',	$centerplacemarkid );

		$centerplacemarkaction = $this->get('CenterPlacemarkAction');
		$this->assignRef( 'centerplacemarkaction',	$centerplacemarkaction );

		$mapzoom = $this->get('MapZoom');
		$this->assignRef( 'mapzoom',	$mapzoom );

		$mapwidth = $this->get('MapWidth');
		$this->assignRef( 'mapwidth',	$mapwidth );
		$mapheight = $this->get('MapHeight');
		$this->assignRef( 'mapheight',	$mapheight );
                
		$externalmarkerlink = $this->get('ExternalMarkerLink');
		$this->assignRef( 'externalmarkerlink',	$externalmarkerlink );

                $usermarkersfilter = $this->get('UserMarkersFilter');
		$this->assignRef( 'usermarkersfilter',	$usermarkersfilter );
		
		//
		$placemarklistid = $this->get('PlacemarkListID');
		$this->assignRef( 'placemarklistid',	$placemarklistid );

		$explacemarklistid = $this->get('ExPlacemarkListID');
		$this->assignRef( 'explacemarklistid',	$explacemarklistid );

		$grouplistid = $this->get('GroupListID');
		$this->assignRef( 'grouplistid',	$grouplistid );

		$categorylistid = $this->get('CategoryListID');
		$this->assignRef( 'categorylistid',	$categorylistid );
		
		$mapid = $this->get('MapID');
		$this->assignRef( 'mapid',	$mapid );

                $pathlistid = $this->get('PathListID');
		$this->assignRef( 'pathlistid',	$pathlistid );

		$expathlistid = $this->get('ExPathListID');
		$this->assignRef( 'expathlistid',	$expathlistid );

		$pathgrouplistid = $this->get('PathGroupListID');
		$this->assignRef( 'pathgrouplistid',	$pathgrouplistid );

		$pathcategorylistid = $this->get('PathCategoryListID');
		$this->assignRef( 'pathcategorylistid',	$pathcategorylistid );
                
		// Display the view
		parent::display($tpl);
	}
}
