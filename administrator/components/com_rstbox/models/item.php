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
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Item Model
 */
class RstboxModelItem extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param       type    The table type to instantiate
     * @param       string  A prefix for the table class name. Optional.
     * @param       array   Configuration array for model. Optional.
     * @return      JTable  A database object
     * @since       2.5
     */
    public function getTable($type = 'Items', $prefix = 'RstboxTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param       array   $data           Data for the form.
     * @param       boolean $loadData       True if the form is to load its own data (default case), false if not.
     * @return      mixed   A JForm object on success, false on failure
     * @since       2.5
     */
    public function getForm($data = array(), $loadData = true) 
    {
        // Get the form.
        $form = $this->loadForm('com_rstbox.item', 'item', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) 
        {
            return false;
        }

        return $form;
    }

    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        $files = array(
            "item_publishingassignments",
            "item_appearance",
            "item_trigger",
            "item_advanced"
        );

        foreach ($files as $key => $value)
        {
            $form->loadFile($value, false);
        }

        $form->addFieldPath(JPATH_PLUGINS . '/system/nrframework/fields');

        JPluginHelper::importPlugin('engagebox');

        parent::preprocessForm($form, $data, $group);
    }

    public function getItem($pk = null)
    {
        $item   = parent::getItem($pk);
        $params = $item->params;

        if (is_array($params) && count($params))
        {
            foreach ($params as $key => $value)
            {
                if (!isset($item->$key) && !is_object($value))
                {
                    $item->$key = $value;
                }
            }

            unset($item->params);
        }

        return $item;
    }

       /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_rstbox.edit.form.data', array());

        if (empty($data))
        {
            $data = $this->getItem();
        }

        // In case the boxtype is missing default to 'custom'
        if (!isset($data->boxtype) || is_null($data->boxtype))
        {
            $data->boxtype = "custom";
        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  The form data.
     *
     * @return  boolean  True on success.
     * @since   1.6
     */

    public function save($data)
    {
        $params = json_decode($data['params'], true);
        
        if (is_null($params))
        {
            $params = array();
        }

        // correct the publish date details
        if (isset($params['assign_datetime_param_publish_up']))
        {
            NRFramework\Functions::fixDateOffset($params['assign_datetime_param_publish_up']);
        }

        if (isset($params['assign_datetime_param_publish_down']))
        {
            NRFramework\Functions::fixDateOffset($params['assign_datetime_param_publish_down']);
        }

        $data['params'] = json_encode($params);

        return parent::save($data);
    }

    /**
     * Method to validate form data.
     */
    public function validate($form, $data, $group = null)
    {
        // Fix empty box title
        if (empty($data["name"]))
        {
            $data["name"] = JText::_("COM_RSTBOX_UNTITLED_BOX");
        }

        $newdata = array();
        $params  = array();
        $this->_db->setQuery('SHOW COLUMNS FROM #__rstbox');
        $dbkeys = $this->_db->loadObjectList('Field');
        $dbkeys = array_keys($dbkeys);

        foreach ($data as $key => $val)
        {
            if (in_array($key, $dbkeys))
            {
                $newdata[$key] = $val;
            }
            else
            {
                $params[$key] = $val;
            }
        }

        $newdata['params'] = json_encode($params);

        return $newdata;
    }

    /**
     * Method to copy an item
     *
     * @access    public
     * @return    boolean    True on success
     */
    function copy($id)
    {
        $item = $this->getItem($id);

        unset($item->_errors);
        $item->id = 0;
        $item->published = 0;
        $item->name = JText::sprintf('NR_COPY_OF', $item->name);

        $item = $this->validate(null, (array) $item);

        return ($this->save($item));
    }
}

