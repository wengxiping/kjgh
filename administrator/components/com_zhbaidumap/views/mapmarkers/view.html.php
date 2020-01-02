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
 * View class for the ZhBaidu MapMarkers Component
 */
class ZhBaiduMapViewMapMarkers extends JViewLegacy
{

	protected $state;
	protected $items;
	protected $pagination;

	// Overwriting JView display method
	function display($tpl = null) 
	{
		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		$this->state = $this->get('State');

		ZhBaiduMapHelper::addSubmenu('mapmarkers');
		
		$mapList = $this->get('mapList');
		$this->assignRef( 'mapList',	$mapList);

		$groupList = $this->get('groupList');
		$this->assignRef( 'groupList',	$groupList);

		$userList = $this->get('userList');
		$this->assignRef( 'userList',	$userList);
		
		$iconList = $this->get('iconList');
		$this->assignRef( 'iconList',	$iconList);
		
                // Check for errors.
                if (count($errors = $this->get('Errors'))) 
                {
                        JError::raiseError(500, implode('<br />', $errors));
                        return false;
                }

 
                // Set the toolbar
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
		$canDo = ZhBaiduMapHelper::getMarkerActions();
		JToolBarHelper::title(JText::_('COM_ZHBAIDUMAP_MAPMARKER_MANAGER'), 'mapmarker');
		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('mapmarker.add', 'JTOOLBAR_NEW');
		}
		if (($canDo->get('core.edit'))  || ($canDo->get('core.edit.own')))
		{
			JToolBarHelper::editList('mapmarker.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) 
		{
				JToolBarHelper::divider();
				JToolBarHelper::publish('mapmarkers.publish', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::unpublish('mapmarkers.unpublish', 'JTOOLBAR_UNPUBLISH', true);
				JToolBarHelper::divider();
		}
		
		if ($canDo->get('core.delete')) 
		{
			JToolBarHelper::deleteList('', 'mapmarkers.delete', 'JTOOLBAR_DELETE');
		}
		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_zhbaidumap');
		}

		JHtmlSidebar::setAction('index.php?option=com_zhbaidumap');

		JHtmlSidebar::addFilter(
			JText::_('COM_ZHBAIDUMAP_MAPMARKER_FILTER_MAP'),
			'filter_mapid',
			JHtml::_('select.options', $this->mapList, 'value', 'text', $this->state->get('filter.mapid'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_ZHBAIDUMAP_MAP_USER_IMAGESELECT'),
			'filter_icontype',
			JHtml::_('select.options', $this->iconList, 'value', 'text', $this->state->get('filter.icontype'))
		);

				
		JHtmlSidebar::addFilter(
			JText::_('COM_ZHBAIDUMAP_MAPMARKER_FILTER_PLACEMARK_GROUP'),
			'filter_markergroup',
			JHtml::_('select.options', $this->groupList, 'value', 'text', $this->state->get('filter.markergroup'))
		);
				
				
		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_zhbaidumap'), 'value', 'text', $this->state->get('filter.category_id'))
		);
				
		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);
				

		JHtmlSidebar::addFilter(
			JText::_('COM_ZHBAIDUMAP_MAPMARKER_FILTER_USER'),
			'filter_createdbyuser',
			JHtml::_('select.options', $this->userList, 'value', 'text', $this->state->get('filter.createdbyuser'))
		);

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
		$document->setTitle(JText::_('COM_ZHBAIDUMAP_MAPMARKER_ADMINISTRATION'));
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'h.id' => JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_ID'),
			'h.title' => JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_TITLE'),
			'h.access' => JText::_('JGRID_HEADING_ACCESS'),		
                        'h.userorder' => JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_USERORDER'),
			'h.published' => JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_PUBLISHED')
		);
	}
	
}
