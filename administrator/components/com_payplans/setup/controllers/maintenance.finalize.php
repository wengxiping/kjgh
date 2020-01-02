<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include parent library
require_once( dirname( __FILE__ ) . '/controller.php' );


class PayPlansControllerMaintenanceFinalize extends PayPlansSetupController
{
	public function execute()
	{
		$this->engine();

		$version = $this->getInstalledVersion();

		// Update the version in the database to the latest now
		$config = PP::table('Config');
		$config->load(array('key' => 'script_version'));

		$config->key = 'script_version';
		$config->value = $version;

		// Save the new config
		$config->store($config->key);

		// Remove any folders in the temporary folder.
		$this->cleanup(PP_TMP);

		// Remove installation temporary file
		JFile::delete(JPATH_ROOT . '/tmp/payplans.installation');

		// Update installation package to 'launcher'
		$this->updatePackage();

		$result = $this->getResultObj(JText::sprintf('COM_PP_INSTALLATION_MAINTENANCE_UPDATED_MAINTENANCE_VERSION', $version), 1, 'success');

		return $this->output($result);
	}

	/**
	 * Perform system wide cleanups after the installation is completed.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function cleanup($path)
	{
		$folders = JFolder::folders($path, '.', false, true);
		$files = JFolder::files($path, '.', false, true);

		if ($folders) {
			foreach ($folders as $folder) {
				JFolder::delete($folder);
			}
		}

		if ($files) {
			foreach ($files as $file) {
				JFile::delete($file);
			}
		}
	}

	/**
	 * Update installation package to launcher package to update issue via update button
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function updatePackage()
	{
		// For beta, we need to update the setup script
		$path = JPATH_ADMINISTRATOR . '/components/com_payplans/setup/bootstrap.php';

		// Read the contents
		$contents = JFile::read($path);

		$contents = str_ireplace("define('PP_INSTALLER', 'full');", "define('PP_INSTALLER', 'launcher');", $contents);
		$contents = preg_replace('/define\(\'PP_PACKAGE\', \'.*\'\);/i', "define('PP_PACKAGE', '');", $contents);

		JFile::write($path, $contents);
	}
}
