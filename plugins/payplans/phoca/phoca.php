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

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansPhoca extends PPPlugins
{
	/**
	 * Check for access in phoca downloads
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck(PPUser $user)
	{
		// Restriction on direct access, if admin create menu link of phoca
		$option = $this->input->get('option', '');
		$view = $this->input->get('view', '');

		if ($option != 'com_phocadownload') {
			return;
		}

		if ($view != 'category') {
			return;
		}

		$userId = $user->getId();
			
		if (!$userId) {
			$userId = -1;
		}

		$helper = $this->getAppHelper();

		// Get the category id from phoca (e.g: 1:phoca-cat-1)
		$categoryId = $this->input->get('id', '', 'int');
		$parentCategories = $helper->getParentCategories($categoryId);

		$apps = $this->getAvailableApps();
		$accessibleCategories = array(); 
		$result = array();


		foreach ($apps as $app) {
			$appHelper = $app->getHelper();
			$appCategories = $appHelper->getAccessibleCategories('active');
			$restrictedCategories = array_intersect($parentCategories, $appCategories);

			if (!empty($restrictedCategories))  {
				$accessibleCategories = $appHelper->getUserAccessibleCategories($userId);

				foreach ($restrictedCategories as $cat) {
					if (!array_key_exists($cat, $accessibleCategories)) {
						$result[] = false;
						break;
					}
				}

				continue;
			}

			$result[] = true;
		}
		
		if (in_array(false, $result)) {
			PP::info()->set('COM_PAYPLANS_APP_PHOCADOWNLOAD_ACCESS_RIGHTS_DENIAL', 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=plan');

			return PP::redirect($redirect);
		}
	}
}