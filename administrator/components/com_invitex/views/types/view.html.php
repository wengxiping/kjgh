<?php
/**
 * @package    Invitex
 *
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
/**
 * View to edit and display invitation types for invite anywhere.
 *
 * @since  1.6
 */
class InvitexViewtypes extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches    through the template paths.
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;

		$this->items = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->apiplugin = $this->get('APIpluginData');
		$this->provider_methods_multiselect = $this->get('methods_multiselect');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();

		$task	= JFactory::getApplication()->input->get('task');

		if ($task == 'edit')
		{
			$this->type_data = $this->get('Typedata');
		}

		InvitexHelper::addSubmenu('types');

		parent::display($tpl);
	}

	/**
	 * Add toolbar
	 *
	 * @return	void
	 */
	public function addToolbar()
	{
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;
		$task = $input->get('task');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$viewTitle = JText::_('TYPES');

		if ($task == 'new' )
		{
			$viewTitle = JText::_('TYPES_NEW');
		}
		elseif ($task == 'edit')
		{
			$viewTitle = JText::_('TYPES_EDIT');
		}

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title($viewTitle, 'list');
		}
		else
		{
			JToolbarHelper::title($viewTitle, 'types.png');
		}

		$layout = JFactory::getApplication()->input->get('layout');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$canDo = InvitexHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_invitex');
		}

		if ($layout != 'type')
		{
			JToolBarHelper::addNew('types.add');
			JToolBarHelper::deleteList('COM_INVITEX_DELETE_TYPE_CONFIRM', 'types.remove', "JTOOLBAR_DELETE");
		}

		$task	= JFactory::getApplication()->input->get('task');

		if ($task == "new" || $task == "edit")
		{
			JToolBarHelper::save('types.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::cancel('types.cancel', 'JTOOLBAR_CANCEL');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields ()
	{
		return array(
			'id' => JText::_('ID'),
			'name' => JText::_('TITLE'),
			'internal_name' => JText::_('INTERNAL_NAME')
		);
	}
}
