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
 * View to display and edit configuration-templates.
 *
 * @since  1.6
 */
class InvitexViewconfig extends JViewLegacy
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
		$this->_setToolBar();

		$this->invhelperObj	= new cominvitexHelper;
		$this->invitex_params	= $this->invhelperObj->getconfigData();

		$apiplugin = $this->get('APIpluginData');
		$this->apiplugin = $apiplugin;

		$provider_methods_multiselect = $this->get('methods_multiselect');
		$this->provider_methods_multiselect = $provider_methods_multiselect;

		$email_alert_plugin_names = $this->get('PluginNames');
		$this->email_alert_plugin_names = $email_alert_plugin_names;
		$model = $this->getModel();

		// Get the description of the plugins from XML file
		$plugin_description_array = $model->getPluginDescriptionFromXML($email_alert_plugin_names);

		// Assign a ref	to the array
		$this->plugin_description_array = $plugin_description_array;
		$allowedDomains	=	$this->get('AllowedDomains');
		$this->allowedDomains = $allowedDomains;

		InvitexHelper::addSubmenu('config');

		parent::display($tpl);
	}

	/**
	 * Set toolbar
	 *
	 * @return	void
	 */
	public function _setToolBar()
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
			JToolbarHelper::title(JText::_('TEMPLATES'), 'list');
		}
		else
		{
			JToolbarHelper::title(JText::_('TEMPLATES'), 'process.png');
		}

		JToolBarHelper::save('config.save', 'JTOOLBAR_APPLY');
		JToolBarHelper::cancel('config.cancel', 'JTOOLBAR_CANCEL');
	}
}
