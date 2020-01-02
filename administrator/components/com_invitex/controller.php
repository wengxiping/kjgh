<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

/**
 * @This component is to be converted from
 * joomla1.o to 1.5 This is the file where
 * the control come after calling by main file
 * in this component main file is invitex.php;
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
/**
 * Invitex Component Controller
 *
 * @since  1.5
 */
class InvitexController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		JLoader::import('components.com_invitex.helpers.invitex', JPATH_ADMINISTRATOR);
		$view = JFactory::getApplication()->input->getCmd('view', 'dashboard');

		JFactory::getApplication()->input->set('view', $view);

		$layout = JFactory::getApplication()->input->getCmd('layout', 'default');
		JFactory::getApplication()->input->set('layout', $layout);

		parent::display();

		return $this;
	}

	/**
	 * Method to get Version
	 *
	 * @return  array   version
	 *
	 * @since  3.7.0
	 */
	public function getVersion()
	{
		echo $recdata = file_get_contents('http://techjoomla.com/vc/index.php?key=abcd1234&product=invitex');
		jexit();
	}

	/**
	 * Method to populate Users
	 *
	 * @return  array   populate users
	 *
	 * @since  3.7.0
	 */
	public function populateUsers()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		$model = $this->getModel('invitation_limit');

		$result = $model->populateUsers();

		header('Content-type: application/json');

		if ($result)
		{
			echo json_encode(1);
		}
		else
		{
			echo json_encode(-1);
		}

		jexit();
	}

	/**
	 * Manual Setup related chages: For now - 1. for overring the bs-2 view
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function setup()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$jinput = JFactory::getApplication()->input;
		$takeBackUp = $jinput->get("takeBackUp", 1);

		$cominvitexHelper     = new cominvitexHelper;
		$defTemplate = $cominvitexHelper->getSiteDefaultTemplate(0);
		$templatePath = JPATH_SITE . '/templates/' . $defTemplate . '/html/';

		$statusMsg = array();
		$statusMsg["component"] = array();

		// 1. Override component view
		$siteBs2views = JPATH_ROOT . "/components/com_invitex/views_bs2/site";

		// Check for com_invitex folder in template override location
		$compOverrideFolder  = $templatePath . "com_invitex";

		if (JFolder::exists($compOverrideFolder))
		{
			if ($takeBackUp)
			{
				// Rename
				$backupPath = $compOverrideFolder . '_' . date("Ymd_H_i_s");
				$status = JFolder::move($compOverrideFolder, $backupPath);
				$statusMsg["component"][] = JText::_('COM_INVITEX_TAKEN_BACKUP_OF_OVERRIDE_FOLDER') . $backupPath;
			}
			else
			{
				$delStatus = JFolder::delete($compOverrideFolder);
			}
		}

		// Copy
		$status = JFolder::copy($siteBs2views, $compOverrideFolder);
		$statusMsg["component"][] = JText::_('COM_INVITEX_OVERRIDE_DONE') . $compOverrideFolder;

		// 2. Modules override
		$modules = JFolder::folders(JPATH_ROOT . "/components/com_invitex/views_bs2/modules/");
		$statusMsg["modules"] = array();

		foreach ($modules as $modName)
		{
			$this->overrideModule($templatePath, $modName, $statusMsg, $takeBackUp);
		}

		$this->displaySetup($statusMsg);
				exit;
	}

	/**
	 * Override the Modules
	 *
	 * @param   string  $templatePath  templatePath eg JPATH_SITE . '/templates/protostar/html/'
	 * @param   string  $modName       Module name
	 * @param   array   &$statusMsg    The array of config values.
	 * @param   array   $takeBackUp    flag
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function overrideModule($templatePath, $modName, &$statusMsg, $takeBackUp)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$bs2ModulePath = JPATH_ROOT . "/components/com_invitex/views_bs2/modules/" . $modName;
		$overrideBs2ModulePath = $templatePath . $modName;

		$statusMsg["modules"][] = JText::sprintf('COM_INVITEX_OVERRIDING_THE_MODULE', $modName);

		if (JFolder::exists($overrideBs2ModulePath))
		{
			if ($takeBackUp)
			{
				// Rename
				$backupPath = $overrideBs2ModulePath . '_' . date("Ymd_H_i_s");
				$status = JFolder::move($overrideBs2ModulePath, $backupPath);

				$statusMsg["modules"][] = JText::sprintf('COM_INVITEX_TAKEN_OF_MODULE_ND_BACKUP_PATH',  $modName, $backupPath);
			}
			else
			{
				$delStatus = JFolder::delete($overrideBs2ModulePath);
			}
		}

		// Copy
		$status = JFolder::copy($bs2ModulePath, $overrideBs2ModulePath);
		$statusMsg["modules"][] = JText::sprintf('COM_INVITEX_COMPLETED_MODULE_OVERRIDE', "<b>" . $modName . "</b>");
	}

	/**
	 * Override the Modules
	 *
	 * @param   array  $statusMsg  The array of config values.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function displaySetup($statusMsg)
	{
		echo "<br/> =================================================================================";
		echo "<br/> " . JText::_("COM_INVITEX_BS2_OVERRIDE_PROCESS_START");
		echo "<br/> =================================================================================";

		foreach ($statusMsg as $key => $extStatus)
		{
			echo "<br/> <br/><br/>*****************  " . JText::_("COM_INVITEX_BS2_OVERRIDING_FOR")
			. " <strong>" . $key . "</strong> ****************<br/>";

			foreach ($extStatus as $k => $status)
			{
				$index = $k + 1;
				echo $index . ") " . $status . "<br/> ";
			}
		}

		echo "<br/> " . JText::_("COM_INVITEX_BS2_OVERRIDING_DONE");
	}
}
