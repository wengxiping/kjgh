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

use Joomla\Utilities\ArrayHelper;

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * ZhBaiduMaps Model
 */
class ZhBaiduMapModelZhBaiduMaps extends JModelList
{
	var $extList;


	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);
	}


	public function getextList() 
	{
            if (!isset($this->extList)) 
            {       

                    $this->_db->setQuery($this->_db->getQuery(true)
                            ->select('h.name, h.manifest_cache, h.type, h.enabled, h.element, h.folder, h.client_id ')
                            ->from('#__extensions as h')
                            ->where('h.element LIKE "%zhbaidu%"')
                            ->order('h.package_id, h.folder, h.element'));

                    $this->extList = $this->_db->loadObjectList();


            }

                       
		return $this->extList;
	}

	
}
