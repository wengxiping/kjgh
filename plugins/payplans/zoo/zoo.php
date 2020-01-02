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

class plgPayplansZoo extends PPPlugins
{
	const DO_NOTHING = -1;
	const ALLOWED = 1;
	const BLOCKED = 0;
	const ALL_PLAN = 'ALL_PLAN';
	const ANY_CAT = -1;

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->helper = $this->getAppHelper();
		$lib = $this->helper->getLib();

		if (!$lib->exists()) {
			return;
		}
	}

	public function onPayplansAccessCheck(PPUser $user)
	{
		if ($this->app->isAdmin() || $user->isAdmin()) {
			return;
		}

		$option = $this->input->get('option', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$task = $this->input->get('task', null, 'default');
		$categoryId = $this->input->get('category_id', 0, 'int');
		$itemId = $this->input->get('itme_id', 0, 'int');

		if ($option != 'com_zoo') {
			return false;
		}

		$flag = 0;
		$edited = false;

		if ($itemId) {
			$edited = true;
		}

		// Check for restriction
		if ($task == 'category' || $task == 'item') {
			if (!isset($categoryId)) {

				$categoryId = 0;
				if (isset($itemId)) {
					$categoryId = $this->helper->getParentFromItemid($itemId);
				}
			}

			$allowView = 0 ;
			$controlOn = array('on_view', 'on_both');
			$appPlans = array();
			$zooCategoryApps = $this->getAvailableApps('zoo');
			$parentCategory = $this->helper->getParentCategories($categoryId);

			foreach ($zooCategoryApps as $app) {
				if (in_array($app->getAppParam('controlOn'), $controlOn)) {
					$categoryOn = $app->getAppParam('add_entry_in');
					$postInCategory = $parentCategory;

					if ($categoryOn == 'on_specific_category') {
						$postInCategory = $app->getAppParam('zoo_category', array());
						$postInCategory = is_array($postInCategory) ? $postInCategory : array($postInCategory);
					}

					if (count(array_intersect($parentCategory,$postInCategory)) != 0) {
						if (!$user->getId()) {
							$msg = JText::_('COM_PAYPLANS_APP_ZOO_SUBMISSION_NOT_LOGIN');
							$this->app->redirect(PPR::_('index.php?option=com_payplans&view=plan&task=subscribe'), $msg);
							return true;
						} else {
							$plan = $app->getPlans();
							$plan = (empty($plan))? array(self::ANY_CAT) : $plan;
							$appPlans = array_merge($appPlans, $plan);
						}
					}
				}
			}

			$model = PP::model('subscription');
			$subscriptionRecords = $model->loadRecords(array('user_id' => $user->getId(), 'status' => PP_SUBSCRIPTION_ACTIVE));

			if (!empty($subscriptionRecords) && !empty($appPlans)) {
				foreach ($subscriptionRecords as $subscriptionRecord) {
					$plan = $subscriptionRecord->plan_id;

					if (in_array($plan, $appPlans)) {
						$allowView = 1;
						break;
					}

					if (in_array(self::ANY_CAT, $appPlans)) {
						$allowView = 1;
						break;
					}
				}
			}

			if ($allowView == 0 && !empty($appPlans)) {
				$msg = JText::_('COM_PAYPLANS_APP_ZOO_CATEGORY_NOT_ALLOW_VIEW');
				$this->app->redirect(PPR::_('index.php?option=com_payplans&view=plan&task=subscribe'), $msg);
			}
		}

		if ($layout == 'submission' && $task == 'save') {

			if (!$user->getId()) {
				$msg = JText::_('COM_PAYPLANS_APP_ZOO_SUBMISSION_NOT_LOGIN');
				$this->app->redirect(PPR::_('index.php?option=com_payplans&view=plan&task=subscribe'), $msg);
				return true;
			}

			$submissionId = $this->input->get('submission_id', null, 'default');
			$typeId = $this->input->get('type_id', null, 'default');
			$elements = $this->input->get('elements', array(), 'array');

			if (array_key_exists('_itemcategory',$elements)) {
				$categories = array_values($elements['_itemcategory']['value']);
			}

			if (!isset($categories)) {

				$db = PP::db();

				$query = 'SELECT `params` FROM `#__zoo_submission` WHERE `id` = ' . $db->Quote($submissionId);

				$db->setQuery($query);
				$submissionParams = $db->loadResult();

				$params = json_decode($submissionParams, true);
				$categories = $params['form.' . $typeId]['category'];
			}

			if (!is_array($categories)) {
				$categories = array($categories);
			}

			$parentCategory = array();

			foreach ($categories as $category) {
				$parent = $this->helper->getParentCategories($category);
				$parentCategory = array_merge($parentCategory, $parent);
			}

			$zooCategoryApps = $this->getAvailableApps('zoo');

			foreach ($parentCategory as $category) {
				$controlOn = array('on_submit','on_both');
				$allowStatus = $this->isZooCategoryAllowed($category, $user, $controlOn);

				if ($allowStatus == self::DO_NOTHING) {
					continue;
				}

				if ($allowStatus == self::BLOCKED) {
					$msg = JText::_('COM_PAYPLANS_APP_ZOO_CATEGORY_SELECT_PLAN');
					$this->app->redirect(PPR::_('index.php?option=com_payplans&view=plan&task=subscribe'), $msg);
				}

				// firstly check for specific category allowed then check for any category
				$category = array($category,$category);
				
				if ($this->helper->redirectDecision($zooCategoryApps, $controlOn, $category, $flag, $user, $edited) == false) {
					$category = array(self::ANY_CAT, 0);

					if ($this->helper->redirectDecision($zooCategoryApps, $controlOn,$category, $flag, $user,$edited) == true) {
						$msg = JText::_('COM_PAYPLANS_APP_ZOO_CATEGORY_YOU_ARE_NOT_ALLOWED_TO_ADD_MORE_SUBMISSIONS');
						$this->app->redirect(PPR::_('index.php?option=com_payplans&view=dashboard'), $msg);
					}
				} else {
					$msg = JText::_('COM_PAYPLANS_APP_ZOO_CATEGORY_YOU_ARE_NOT_ALLOWED_TO_ADD_MORE_SUBMISSIONS');
					$this->app->redirect(PPR::_('index.php?option=com_payplans&view=dashboard'), $msg);
				}
			}
		}
	}

	/**
	 * Process trigger when preparing the content
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onContentPrepare($context,$article, $params, $limitstart)
	{
		return $this->processRestriction($article, $params);
	}

	/**
	 * Process the restriction of the article or category
	 *
	 * @since	4.0.0
	 * @access	protected
	 */
	public function processRestriction($article, $params)
	{
		$option = $this->input->get('option', '', 'default');

		if ($option !== 'com_zoo') {
			return false;
		}

		$user = PP::user();
		
		// don't block if admin
		if ($user->isAdmin()) {
			return true;
		}

		// if params is null then create registry object
		if (!is_object($params)) {
			$params = new JRegistry();
		}

		// get the category and app which is being accessed
		$itemId = $params->get('item_id', 0);

		if (!$itemId) {
			return false;
		}

		$categories = $this->helper->getCategory($itemId);
		$parentCategory = array();

		foreach ($categories as $category) {
			$parent = $this->helper->getParentCategories($category);
			$parentCategory = array_merge($parentCategory, $parent);
		}

		$controlOn = array('on_view','on_both');

		foreach ($parentCategory as $category) {
			$isAllowedOrNot = self::isZooCategoryAllowed($category, $user, $controlOn);

			if ($isAllowedOrNot==self::ALLOWED || $isAllowedOrNot == self::DO_NOTHING) {
				continue;
			} else {
				$url = PPR::_('index.php?option=com_payplans&view=plan&task=subscribe');
				$article->text = '<a href="'. $url .'">' . JText::_('COM_PAYPLANS_APP_ZOO_CATEGORY_SUBSCRIBE_PLAN') . '</a>';
				return true;
			}
		}
	}

	/**
	 * Determine if the category is allowed for all app instances available
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isZooCategoryAllowed($category, $user, $controlOn)
	{
		$zooCategoryApps = $this->getAvailableApps('zoo');

		// check which plan is required for this category/app
		$applicablePlans = array();
		foreach($zooCategoryApps as $app) {
			$restrictionOn = $app->getAppParam('controlOn', 'on_view');

			if (!in_array($restrictionOn, $controlOn)) {
				continue;
			}

			$appCategory = array();
			$postInCategory = $app->getAppParam('add_entry_in', 'any_category');

			if ($postInCategory == 'any_category') {
				$appCategory[] = 0;
			} else {
				$appCategory = $app->getAppParam('zoo_category', $appCategory);
			}

			$appCategory = is_array($appCategory) ? $appCategory : array($appCategory);

			//if app is not applicable on that category 
			if (in_array($category, $appCategory) || in_array('0', $appCategory)) {
				// if apply to all then break
				if ($app->getParam('applyAll')) {
					$applicablePlans = array(self::ALL_PLAN);
					break;
				} else {
					// mearge all the plans
					$applicablePlans = array_merge($applicablePlans, $app->getPlans()) ;
				}
			}
		}

		// applicable plans is empty
		if (count($applicablePlans) <= 0) {
			return self::DO_NOTHING;
		}

		// if user is not logged in or does not have any plan, ask him to subscribe
		if (!$user->getId() || count($user->getPlans()) <= 0) {
			return self::BLOCKED;
		}

		// user has any plan
		$userPlans = $user->getPlans();

		$plans = array();

		foreach ($userPlans as $plan) {
			$plans[] = $plan->getId();
		}

		// if cat/app is available to any plans, return treu because user has any plan
		if (in_array(self::ALL_PLAN, $applicablePlans)) {
			return self::ALLOWED;
		}

		// if no allowed but any one blocked then block
		if (count(array_intersect($applicablePlans, $plans)) >= 1) {
			return self::ALLOWED;
		}

		return self::BLOCKED;
	}
}
