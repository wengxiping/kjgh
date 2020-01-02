<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.controller');

class com_invitexInstallerScript
{
	/** @var array The list of extra modules and plugins to install */
	private $oldversion="";
	private $installation_queue = array(

		'modules'=>array(
			'admin'=>array(),
			'site'=>array()
		),
		'plugins'=>array()
	);

	private $uninstall_queue = array(
		'modules'=>array(
			'admin'=>array(),
			'site'=>array()
		),
		'plugins'=>array()
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
	}

	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param JInstaller $parent
	 */
	function install($parent)
	{
	}

	function postflight( $type, $parent )
	{
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param JInstaller $parent
	 */
	function uninstall($parent)
	{
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{
		$this->fix_db_on_update();

		$db = JFactory::getDbo();

		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/admin/install.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/install.sql';
		}

		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);

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

						if (!$db->query())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}

		$config=JFactory::getConfig();

		if (JVERSION >= 3.0)
		{
			$dbname = $config->get('db');
			$dbprefix=$config->get('dbprefix');
		}
		else
		{
			$dbname = $config->getValue('config.db');
			$dbprefix=$config->getvalue('config.dbprefix');
		}

		// Remove unwanted menus
		$this->fix_menus_on_update();
	}

	function fix_menus_on_update()
	{
		// since 2.9
		//Remove invitex menu in backend
		$backend_configmenu = $this->menuExists('index.php?option=com_invitex&view=config');
		$db = JFactory::getDbo();

		if (!empty($backend_configmenu))
		{
			$menu = new stdClass();
			$menu->link = 'index.php?option=com_invitex&view=config';
			$menu->id = $backend_configmenu;

			if (!$db->deleteObject('#__menu', $menu, 'id'))
			{
				echo $db->stderr();
				return false;
			}
		}

	}

	function menuExists($link, $menutype = null)
	{
		$db = JFactory::getDbo();
		$query = 'SELECT `id`
		 FROM `#__menu`
		 WHERE `link` LIKE "%' . $link . '"';

		if ($menutype != null)
		{
			$query .= ' AND `menutype`="' . $menutype .'"';
		}

		$db->setQuery( $query );

		return $db->loadResult();
	}

	function runSQL($parent,$sqlfile)
	{
		$db = JFactory::getDbo();
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if(method_exists($parent, 'extension_root')) {
			$sqlfile = $parent->getPath('extension_root') . '/admin/' . $sqlfile;
		} else {
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/' . $sqlfile;
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false) {
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0) {
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->query()) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}
	}//end run sql

	//since version 1.0.2
	//since version 1.0.2
	function fix_db_on_update()
	{
		$db = JFactory::getDbo();

		$imports_table_columns=array('inviter_id'=>'int(11)','provider_email'=>'varchar(100)','message'=>'TEXT','provider'=>'varchar(50)','invites_count'=>'INT(11)','date'=>'TEXT','invite_type'=>'int(11)','invite_url'=>'TEXT','catch_act'=>'TEXT','invite_type_tag'=>'TEXT','message_type'=>'TEXT');

		$query="SHOW COLUMNS FROM #__invitex_imports";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$res = $db->loadResultArray();
		}
		else
		{
			$res = $db->loadColumn();
		}

		foreach ($imports_table_columns as $c => $t)
		{
			$prev_ele = prev($imports_table_columns);

			if (!in_array( $c, $res))
			{
				$query="ALTER TABLE #__invitex_imports add column $c $t ;";
				$db->setQuery($query);

				if(JVERSION < 3.0)
				{
					$db->query();
				}
				else
				{
					$db->execute();
				}
			}
		}

		$emails_table_columns = array('import_id'=>'int(11)','inviter_id'=>'int(11)', 'guest'=>'varchar(255)','invitee_email'=>' varchar(400)','invitee_name'=>' text','expires'=>'int(11)','sent'=>' tinyint(4)','sent_at'=>'int(11)','invitee_id'=>' int(11)','ip' =>'varchar(100)','friend_count'=>'int(11)','click_count'=>'int(11)','blocked' =>'tinyint(11)','modified'=>' int(11)','resend'=>'int(11)','resend_count'=>' int(11)','remind'=>'tinyint(4)','remind_count'=>'int(11)');
		$query="SHOW COLUMNS FROM #__invitex_imports_emails";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$res = $db->loadResultArray();
		}
		else
		{
			$res = $db->loadColumn();
		}

		$prev = '';

		foreach ($emails_table_columns as $c => $t)
		{
			if (!in_array( $c, $res))
			{
				$query = "ALTER TABLE #__invitex_imports_emails add column $c $t ";

				if ($prev)
				{
					$query.= " AFTER $prev ;";
				}

				$db->setQuery($query);

				if (JVERSION < 3.0)
				{
					$db->query();
				}
				else
				{
					$db->execute();
				}
			}
			else
			{
				if ($prev)
				{
					$query="ALTER TABLE #__invitex_imports_emails MODIFY $c $t not null AFTER $prev;";
					$db->setQuery($query);

					if(JVERSION < 3.0)
					{
						$db->query();
					}
					else
					{
						$db->execute();
					}
				}
			}

			$prev=$c;
		}

		if (in_array('clicked',$res))
		{
			$query="	ALTER TABLE #__invitex_imports_emails DROP COLUMN clicked";
			$db->setQuery($query);

			if (JVERSION < 3.0)
			{
				$db->query();
			}
			else
			{
				$db->execute();
			}
		}

		// To add default personal message for invite anywhere invitaion types
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__invitex_types`";
		$db = JFactory::getDbo();
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('personal_message', $field_array))
		{
			$query = "ALTER TABLE `#__invitex_types` ADD `personal_message` text NULL AFTER `description`";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__invitex_types table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		$type_table_columns = array('name'=>'TEXT','internal_name'=>'TEXT','description'=>'TEXT','template_html'=>'TEXT','template_html_subject'=>'TEXT',
'template_text'=>'TEXT','template_text_subject'=>'TEXT','common_template_text'=>'TEXT','common_template_text_subject'=> 'text','template_twitter'=>'text',
'template_fb_request'=>'TEXT','invite_methods'=>'TEXT','invite_apis'=>'TEXT','integrate_activity_stream'=>'INT(11)','activity_stream_text'=>'TEXT','widget'=>'TEXT','catch_action'=>'TEXT','template_sms'=>'TEXT');

		$query="SHOW COLUMNS FROM #__invitex_types";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$field_array=$db->loadResultArray();
		}
		else
		{
			$field_array=$db->loadColumn();
		}

		$prev='';

		foreach ($type_table_columns as $c => $t)
		{
			if (!in_array( $c, $field_array))
			{
				$query="ALTER TABLE #__invitex_types add column $c $t ";

				if ($prev)
				{
					$query.= " AFTER $prev ;";
				}

				$db->setQuery($query);

				if (JVERSION < 3.0)
				{
					$db->query();
				}
				else
				{
					$db->execute();
				}
			}
			else
			{
				if ($prev)
				{
					$query="ALTER TABLE #__invitex_types MODIFY $c $t not null AFTER $prev;";
					$db->setQuery($query);

					if (JVERSION < 3.0)
					{
						$db->query();
					}
					else
					{
						$db->execute();
					}
				}
			}

			$prev = $c;
		}

		if (in_array('invite_type_tag', $field_array))
		{
			$query = "ALTER TABLE #__invitex_types DROP COLUMN invite_type_tag";
			$db->setQuery($query);

			if (JVERSION < 3.0)
			{
				$db->query();
			}
			else
			{
				$db->execute();
			}
		}

		//for token table..
		$query="CREATE TABLE IF NOT EXISTS #__invitex_stored_tokens
				(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`import_id` int(11) DEFAULT NULL,
				`user_id` int(11) DEFAULT NULL,
				`token` varchar(255) DEFAULT NULL,
				PRIMARY KEY (`id`)
				);";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$db->query();
		}
		else
		{
			$db->execute();
		}

		/***ADD column 'blocked' in #__invitex_stored_emails***/
		$query="CREATE TABLE IF NOT EXISTS `#__invitex_stored_emails` (
				`id` int(11) NOT NULL auto_increment,
				`email` text NOT NULL,
				`name` text NOT NULL,
				`importedby` text NOT NULL,
				`importedcount` int(11) NOT NULL,
				`sent_count` int(11) NOT NULL,
				`last_sent_date` text NOT NULL,
				`notification` int(11) NOT NULL,
				`blocked` int(11) NOT NULL,
				PRIMARY KEY  (`id`)
			)DEFAULT CHARSET=utf8";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$db->query();
		}
		else
		{
			$db->execute();
		}

		//sms_delivery table
		$query="CREATE TABLE IF NOT EXISTS `#__invite_sms_delivery` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `import_email_id` int(11) NOT NULL,
			  `apisms_id` varchar(255) NOT NULL,
			  `delivered` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$db->query();
		}
		else
		{
			$db->execute();
		}

		$field_array = array();
		$query = "SHOW COLUMNS FROM #__invitex_stored_emails";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$field_array=$db->loadResultArray();
		}
		else
		{
			$field_array=$db->loadColumn();
		}

		if (!in_array('unsubscribe', $field_array) and in_array('blocked', $field_array))
		{
			$query = "ALTER TABLE #__invitex_stored_emails  CHANGE  `blocked`  `unsubscribe` TINYINT( 2 ) NOT NULL;";
			$db->setQuery($query);

			if(JVERSION < 3.0)
			{
				$db->query();
			}
			else
			{
				$db->execute();
			}
		}

		if (!in_array('notification', $field_array))
		{
			$query = "ALTER TABLE #__invitex_stored_emails add column notification int(11) ;";
			$db->setQuery($query);

			if (JVERSION < 3.0)
			{
				$db->query();
			}
			else
			{
				$db->execute();
			}
		}

		$field_array = array();
		$query = "SHOW COLUMNS FROM #__invitex_imports_emails";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$field_array = $db->loadResultArray();
		}
		else
		{
			$field_array = $db->loadColumn();
		}

		if (!in_array('unsubscribe', $field_array) and in_array('blocked', $field_array))
		{
			$query="ALTER TABLE #__invitex_imports_emails  CHANGE  `blocked`  `unsubscribe` TINYINT( 2 ) NOT NULL;";
			$db->setQuery($query);

			if (JVERSION < 3.0)
			{
				$db->query();
			}
			else
			{
				$db->execute();
			}
		}

		// DELETE UNWANTED VALUES FROM CONFIG TABLE
		$values_to_restore_in_config_table=array('message_subject','message_body','pm_message_body_sub','pm_message_body','pm_message_body_no_replace_sub','pm_message_body_no_replace','twitter_message_body','fb_request_body','sms_message_body','reminder_subject','reminder_body','friendsonsite_subject','friendsonsite_body');
		$values_to_restore_in_config_table_str=implode("','",$values_to_restore_in_config_table);
		$query="DELETE FROM #__invitex_config WHERE namekey NOT IN('".$values_to_restore_in_config_table_str."');";
		$db->setQuery($query);

		if(JVERSION < 3.0)
		{
			$db->query();
		}
		else
		{
			$db->execute();
		}

		//2.9 Drop __invitex_inviter_url table
		$query="DROP TABLE IF EXISTS #__invitex_inviter_url; ";
		$db->setQuery($query);

		if(JVERSION < 3.0)
		{
			$db->query();
		}
		else
		{
			$db->execute();
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__techjoomlaAPI_users` (
				  `id` int(11) NOT NULL auto_increment,
				  `api` varchar(200) NOT NULL,
				  `token` text NOT NULL,
				  `user_id` int(11) NOT NULL,
				  `client` varchar(200) NOT NULL,
				  PRIMARY KEY  (`id`)
				)   DEFAULT CHARSET=utf8";

		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$db->query();
		}
		else
		{
			$db->execute();
		}
	}
}
