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

require_once(__DIR__ . '/lib.php');

class PPHelperMtree extends PPHelperStandardApp
{
	const ALLOWED = 1;
	const BLOCKED = 0;

	/**
	 * Retrieves available apps with the same element
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAvailableApps($type = 'mtree')
	{
		static $apps = null;

		if (is_null($apps)) {
			$apps = parent::getAvailableApps('mtree');
		}

		return $apps;
	}

	/**
	 * Retrieve library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLibrary()
	{
		static $lib = null;

		if (is_null($lib)) {
			$lib = new PPMosets();
		}

		return $lib;
	}

	/**
	 * Retrieves the total number of allowed published items
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalAllowedToPublish()
	{
		static $total = null;

		if (is_null($total)) {
			$total = $this->params->get('publish_listings_on_active', 0);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of allowed featured items
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalAllowedToFeature()
	{
		static $total = null;

		if (is_null($total)) {
			$total = $this->params->get('featured_listings_on_active', 0);
		}

		return $total;
	}

	/**
	 * Retrieves the restriction type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRestrictedCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$categories = $this->params->get('restrict_specific', null);

			if (!is_array($categories)) {
				$categories = array($categories);
			}
		}

		return $categories;
	}

	/**
	 * Retrieves the restriction type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRestrictionType()
	{
		static $type = null;

		if (is_null($type)) {
			$type = $this->params->get('category_to_restrict', 'any');
		}

		return $type;
	}

	/**
	 * Determines if mosets tree exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			$lib = $this->getLibrary();

			$exists = $lib->exists();
		}

		return $exists;
	}

	/**
	 * Retrieves the total number of items a user has with Mosets
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalItems($userId)
	{
		$db = PP::db();
		$query = array();

		$query = 'SELECT COUNT(1) FROM ' . $db->qn('#__mt_links') . ' WHERE ' . $db->qn('user_id') . '=' . $db->Quote($userId);

		$db->setQuery($query);
		$total = $db->loadResult(); 

		return $total;
	}

	/**
	 * Retrieves the total number of items a user has with Mosets
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalFeaturedItems($userId)
	{
		$db = PP::db();
		$query = array();

		$query = 'SELECT COUNT(1) FROM ' . $db->qn('#__mt_links') . ' WHERE ' . $db->qn('user_id') . '=' . $db->Quote($userId) . ' AND ' . $db->qn('link_featured') . '=' . $db->Quote(1);

		$db->setQuery($query);
		$total = $db->loadResult(); 

		return $total;
	}

	/**
	 * Retrieves the resource for a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserResource($userId)
	{
		$model = PP::model('Resource');
		$options = array(
			'user_id' => $userId,
			'title' => 'com_mtree.publish'
		);
		$result = $model->loadRecords($options);

		if (!$result) {
			return;
		}

		$item = array_shift($result);
		return $item;
	}

	/**
	 * Retrieves a list of child categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getChildCategory($categories)
	{
		$db = PP::db();

		$query = ' SELECT `cat_id` as cat_id'
				 .' FROM `#__mt_cats`'
				 .' WHERE `cat_parent` IN ( '.implode(',', $categories).' ) AND `cat_published`=1';

		$db->setQuery($query);
		
		return $db->loadColumn();
	}

	/**
	 * Determines if user is allowed to create listings
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUserAllowed($user)
	{
		$apps = PPHelperApp::getAvailableApps('mtree');
		$allowed = array();

		if (!$apps) {
			return true;
		}

		foreach ($apps as $app) {

			if ($app->getParam('applyAll', false) != false) {
				$allowed[] = self::ALLOWED;
				continue;
			}
			
			$appPlans = $app->getPlans();
			$userSubs = $user->getPlans();

			// if user have an active subscription of the plan attached with the app then return true
			$planIds = array();
			foreach ($userSubs as $sub) {
				$planIds[] = $sub->getId();	

			}
			
			if (array_intersect($planIds, $appPlans) != false) {
				$allowed[] = self::ALLOWED;
				continue;
			}

			$allowed[] = self::BLOCKED;
		}
		
		$allowed = in_array(self::ALLOWED, $allowed);
		return $allowed;
	}
	
	/**
	 * Retrieves items created by the user in Mosets
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserItems($userId)
	{
		$db = PP::db();
		$query =  ' SELECT `link_id` as link_id'
				 .' FROM `#__mt_links`'
				 .' WHERE `user_id`= '.$userId.' AND `link_published`=1';

		$db->setQuery($query);
		return $db->loadColumn();
	}

	/**
	 * Retrieves total items created by user in a specific category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalUserItemsInCategory($items, $categories)
	{
		$db = PP::db();
		$query =  ' SELECT count(*)'
				 .' FROM `#__mt_cl`'
				 .' WHERE `cat_id` IN ('.implode(',', $categories).") AND `link_id` IN ('".implode("', '", $items)."')";
		
		$db->setQuery($query);
		return $db->loadResult();
	}
}
