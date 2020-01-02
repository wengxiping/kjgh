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

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * ZhBaiduPath Model
 */
class ZhBaiduMapModelMapPath extends JModelAdmin
{
	var $mapList;
        var $mapapikey;

	var $mapDefLat;
	var $mapDefLng;

	var $markerGroupList;
	var $mapTypeList;
        var $httpsprotocol;
        
        var $map_height;
	

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'MapPath', $prefix = 'ZhBaiduMapTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_zhbaidumap.mappath', 'mappath', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_zhbaidumap/models/forms/mappath.js';
	}
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_zhbaidumap.edit.mappath.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}

	public function getmapList() 
	{
		if (!isset($this->mapList)) 
		{       

			$this->_db->setQuery($this->_db->getQuery(true)
				->select('h.title as text, h.id as value ')
				->from('#__zhbaidumaps_maps as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->where('1=1')
				->order('h.title'));

			$this->mapList = $this->_db->loadObjectList();

			// Custom Fields
			//if (!$this->mapList = $this->_db->loadObjectList()) 
			//{
			//	$this->setError($this->_db->getError());
			//}

		}

		return $this->mapList;
	}

	public function getmarkerGroupList() 
	{
		if (!isset($this->markerGroupList)) 
		{       

			$this->_db->setQuery($this->_db->getQuery(true)
				->select('h.title as text, h.id as value ')
				->from('#__zhbaidumaps_markergroups as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->where('1=1')
				->order('h.title'));

			$this->markerGroupList = $this->_db->loadObjectList();

			// Custom Fields
			//if (!$this->markerGroupList = $this->_db->loadObjectList()) 
			//{
			//	$this->setError($this->_db->getError());
			//}


		}

		return $this->markerGroupList;
	}
	
	
	public function getAPIKey() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $mapapikey = $params->get( 'map_map_key', '' );
	}
	public function getAPIVersion() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $mapapiversion = $params->get( 'map_api_version', '' );
	}
        
	public function getHttpsProtocol() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $httpsprotocol = $params->get( 'httpsprotocol', '' );
	}
        
        public function getMapHeight() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $map_height = $params->get( 'map_height', '' );
	}
	public function getDefLat() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $mapDefLat = $params->get( 'map_lat', '' );
	}

	public function getDefLng() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $mapDefLng = $params->get( 'map_lng', '' );
	}

	public function getMapTypeBaidu() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $mapMapTypeBaidu = $params->get( 'map_type_baidu', '' );
	}
	public function getMapTypeOSM() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $mapMapTypeOSM = 0; //$params->get( 'map_type_osm', '' );
	}
	public function getMapTypeCustom() 
	{
		// Get global params
		$params = JComponentHelper::getParams( 'com_zhbaidumap' );

		return $mapMapTypeCustom = 0; //$params->get( 'map_type_custom', '' );
	}
	
	public function getmapTypeList() 
	{
		if (!isset($this->mapTypeList)) 
		{       

			$this->_db->setQuery($this->_db->getQuery(true)
				->select('h.*, c.title as category ')
				->from('#__zhbaidumaps_maptypes as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->where('h.published=1')
				->order('h.title'));

			$this->mapTypeList = $this->_db->loadObjectList();

			// Custom Fields
			//if (!$this->mapTypeList = $this->_db->loadObjectList()) 
			//{
			//	$this->setError($this->_db->getError());
			//}

		}

		return $this->mapTypeList;
	}
	
}
