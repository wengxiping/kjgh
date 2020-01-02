<?php

/**
 * @package         EngageBox
 * @version         3.5.2 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

// Load Framework
if (!@include_once(JPATH_PLUGINS . '/system/nrframework/autoload.php'))
{
	throw new RuntimeException('Novarain Framework is not installed', 500);
}

$app = JFactory::getApplication();

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_rstbox'))
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
	return;
}

use NRFramework\Functions;
use NRFramework\Extension;

if (version_compare(JVERSION, '4.0', 'ge'))
{
	define('J4', true);
}

// Load framework's and component's language files
Functions::loadLanguage();
Functions::loadLanguage('com_rstbox');
Functions::loadLanguage('plg_system_rstbox');

// Check required extensions
if (!Extension::pluginIsEnabled('nrframework'))
{
	$app->enqueueMessage(JText::sprintf('NR_EXTENSION_REQUIRED', JText::_('RSTBOX'), JText::_('PLG_SYSTEM_NRFRAMEWORK')), 'error');
}

if (!Extension::pluginIsEnabled('rstbox'))
{
	$app->enqueueMessage(JText::sprintf('NR_EXTENSION_REQUIRED', JText::_('RSTBOX'), JText::_('PLG_SYSTEM_RSTBOX')), 'error');
}

if (!Extension::componentIsEnabled('ajax'))
{
	$app->enqueueMessage(JText::sprintf('NR_EXTENSION_REQUIRED', JText::_('RSTBOX'), 'Ajax Interface'), 'error');
}

// Initialize component's library
JLoader::register('EBHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/helper.php');

// Load component backend CSS
JHtml::stylesheet('com_rstbox/engagebox.sys.css', false, true, false);

// Perform the Request task
$controller = JControllerLegacy::getInstance('Rstbox');
$controller->execute($app->input->get('task'));
$controller->redirect();

