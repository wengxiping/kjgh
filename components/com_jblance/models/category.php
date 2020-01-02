<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	23 March 2012
 * @file name	:	models/category.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.model');

class JblanceModelCategory extends JModelLegacy {


	protected $item;

	/**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return	void
     * @since	2.5
     */
	protected function populateState()
    {
        $app = JFactory::getApplication();
        // Get the message id
        $input = JFactory::getApplication()->input;
        $id = $input->getInt('id');
        $this->setState('message.id', $id);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
        parent::populateState();
    }

	/**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	2.5
     */
	public function getTable($type = 'HelloWorld', $prefix = 'HelloWorldTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

	/**
     * Get the message
     * @return object The message to be displayed to the user
     */
	public function getItem()
    {
        if (!isset($this->item))
        {
            $id = $this->getState('message.id');
            $this->_db->setQuery($this->_db->getQuery(true)
                ->from('#__helloworld as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->select('h.greeting, h.params, c.title as category')
                ->where('h.id=' . (int)$id));
            if (!$this->item = $this->_db->loadObject())
            {
                $this->setError($this->_db->getError());
            }
            else
            {
                // Load the JSON string
                $params = new JRegistry;
                // loadJSON is @deprecated    12.1  Use loadString passing JSON as the format instead.
                //$params->loadString($this->item->params, 'JSON');
                $params->loadJSON($this->item->params);
                $this->item->params = $params;

                // Merge global params with item params
                $params = clone $this->getState('params');
                $params->merge($this->item->params);
                $this->item->params = $params;
            }
        }
        return $this->item;
    }

}
