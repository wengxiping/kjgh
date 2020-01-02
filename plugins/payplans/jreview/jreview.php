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
require_once(__DIR__ . '/app/lib.php');
require_once(__DIR__ . '/hook.php');

class plgPayplansJreview extends PPPlugins
{
	/**
	 * Retrieves the library for jreviews
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getLib()
	{
		static $lib = null;

		if (is_null($lib)) {
			$lib = new PPJReview();
		}

		return $lib;
	}

	/**
	 * Triggered by hooks.php before a listing is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansJreviewBeforeSaveList($data)
	{
		$user = PP::user();

		if ($user->isAdmin()) {
			return true;
		}

		$listing = PP::normalize($data, 'Listing', array());
		$edited = PP::normalize($listing, 'id', false);
		$postCategories = array($listing['catid']);

		$lib = $this->getLib();
		$postCategories = $lib->getParentCategories($listing['catid'], $postCategories);

		// Check if user is allowed to post in the category
		$allowed = $this->isAllowed($postCategories, $user, false, $edited);

		return $allowed;
	}

	/**
	 * Triggered by hooks.php before a media item is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansJreviewBeforeSaveMedia($data)
	{
		$user = PP::user();

		if ($user->isAdmin()) {
			return true;
		}

		$lib = $this->getLib();

		$categoryId = $lib->getCategoryFromlist($data['listing_id']);
		$postCategories = array($categoryId);
		$postCategories = $lib->getParentCategories($categoryId, $postCategories);
		
		// Check what type of media is going to be checked
		$mediaType = PP::normalize($data, 'media_type', '');

		$allowed = $this->isAllowed($postCategories, $user, true, false, $mediaType);

		return $allowed;
	}

	/**
	 * Determines if user is allowed to post in the category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isAllowed($postInCategories = array(), $user, $mediaRestriction = false, $edited = false, $mediaType = 'image')
	{
		$postInCategories[] = 0;

		$apps = $this->getJReviewApps();
		$lib = $this->getLib();

		foreach ($postInCategories as $category) {
			$usage = $lib->getResourceUsageOfUser($category, $user->getId(), $mediaRestriction, $mediaType);

			// If user count is empty means no plan subscribed
			if (!$usage) {
			
				// If intersect count is zero means no app is created
				if (!array_key_exists($category, $apps)) {
					continue;
				}

				return false;
			}
			
			// allowed for that category on which app is not created or disabled after allocating resorces
			if (!array_key_exists($category, $apps)) {
				continue;
			}

			if ($mediaRestriction) {
				$userCount = $lib->getTotalMediaEntries($category, $user->getId(), false, $mediaType) + 1;
			}

			// We calculate the past as well as current entry
			if (!$mediaRestriction) {

				$userCount = $lib->getTotalPostEntries($category, $user->getId()) + 1;
				
				if ($edited) {
					$userCount--;
				}
			}

			// User posted more than allowed
			if ($userCount > $usage) {
				return false;
			}
		}	
		
		return true;
	}

	/**
	 * Retrieves a list of apps that is associated with the 
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function getJReviewApps()
	{
		$apps = $this->getAvailableApps();
		$result = array();
		
		foreach ($apps as $app) {
			
			$appConfiguration = $app->helper->getAppConfiguration();
			$categories = array(0);

			if ($appConfiguration->type != 'any_category') {
				$categories = $appConfiguration->categories;
			}
			
			foreach ($categories as $category) {
				$result[$category]['listing'] = $appConfiguration->total;
				$result[$category]['images'] = $appConfiguration->images;
				$result[$category]['audio'] = $appConfiguration->audio;
				$result[$category]['video'] = $appConfiguration->video;
				$result[$category]['attachment'] = $appConfiguration->attachments;
			}
		}	

		return $result;
	}	
}