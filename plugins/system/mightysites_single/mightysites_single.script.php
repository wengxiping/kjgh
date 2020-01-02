<?php
/**
* @package		Mightysites
* @copyright	Copyright (C) 2009-2013 AlterBrains.com. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
*/
class plgSystemMightysites_SingleInstallerScript
{

	function install($parent) {
		JFactory::getApplication()->enqueueMessage(JText::_('Successfully installed "System - MightySites single login/logout" plugin!'));
	}

	function uninstall($parent) {
		JFactory::getApplication()->enqueueMessage(JText::_('Successfully uninstalled "System - MightySites single login/logout" plugin!'));
	}

	function update($parent) {
		JFactory::getApplication()->enqueueMessage(JText::_('Successfully updated "System - MightySites single login/logout" plugin!'));
	}
	
}