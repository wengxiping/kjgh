<?php

/**
 * @package         EngageBox
 * @version         3.5.2 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * Items View
 */
class RstboxViewItems extends JViewLegacy
{
    /**
     * Items view display method
     * 
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * 
     * @return  mixed  A string if successful, otherwise a JError object.
     */
    function display($tpl = null) 
    {
        $this->items         = $this->get('Items');
        $this->state         = $this->get('State');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->config        = JComponentHelper::getParams('com_rstbox');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) 
        {
            JFactory::getApplication()->enqueueMessage($errors, 'error');
            return false;
        }

        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);
    }

    /**
     *  Add Toolbar to layout
     */
    protected function addToolBar() 
    {

        $canDo = EBHelper::getActions();
        $state = $this->get('State');
        $viewLayout = JFactory::getApplication()->input->get('layout', 'default');

        if ($viewLayout == 'import')
        {
            JFactory::getDocument()->setTitle(JText::_('RSTBOX') . ': ' . JText::_('NR_IMPORT_ITEMS'));
            JToolbarHelper::title(JText::_('RSTBOX') . ': ' . JText::_('NR_IMPORT_ITEMS'));
            JToolbarHelper::back();
        }
        else
        {
            JToolBarHelper::title(JText::_('RSTBOX'));

            if ($canDo->get('core.create'))
            {
                JToolbarHelper::addNew('item.add');
            }
            
            if ($canDo->get('core.edit'))
            {
                JToolbarHelper::editList('item.edit');
            }

            if ($canDo->get('core.create'))
            {
                JToolbarHelper::custom('items.copy', 'copy', 'copy', 'JTOOLBAR_DUPLICATE', true);
            }

            if ($canDo->get('core.edit.state') && $state->get('filter.state') != 2)
            {
                JToolbarHelper::publish('items.publish', 'JTOOLBAR_PUBLISH', true);
                JToolbarHelper::unpublish('items.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            }

            if ($canDo->get('core.delete') && $state->get('filter.state') == -2)
            {
                JToolbarHelper::deleteList('', 'items.delete', 'JTOOLBAR_EMPTY_TRASH');
            }
            else if ($canDo->get('core.edit.state'))
            {
                JToolbarHelper::trash('items.trash');
            }

            if ($canDo->get('core.create'))
            {
                JToolbarHelper::custom('items.export', 'box-remove', 'box-remove', 'NR_EXPORT');
                JToolbarHelper::custom('items.import', 'box-add', 'box-add', 'NR_IMPORT', false);
            }

            JToolbarHelper::custom('items.reset', 'refresh', 'box-reset', 'COM_RSTBOX_RESET_STATISTICS');

            if ($canDo->get('core.admin'))
            {
                JToolbarHelper::preferences('com_rstbox');
            }
        }

        JToolbarHelper::help("Help", false, "http://www.tassos.gr/joomla-extensions/responsive-scroll-triggered-box-for-joomla/docs");
    }
}