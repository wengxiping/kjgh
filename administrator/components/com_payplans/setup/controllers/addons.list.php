<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/controller.php');

class PayplansControllerAddonsList extends PayplansSetupController
{
	public function execute()
	{
		$this->engine();

		// Get a list of folders in the module and plugins.
		$path = $this->input->get('path', '', 'default');

		// if ($this->isDevelopment()) {

		// 	$result = new stdClass();
		// 	$result->html = '<div style="padding:20px;background: #f4f4f4;border: 1px dotted #d7d7d7;margin-top:20px;">In development mode, this option is disabled.</div>';

		// 	return $this->output($result);
		// }

		$modulesExtractPath = PP_TMP . '/modules';
		$pluginsExtractPath = PP_TMP . '/plugins';

		// Get the modules list
		$modules = $this->getModulesList($path, $modulesExtractPath);

		// Get the plugins list
		$plugins = $this->getPluginsList($path, $pluginsExtractPath);

		$data = new stdClass();
		$data->modules = $modules;
		$data->plugins = $plugins;

		ob_start();
		include(dirname(__DIR__) . '/themes/steps/addons.list.php');
		$contents = ob_get_contents();
		ob_end_clean();

		$result = new stdClass();
		$result->html = $contents;
		$result->modulePath = $modulesExtractPath;
		$result->pluginPath = $pluginsExtractPath;

		// Since we combine maintenance page with this,
		// we need to get the scripts to execute as well
		$maintenance = $this->getMaintenanceScripts();

		$result->scripts = $maintenance['scripts'];
		$result->maintenanceMsg = $maintenance['message'];

		return $this->output($result);
	}

	private function getMaintenanceScripts()
	{
		$maintenance = PP::maintenance();

		// Get previous version installed
		$previous = $this->getPreviousVersion('script_version');

		$files = $maintenance->getScriptFiles($previous);

		$msg = JText::sprintf('COM_PP_INSTALLATION_MAINTENANCE_NO_SCRIPTS_TO_EXECUTE');

		if ($files) {
			$msg = JText::sprintf('COM_PP_INSTALLATION_MAINTENANCE_TOTAL_FILES_TO_EXECUTE', count($files));
		}

		$result = array('message' => $msg, 'scripts' => $files);

		return $result;
	}

	private function getPluginsList($path, $tmp)
	{
		$zip = $path . '/plugins.zip';

		$state = $this->ppExtract($zip, $tmp);

		// @TODO: Return errors
		if (!$state) {
			return false;
		}

		// Get a list of plugin groups
		$groups = JFolder::folders($tmp, '.', false, true);

		$plugins = array();

		foreach ($groups as $group) {
			$groupTitle = basename($group);

			// Get a list of items in each groups
			$items = JFolder::folders($group, '.', false, true);

			foreach ($items as $item) {
				$element = basename($item);
				$manifest = $item . '/' . $element . '.xml';

				// Read the xml file
				$parser = PP::getXml($manifest);

				if (!$parser) {
					continue;
				}

				$plugin = new stdClass();
				$plugin->element = $element;
				$plugin->group = $groupTitle;
				$plugin->title = (string) $parser->name;
				$plugin->version = (string) $parser->version;
				$plugin->description = (string) $parser->description;
				$plugin->description = trim($plugin->description);

				// all plugins should be installed by default
				$plugin->disabled = true;
				$plugin->checked = true;

				$plugins[] = $plugin;
			}
		}

		return $plugins;
	}

	private function getModulesList($path, $tmp)
	{
		$zip = $path . '/modules.zip';

		$state = $this->ppExtract($zip, $tmp);

		// @TODO: Return errors
		if (!$state) {
			return false;
		}

		// Get a list of modules
		$items = JFolder::folders($tmp, '.', false, true);

		$modules = array();
		$installedModules = array();

		// Get installed module.
		$installedModules = $this->getInstalledModules();

		// Get previous version installed.
		// If previous version exists, means this is an upgrade
		$isUpgrade = $this->getPreviousVersion('script_version');

		foreach ($items as $item) {
			$element = basename($item);
			$manifest = $item . '/' . $element . '.xml';

			// Read the xml file
			$parser = PP::getXml($manifest);

			$module = new stdClass();
			$module->title = (string) $parser->name;
			$module->version = (string) $parser->version;
			$module->description = (string) $parser->description;
			$module->description = trim($module->description);
			$module->element = $element;
			$module->disabled = false;
			$module->checked = false;

			// Check if the module already installed, put a flag
			// Disable this only if the module is checked.
			if ($installedModules && in_array($module->element, $installedModules)) {
				$module->disabled = true;
				$module->checked = true;
			}

			$modules[] = $module;
		}

		return $modules;
	}

	private function getInstalledPlugins($folder)
	{
		$db = PP::db();

		$query = "select `element` from `#__extensions` where `type` = " . $db->Quote('plugin');
		$query .= " and `folder` = " . $db->Quote($folder);
		$query .= " and `enabled` = " . $db->Quote('1');

		$db->setQuery($query);
		$results = $db->loadColumn();

		return $results;
	}



	/**
	 * Get all installed modules from the site (only for 3.x upgrade)
	 *
	 * @since   4.0
	 * @access  public
	 */
	private function getInstalledModules()
	{
		if ($this->isDevelopment()) {
			return array();
		}

		$db = PP::db();

		$moduleNames = array('mod_payplans_quickicon','mod_payplans_subscription');

		$modules = '';
		foreach($moduleNames as $module){
			$modules .= ($modules) ? ',' . $db->Quote($module) : $db->Quote($module);
		}

		// jos_modules
		$query = array();
		$query[] = 'SELECT '. $db->quoteName('module') .' FROM ' . $db->quoteName('#__modules');
		$query[] = ' WHERE ' . $db->quoteName('module') . ' IN (' . $modules . ')';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$modules = $db->loadColumn();

		return $modules;
	}

	private function isUpgradeFrom3x()
	{
		static $isUpgrade = null;

		if (is_null($isUpgrade)) {

			$isUpgrade = false;

			$db = JFactory::getDBO();

			$jConfig = JFactory::getConfig();
			$prefix = $jConfig->get('dbprefix');

			$query = "SHOW TABLES LIKE '%" . $prefix . "payplans_config%'";
			$db->setQuery($query);

			$result = $db->loadResult();

			if ($result) {
				// this is an upgrade. lets check if the upgrade from 3.x or not.
				$query = 'SELECT ' . $db->quoteName('value') . ' FROM ' . $db->quoteName('#__payplans_config') . ' WHERE ' . $db->quoteName('key') . '=' . $db->Quote('script_version');
				$db->setQuery($query);

				$scriptversion = $db->loadResult();

				if ($scriptversion) {
					$scriptversion = explode('.', $scriptversion);

					// We know if the scriptversion is equal to 3, this is upgrade from version 3.x
					if ($scriptversion[0] == '3') {
						$isUpgrade = true;
					}
				}
			}
		}

		return $isUpgrade;
	}
}
