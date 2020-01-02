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

class PPHelperSobipro extends PPHelperStandardApp
{
	const ALLOWED = 1;
	const BLOCKED = 0;

	/**
	 * Determine if sobipro is exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_sobipro');
		$path = JFile::exists(JPATH_ROOT . '/components/com_sobipro/sobipro.php');

		if (!$enabled || !$path) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if this user is restricted to submit or not
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function restrictSubmission($categoryIds = array(), $user, $sectionId = 0)
	{
		$allCategories = array();

		// Get all selected categories from the form
		foreach ($categoryIds as $categoryId) {
			$temp = $this->getAllParents($categoryId);
			$allCategories = array_merge($allCategories, $temp);
		}
		
		// We need treat section as category also
		$allCategories[] = 0;
		
		// Get restricted categories from existing apps
		$restrictedAppCategories = $this->getRestrictedAppCategories();

		// Filter out the categories that are not included in app config
		$count = 0;

		foreach ($allCategories as $categoryId) {

			// Unset those restricted categories id if not match with selected categories 
			if (!array_key_exists($categoryId, $restrictedAppCategories)) {
				unset($allCategories[$count]);
			}

			$count++;
		}

		// Skip this if there do not have return any categories 
		// which mean that there do not have match any restricted categories when user publish this new entry.
		if (empty($allCategories)) {
			return true;
		}

		// Ensure that all is unique category id
    	$allCategories = array_unique($allCategories);

    	// The current behavior if the all categories contain section id
    	// we assume one of the sobipro app set that entry type to section
    	// set the flag for determine all these categories contain section id
    	$hasContainSectionId = false;

    	if (in_array($sectionId, $allCategories)) {
    		$hasContainSectionId = true;
    	}

		// Now check for individual category and store it's result in array
		$appResult = array();

		foreach ($allCategories as $categoryId) {
			$appResult[] = $this->isUserAllowed($user, $categoryId, $restrictedAppCategories, $sectionId, $hasContainSectionId);
		}

		// If appresult array contain BLOCKED then this user not allowed to post
		if (in_array(self::BLOCKED, $appResult)) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if user allowed or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUserAllowed($user, $categoryId, $restrictedAppCategories, $sectionId, $hasContainSectionId = false)
	{
		// Fetch all the user resource of that specific category
		$userResource = $this->getSobiproResource($user->getId(), true, $categoryId, $sectionId, $hasContainSectionId);
		
		// App sobipro entry set to any category
		if ($categoryId == 0) {
			$userResource = $this->getSobiproResource($user->getId(), false, $categoryId, $sectionId, $hasContainSectionId);
		}

		// force to set the status to allowed if contain section ID from that list of categories.
		if ($userResource == 'forceAllowed' && $hasContainSectionId) {
			return self::ALLOWED;
		}

		// If there do not have any resource from this current user
		// validate for the selected category id and restricted category id
		if (!$userResource) {

			if (array_key_exists($categoryId, $restrictedAppCategories)) {
				return self::BLOCKED;
			}

			return self::ALLOWED;
		}

		// determine for the current user whether exceeded that limit or not
		$isExceeded = $this->isExceeded($userResource, $categoryId, $user);

		return $isExceeded;
	}

	/**
	 * Get resource from the specific user
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getSobiproResource($userId, $specificCategoryOption = false, $categoryId, $sectionId, $hasContainSectionId = false)
	{
		// Currently App sobipro entry type has 3 options
		// - Any category 
		// - Specific category
		// - Section

		$sectionResource = '';
		$specificCategoryResource = '';

		// Check for the section first
		if ($sectionId && $hasContainSectionId) {

			$title = 'com_sobipro.entry' . trim($categoryId);
			$sectionResource = $this->getResource($userId, $categoryId, $title);

			// block it if current user resource doesn't have this section id
			if (!$sectionResource->resource_id && ($categoryId == $sectionId)) {
				return false;
			}

			// if it reached here mean those category id is not equal to section
			// we need to force to set to allowed for that status
			if (!$sectionResource->resource_id) {
				return 'forceAllowed';
			}

			// only pass the user resource if found any
			if ($sectionResource->resource_id) {
				return $sectionResource;
			}			
		}

		// check for the specific category type resources
		// at the same time this will be check also that section id as well since both type also using the same format e.g. com_sobipro.entry+categoryId
		if ($specificCategoryOption) {
			$title = 'com_sobipro.entry' . trim($categoryId);
			$specificCategoryResource = $this->getResource($userId, $categoryId, $title);

			if ($specificCategoryResource->resource_id) {
				return $specificCategoryResource;
			}
		}

		// This key for Any Category entry type
		$title = 'com_sobipro.entry*';
		$resource = $this->getResource($userId, 0, $title);

		if (!$specificCategoryResource->resource_id && !$resource->resource_id && !$sectionResource->resource_id) {
			return false;
		}

		return $resource;
	}

	/**
	 * Determine user entries exceeded resource entries
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function isExceeded($userResource, $categoryId, $user)
	{
		$userEntries = $this->getUserEntries($user->getId(), $categoryId);

		$entryId = $this->input->get('sid', 0);

		// If user is editing his published entry,
		// we have to allow him
		if (isset($entryId) && key_exists($entryId, $userEntries)) {
			return self::ALLOWED;
		}

		// If user's entries is exceeded the allowed count, block
		if ($userEntries && $userResource->count && (count($userEntries) >= $userResource->count)) {
			return self::BLOCKED;
		}

		return self::ALLOWED;
	}

	/**
	 * Retrieve all user's entries
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getUserEntries($userId, $categoryId = 0)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT DISTINCT(obj.`id`) FROM `#__sobipro_relations` as rel';
		$query[] = 'LEFT JOIN `#__sobipro_object` as obj';
		$query[] = 'ON rel.' . $db->qn('id') . ' = obj.' . $db->qn('id');
		$query[] = 'WHERE ' . $db->qn('owner') . ' =' . $db->Quote($userId);
		$query[] = 'AND rel. ' . $db->qn('oType') . ' = ' . $db->Quote('entry') . ' AND ' . $db->qn('state') . ' = ' . $db->Quote(1);

		if ($categoryId) {
			$subCategories = $this->getAllSubCategories($categoryId);
			$subCategories[] = $categoryId;
			$query[] = 'AND rel.' . $db->qn('pid') . ' IN ('.implode(',', $subCategories).')';
		}

		$query = implode(' ', $query);

		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	/**
	 * Retrieve all child categories
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getAllSubCategories($catId, &$subCategories = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM `#__sobipro_object`';
		$query[] = 'WHERE ' . $db->qn('oType') . '=' . $db->Quote('category');
		$query[] = 'AND ' . $db->qn('parent') . '=' . $db->Quote($catId);

		$query = implode(' ', $query);
		
		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		if (!empty($results)) {
			foreach ($results as $res) {
				$subCategories[] = $res->id;
				$this->getAllSubCategories($res->id, $subCategories);
			}
		}

		return $subCategories;
	}

	/**
	 * Determine whether the site has setup any sobipro app
	 *
	 * @since   4.0.15
	 * @access  public
	 */
	public function hasSobiproApp()
	{
		$app = PPHelperApp::getAvailableApps('sobipro');
		
		// Do not do anything if there do not have any subipro app
		if (!$app) {
			return false;
		}

		return $app;
	}

	/**
	 * Fetch all app restricted categories with their plans
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getRestrictedAppCategories()
	{
		$data = array();

		$sobiproApps = PPHelperApp::getAvailableApps('sobipro');
		
		foreach ($sobiproApps as $app) {

			$sobiproAppType = $app->getAppParam('addEntryIn', 'any_category');
			
			if ($sobiproAppType == 'any_category') {
				
				$appPlans = $app->getPlans();
				
				if (empty($appPlans)) {
					$appPlans = 0;
				}
				
				if (empty($data[0])) {
					$data[0] = $appPlans;
				} else {
					$data[0] = array_merge($data[0], $appPlans);
				}

				continue;
			}

			// Get the sobiPro category that been set in app
			$categories = $app->getAppParam('addEntryInCategory');

			if (empty($categories)) {
				$categories = $app->getAppParam('addEntryInSection');
			}

			// Section type 
			if ($sobiproAppType == 'on_section') {
				$sectionId = $app->getAppParam('addEntryInSection');

				// retrieve a list of parent category ids from the section
				$parentCatIdsFromSection = $this->getParentCatIdsFromSection($sectionId);
				$subCategoriesWithParent = array();
 				$combineAllChildCats = array();

				foreach ($parentCatIdsFromSection as $parentCategory) {
					$subCategories = $this->getAllSubCategories($parentCategory->id);
					
					$subCategoriesWithParent[] = $subCategories;
					$parentCatIds[] = $parentCategory->id;
				}

				if ($subCategoriesWithParent) {

			    	foreach($subCategoriesWithParent as $child) {
			     
						foreach($child as $childId) {
							$combineAllChildCats[] = $childId;
						}
				    }
				}

				// combine all the categories together under that section id
				$categories = array_merge($parentCatIds, $combineAllChildCats);

				// need to merge back that section id into this
				$categories = array_merge($sectionId, $categories);

				// Ensure that all is unique category id 
		    	$categories = array_unique($categories);
			}

			foreach ($categories as $category) {

				$appPlans = $app->getPlans();
				
				if (empty($appPlans)) {
					$appPlans = 0;
				}

				// $applyAll = $app->getParam('applyAll', 0);

				// if ($applyAll) {
				// 	$appPlans = PPHelperPlan::getPlans(array('published' => 1), false);
				// }

				if (empty($data[$category])) {
					$data[$category] = $appPlans;

				} else {
					$data[$category] = array_merge($data[$category], $appPlans);
				
				}
			}
		}

		return $data;
	}

	/**
	 * Get Parent Catgeories from sections
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParentCatIdsFromSection($sectionId)
	{
		$db = PP::db();
		$query = array();

		if (is_array($sectionId)) {
			$sectionId = implode(',', $sectionId);
		}

		$query[] = 'SELECT `id` FROM `#__sobipro_relations`';
		$query[] = 'WHERE ' . $db->qn('pid') . ' IN (' . $db->Quote($sectionId) . ')';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Get Entry category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEntryCategory($entryId = 0)
	{
		$db = PP::db();
		$query = array();

		$query[] = 'SELECT `pid` FROM `#__sobipro_relations`';
		$query[] = 'WHERE ' . $db->qn('id') . '=' . $db->Quote($entryId);

		$query = implode(' ', $query);
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	/**
	 * Fetch all the parent categories of specified category
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getAllParents($categoryId, &$parentwithchild = array())
	{
		$parentwithchild[] = $categoryId;
		
		$db = PP::db();
		$query = array();

		$query[] = 'SELECT * FROM `#__sobipro_object`';
		$query[] = 'WHERE ' . $db->qn('oType') . '=' . $db->Quote('category');
		$query[] = 'AND ' . $db->qn('id') . '=' . $db->Quote($categoryId);

		$query = implode(' ', $query);
		
		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		if (empty($results)) {
			return $parentwithchild;
		}
		
		$result = array_shift($results);
		
		if ($result->parent == 0) {
			return $parentwithchild;
		}

		$this->getAllParents($result->parent, $parentwithchild);
		
		return $parentwithchild;
	}

	/**
	 * Toggle publish state
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function toggleCategoryEntry($userid, $publishCount, $action, $subscription, $categoryId = 0)
	{
		$complement = 1 - $action;
		$restricted_cat = array($categoryId);

		if ($categoryId != 0) {
			$restricted_cat = $this->getAllSubCategories($categoryId);
			$restricted_cat[] = $categoryId;
		}
		
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT `id` FROM `#__sobipro_relations`';
		$query[] = 'WHERE `oType` = ' . $db->Quote('entry');
		$query[] = 'AND `pid` IN (' . implode(',', $restricted_cat) . ')';

		$query = implode(' ', $query);
		
		$db->setQuery($query);
		$sectionIds = $db->loadObjectList();

		$entryIds = array();

		foreach ($sectionIds as $sId){
			$entryIds[] = $sId->id;
		}

		// Change state of all the fields related with the entry
		$query = array();
		$query[] = 'UPDATE `#__sobipro_field_data`';
		$query[] = 'SET `enabled`=' . $db->Quote($action);
		$query[] = 'WHERE `createdBy`=' . $db->Quote($userid);
		$query[] = 'AND `enabled`=' . $db->Quote($complement);

		if ($categoryId != 0 && !empty($entryIds)) {
			$query[] = 'AND `sid` IN (' . implode(',',$entryIds) . ')';
		}
		
		$query[] = 'LIMIT ' . $publishCount;

		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();
		
		// Change state of the entry in object table
		$query = array();
		$query[] = 'UPDATE `#__sobipro_object`';
		$query[] = 'SET `state`=' . $db->Quote($action) . ', `stateExpl`=' . $db->Quote('');
		$query[] = 'WHERE `owner`=' . $db->Quote($userid);
		$query[] = 'AND `oType`=' . $db->Quote('entry');
		$query[] = 'AND `state`=' . $db->Quote($complement);

		if ($categoryId != 0 && !empty($entryIds)) {
			$query[] = 'AND `id` IN (' . implode(',',$entryIds) . ')';
		}

		$query[] = 'LIMIT ' . $publishCount;
		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Toggle publish state of section
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function toggleSectionEntry($userid, $publishCount, $action, $subscription, $section_id = 0)
	{
		$complement = 1 - $action;
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT `id` FROM `#__sobipro_object`';
		$query[] = 'WHERE `oType` = ' . $db->Quote('entry');
		$query[] = 'AND `owner` = ' . $db->Quote($userid);

		$query = implode(' ', $query);
		
		$db->setQuery($query);
		$sectionIds = $db->loadObjectList();

		$entryIds = array();

		foreach ($sectionIds as $sId){
			$entryIds[] = $sId->id;
		}

		// Change state of all the fields related with the entry
		$query = array();
		$query[] = 'UPDATE `#__sobipro_field_data`';
		$query[] = 'SET `enabled`=' . $db->Quote($action);
		$query[] = 'WHERE `createdBy`=' . $db->Quote($userid);
		$query[] = 'AND `enabled`=' . $db->Quote($complement);
		$query[] = 'AND `section`=' . $db->Quote($section_id);

		if (!empty($entryIds)) {
			$query[] = 'AND `sid` IN (' . implode(',',$entryIds) . ')';
		}
		
		$query[] = 'LIMIT ' . $publishCount;

		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();

		// Change the state of the entry in object table
		$query = array();
		$query[] = 'SELECT `sid` FROM `#__sobipro_field_data`';
		$query[] = 'WHERE `section` = ' . $db->Quote($section_id);
		$query[] = 'AND `createdBy` = ' . $db->Quote($userid);
		$query[] = 'AND `enabled` = ' . $db->Quote($complement);
		$query[] = 'LIMIT ' . $publishCount;

		$query = implode(' ', $query);
		
		$db->setQuery($query);
		$entries = $db->loadColumn();
		$entries = array_unique($entries);

		// Change state of the entry in object table
		$query = array();
		$query[] = 'UPDATE `#__sobipro_object`';
		$query[] = 'SET `state`=' . $db->Quote($action) . ', `stateExpl`=' . $db->Quote('');
		$query[] = 'WHERE `owner`=' . $db->Quote($userid);
		$query[] = 'AND `oType`=' . $db->Quote('entry');
		$query[] = 'AND `state`=' . $db->Quote($complement);

		if (!empty($entries)) {
			$query[] = 'AND `id` IN (' . implode(',',$entries) . ')';
		}

		$query[] = 'LIMIT ' . $publishCount;
		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Get Entry Resources
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getEntryResource($userId)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT * FROM `#__payplans_resource`';
		$query[] = 'WHERE `title` LIKE ' . $db->Quote('com_sobipro.entry%');
		$query[] = 'AND `user_id` = ' . $db->Quote($userId);

		$query = implode(' ', $query);

		$db->setQuery($query);
		$records = $db->loadObjectList();

		foreach ($records as $record) {
			$record->title = str_replace('com_sobipro.entry', '', $record->title);
			
			if ($record->title == '*') {
				$record->title = 0;
			}
			
			$entries = $this->getUserEntries($userId, $record->title);
			$record->consumed = count($entries);
			$record->title = $this->getCategoryTitle($record->title);
		}

		return $records;
	}

	/**
	 * Retrieve the category title from category id
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getCategoryTitle($catId)
	{
		if ($catId == 0) {
			return JText::_("COM_PAYPLANS_APP_SOBIPRO_ANY_CATEGORY");
		}

		$db = PP::db();
		$query = array();
		$query[] = 'SELECT name FROM `#__sobipro_object` as obj';
		$query[] = 'WHERE `id`=' . $db->Quote($catId);

		$query = implode(' ', $query);
		
		$db->setQuery($query);
		$results = $db->loadResult();
		
		return $results;
	}
}