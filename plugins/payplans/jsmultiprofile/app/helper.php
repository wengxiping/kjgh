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

class PPHelperJsmultiprofile extends PPHelperStandardApp
{
	/**
	* Determines if Jomsocial is installed
	*
	* @since	4.0.0
	* @access	public
	*/
	public function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			jimport('joomla.filesystem.file');

			$file = JPATH_ROOT . '/components/com_community/libraries/core.php';
			$fileExists = JFile::exists($file);
			$exists = false;

			if ($fileExists && JComponentHelper::isEnabled('com_community')) {
				$exists = true;

				include_once($file);
			}
		}

		return $exists;
	}

	public function setJsmultiprofile($userId, $jsmultiprofile)
	{
		// Check if there is any multiprofile to set
		if (!$jsmultiprofile) {
			return true;
		}

		if (!$this->exists()) {
			return true;
		}

		$user = CFactory::getUser($userId);
		$user->set('_profile_id', $jsmultiprofile[0]);

		return $user->save();
	}
}