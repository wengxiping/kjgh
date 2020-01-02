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
 * View class for the ZhBaidu MapMarkerGroups Component
 */
class ZhBaiduMapViewMapMarkerGroups extends JViewLegacy
{

	protected $state;

	// Overwriting JView display method
	function display($tpl = null) 
	{
                // Get data from the model
                $items = $this->get('Items');
                $pagination = $this->get('Pagination');
 
				$this->state = $this->get('State');
 
 				ZhBaiduMapHelper::addSubmenu('mapmarkergroups');

                // Check for errors.
                if (count($errors = $this->get('Errors'))) 
                {
                        JError::raiseError(500, implode('<br />', $errors));
                        return false;
                }
                // Assign data to the view
                $this->items = $items;
                $this->pagination = $pagination;
 
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
		$canDo = ZhBaiduMapHelper::getMarkerGroupActions();
		JToolBarHelper::title(JText::_('COM_ZHBAIDUMAP_MAPMARKERGROUP_MANAGER'), 'mapmarkergroup');
		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('mapmarkergroup.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) 
		{
			JToolBarHelper::editList('mapmarkergroup.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) 
		{
				JToolBarHelper::divider();
				JToolBarHelper::publish('mapmarkergroups.publish', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::unpublish('mapmarkergroups.unpublish', 'JTOOLBAR_UNPUBLISH', true);
				JToolBarHelper::divider();
		}
		if ($canDo->get('core.delete')) 
		{
			JToolBarHelper::deleteList('', 'mapmarkergroups.delete', 'JTOOLBAR_DELETE');
		}
		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_zhbaidumap');
		}


		JHtmlSidebar::setAction('index.php?option=com_zhbaidumap');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);
				
				
		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_zhbaidumap'), 'value', 'text', $this->state->get('filter.category_id'))
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
		$document->setTitle(JText::_('COM_ZHBAIDUMAP_MAPMARKERGROUP_ADMINISTRATION'));
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
			'h.id' => JText::_('COM_ZHBAIDUMAP_MAPMARKERGROUP_HEADING_ID'),
			'h.title' => JText::_('COM_ZHBAIDUMAP_MAPMARKERGROUP_HEADING_TITLE'),
                        'h.userorder' => JText::_('COM_ZHBAIDUMAP_MAPMARKERGROUP_HEADING_USERORDER'),
			'h.published' => JText::_('COM_ZHBAIDUMAP_MAPMARKERGROUP_HEADING_PUBLISHED')
		);
	}

}
