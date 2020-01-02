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
 * View to edit and display invitation limits.
 *
 * @since  1.6
 */
class InvitexViewinvitation_Limit extends JViewLegacy
{
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
		$this->pagination    	= $this->get('Pagination');
		$this->state         	= $this->get('State');
		$this->activeFilters 	= $this->get('ActiveFilters');
		$this->inviters 		= $this->get('Inviters');
		$this->providers		= $this->get('Providers');
		$this->limit_installed	= $this->get('Limit_installed');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		InvitexHelper::addSubmenu('invitation_limit');

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

		if (JVERSION < 3.0)
		{
			$button = '<a class="toolbar" class="button validate" type="submit"
			onclick="javascript:confirm_update()" href="#"><span title="Save" class="icon-32-save"></span>Save</a>';
		}
		else
		{
			$button = '<button class="btn btn-small" onclick="javascript:confirm_update()" href="#">
				<span class="icon-save ">
				</span> Update Limits</button>';
		}

		$bar->appendButton('Custom', $button);

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('INV_LIMIT'), 'list');
		}
		else
		{
			JToolbarHelper::title(JText::_('INV_LIMIT'), 'limit.png');
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
			'il.userid' => JText::_('USER_ID'),
			'u.username' => JText::_('USER_NAME'),
			'il.limit' => JText::_('INV_LIMIT')
		);
	}
}
