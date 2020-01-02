<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$app = JFactory::getApplication();
$input = $app->input;

// Ensure that the Joomla sections don't appear.
$input->set('tmpl', 'component');

// Determines if the current mode is re-install
$reinstall = $input->get('reinstall', false, 'bool') || $input->get('install', false, 'bool');

// If the mode is update, we need to get the latest version
$update = $input->get('update', false, 'bool');

// Determines if we are now in developer mode.
$developer = $input->get('developer', false, 'bool');

if ($developer) {
	$session = JFactory::getSession();
	$session->set('easysocial.developer', true);
}

############################################################
#### Constants
############################################################
$path = __DIR__;
define('ES_PACKAGES', $path . '/packages');
define('ES_CONFIG', $path . '/config');
define('ES_THEMES', $path . '/themes');
define('ES_LIB', $path . '/libraries');
define('ES_CONTROLLERS', $path . '/controllers');
define('ES_TMP', $path . '/tmp');
define('ES_VERIFIER', 'https://stackideas.com/updater/verify');
define('ES_DOWNLOADER', 'https://stackideas.com/updater/services/download/easysocial');
define('ES_MANIFEST', 'https://stackideas.com/updater/manifests/easysocial');
define('ES_BETA', false);
define('ES_SETUP_URL', rtrim(JURI::root(), '/') . '/administrator/components/com_easysocial/setup');
define('ES_KEY', 'c885194982628876191105aabd25cae5');
define('ES_INSTALLER', 'launcher');
define('ES_PACKAGE', '');

############################################################
#### Process ajax calls
############################################################
if ($input->get('ajax', false, 'bool')) {

	$controller = $input->get('controller', '', 'cmd');
	$task = $input->get('task', '', 'cmd');

	$controllerFile = ES_CONTROLLERS . '/' . strtolower( $controller ) . '.php';

	require_once($controllerFile);

	$controllerName = 'EasySocialController' . ucfirst( $controller );
	$controller = new $controllerName();

	return $controller->$task();
}

############################################################
#### Process controller
############################################################
$controller = $input->get('controller', '', 'cmd');

if (!empty($controller)) {
	$controllerFile = ES_CONTROLLERS . '/' . strtolower($controller) . '.php';

	require_once($controllerFile);

	$controllerName = 'EasySocialController' . ucfirst( $controller );
	$controller = new $controllerName();
	return $controller->execute();
}

############################################################
#### Initialization
############################################################
$contents = JFile::read(ES_CONFIG . '/installation.json');
$steps = json_decode($contents);

############################################################
#### Workflow
############################################################
$active = $input->get('active', 0, 'default');

if ($active === 'complete') {
	$activeStep = new stdClass();

	$activeStep->title = JText::_('Installation Completed');
	$activeStep->template = 'complete';

	// Assign class names to the step items.
	if ($steps) {
		foreach ($steps as $step) {
			$step->className = ' done';
		}
	}

	// check if system has unsynced media privacy. #3289
	$model = ES::model('Maintenance');
	$unsyncedPrivacyCount = $model->getMediaPrivacyCounts();

	// Remove installation temporary file
	JFile::delete(JPATH_ROOT . '/tmp/easysocial.installation');

} else {

	if ($active == 0) {
		$active = 1;
		$stepIndex = 0;
	} else {
		$active += 1;
		$stepIndex = $active - 1;
	}

	// Get the active step object.
	$activeStep = $steps[$stepIndex];

	// Assign class names to the step items.
	foreach ($steps as $step) {
		$step->className = $step->index == $active || $step->index < $active ? ' current' : '';
		$step->className .= $step->index < $active ? ' done' : '';
	}

	// If this site meets all requirement, we skip the requirement page
	if ($stepIndex == 0) {

		$gd = function_exists('gd_info');
		$curl = is_callable('curl_init');

		// MySQL info
		$db = JFactory::getDBO();
		$mysqlVersion = $db->getVersion();

		// PHP info
		$phpVersion = phpversion();
		$uploadLimit = ini_get('upload_max_filesize');
		$memoryLimit = ini_get('memory_limit');
		$postSize = ini_get('post_max_size');
		$magicQuotes = get_magic_quotes_gpc() && JVERSION > 3;

		if (stripos($memoryLimit, 'G') !== false) {

			list($memoryLimit) = explode('G', $memoryLimit);

			$memoryLimit = $memoryLimit * 1024;
		}

		$postSize = 4;
		$hasErrors = false;

		if (!$gd || !$curl || $magicQuotes) {
			$hasErrors = true;
		}

		$files = array();

		$files['admin'] = new stdClass();
		$files['admin']->path = JPATH_ROOT . '/administrator/components';
		$files['site'] = new stdClass();
		$files['site']->path = JPATH_ROOT . '/components';
		$files['tmp'] = new stdClass();
		$files['tmp']->path = JPATH_ROOT . '/tmp';
		$files['media'] = new stdClass();
		$files['media']->path = JPATH_ROOT . '/media';
		$files['user'] = new stdClass();
		$files['user']->path = JPATH_ROOT . '/plugins';
		$files['module'] = new stdClass();
		$files['module']->path = JPATH_ROOT . '/modules';

		// Debugging
		$posixExists = function_exists('posix_getpwuid');

		if ($posixExists) {
			$owners = array();
		}

		// If until here no errors, we don't display the setting section
		$showSettingsSection = $hasErrors;

		// Determines write permission on folders
		$showDirectorySection = false;

		foreach ($files as $file) {

			// The only proper way to test this is to not use is_writable
			$contents = "<body></body>";
			$state = JFile::write($file->path . '/tmp.html', $contents);

			// Initialize this to false by default
			$file->writable = false;

			if ($state) {
				JFile::delete($file->path . '/tmp.html');

				$file->writable = true;
			}

			if (!$file->writable) {
				$showDirectorySection = true;
				$hasErrors = true;
			}

			if ($posixExists) {
				$owner = posix_getpwuid(fileowner($file->path));
				$group = posix_getpwuid(filegroup($file->path));

				$file->owner = $owner['name'];
				$file->group = $group['name'];
				$file->permissions = substr(decoct(fileperms($file->path)), 1);
			}
		}

		if ($hasErrors) {
			$errorStep = new stdCLass;
			$errorStep->index = 0;
			$errorStep->title = 'COM_ES_INSTALLATION_REQUIREMENTS_ERROR';
			$errorStep->desc = 'COM_ES_INSTALLATION_REQUIREMENTS_ERROR_DESC';
			$errorStep->template = 'requirements';
			$activeStep = $errorStep;

			require(ES_THEMES . '/default.php');
			return;
		}

	}
}

require(ES_THEMES . '/default.php');
