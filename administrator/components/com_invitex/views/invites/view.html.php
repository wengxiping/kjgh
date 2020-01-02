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
 * View to display and edit send invitations.
 *
 * @since  1.6
 */
class InvitexViewInvites extends JViewLegacy
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
		$this->items = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->inviters 	= $this->get('Inviters');
		$this->providers	= $this->get('Providers');

		$sstatus[] = JHtml::_('select.option', '', JText::_('COM_INVITEX_SELECT_ACC'));
		$sstatus[] = JHtml::_('select.option', 1, JText::_('COM_INVITEX_YES'));
		$sstatus[] = JHtml::_('select.option', 2, JText::_('COM_INVITEX_NO'));

		$this->sstatus = $sstatus;

		$sent_status[] = JHtml::_('select.option', '', JText::_('COM_INVITEX_SELECT_SENT'));
		$sent_status[] = JHtml::_('select.option', 1, JText::_('COM_INVITEX_YES'));
		$sent_status[] = JHtml::_('select.option', 2, JText::_('COM_INVITEX_NO'));

		$this->sent_status = $sent_status;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		InvitexHelper::addSubmenu('invites');

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

		// Get the toolbar object instance.
		$bar = JToolBar::getInstance('toolbar');
		$canDo = InvitexHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_invitex');
		}

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('COM_INVITEX_INVITES'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_INVITEX_INVITES'), 'stat.png');
		}

		JToolBarHelper::DeleteList('COM_INVITEX_DELETE_QUEUE_CONFRIM', 'invites.remove', 'JTOOLBAR_DELETE');
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
			'import_email.id' => 'Email ID',
			'ii.message' => 'Message',
			'import_email.sent' => 'Sent',
			'import_email.invitee_name' => 'Invitee Name',
			'expires' => 'Expires',
			'provider_email' => 'provider Email',
		);
	}
}
