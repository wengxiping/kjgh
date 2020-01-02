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
 * View class for the ZhBaidu MapRouters Component
 */
class ZhBaiduMapViewMapTypes extends JViewLegacy
{
	protected $state;

	// Overwriting JView display method
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		$this->state = $this->get('State');

		ZhBaiduMapHelper::addSubmenu('maptypes');

 
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
		$canDo = ZhBaiduMapHelper::getTypeActions();
		JToolBarHelper::title(JText::_('COM_ZHBAIDUMAP_MAPTYPE_MANAGER'), 'maptype');
		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('maptype.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) 
		{
			JToolBarHelper::editList('maptype.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) 
		{
				JToolBarHelper::divider();
				JToolBarHelper::publish('maptypes.publish', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::unpublish('maptypes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
				JToolBarHelper::divider();
		}
		if ($canDo->get('core.delete')) 
		{
			JToolBarHelper::deleteList('', 'maptypes.delete', 'JTOOLBAR_DELETE');
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
		$document->setTitle(JText::_('COM_ZHBAIDUMAP_MAPTYPE_ADMINISTRATION'));
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
			//'h.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'h.id' => JText::_('COM_ZHBAIDUMAP_MAPTYPE_HEADING_ID'),
			'h.title' => JText::_('COM_ZHBAIDUMAP_MAPTYPE_HEADING_TITLE'),
			'h.published' => JText::_('COM_ZHBAIDUMAP_MAPTYPE_HEADING_PUBLISHED')
		);
	}

}
