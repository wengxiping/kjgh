<?php
/**
 * @package     InviteX
 * @subpackage  com_invitex
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

/**
 * InviteX config model.
 *
 * @since  1.6
 */
class InvitexModelConfig extends JModelLegacy
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  1.0
	 */
	public function __construct($config = array())
	{
		$this->invhelperObj = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();

		parent::__construct($config);
	}

	/**
	 * Function to get plugin data
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getmethods_multiselect()
	{
		$this->invhelperObj = new cominvitexHelper;
		$config_methods = $this->invitex_params->get('invite_methods');
		$opt = $this->getmethods_multiselect_options();

		return JHTML::_('select.genericlist', $opt, 'config[invite_methods][]', 'class="inputbox" multiple="multiple"', 'value', 'text', $config_methods);
	}

	/**
	 * Function to get multiselect options
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getmethods_multiselect_options()
	{
		$this->invhelperObj = new cominvitexHelper;
		$config_methods = $this->invitex_params->get('invite_methods');
		$opt = $inv_methods = array();
		$inv_methods['manual'] = JText::_('INV_METHOD_MANUAL');
		$oi_path = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';

		if (JFile::exists($oi_path))
		{
			$inv_methods['oi_email'] = JText::_('INV_METHOD_OI_EMAIL');
			$inv_methods['oi_social'] = JText::_('INV_METHOD_OI_SOCIAL');
		}

		$inv_methods['other_tools']	= JText::_('INV_METHOD_OTHER_TOOLS');
		$inv_methods['inv_by_url']	= JText::_('INV_METHOD_BY_URL');
		$inv_methods['social_apis']	= JText::_('INV_METHOD_SOCIAL_APIS');
		$inv_methods['email_apis']	= JText::_('INV_METHOD_EMAIL_APIS');
		$inv_methods['sms_apis'] = JText::_('INV_METHOD_SMS_APIS');

		foreach ($config_methods as $m)
		{
			if (isset($inv_methods[$m]))
			{
				$opt[] = JHTML::_('select.option', $m, $inv_methods[$m]);
				unset($inv_methods[$m]);
			}
		}

		if ($inv_methods)
		{
			foreach ($inv_methods as $v => $t)
			{
				$opt[] = JHTML::_('select.option', $v, $t);
			}
		}

		return $opt;
	}

	/**
	 * Function to get plugin data
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getAPIpluginData()
	{
		$query = "SELECT extension_id as id,name,element,enabled as published FROM #__extensions WHERE enabled=1 AND folder ='techjoomlaAPI'";

		$this->_db->setQuery($query);

		return $this->_db->loadobjectList();
	}

	/**
	 * Function to get plugin names
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getPluginNames()
	{
		// FIRST GET THE EMAIL-ALERTS RELATED PLUGINS FRM THE `jos_plugins` TABLE
		$email_alert_plugins_array = array();
		$this->_db->setQuery('SELECT element FROM #__extensions WHERE folder = \'emailalerts\'  AND enabled = 1');

		$email_alert_plugins_array = $this->_db->loadColumn();

		return  $email_alert_plugins_array;
	}

	/**
	 * Function to return array of allowed domains for sending the invitation
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getAllowedDomains()
	{
		$domain_names = array();

		if ($this->invitex_params->get('invite_domains'))
		{
			$domain_names = explode(",", $this->invitex_params->get('invite_domains'));
		}

		return $domain_names;
	}

	/**
	 * Function to return description of the 'emailalert' plugins from the XML file
	 *
	 * @param   array  $plugin_array  Plugins array
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getPluginDescriptionFromXML($plugin_array)
	{
		$plugin_description_array = array();

		$i = 0;

		if ($plugin_array)
		{
			foreach ($plugin_array as $emailalert_plugin)
			{
				$data = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . '/plugins/emailalerts/' . $emailalert_plugin . '/' . $emailalert_plugin . '.xml');

				// Store it in the array
				$plugin_description_array[$i++] = $data['description'];
			}
		}

		// Return the array
		return $plugin_description_array;
	}

	/**
	 * Method to store invitex template config
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function store()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		if ($post)
		{
			$OI_file = JPATH_SITE . "/components/com_invitex/openinviter/config.php";

			// Save Initex config into database
			$db = JFactory::getDbo();
			$query = "SELECT namekey from `#__invitex_config`";
			$db->setQuery($query);

			$config_rows = $db->loadColumn();

			$inv_config = $input->post->get('config', '', 'RAW');

			// Plugin trigger on before save template config
			JPluginHelper::importPlugin('actionlog');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('invitexOnBeforeSaveInviteTemplateConfig', array($inv_config));

			foreach ($inv_config as $k => $v)
			{
				if (is_array($v))
				{
					$v = implode(',', $v);
				}

				$c_data = new stdClass;
				$c_data->namekey = $k;
				$c_data->value = $v;

				if (!in_array($k, $config_rows))
				{
					$db->insertObject('#__invitex_config', $c_data, 'id');
				}
				else
				{
					$query = "SELECT id from `#__invitex_config` where namekey='" . $k . "'";
					$db->setQuery($query);
					$c_data->id = $db->loadResult();
					$db->updateObject('#__invitex_config', $c_data, 'id');
				}
			}

			// Plugin trigger on after save template config
			JPluginHelper::importPlugin('actionlog');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('invitexOnAfterSaveInviteTemplateConfig', array($inv_config));

			return true;
		}
		else
		{
			return false;
		}
	}
}
