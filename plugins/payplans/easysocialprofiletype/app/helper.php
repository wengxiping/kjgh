<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class PPHelperEasysocialProfiletype extends PPHelperStandardApp
{
	protected $_resource = '';
	
	/**
	* Determines if EasySocial is installed
	*
	* @since	4.0.0
	* @access	public
	*/
	public function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			jimport('joomla.filesystem.file');

			$file = JPATH_ROOT . '/administrator/components/com_easysocial/includes/easysocial.php';
			$fileExists = JFile::exists($file);
			$exists = false;

			if ($fileExists && JComponentHelper::isEnabled('com_easysocial')) {
				$exists = true;

				include_once($file);
			}
		}

		return $exists;
	}
	
	public static function getDefaultEasysocialProfiletypes()
	{
		$db = PP::db();

		$query = 'SELECT `id` FROM `#__social_profiles` WHERE `default` = 1';

		$db->setQuery($query);
		$result = $db->loadResult();
		
		return $result;
	}

	public function setEasysocialprofile($userId, $easysocialprofileId, $upgrade = false)
	{
		// If profiletype not set
		if (empty($easysocialprofileId)) {
			return true;
		}

		$workflow = ES::workflows()->getWorkflow($easysocialprofileId, SOCIAL_TYPE_USER);

		$profileModel = ES::model('Profiles');

		$user = ES::user($userId);
		// remove cache copy of user from es so that
		// the juser's params value is clean. #611
		$user->removeFromCache();

		$profileModel->updateUserProfile($userId, $easysocialprofileId, $workflow->id);

		// Update usergroup when profiletype will update
		if ($upgrade) {
			$profileModel->updateJoomlaGroup($userId, $easysocialprofileId);
		}
	}
}