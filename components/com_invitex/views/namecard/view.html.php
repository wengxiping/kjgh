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
 * Namecard view
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class InvitexViewNamecard extends JViewLegacy
{
	/**
	 * Namecard view
	 *
	 * @param   string  $tpl  Name of template
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$this->invhelperObj   = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();
		$user                 = JFactory::getUser();

		$itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
		$uid    = $this->invhelperObj->getUserID();
		$table        = JUser::getTable();
		$this->oluser = '';

		if ($table->load($uid))
		{
		$this->oluser = JFactory::getUser($uid);
		}

		$this->model = $this->getModel();

		if ($this->oluser)
		{
			$this->tmpls = $this->model->getnamecardtemplates();
		}

		if (empty($this->oluser))
		{
			$mainframe = JFactory::getApplication();
			$msg = JText::_('NON_LOGIN_MSG');
			$uri = $_SERVER["REQUEST_URI"];
			$url = base64_encode($uri);
			$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		$this->itemid = $itemid;

		// Add Easysocial/Jomsocial toolbar
		$this->toolbarHtml	= '';

		if ($this->invitex_params->get("reg_direct") == "JomSocial"  && $this->invitex_params->get("jstoolbar") == '1')
		{
			$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject('JomSocial');
			$this->toolbarHtml = $sociallibraryobj->getToolbar();
		}

		if ($this->invitex_params->get("reg_direct") == "EasySocial"  && $this->invitex_params->get("estoolbar") == '1')
		{
			$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject('EasySocial');
			$this->toolbarHtml = $sociallibraryobj->getToolbar();
		}

		parent::display($tpl);
	}
}
