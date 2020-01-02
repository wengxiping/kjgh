<?php
/**
 * ------------------------------------------------------------------------
 * JA Builder Admin Menu Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
// No direct access to this file
defined('_JEXEC') or die;

class mod_jabuilder_admin_menuInstallerScript
{
	/**
	 * Method to install the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function install($parent) 
	{
		$db = JFactory::getDBO();
		$sql = 'SELECT id FROM #__modules WHERE module = "mod_jabuilder_admin_menu"';
		$db->setQuery($sql);
		$id = $db->loadResult();
		$sql = 'UPDATE #__modules SET position = \'menu\' , published = 1, access = 3, ordering = 10, params = \'{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}\'
				WHERE id = '.$id;
		$db->setQuery($sql);
		$db->execute();
		$sql = 'SELECT moduleid FROM #__modules_menu WHERE moduleid = '.$id.'';
		$db->setQuery($sql);
		$modid = $db->loadResult();
		if (empty($modid)) {
			$sql = 'INSERT INTO `#__modules_menu` (moduleid, menuid) VALUES ('.$id.', 0)';
			$db->setQuery($sql);
			$db->execute();
		}
	}
 
	/**
	 * Method to uninstall the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function uninstall($parent) {}
 
	/**
	 * Method to update the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function update($parent) {}
 
	/**
	 * Method to run before an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function preflight($type, $parent) {}
 
	/**
	 * Method to run after an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function postflight($type, $parent) {}
}