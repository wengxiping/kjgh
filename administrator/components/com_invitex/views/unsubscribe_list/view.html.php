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
 * View to edit and display unsubscribe list.
 *
 * @since  1.6
 */
class InvitexViewunsubscribe_List extends JViewLegacy
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

		/* $this->filterForm    = $this->get('FilterForm');
		 $this->activeFilters = $this->get('ActiveFilters');
		 Check for errors.*/
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		InvitexHelper::addSubmenu('unsubscribe_list');

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
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$canDo = InvitexHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_invitex');
		}

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('UNSUB_LIST'), 'list');
		}
		else
		{
			JToolbarHelper::title(JText::_('UNSUB_LIST'), 'unsubscribe.png');
		}

		JToolBarHelper::DeleteList('COM_INVITEX_DELETE_UNSUB_LIST_CONFIRM', 'unsubscribe_list.remove', 'JTOOLBAR_DELETE');
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
				'invitee_email', 'invitee_email',
		);
	}
}
