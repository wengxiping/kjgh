<?php
// namespace components\com_jrealtimeanalytics;
/**
 * Entrypoint dell'application di frontend
 * 
 * @package JREALTIMEANALYTICS::components::com_jrealtimeanalytics
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
if(!JComponentHelper::getParams('com_jrealtimeanalytics')->get('enable_debug', 0)) {
	ini_set('display_errors', 0);
	ini_set('error_reporting', E_ERROR);
}

// Auto loader setup
// Register autoloader prefix
require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/loader.php';
JRealtimeLoader::setup ();
JRealtimeLoader::registerPrefix ( 'JRealtime', JPATH_COMPONENT_ADMINISTRATOR . '/framework' );

// Main application object
$app = JFactory::getApplication ();

// Manage partial language translations
$jLang = JFactory::getLanguage ();
$jLang->load ( 'com_jrealtimeanalytics', JPATH_COMPONENT, 'en-GB', true, true );
$jLang->load ( 'com_jrealtimeanalytics', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true, true );
if ($jLang->getTag () != 'en-GB') {
	$jLang->load ( 'com_jrealtimeanalytics', JPATH_SITE, null, true, false );
	$jLang->load ( 'com_jrealtimeanalytics', JPATH_ADMINISTRATOR, null, true, false );
	$jLang->load ( 'com_jrealtimeanalytics', JPATH_COMPONENT, null, true, false );
	$jLang->load ( 'com_jrealtimeanalytics', JPATH_COMPONENT_ADMINISTRATOR, null, true, false );
}

/**
 * All SMVC logic is based on controller.task correcting the wrong Joomla concept
 * of base execute on view names.
 * When task is not specified because Joomla force view query string such as menu
 * the view value is equals to controller and viewname = controller.display
 */
$viewName = $app->input->get('view', 'stream');
$controller_command = $app->input->get('task', $viewName . '.display');
if (strpos ( $controller_command, '.' )) {
	list ( $controller_name, $controller_task ) = explode ( '.', $controller_command );
}
// Defaults
if (! isset ( $controller_name )) {
	$controller_name = 'stream';
}
if (! isset ( $controller_task )) {
	$controller_task = 'display';
}

$path = JPATH_COMPONENT . '/controllers/' . strtolower ( $controller_name ) . '.php';
if (file_exists ( $path )) {
	require_once $path;
} else {
	$app->enqueueMessage ( JText::_ ( 'No controller file' ), 'error' );
	return false;
}

// Create the controller
$classname = 'JRealtimeController' . ucfirst ( $controller_name );
if (class_exists ( $classname )) {
	$controller = new $classname ();
	// Perform the Request task
	$controller->execute ( $controller_task );
	
	// Redirect if set by the controller
	$controller->redirect ();
} else {
	$app->enqueueMessage ( JText::_ ( 'No controller class found' ), 'error' );
	return false;
}
