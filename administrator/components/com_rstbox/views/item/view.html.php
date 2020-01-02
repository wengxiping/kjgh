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

JHtml::_('behavior.modal');
jimport('joomla.application.component.view');
 
/**
 * Item View
 */
class RstboxViewItem extends JViewLegacy
{
    /**
     * display method of Item view
     * @return void
     */
    public function display($tpl = null) 
    {
        // Check for errors.
        if (count($errors = $this->get('Errors'))) 
        {
            JFactory::getApplication()->enqueueMessage($errors, 'error');
            return false;
        }

        // Load Smart Tags
        if (JFactory::getApplication()->input->get('layout') == "smarttags")
        {
            $smartTags = new NRFramework\SmartTags();
            $this->tags = $smartTags->get();  
        } else 
        {
            // Assign the Data
            $this->form     = $this->get('Form');
            $this->item     = $this->get('Item');
            $this->isnew    = (!isset($_REQUEST["id"])) ? true : false;
            $this->addToolBar();
        }

        // Display the template
        parent::display($tpl);
    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar() 
    {
        $input = JFactory::getApplication()->input;
        $input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);

        JToolBarHelper::title($isNew ? JText::_('New Box') : JText::_('Edit Box: ' . $this->item->name . " - ". $this->item->id));

        if (defined('nrJ4'))
        {
            \JToolbarHelper::saveGroup(
                [
                    ['apply', 'item.apply'],
                    ['save', 'item.save'],
                    ['save2new', 'item.save2new']
                ],
                'btn-success'
            );

            JToolbarHelper::modal('smarttags', 'icon-tag', JText::_("NR_SMARTTAGS"));
            JToolbarHelper::cancel('item.cancel');

            return;
        }
        
        JToolbarHelper::apply('item.apply');
        JToolBarHelper::save('item.save');
        JToolbarHelper::save2new('item.save2new');
        JToolbarHelper::modal('smarttags', 'icon-tag', JText::_("NR_SMARTTAGS"));
        JToolBarHelper::cancel('item.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}