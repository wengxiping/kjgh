<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2012
 * @file name	:	jblance.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

// no direct access
defined('_JEXEC') or die;

include_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/jblance.php');	//include this helper file to make the class accessible in all other PHP files
JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jblance/tables');	//include the tables path in order to use JTable in this controller file

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jblance')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Jblance');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
