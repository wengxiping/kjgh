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

/**
 * ZhBaiduMap component helper.
 */
abstract class ZhBaiduMapHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
		JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_DASHBOARD'), 'index.php?option=com_zhbaidumap&view=zhbaidumaps', $submenu == 'zhbaidumaps');
		JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPS'), 'index.php?option=com_zhbaidumap&view=mapmaps', $submenu == 'mapmaps');
		JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERS'), 'index.php?option=com_zhbaidumap&view=mapmarkers', $submenu == 'mapmarkers');
		JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERGROUPS'), 'index.php?option=com_zhbaidumap&view=mapmarkergroups', $submenu == 'mapmarkergroups');
		//JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPROUTERS'), 'index.php?option=com_zhbaidumap&view=maprouters', $submenu == 'maprouters');
		JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPPATHS'), 'index.php?option=com_zhbaidumap&view=mappaths', $submenu == 'mappaths');
		//JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPTYPES'), 'index.php?option=com_zhbaidumap&view=maptypes', $submenu == 'maptypes');
		JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_zhbaidumap', $submenu == 'categories');
		JHtmlSidebar::addEntry(JText::_('COM_ZHBAIDUMAP_SUBMENU_ABOUT'), 'index.php?option=com_zhbaidumap&view=abouts', $submenu == 'abouts');
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-zhbaidumap {background-image: url(../media/com_zhbaidumap/images/map-48x48.png);}');
		if ($submenu == 'categories') 
		{
			$document->setTitle(JText::_('COM_ZHBAIDUMAP_ADMINISTRATION_CATEGORIES'));
		}
	}
	/**
	 * Get the actions
	 */
	public static function getMapActions($mapId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($mapId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.map.'.(int) $mapId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function getMarkerActions($mapmarkerId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($mapmarkerId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.mapmarker.'.(int) $mapmarkerId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function getMarkerGroupActions($mapmarkerGroupId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($mapmarkerGroupId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.mapmarkergroup.'.(int) $mapmarkerGroupId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}


	public static function getRouterActions($maprouterId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($maprouterId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.maprouter.'.(int) $maprouterId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function getPathActions($mappathId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($mappathId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.mappath.'.(int) $mappathId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function getTypeActions($maptypeId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($maptypeId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.maptype.'.(int) $maptypeId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
	

	        public static function getUtilActions($mapUtilId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($mapUtilId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.util.'.(int) $mapUtilId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
	
	      public static function getAboutActions($mapAboutId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($mapAboutId)) {
			$assetName = 'com_zhbaidumap';
		}
		else {
			$assetName = 'com_zhbaidumap';
			//$assetName = 'com_zhbaidumap.about.'.(int) $mapAboutId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
	
	
}
