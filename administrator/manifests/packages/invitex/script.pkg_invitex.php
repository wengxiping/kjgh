<?php
/**
 * @package    AdminTools
 * @copyright  Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 3, or later
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package     InviteX
 * @subpackage  com_invitex
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
jimport('joomla.filesystem.file');

$tjInstallerPath = JPATH_ROOT . '/administrator/manifests/packages/invitex/tjinstaller.php';

if (JFile::exists(__DIR__ . '/tjinstaller.php'))
{
	include_once __DIR__ . '/tjinstaller.php';
}
elseif (JFile::exists($tjInstallerPath))
{
	include_once $tjInstallerPath;
}

/**
 * InviteX installer class
 *
 * @since  2.5.0
 */
class Pkg_InvitexInstallerScript extends TJInstaller
{
	protected $extensionName = 'InviteX';

	/** @var array The list of extra modules and plugins to install */
	private $oldversion = "";

	/** @var  array  The list of extra modules and plugins to install */
	protected $installationQueue = array (
		'postflight' => array(
			'easysocialApps' => array (
				'event' => array (
					'easysocial_invitex_event' => 0
				),
				'group' => array (
					'easysocial_invitex_group' => 0
				),
				'profile' => array (
					'easysocial_invitex_profile' => 0
				),
				'site' => array (
					'easysocial_invitex' => 0
				)
			),

			'files' => array(
				'tj_strapper' => 1
			),

			/*plugins => { (folder) => { (element) => (published) }}*/
			'plugins' => array (
				'system' => array (
					'tjassetsloader' => 1,
					'tjupdates'      => 1
				),
				'sms' => array (
					'clickatell' => 0,
					'smshorizon' => 0
				),
				'techjoomlaAPI' => array (
					'facebook' => 0,
					'gmail'    => 0,
					'hotmail'  => 0,
					'sms'      => 0,
					'twitter'  => 0,
					'yahoo'    => 0
				),
			),

			'libraries' => array (
				'techjoomla' => 1
			)
		)
	);

	/** @var  array  The list of extra modules and plugins to uninstall */
	protected $uninstallQueue = array (
		/*plugins => { (folder) => { (element) => (published) }}*/
		'plugins' => array ()
	);

	/** @var array The list of obsolete extra modules and plugins to uninstall when upgrading the component */
	protected $obsoleteExtensionsUninstallationQueue = array (
		// @modules => { (folder) => { (module) }* }*
		'modules' => array (
			'admin' => array (
			),
			'site' => array (
			)
		),
		// @plugins => { (folder) => { (element) }* }*
		'plugins' => array (
			'system' => array (
				'plg_sys_jma_integration'
			)
		)
	);

	/**
	 * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
	 * type (plugin, module, ...), name (of the extension), status (0 - unpublish, 1 - publish),
	 * client (0=site, 1=admin), group (for plugins), position (for modules).
	 *
	 * @var array
	 */
	protected $extensionsToEnable = array (
		// InviteX modules
		array ('module', 'mod_invite_anywhere', 1, 0, '', ''),
		array ('module', 'mod_invite_friends', 1, 0, '', ''),
		array ('module', 'mod_inviters', 1, 0, '', ''),

		// InviteX plugins
		array ('plugin', 'userinfo', 1, 1, 'content'),
		array ('plugin', 'invitex', 1, 1, 'privacy'),
		array ('plugin', 'plug_invitex_sample_development', 1, 1, 'system'),
		array ('plugin', 'plug_sys_invitex', 1, 1, 'system'),
		array ('plugin', 'plg_user_invitex', 1, 1, 'user')
	);

	/** @var array Obsolete files and folders to remove*/
	private $removeFilesAndFolders = array(
		'files'	=> array(
			'administrator/components/com_invitex/views/dashboard/tmpl/default1.php',
			'administrator/components/com_invitex/models/cp.php',
			'administrator/components/com_invitex/views/statistics/view.html.php',
			'administrator/components/com_invitex/views/users/view.html.php',
			'administrator/components/com_invitex/assets/elements/invite_methods.php'

		),
		'folders' => array(
			'components/com_invitex/js',
			'components/com_invitex/css',
			'components/com_invitex/bootstrap',
			'components/com_invitex/com_invitex/views/invites/tmpl',
			'administrator/components/com_invitex/js',
			'administrator/components/com_invitex/css',
			'administrator/components/com_invitex/images',
			'administrator/components/com_invitex/elements',
			'administrator/components/com_invitex/views/cp',
			'administrator/components/com_invitex/views/users',
			'administrator/components/com_invitex/views/statistics'
		)
	);

	/**
	 * Runs before install, update or discover_update
	 *
	 * @param   string      $type    install, update or discover_update
	 * @param   JInstaller  $parent  The class calling this method
	 *
	 * @return  void
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * Runs after fresh install
	 *
	 * @param   JInstaller  $parent  The class calling this method
	 *
	 * @return  void
	 */
	public function install($parent)
	{
		$this->write_config();

		// Enable the extensions on fresh install
		$this->enableExtensions();
	}

	/**
	 * Runs after update
	 *
	 * @param   JInstaller  $parent  The class calling this method
	 *
	 * @return  void
	 */
	public function update($parent)
	{
		$this->write_config();
	}

	/**
	 * Method to uninstall the component
	 *
	 * @param   JInstaller  $parent  Class calling this method
	 *
	 * @return  void
	 */
	public function uninstall($parent)
	{
		// Uninstall subextensions
		$status = $this->uninstallSubextensions($parent);

		// Show the post-uninstallation page
		$this->renderPostUninstallation($status);
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string      $type    install, update or discover_update
	 * @param   JInstaller  $parent  The class calling this method
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
		// Copy tjinstaller file into packages folder
		$this->copyInstaller($parent);

		// Install subextensions
		$status = $this->installSubextensions($parent, 'postflight');

		// Uninstall obsolete subextensions
		$uninstallStatus = $this->uninstallObsoleteSubextensions($parent);

		// Remove obsolete files and folders
		$this->removeObsoleteFilesAndFolders($this->removeFilesAndFolders);

		// Show the post-installation page
		$this->renderPostInstallation($status);
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	public function write_config()
	{
		// $parent is the class calling this method
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$gmailsrcapi = JPATH_SITE . '/components/com_invitex/gmailAPI/techjoomlaApi_gmail.php';
		$gmaildestapi = JPATH_SITE . '/techjoomlaApi_gmail.php';
		JFile::move($gmailsrcapi, $gmaildestapi);

		$hotmailsrcapi = JPATH_SITE . '/components/com_invitex/hotmailAPI/techjoomla_hotmail_api.php';
		$hotmaildestapi = JPATH_SITE . '/techjoomla_hotmail_api.php';
		JFile::move($hotmailsrcapi, $hotmaildestapi);

		JFolder::delete(JPATH_SITE . '/components/com_invitex/gmailAPI');

		$source_media = JPATH_SITE . '/components/com_invitex/media';
		$dest_media = JPATH_SITE . '/media';
		JFolder::copy($source_media, $dest_media, $path = '', 'true');
		JFolder::delete($source_media);

		$inv_sef_ext = JPATH_SITE . '/components/com_invitex/sef_ext';
		$sef_path = JPATH_SITE . '/components/com_sh404sef';

		if (JFolder::exists($sef_path))
		{
			$inv_sef_ext_dest = JPATH_SITE . '/components/com_sh404sef/sef_ext';
			JFolder::copy($inv_sef_ext, $inv_sef_ext_dest, $path = '', 'true');
		}

		$filename = JPATH_SITE . '/components/com_invitex/openinviter/plugins/facebook.plg.php';

		if (JFile::exists($filename))
		{
			JFile::delete($filename);
		}

		$filename = JPATH_SITE . '/components/com_invitex/openinviter/plugins/linkedin.plg.php';

		if (JFile::exists($filename))
		{
			JFile::delete($filename);
		}

		$filename = JPATH_SITE . '/components/com_invitex/openinviter/plugins/orkut.plg.php';

		if (JFile::exists($filename))
		{
			JFile::delete($filename);
		}

		// Old config
		$oi_config_file = JPATH_SITE . '/components/com_invitex/openinviter/config.php';
		$oi_config_file_default = JPATH_SITE . '/components/com_invitex/openinviter/config_default.php';

		// New config
		if (!JFile::exists($oi_config_file))
		{
			if (JFile::exists($oi_config_file_default))
			{
					JFile::move($oi_config_file_default, $oi_config_file);
					JFile::delete($oi_config_file_default);
			}
		}

		$db = JFactory::getDbo();
		$config_rows = array();
		$query = "SELECT namekey from `#__invitex_config`";
		$db->setQuery($query);

		$config_rows = $db->loadColumn();

		// Old config
		$filename = JPATH_SITE . '/components/com_invitex/config.php';

		// New config
		$filename_default = JPATH_SITE . '/components/com_invitex/config_default.php';

		include $filename_default;

		if (!JFile::exists($filename))
		{
			foreach ($invitex_default_settings as $k => $v)
			{
				if (!in_array($k, $config_rows))
				{
					if (is_array($v))
					{
						$v = implode(',', $v);
					}

					$c_data = new stdClass;
					$c_data->namekey = $k;
					$c_data->value = $v;
					$db->insertObject('#__invitex_config', $c_data, 'id');
				}
			}

			JFile::delete($filename_default);
		}
		else
		{
			include $filename;

			foreach ($invitex_default_settings as $k => $v)
			{
				$c_data = new stdClass;

				if (!in_array($k, $config_rows))
				{
					$c_data->namekey = $k;

					if (array_key_exists($k, $invitex_settings))
					{
						if (is_array($invitex_settings[$k]))
						{
							$var = implode(',', $invitex_settings[$k]);
						}
						else
						{
							$var = $invitex_settings[$k];
						}

						$c_data->value = $var;
					}
					else
					{
						if (is_array($v))
						{
							$v = implode(',', $v);
						}

						$c_data->value = $v;
					}

					$db->insertObject('#__invitex_config', $c_data, 'id');
				}
			}

			JFile::delete($filename);
			JFile::delete($filename_default);
		}

		$query = "SELECT ordering from `#__extensions` where element='joomla' AND folder='user'";
		$db->setQuery($query);
		$order = $db->loadResult();
		$order++;

		$sql = "UPDATE `#__extensions` SET `ordering` = '" . $order . "' WHERE element='plug_user_invitex' AND folder='user'";
		$db->setQuery($sql);
		$db->query();

		$query = "select id,internal_name from #__invitex_types";
		$db->setQuery($query);
		$res = $db->loadObjectList();
		$internal_name_array = array();
		$inv_type = array();

		if ($res)
		{
			foreach ($res as $r)
			{
				$internal_name_array[] = $r->internal_name;
				$inv_type[$r->internal_name] = $r->id;
			}
		}

		$directory = JPATH_SITE . '/components/com_invitex/inv_types';
		$files = JFolder::files($directory);

		$table_columns = array('name', 'internal_name', 'description', 'personal_message', 'template_html',
		'template_html_subject', 'template_text', 'template_text_subject', 'widget', 'common_template_text',
		'common_template_text_subject', 'template_twitter', 'template_fb_request', 'catch_action', 'invite_methods',
		'invite_apis', 'integrate_activity_stream', 'activity_stream_text');

		foreach ($files as $file)
		{
			$typenumber = basename($file, ".php");
			include $directory . '/' . $file;

			$type = $settings['internal_name'];
			$data = new stdClass;

			if (!in_array(trim($type), $internal_name_array))
			{
				foreach ($table_columns as $column)
				{
					$data->$column = $settings[$column];
				}

				$db->insertObject('#__invitex_types', $data, 'id');
				$row_id = $db->insertid();
			}
			else
			{
					$row_id = $data->id = $inv_type[$type];
			}

			$wid = $settings['widget'];

			$wid .= "\$link = \"index.php?option=com_invitex&view=invites\";
			require_once JPATH_SITE . '/components/com_invitex/helper.php';\$this->invhelperObj = new cominvitexHelper();
			\$itemid =\$this->invhelperObj->getItemId(\$link);\$link .= \"&Itemid=\".\$itemid;
			\$link = JRoute::_(\$link);\$link .= strpos(\$link,'?')?\"&catch_act=\":\"?catch_act=\";";

			$wid .= "\$link.=\"&invite_type=" . $row_id . "&invite_anywhere=1&invite_url=\".\$invite_url.\"&tag=[name=\".\$name.\"]\";";

			$wid .= addslashes(htmlspecialchars("<a href=\"<?php echo \$link ;?>\">Invite Anywhere</a>"));

			$u_data = new stdClass;
			$u_data->id = $row_id;
			$u_data->widget = $wid;
			$db->updateObject('#__invitex_types', $u_data, 'id');

			JFile::delete($directory . '/' . $file);
		}
	}

	/**
	 * Method to copy installer file
	 *
	 * @param   JInstaller  $parent  Class calling this method
	 *
	 * @return  void
	 */
	protected function copyInstaller($parent)
	{
		$src  = $parent->getParent()->getPath('source') . '/tjinstaller.php';
		$dest = JPATH_ROOT . '/administrator/manifests/packages/invitex/tjinstaller.php';

		JFile::copy($src, $dest);
	}
}
