<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * View to display and resend reminders.
 *
 * @since  1.6
 */
class InvitexViewReminder extends JViewLegacy
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
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
		$this->activeFilters = $this->get('ActiveFilters');
		$this->inviters 	= $this->get('Inviters');
		$this->providers	= $this->get('Providers');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		InvitexHelper::addSubmenu('reminder');

		if (JVERSION >= 3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function addToolbar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$canDo = InvitexHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_invitex');
		}

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('REMINDER'), 'list');
		}
		else
		{
			JToolbarHelper::title(JText::_('REMINDER'), 'reminder.png');
		}

		JToolBarHelper::save('reminder.send_reminder', 'COM_INVITEX_SEND');

		// JToolBarHelper::cancel( 'cancel', 'Close' );
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
			'e.invitee_email' => JText::_('INVITEE_ID'),
			'e.modified' => JText::_('LAST_INV_SENT_ON')
		);
	}
}
