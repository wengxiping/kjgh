<?php
/**
 * @package         Advanced Module Manager
 * @version         7.12.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\AbstractRegistryFormat as JRegistryFormat;

require_once __DIR__ . '/script.install.helper.php';

class Com_AdvancedModulesInstallerScript extends Com_AdvancedModulesInstallerScriptHelper
{
	public $name           = 'ADVANCED_MODULE_MANAGER';
	public $alias          = 'advancedmodulemanager';
	public $extname        = 'advancedmodules';
	public $extension_type = 'component';

	public function uninstall($adapter)
	{
		$this->uninstallPlugin($this->extname, 'system');
		$this->uninstallPlugin($this->extname, 'actionlog');
	}

	public function onBeforeInstall($route)
	{
		if ( ! parent::onBeforeInstall($route))
		{
			return false;
		}

		// Fix incorrectly formed versions because of issues in old packager
		$this->fixFileVersions(
			[
				JPATH_ADMINISTRATOR . '/components/com_advancedmodules/advancedmodules.xml',
				JPATH_PLUGINS . '/system/advancedmodules/advancedmodules.xml',
			]
		);

		return true;
	}

	public function onAfterInstall($route)
	{
		$this->createTable();
		$this->fixAssignments();
		$this->fixAssetIdField();
		$this->fixMirrorIdField();
		$this->fixCategoryField();
		$this->removeAdminMenu();
		$this->removeFrontendComponentFromDB();
		$this->deleteOldFiles();
		$this->fixAssetsRules();
		$this->checkForGeoIP();

		return parent::onAfterInstall($route);
	}

	private function createTable()
	{
		// main table
		$query = "CREATE TABLE IF NOT EXISTS `#__advancedmodules` (
			`moduleid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
			`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
			`mirror_id` INT(10) NOT NULL DEFAULT '0',
			`category` VARCHAR(50) NOT NULL,
			`params` TEXT NOT NULL,
			PRIMARY KEY (`moduleid`)
		) DEFAULT CHARSET=utf8;";
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function fixOldConfigNames()
	{
		$query = $this->db->getQuery(true)
			->select('params')
			->from('#__extensions')
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_advancedmodules'));
		$this->db->setQuery($query);

		$params = $this->db->loadResult();

		// Updating stuff from before v7.9.0
		if (strpos($params, 'use_categories') !== false)
		{
			return;
		}

		$params = str_replace([
			'"show_category":',
			'"show_color":',
			'"show_hideempty":',
			'"show_notes":',
			'"show_extra":',
		], [
			'"use_category":',
			'"use_color":',
			'"use_hideempty":',
			'"use_notes":',
			'"use_extra_fields":',
		], $params);

		$query = $this->db->getQuery(true)
			->update('#__extensions')
			->set($this->db->quoteName('params') . ' = ' . $this->db->quote($params))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_advancedmodules'));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function fixAssetIdField()
	{
		// add asset_id column
		$query = "SHOW COLUMNS FROM `" . $this->db->getPrefix() . "advancedmodules` LIKE 'asset_id'";
		$this->db->setQuery($query);
		$has_field = $this->db->loadResult();
		if ($has_field)
		{
			return;
		}

		$query = "ALTER TABLE `#__advancedmodules` ADD `asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `moduleid`";
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function fixMirrorIdField()
	{
		// add mirror_id column
		$query = "SHOW COLUMNS FROM `" . $this->db->getPrefix() . "advancedmodules` LIKE 'mirror_id'";
		$this->db->setQuery($query);
		$has_field = $this->db->loadResult();

		if ($has_field)
		{
			return;
		}

		$query = "ALTER TABLE `#__advancedmodules` ADD `mirror_id` INT(10) NOT NULL DEFAULT '0' AFTER `asset_id`";
		$this->db->setQuery($query);
		$this->db->execute();

		$this->fixMirrorIdFieldFixParams();
	}

	private function fixCategoryField()
	{
		// add asset_id column
		$query = "SHOW COLUMNS FROM `" . $this->db->getPrefix() . "advancedmodules` LIKE 'category'";
		$this->db->setQuery($query);
		$has_field = $this->db->loadResult();
		if ($has_field)
		{
			return;
		}

		$query = "ALTER TABLE `#__advancedmodules` ADD COLUMN `category` VARCHAR(50) NOT NULL AFTER `mirror_id`";
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function fixMirrorIdFieldFixParams()
	{
		// correct old keys and values
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('moduleid', 'id'))
			->select($this->db->quoteName('params'))
			->from($this->db->quoteName('#__advancedmodules'));
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		foreach ($rows as $row)
		{
			if (empty($row->params))
			{
				continue;
			}

			$params = json_decode($row->params);

			if (is_null($params))
			{
				continue;
			}

			// set urls_regex value if assignto_urls is used
			if (empty($params->mirror_module) || empty($params->mirror_moduleid))
			{
				continue;
			}

			$mirror_id = $params->mirror_moduleid;
			unset($params->mirror_module);
			unset($params->mirror_moduleid);

			$query->clear()
				->update($this->db->quoteName('#__advancedmodules'))
				->set($this->db->quoteName('mirror_id') . ' = ' . (int) $mirror_id)
				->set($this->db->quoteName('params') . ' = ' . $this->db->quote(json_encode($params)))
				->where($this->db->quoteName('moduleid') . ' = ' . (int) $row->id);
			$this->db->setQuery($query);
			$this->db->execute();
		}
	}

	private function removeAdminMenu()
	{
		// hide admin menu
		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__menu'))
			->where($this->db->quoteName('path') . ' = ' . $this->db->quote('advancedmodules'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
			->where($this->db->quoteName('client_id') . ' = 1');
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function removeFrontendComponentFromDB()
	{
		// remove frontend component from extensions table
		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_advancedmodules'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
			->where($this->db->quoteName('client_id') . ' = 0');
		$this->db->setQuery($query);
		$this->db->execute();

		JFactory::getCache()->clean('_system');
	}

	private function fixAssignments()
	{
		$this->fixAssignmentsRemoveInitialAssignments();
		$this->fixAssignmentsCorrectOldKeys();
	}

	private function fixAssignmentsRemoveInitialAssignments()
	{
		// remove initial menu assignment settings
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__advancedmodules'))
			->set($this->db->quoteName('params') . ' = ' . $this->db->quote(''))
			->where($this->db->quoteName('params') . ' = ' . $this->db->quote('{"assignto_menuitems":0,"assignto_menuitems_selection":[]}'));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function fixAssignmentsCorrectOldKeys()
	{
		// correct old keys and values
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('moduleid', 'id'))
			->select($this->db->quoteName('params'))
			->from($this->db->quoteName('#__advancedmodules'));
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		foreach ($rows as $row)
		{
			if (empty($row->params))
			{
				continue;
			}

			if ($row->params[0] != '{')
			{
				$row->params = str_replace('assignto_secscats', 'assignto_cats', $row->params);
				$row->params = str_replace('flexicontent', 'fc', $row->params);

				$params = JRegistryFormat::getInstance('INI')->stringToObject($row->params);
			}
			else
			{
				$params = json_decode($row->params);
				if (is_null($params))
				{
					$params = (object) [];
				}
			}

			// move tooltip to notes field
			if ( ! empty($params->tooltip))
			{
				$query->clear()
					->update($this->db->quoteName('#__modules'))
					->set($this->db->quoteName('note') . ' = ' . $this->db->quote($params->tooltip))
					->where($this->db->quoteName('id') . ' = ' . (int) $row->id);
				$this->db->setQuery($query);
				$this->db->execute();
				unset($params->tooltip);
			}

			// concatenate sef and non-sef url fields
			if (isset($params->assignto_urls_selection_sef))
			{
				$params->assignto_urls_selection = trim($params->assignto_urls_selection . "\n" . $params->assignto_urls_selection_sef);
				unset($params->assignto_urls_selection_sef);
				unset($params->show_url_field);
			}

			// set urls_regex value if assignto_urls is used
			if ( ! empty($params->assignto_urls) && ! isset($params->assignto_urls_regex))
			{
				$params->assignto_urls_regex = 1;
			}

			foreach ($params as $k => &$v)
			{
				switch ($k)
				{
					case 'assignto_php_selection':
					case 'assignto_urls_selection':
					case 'assignto_ips_selection':
						$v = str_replace(['\n', '\|'], ["\n", '|'], $v);
						break;
					case 'color':
						$v = str_replace('#', '', $v);
						$v = (empty($v) || $v == 'none') ? 'none' : $v;
						if ($v && $v != 'none')
						{
							$v = '#' . strtolower($v);
						}
						break;
					case 'assignto_users_selection':
						if ( ! is_array($v))
						{
							$v = explode('|', $v);
						}
						break;
					default:
						if (
							(substr($k, -10) == '_selection' || substr($k, -4) == '_inc')
							&& ! is_array($v)
						)
						{
							// convert | separated strings to arrays
							$v = explode('|', $v);
						}
						break;
				}
			}

			if ( ! empty($params->assignto_cats_selection))
			{
				foreach ($params->assignto_cats_selection as $key => $val)
				{
					if (strpos($val, ':') !== false)
					{
						$params->assignto_cats_selection[$key] = substr($val, strpos($val, ':') + 1);
					}
				}
			}

			$params = json_encode($params);

			if ($params == $row->params)
			{
				continue;
			}

			$query->clear()
				->update($this->db->quoteName('#__advancedmodules'))
				->set($this->db->quoteName('params') . ' = ' . $this->db->quote($params))
				->where($this->db->quoteName('moduleid') . ' = ' . (int) $row->id);
			$this->db->setQuery($query);
			$this->db->execute();
		}
	}

	private function deleteOldFiles()
	{
		JFile::delete(
			[
				JPATH_ADMINISTRATOR . '/components/com_advancedmodules/script.advancedmodules.php',
				JPATH_SITE . '/components/com_advancedmodules/advancedmodules.xml',
				JPATH_SITE . '/components/com_advancedmodules/script.advancedmodules.php',
				JPATH_SITE . '/plugins/system/advancedmodules/modulehelper.php',
			]
		);
	}

	public function fixAssetsRules()
	{
		parent::fixAssetsRules();

		// Remove unused assets entry (uses com_modules)
		$query = $this->db->getQuery(true)
			->delete('#__assets')
			->where('name LIKE ' . $this->db->quote('com_advancedmodules.module.%'));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function checkForGeoIP()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('geoip'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('library'));
		$this->db->setQuery($query, 0, 1);
		$result = $this->db->loadResult();

		// GeoIP library is installed, so ignore
		if ( ! empty($result))
		{
			return;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('moduleid'))
			->from($this->db->quoteName('#__advancedmodules'))
			->where($this->db->quoteName('params') . ' RLIKE ' . $this->db->quote('"assignto_geo(continents|countries|regions)":"1"'));
		$this->db->setQuery($query, 0, 1);
		$result = $this->db->loadResult();

		// No modules found with Geo assignments, so ignore
		if (empty($result))
		{
			return;
		}

		JFactory::getApplication()->enqueueMessage(
			'Advanced Module Manager no longer uses external services for the <strong>Geolocation assignments</strong>.<br>
			It now makes use of a new <strong>Regular Labs GeoIP library</strong>.<br>
			<br>
			You currently have modules with Geo assignments. To continue using these assignments you are required to install the Regular Labs GeoIP library<br><br>
			<a href="https://www.regularlabs.com/geoip" target="_blank" class="btn btn-default">Install the Regular Labs GeoIP library</a>',
			'warning'
		);
	}
}
