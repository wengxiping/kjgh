<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
jimport('joomla.application.component.view');

/**
 * Url Stats view
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class InvitexViewUrlstats extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Stats view
	 *
	 * @param   string  $tpl  Name of template
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;
		$this->invhelperObj = new cominvitexHelper;
		$this->invitex_params	= $this->invhelperObj->getconfigData();
		$user = JFactory::getUser();

		if (!$user->id)
		{
			$title = JText::_('LOGIN_TITLE_STAT');
			$mainframe = JFactory::getApplication();
			$msg = JText::_('LOGIN_TITLE_STAT');
			$uri = $_SERVER["REQUEST_URI"];
			$url = base64_encode($uri);
			$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);

			return false;
		}

		$this->itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
		$this->items = $this->get('Items');

		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->invite_status = $this->get('status');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Add Easysocial/Jomsocial toolbar
		if ($this->invitex_params->get("reg_direct") == "JomSocial"  && $this->invitex_params->get("jstoolbar") == '1')
		{
			$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject('JomSocial');
			echo $sociallibraryobj->getToolbar();
		}

		if ($this->invitex_params->get("reg_direct") == "EasySocial"  && $this->invitex_params->get("estoolbar") == '1')
		{
			$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject('EasySocial');
			echo $sociallibraryobj->getToolbar();
		}

		parent::display($tpl);
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
			'u.email' => JText::_('EMAILS') ,
			'u.name' => JText::_('NAMES')
		);
	}
}
