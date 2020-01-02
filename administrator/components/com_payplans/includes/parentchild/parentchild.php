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

class PPParentChild extends PayPlans
{
	public static function factory()
	{
		return new self();
	}

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Determine if user is able to subscribe to the specified plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canSubscribe($planId, $userId = null)
	{
		$parent = $this->getParent($planId);

		if (!$parent) {
			return true;
		}

		// if base plan is set then explode else continue for next record
		if (isset($parent->base_plan) && $parent->base_plan == '') {
			return true;
		}

		// Get user plans
		$user = PP::user($userId);
		$userPlans = $user->getPlans();
		$userPlansIds = array();

		if ($userPlans) {
			foreach ($userPlans as $plan) {
				$userPlansIds[] = $plan->getId();
			}
		}

		$parentRelation = $parent->relation;
		$parentPlans = explode(',', $parent->base_plan);

		$subscribedParentPlans = array_intersect($parentPlans, $userPlansIds);
		$unsubscribedParentPlans = array_diff($parentPlans, $userPlansIds);

		// Any of the plan is subscribed
		if ($parentRelation == PP_CONST_ANY) {

			if (count($subscribedParentPlans) > 0) {
				return true;
			} else {
				$planTitle = array();
				foreach ($subscribedParentPlans as $planId) {
					$planTitle[] = PP::plan($planId)->getTitle();
				}

				$planTitle = implode(', ', $planTitle);

				$message = JText::sprintf('COM_PP_PARENTCHILD_SUBSCRIBE_TO_ANY', $planTitle);
				$this->setError($message);
			}
		}

		// Allof the plan is subscribed
		if ($parentRelation == PP_CONST_ALL) {

			if (count($unsubscribedParentPlans) == 0) {
				return true;
			} else {
				$planTitle = array();
				foreach ($unsubscribedParentPlans as $planId) {
					$planTitle[] = PP::plan($planId)->getTitle();
				}

				$planTitle = implode(', ', $planTitle);

				$message = JText::sprintf('COM_PP_PARENTCHILD_SUBSCRIBE_TO_ALL', $planTitle);
				$this->setError($message);
			}
		}

		if ($parentRelation == PP_CONST_NONE) {

			if (count($subscribedParentPlans) == 0) {
				return true;
			} else {
				$planTitle = array();
				foreach ($subscribedParentPlans as $planId) {
					$planTitle[] = PP::plan($planId)->getTitle();
				}

				$planTitle = implode(', ', $planTitle);

				$message = JText::sprintf('COM_PP_PARENTCHILD_SUBSCRIBE_TO_NONE', $planTitle);
				$this->setError($message);
			}
		}

		return false;
	}

	/**
	 * Determine if the plan has the parent
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParent($planId)
	{
		$model = PP::model('parentChild');
		$record = $model->loadRecords(array('dependent_plan' => $planId));

		if ($record) {
			$record = array_pop($record);
		}

		return $record;
	}

	/**
	 * Filter the plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function filterPlans($givenPlans, $records = false, &$removedPlans = array())
	{
		// Get all records from parenthcild table
		if (!$records) {
			$model = PP::model('parentChild');
			$records = $model->loadRecords();
		}

		if (!$records) {
			return $givenPlans;
		}

		$user = PP::user();

		// Get all plans from the site
		$planModel = PP::model('Plan');
		$plans = $planModel->loadRecords(array('published' => '1'), array(), '', 'ordering');
		$allPlans = array_keys($plans);

		// Get user plans
		$userPlans = $user->getPlans();
		$userPlansIds = array();

		if ($userPlans) {
			foreach ($userPlans as $plan) {
				$userPlansIds[] = $plan->getId();
			}
		}

		// Only check for plans that are not associated with the user
		$unsubscribedPlans = array_diff($allPlans, $userPlansIds);

		foreach ($records as $id => $record) {

			// if base plan is set then explode else continue for next record
			if (isset($record->base_plan) && $record->base_plan == '') {
				continue;
			}

			$parentRelation = $record->relation;
			$parentPlans = explode(',', $record->base_plan);
			$subscribedParentPlans = array_intersect($parentPlans, $userPlansIds);
			$unsubscribedParentPlans = array_intersect($parentPlans, $unsubscribedPlans);

			// if all plans are subscribed
			if ($parentRelation == PP_CONST_ALL && count($unsubscribedParentPlans) == 0) {
				continue;
			}

			// if any plan is subscribed
			if ($parentRelation == PP_CONST_ANY && count($subscribedParentPlans) > 0) {
				continue;
			}

			if ($parentRelation == PP_CONST_NONE && count($subscribedParentPlans) == 0) {
				continue;
			}

			// else unset plan
			$removedPlans[$id] = array($parentRelation, $parentPlans);

			foreach ($givenPlans as $key => $value) {
		 		if ($id == $value->getId()) {
		 			unset($givenPlans[$key]);
				}
			}		
		}

		return $givenPlans;
	}

	public function filterGroups($givenGroups, $records = false)
	{
		if (!$givenGroups) {
			return $givenGroups;
		}

		// Get all records from parenthcild table
		if (!$records) {
			$model = PP::model('parentChild');
			$records = $model->loadRecords();
		}

		if (!$records) {
			return $givenGroups;
		}

		foreach ($givenGroups as $group) {

			// get its child groups
			$groupModel = PP::model('group');
			$childGroups = $groupModel->loadRecords(array('parent' => $group->getId()));

			// if has any child group then do nothing
			if (count($childGroups) > 0) {
				continue;
			}

			//otherwise check for its child plans
			$childPlans = $group->getPlans();

			if (empty($childPlans)) {
				continue;
			}

			// Why need to combine?
			$childPlans = array_combine($childPlans, $childPlans);

			$plans = array();
			foreach ($childPlans as $value) {
				$plans[] = PP::plan($value);
			}

			// if all child plans are not accessible then hide that group also
			$filteredPlans = $this->filterPlans($plans, $records); 

			if (count($filteredPlans) == 0) {
				foreach ($givenGroups as $key => $value) {
			 		if ($group->getId() == $value->getId()) {
			 			unset($givenGroups[$key]);
						continue;
					}
				}
			}
		}

		return $givenGroups;
	}
}