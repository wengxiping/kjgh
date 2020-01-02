<?php
/**
 * @package    Invitex
 *
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to display top inviters
 *
 * @since  1.6
 */
class InvitexViewTopinviters extends JViewLegacy
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

		$option = $input->get('option');
		$layout = $input->get('layout', 'default');

		$this->items = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');

		// Check for errors.

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		InvitexHelper::addSubmenu('topinviters');

		if (JVERSION >= 3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}

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
		$option    = $input->get('option');
		$layout    = $input->get('layout', 'default');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$canDo = InvitexHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_invitex');
		}

		// Get the toolbar object instance.
		$bar = JToolBar::getInstance('toolbar');

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('TOP_INVITERS'), 'list');
		}
		else
		{
			JToolbarHelper::title(JText::_('TOP_INVITERS'), 'users.png');
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
				'u.username' => JText::_('COM_INVITEX_INVITER'),
				'total_sent' => JText::_('TOTAL_SENT'),
		);
	}
}
