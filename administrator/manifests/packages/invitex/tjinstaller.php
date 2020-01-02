<?php
/**
 * @package     Techjoomla
 * @subpackage  tjinstaller
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
jimport('joomla.filesystem.file');

/**
 * TJ installer class
 *
 * @since  __DEPLOY_VERSION__
 */
class TJInstaller
{
	protected $obsoleteExtensionsUninstallationQueue = array();

	/**
	 * Installs subextensions
	 *
	 * @param   JInstaller  $parent  The class calling this method
	 * @param   String      $type    Preflight/postflight
	 *
	 * @return  object
	 */
	protected function installSubextensions($parent, $type = 'postflight')
	{
		$src = $parent->getParent()->getPath('source');
		$db  = JFactory::getDbo();

		$status           = new stdClass;
		$status->modules  = array();
		$status->plugins  = array();
		$status->packages = array();

		// Install modules
		if (isset($this->installationQueue[$type]['modules']) && count($this->installationQueue[$type]['modules']))
		{
			foreach ($this->installationQueue[$type]['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Install the module
						if (empty($folder))
						{
							$folder = 'site';
						}

						$path = "$src/modules/$folder/$module";

						if (!is_dir($path))
						{
							$path = "$src/modules/$folder/mod_$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/modules/$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/modules/mod_$module";
						}

						if (!is_dir($path))
						{
							continue;
						}

						// Was the module already installed?
						$sql = $db->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($db->qn('module') . ' = ' . $db->q($module));
						$db->setQuery($sql);

						$count     = $db->loadResult();
						$installer = new JInstaller;
						$result    = $installer->install($path);

						$status->modules[] = array (
							'name'   => $module,
							'client' => $folder,
							'result' => $result
						);

						// Modify where it's published and its published state
						if (!$count)
						{
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;

							if ($modulePosition == 'cpanel')
							{
								$modulePosition = 'icon';
							}

							$sql = $db->getQuery(true)
								->update($db->qn('#__modules'))
								->set($db->qn('position') . ' = ' . $db->q($modulePosition))
								->where($db->qn('module') . ' = ' . $db->q($module));

							if ($modulePublished)
							{
								$sql->set($db->qn('published') . ' = ' . $db->q('1'));
							}

							$db->setQuery($sql);
							$db->execute();

							// B. Change the ordering of back-end modules to 1 + max ordering
							if ($folder == 'admin')
							{
								$query = $db->getQuery(true);
								$query->select('MAX(' . $db->qn('ordering') . ')')
									->from($db->qn('#__modules'))
									->where($db->qn('position') . '=' . $db->q($modulePosition));
								$db->setQuery($query);
								$position = $db->loadResult();
								$position++;

								$query = $db->getQuery(true);
								$query->update($db->qn('#__modules'))
									->set($db->qn('ordering') . ' = ' . $db->q($position))
									->where($db->qn('module') . ' = ' . $db->q($module));
								$db->setQuery($query);
								$db->execute();
							}

							// C. Link to all pages
							$query = $db->getQuery(true);
							$query->select('id')->from($db->qn('#__modules'))
								->where($db->qn('module') . ' = ' . $db->q($module));
							$db->setQuery($query);
							$moduleid = $db->loadResult();

							$query = $db->getQuery(true);
							$query->select('*')->from($db->qn('#__modules_menu'))
								->where($db->qn('moduleid') . ' = ' . $db->q($moduleid));
							$db->setQuery($query);
							$assignments = $db->loadObjectList();
							$isAssigned = !empty($assignments);

							if (!$isAssigned)
							{
								$o = (object) array (
									'moduleid' => $moduleid,
									'menuid'   => 0
								);
								$db->insertObject('#__modules_menu', $o);
							}
						}
					}
				}
			}
		}

		// Install plugins
		if (isset($this->installationQueue[$type]['plugins']) && count($this->installationQueue[$type]['plugins']))
		{
			foreach ($this->installationQueue[$type]['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$path = "$src/plugins/$folder/$plugin";

						if (!is_dir($path))
						{
							$path = "$src/plugins/$folder/plg_$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/plugins/$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/plugins/plg_$plugin";
						}

						if (!is_dir($path))
						{
							continue;
						}

						// Was the plugin already installed?
						$query = $db->getQuery(true)
							->select('COUNT(*)')
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$count = $db->loadResult();

						$installer = new JInstaller;
						$result    = $installer->install($path);

						$status->plugins[] = array (
							'name'   => $plugin,
							'group'  => $folder,
							'result' => $result
						);

						if ($published && !$count)
						{
							$query = $db->getQuery(true)
								->update($db->qn('#__extensions'))
								->set($db->qn('enabled') . ' = ' . $db->q('1'))
								->where($db->qn('element') . ' = ' . $db->q($plugin))
								->where($db->qn('folder') . ' = ' . $db->q($folder));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
		}

		// Install libs
		if (isset($this->installationQueue[$type]['libraries']) && count($this->installationQueue[$type]['libraries']))
		{
			foreach ($this->installationQueue[$type]['libraries'] as $folder => $published)
			{
				$path = "$src/libraries/$folder";

				if (file_exists($path))
				{
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )')
						->where($db->qn('folder') . ' = ' . $db->q($folder));
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);

					$status->libraries[] = array(
						'name' => $folder,
						'group' => $folder,
						'result' => $result,
						'status' => $published
					);

					if ($published && !$count)
					{
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled') . ' = ' . $db->q('1'))
							->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )')
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}

		// Install files
		if (isset($this->installationQueue[$type]['files']) && count($this->installationQueue[$type]['files']))
		{
			foreach ($this->installationQueue[$type]['files'] as $folder => $published)
			{
				$path = "$src/files/$folder";

				if (file_exists($path))
				{
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )')
						->where($db->qn('folder') . ' = ' . $db->q($folder));
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);

					$status->files[] = array(
						'name' => $folder,
						'result' => $result,
						'status' => $published
					);

					if ($published && !$count)
					{
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled') . ' = ' . $db->q('1'))
							->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )')
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}

		// Install packages
		if (isset($this->installationQueue[$type]['packages']) && count($this->installationQueue[$type]['packages']))
		{
			foreach ($this->installationQueue[$type]['packages']  as $folder => $publish)
			{
				$path = "$src/packages/$folder";

				if (file_exists($path))
				{
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )');
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);

					$status->packages[] = array (
						'name'   => $folder,
						'result' => $result
					);

					if (!$count && $publish)
					{
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled') . ' = ' . $db->q('1'))
							->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )');
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}

		// Install Easysocial Apps
		if (isset($this->installationQueue[$type]['easysocialApps']) && count($this->installationQueue[$type]['easysocialApps']))
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
			{
				require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';

				foreach ($this->installationQueue[$type]['easysocialApps'] as $folder => $easysocialApps)
				{
					if (count($easysocialApps))
					{
						foreach ($easysocialApps as $easysocialApp => $published)
						{
							$installer   = Foundry::get('Installer');

							$installer->load($src . "/easysocial_apps/" . $folder . '/' . $easysocialApp);
							$esAppInstall = $installer->install();

							$status->easysocialAppInstall[]  = array (
								'name'   => $easysocialApp,
								'group'  => $folder,
								'result' => $esAppInstall,
								'status' => '0'
							);
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstaller  $parent  The class calling this method
	 *
	 * @return JObject The subextension uninstallation status
	 */
	protected function uninstallSubextensions($parent)
	{
		jimport('joomla.installer.installer');

		$db = JFactory::getDBO();

		$status         = new stdClass;
		$status->modules = array ();
		$status->plugins = array ();

		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if (isset($this->uninstallQueue['modules']) && count($this->uninstallQueue['modules']))
		{
			foreach ($this->uninstallQueue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Find the module ID
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q($module))
							->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);
						$id = $db->loadResult();

						// Uninstall the module
						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('module', $id, 1);
							$status->modules[] = array (
								'name' => $module,
								'client' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (isset($this->uninstallQueue['plugins']) && count($this->uninstallQueue['plugins']))
		{
			foreach ($this->uninstallQueue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						$id = $db->loadResult();

						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('plugin', $id);
							$status->plugins[] = array (
								'name' => $plugin,
								'group' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Update subextensions (modules, plugins) package id
	 *
	 * @param   JInstaller  $parent  The class calling this method
	 *
	 * @return boolean
	 */
	protected function updatePackageId($parent)
	{
		$src = $parent->getParent()->getPath('source');
		$db  = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('extension_id')
			->from($db->qn('#__extensions'))
			->where($db->qn('element') . ' = ' . $db->q($this->packageName));
		$db->setQuery($query);
		$extension_id = $db->loadResult();

		// Plugins package id update
		if (count($this->subextensionsQueue['plugins']))
		{
			foreach ($this->subextensionsQueue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('package_id') . ' = ' . $db->q($extension_id))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}

		// Component package id update
		if (count($this->subextensionsQueue['components']))
		{
			foreach ($this->subextensionsQueue['components'] as $element => $published)
			{
				$query = $db->getQuery(true)
					->update($db->qn('#__extensions'))
					->set($db->qn('package_id') . ' = ' . $db->q($extension_id))
					->where($db->qn('type') . ' = ' . $db->q('component'))
					->where($db->qn('element') . ' = ' . $db->q($element));
				$db->setQuery($query);
				$db->execute();
			}
		}

		// Module package id update
		if (count($this->subextensionsQueue['modules']))
		{
			foreach ($this->subextensionsQueue['modules'] as $element => $published)
			{
				$query = $db->getQuery(true)
					->update($db->qn('#__extensions'))
					->set($db->qn('package_id') . ' = ' . $db->q($extension_id))
					->where($db->qn('type') . ' = ' . $db->q('module'))
					->where($db->qn('element') . ' = ' . $db->q($element));
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeFilesAndFolders  Array of files and folder to me removed
	 *
	 * @return  void
	 */
	protected function removeObsoleteFilesAndFolders($removeFilesAndFolders)
	{
		// Remove files
		jimport('joomla.filesystem.file');

		if (!empty($removeFilesAndFolders['files']))
		{
			foreach ($removeFilesAndFolders['files'] as $file)
			{
				$f = JPATH_ROOT . '/' . $file;

				if (!JFile::exists($f))
				{
					continue;
				}
				else
				{
					JFile::delete($f);
				}
			}
		}

		// Remove folders
		if (!empty($removeFilesAndFolders['folders']))
		{
			foreach ($removeFilesAndFolders['folders'] as $folder)
			{
				$f = JPATH_ROOT . '/' . $folder;

				if (!JFolder::exists($f))
				{
					continue;
				}
				else
				{
					JFolder::delete($f);
				}
			}
		}
	}

	/**
	 * Uninstalls obsolete subextensions (modules, plugins) bundled with the main extension
	 *
	 * @return  JObject     The subextension uninstallation status
	 */
	protected function uninstallObsoleteSubextensions()
	{
		JLoader::import('joomla.installer.installer');

		$db = JFactory::getDbo();

		$status          = new stdClass;
		$status->modules = array();
		$status->plugins = array();

		// Modules uninstallation
		if (count($this->obsoleteExtensionsUninstallationQueue['modules']))
		{
			foreach ($this->obsoleteExtensionsUninstallationQueue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module)
					{
						// Find the module ID
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q($module))
							->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);
						$id = $db->loadResult();

						// Uninstall the module
						if ($id)
						{
							$installer = new JInstaller;
							$result    = $installer->uninstall('module', $id, 1);
							$status->modules[] = array(
								'name'   => $module,
								'client' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (count($this->obsoleteExtensionsUninstallationQueue['plugins']))
		{
			foreach ($this->obsoleteExtensionsUninstallationQueue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						$id = $db->loadResult();

						if ($id)
						{
							$installer = new JInstaller;
							$result    = $installer->uninstall('plugin', $id, 1);
							$status->plugins[] = array(
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Run sql file
	 *
	 * @param   string  $sqlfilePath  Path of file
	 *
	 * @return  boolean
	 */
	protected function runSQL($sqlfilePath)
	{
		$db = JFactory::getDBO();

		// Don't modify below this line
		$buffer = file_get_contents($sqlfilePath);

		if ($buffer !== false)
		{
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);

			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);

						if (!$db->execute())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

							return false;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Enable modules and plugins after installing them
	 *
	 * @return  void
	 */
	protected function enableExtensions()
	{
		foreach ($this->extensionsToEnable as $ext)
		{
			$modPosition = isset($ext[5]) ? $ext[5] : '';

			// 0 - type, 1 - name, 2 - publish?, 3 - client, 4 - group, 5 - position
			$this->enableExtension($ext[0], $ext[1], $ext[2], $ext[3], $ext[4], $modPosition);
		}
	}

	/**
	 * Enable an extension
	 *
	 * @param   string   $type            The extension type.
	 * @param   string   $name            The name of the extension (the element field).
	 * @param   integer  $publish         Publish - ? 0 - no, 1 - yes
	 * @param   integer  $client          The application id (0: Joomla CMS site; 1: Joomla CMS administrator).
	 * @param   string   $group           The extension group (for plugins).
	 * @param   string   $modulePosition  Module position (for modules).
	 *
	 * @return  boolean
	 */
	protected function enableExtension($type, $name, $publish = 1, $client = 1, $group = null, $modulePosition = null)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
					->update('#__extensions')
					->set($db->qn('enabled') . ' = ' . $db->q('1'))
					->where('type = ' . $db->quote($type))
					->where('element = ' . $db->quote($name));
		}
		catch (\Exception $e)
		{
			return false;
		}

		switch ($type)
		{
			case 'plugin':
				// Plugins have a folder but not a client
				$query->where('folder = ' . $db->quote($group));
				break;

			case 'language':
			case 'module':
			case 'template':
				// Languages, modules and templates have a client but not a folder
				$client = JApplicationHelper::getClientInfo($client, true);
				$query->where('client_id = ' . (int) $client);
				break;

			default:
			case 'library':
			case 'package':
			case 'component':
				// Components, packages and libraries don't have a folder or client.
				// Included for completeness.
				break;
		}

		try
		{
			$db->setQuery($query);
			$db->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		if ($type == 'module')
		{
			// 1. Publish module
			$sql = $db->getQuery(true)
				->update($db->qn('#__modules'))
				->set($db->qn('position') . ' = ' . $db->q($modulePosition))
				->set($db->qn('published') . ' = ' . $db->q($publish))
				->where($db->qn('module') . ' = ' . $db->q($name));

			$db->setQuery($sql);
			$db->execute();

			// 2. Link to all pages
			$query = $db->getQuery(true);
			$query->select('id')->from($db->qn('#__modules'))
				->where($db->qn('module') . ' = ' . $db->q($name));
			$db->setQuery($query);
			$moduleid = $db->loadResult();

			$query = $db->getQuery(true);
			$query->select('*')->from($db->qn('#__modules_menu'))
				->where($db->qn('moduleid') . ' = ' . $db->q($moduleid));
			$db->setQuery($query);
			$assignments = $db->loadObjectList();
			$isAssigned = !empty($assignments);

			if (!$isAssigned)
			{
				$o = (object) array(
					'moduleid' => $moduleid,
					'menuid'   => 0
				);
				$db->insertObject('#__modules_menu', $o);
			}
		}

		return true;
	}

	/**
	 * Renders post installtion status
	 *
	 * @param   object  $status  Subextensions install status array
	 *
	 * @return  void
	 */
	protected function renderPostInstallation($status)
	{
		$rows = 1;
		?>

		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>

			<tbody>
				<tr>
					<th colspan="2">Component</th>
					<th></th>
				</tr>
				<tr class="row1">
					<td class="key" colspan="2">
						<?php echo $this->extensionName; ?>
					</td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>

				<?php
				if (isset($status->packages))
				{
					if (count($status->packages))
					{
						?>
						<tr>
							<th colspan="2">Package</th>
							<th></th>
						</tr>

						<?php
						foreach ($status->packages as $package)
						{
							$rows++;
							?>
							<tr class="row1">
								<td colspan="2" class="key"><?php echo $package['name'];  ?></td>
								<td>
									<strong style="color: <?php echo ($package['result']) ? "green" : "red"?>">
										<?php echo ($package['result']) ? 'Installed' : 'Not installed';?>
									</strong>
								</td>
							</tr>
							<?php
						}
					}
				}
				?>

				<?php
				if (isset($status->libraries))
				{
					if (count($status->libraries))
					{
						?>
						<tr>
							<th colspan="2">Library</th>
							<th></th>
						</tr>

						<?php
						foreach ($status->libraries as $libraries)
						{
							$rows++;
							?>
							<tr class="row1">
								<td colspan="2" class="key"><?php echo $libraries['name'];  ?></td>
								<td>
									<strong style="color: <?php echo ($libraries['result']) ? "green" : "red"?>">
										<?php echo ($libraries['result']) ? 'Installed' : 'Not installed';?>
									</strong>
								</td>
							</tr>
							<?php
						}
					}
				}
				?>

				<?php
				if (isset($status->files))
				{
					if (count($status->files))
					{
						?>
						<tr>
							<th colspan="2">File</th>
							<th></th>
						</tr>

						<?php
						foreach ($status->files as $files)
						{
							$rows++;
							?>
							<tr class="row1">
								<td colspan="2" class="key"><?php echo $files['name'];  ?></td>
								<td>
									<strong style="color: <?php echo ($files['result']) ? "green" : "red"?>">
										<?php echo ($files['result']) ? 'Installed' : 'Not installed';?>
									</strong>
								</td>
							</tr>
							<?php
						}
					}
				}
				?>

				<?php
				if (isset($status->modules))
				{
					if (count($status->modules))
					{
						?>
						<tr>
							<th>Module</th>
							<th>Client</th>
							<th></th>
						</tr>

						<?php
						foreach ($status->modules as $module)
						{
							$rows++;
							?>
							<tr class="row <?php echo ($rows % 2);?>">
								<td class="key"><?php echo $module['name'];?></td>
								<td class="key"><?php echo ucfirst($module['client']);?></td>
								<td>
									<strong style="color: <?php echo ($module['result']) ? "green" : "red"?>">
										<?php echo ($module['result']) ? 'Installed' : 'Not installed';?>
									</strong>
								</td>
							</tr>
							<?php
						}
					}
				}

				if (isset($status->plugins))
				{
					if (count($status->plugins))
					{
						?>
						<tr>
							<th>Plugin</th>
							<th>Group</th>
							<th></th>
						</tr>

						<?php
						foreach ($status->plugins as $plugin)
						{
							$rows++;
							?>
							<tr class="row<?php echo ($rows % 2);?>">
								<td class="key"><?php echo $plugin['name'];  ?></td>
								<td class="key"><?php echo $plugin['group']; ?></td>
								<td>
									<strong style="color: <?php echo ($plugin['result']) ? "green" : "red"?>">
										<?php echo ($plugin['result']) ? 'Installed' : 'Not installed';?>
									</strong>
								</td>
							</tr>
							<?php
						}
					}
				}
				?>

				<?php
				if (isset($status->easysocialAppInstall))
				{
					if (count($status->easysocialAppInstall))
					{
						?>
						<tr class="row1">
							<th>EasySocial Apps</th>
							<th></th>
							<th></th>
						</tr>

						<?php
						foreach ($status->easysocialAppInstall as $easysocialAppInstall)
						{
							?>
							<tr class="row2">
								<td class="key"><?php echo $easysocialAppInstall['name'];  ?></td>
								<td class="key"><?php echo $easysocialAppInstall['group']; ?></td>
								<td>
									<strong style="color: <?php echo ($easysocialAppInstall['result'])? "green" : "red"?>">
										<?php echo ($easysocialAppInstall['result']) ? 'Installed' : 'Not installed'; ?>
									</strong>

									<?php
									// If installed then only show msg
									if (!empty($easysocialAppInstall['result']))
									{
										echo (
											$easysocialAppInstall['status'] ?
											"<span class=\"label label-success\">Enabled</span>" :
											"<span class=\"label label-important\">Disabled</span>"
										);
									}
									?>
								</td>
							</tr>
							<?php
						}
					}
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Renders post installtion status
	 *
	 * @param   object  $status  Subextensions install status array
	 *
	 * @return  void
	 */
	protected function renderPostUninstallation($status)
	{
		$rows = 1;
		?>

		<h4><?php echo JText::_($this->extensionName . ' Uninstallation Status'); ?></h4>

		<table class="adminlist table table-striped table-condensed" style="font-weight:normal !important;">
			<thead>
				<tr>
					<th colspan="2"><?php echo JText::_('Extension'); ?></th>
					<th width="30%"><?php echo JText::_('Status'); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>

			<tbody>
				<tr>
					<td colspan="2"><?php echo $this->extensionName . ' ' . JText::_('Component'); ?></td>
					<td><strong style="color: green"><?php echo JText::_('Removed'); ?></strong></td>
				</tr>

				<?php
				if (count($status->modules))
				{
					?>
					<tr>
						<th><?php echo JText::_('Module'); ?></th>
						<th><?php echo JText::_('Client'); ?></th>
						<th></th>
					</tr>

					<?php
					foreach ($status->modules as $module)
					{
						?>
						<tr class="row<?php echo (++ $rows % 2); ?>">
							<td><?php echo $module['name']; ?></td>
							<td><?php echo $module['client']; ?></td>
							<td>
								<strong style="color: <?php echo ($module['result'])? "green" : "red"?>">
									<?php echo ($module['result']) ? JText::_('Removed') : JText::_('Not removed'); ?>
								</strong>
							</td>
						</tr>
						<?php
					}
				}
				?>

				<?php
				if (count($status->plugins))
				{
					?>
					<tr>
						<th><?php echo JText::_('Plugin'); ?></th>
						<th><?php echo JText::_('Group'); ?></th>
						<th></th>
					</tr>

					<?php
					foreach ($status->plugins as $plugin)
					{
						?>
						<tr class="row<?php echo (++ $rows % 2); ?>">
							<td><?php echo $plugin['name']; ?></td>
							<td><?php echo $plugin['group']; ?></td>
							<td>
								<strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>">
									<?php echo ($plugin['result']) ? JText::_('Removed') : JText::_('Not removed'); ?>
								</strong>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<?php
	}
}
