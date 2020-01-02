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
 * View class for the ZhBaiduMap Util Component
 */
class ZhBaiduMapViewZhBaiduMaps extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
		ZhBaiduMapHelper::addSubmenu('zhbaidumaps');

		$extList = $this->get('extList');
		$this->assignRef( 'extList',	$extList);                
                 
		$this->addToolBar();

                // Display the template
                parent::display($tpl);

		// Set the document
		$this->setDocument();

	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
            $help_url = 'http://wiki.zhuk.cc/index.php/Zh_BaiduMap_Description';
            JToolbarHelper::help('', false, $help_url);

            $canDo = ZhBaiduMapHelper::getUtilActions();
            
		JToolBarHelper::title(JText::_('COM_ZHBAIDUMAP_DASHBOARD_MANAGER'), 'util');
                
		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_zhbaidumap');
		}

		JHtmlSidebar::setAction('index.php?option=com_zhbaidumap');
                
		$this->sidebar = JHtmlSidebar::render();
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_ZHBAIDUMAP_DASHBOARD'));
	}

}
