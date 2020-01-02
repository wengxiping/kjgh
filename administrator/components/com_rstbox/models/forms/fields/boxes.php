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

JFormHelper::loadFieldClass('list');

class JFormFieldBoxes extends JFormFieldList
{
    /**
     * The box list
     *
     * @var object
     */
    private $boxes;

    /**
     * Render the input field
     *
     * @return void
     */
    protected function getInput()
    {
        if (!$this->boxes = $this->getBoxes())
        {
            return  JText::_('COM_RSTBOX_NO_BOXES_FOUND');
        }

        return parent::getInput();
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return    array   An array of JHtml options.
     */
    protected function getOptions()
    {
        if (!$this->boxes)
        {
            return;
        }

        $options = [];

        foreach ($this->boxes as $key => $box)
        {
            $options[] = JHTML::_('select.option', $box->id, $box->name . ' (' . $box->id . ')');
        }   

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get list of boxes
     *
     * @return void
     */
    private function getBoxes()
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'models');

        $model = JModelLegacy::getInstance('Items', 'RstboxModel', array('ignore_request' => true));
        $model->setState('filter.state', 1);
        $model->setState('filter.impressions', false);

        // Exclude active editing box
        if ($this->element['excludeeditingbox'] == 'true')
        {
            $input = JFactory::getApplication()->input;

            if ($input->get('option') == 'com_rstbox' && $input->get('layout') == 'edit')
            {
                $model->setState('filter.exclude', $input->getInt('id'));
            }
        }

        return $model->getItems();
    }
}