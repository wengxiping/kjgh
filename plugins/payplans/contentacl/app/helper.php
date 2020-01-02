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

class PPHelperContentacl extends PPHelperStandardApp
{
	/**
	 * Construct plan url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlanUrl()
	{
		return PPR::_('index.php?option=com_payplans&view=plan');
	}

	/**
	 * Process category ACL
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processCategory($context, &$row, &$params, $page = 0)
	{
		$allowedCat = $this->params->get('joomla_category', 0);
		$allowedCat = is_array($allowedCat) ? $allowedCat : array($allowedCat);
		$catId = (isset($row->catid)) ? $row->catid : null;

		// get all parent category of current cat
		$allCat = $this->getParentCategories($catId);
		$tempArray = array_intersect($allCat, $allowedCat);

		if (empty($tempArray)) {
			return true;
		}

		if ($this->app->getParam('applyAll', false)) {
			$row->text = $row->introtext . '<a id="pp_contentacl_joomla_category" href="' . $this->getPlanUrl() . '">' . JText::_('COM_PAYPLANS_CONTENTACL_SUBSCRIBE_PLAN') . '</a>';
		} else {
			// we need to check if there is any other content app that is responsible for article since the article is the controller.
			$hasArticleApps = false;
			$contentApps = PPHelperApp::getAvailableApps('contentacl');

			foreach ($contentApps as $app) {
				$type = $app->getAppParam('block_j17', 'none');

				if ($type == 'joomla_article') {
					$isArticle = $app->getAppParam('joomla_article', 0);
					$allowedArticle = is_array($isArticle) ? $isArticle : array($isArticle);
					$articleId 	= (isset($row->id)) ? $row->id : null;

					if (in_array($articleId, $allowedArticle)) {
						$hasArticleApps = true;
					}
				}
			}

			// if there is no article contentacl app, then we just need to append the plan links here.
			if (!$hasArticleApps) {
				$links = $this->getPlanlinks(true);

				$planLinks = '';

				if ($links) {
					foreach ($links as $link) {
						$tmp = '<strong>' . JText::_('COM_PAYPLANS_CONTENTACL_SUBSCRIBE_PLAN') . '</strong> ( ' . $link . ' )';
						$planLinks .= ($planLinks) ? '<br />' . $tmp : $tmp;
					}
				}

				$row->text  = $row->introtext.$planLinks;
			}
		}
	}

	/**
	 * Process Article ACL
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processArticle($context, &$row, &$params, $page = 0)
	{
		$allowedArticle = $this->params->get('joomla_article', 0);
		$allowedArticle = is_array($allowedArticle) ? $allowedArticle : array($allowedArticle);
		$articleId = (isset($row->id)) ? $row->id : null;

		if (!in_array($articleId, $allowedArticle)) {
			return true;
		}

		$planLinks = array();

		$categoryId = $row->catid;
		$contentApps = PPHelperApp::getAvailableApps('contentacl');
		$plans1 = array();
		$links = "";

		foreach ($contentApps as $app) {
			$type = $app->getAppParam('block_j17', 'none');

			if ($type == 'joomla_category') {
				$allowedCat = $app->getAppParam('joomla_category', 0);

				if ($allowedCat) {
					$allowedCat = is_array($allowedCat) ? $allowedCat : array($allowedCat);
					$allCat = $this->getParentCategories($categoryId);
					$tempArray = array_intersect($allCat, $allowedCat);

					if (!empty($tempArray)) {
						if (!$app->getParam('applyAll', false)) {
							$plans1 = $app->getPlans();
							$plans1 = $this->isValidPlan($plans1);

							if (empty($plans1)) {
								continue;
							}

							foreach ($plans1 as $plan) {
								$planName = $plan->title;
								$planLinks[] = '<a href="' . PPR::_('index.php?option=com_payplans&task=plan.subscribe&plan_id=' . $plan->plan_id) . '&tmpl=component">' . $planName . '</a>';
							}
						}
					}
				}
			}
		}

		if ($this->app->getParam('applyAll', false)) {
			$row->text = $row->introtext . '<a id="pp_contentacl_joomla_article" href="' . $this->getPlanUrl() . '">' . JText::_('COM_PAYPLANS_CONTENTACL_SUBSCRIBE_PLAN') . '</a>';
		} else {

			//article links
			$artLinks  = $this->getPlanlinks(true);
			$allPlanLinks = array_merge($artLinks, $planLinks);
			$allPlanLinks = array_unique($allPlanLinks);

			$links = '';

			if ($allPlanLinks) {
				foreach ($allPlanLinks as $link) {
					$tmp = '<strong>' . JText::_('COM_PAYPLANS_CONTENTACL_SUBSCRIBE_PLAN') . '</strong> ( ' . $link . ' )';
					$links .= ($links) ? '<br />' . $tmp : $tmp;
				}
			}

			$row->text  = $row->introtext.$links;
		}
	}

	/**
	 * Determine if user is allowed here
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUserAllowed()
	{
		$user = PP::user();

		if (!$user->user_id) {
			return false;
		}

		$userSubs = $user->getPlans();

		// return false when user is non-subscriber
		if (empty($userSubs)) {
			return false;
		}

		// return true when app is core app,
		// no need to check whether plan is attached with this app or not
		if ($this->app->getParam('applyAll', false) != false) {
			return true;
		}

		$plans = $this->app->getPlans();
		
		// if user have an active subscription of the plan attached with the app then return true
		foreach ($userSubs as $sub) {
			$planId = $sub->getId();

			if (in_array($planId, $plans)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve all of the parent categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParentCategories($catId)
	{
		$allCat = array();

		while ($catId) {
			$allCat[] = $catId;

			$db = PP::db();
			$query = 'SELECT `parent_id` FROM `#__categories`';
			$query .= ' WHERE `published` = 1 AND `id` = ' . $db->Quote($catId);

			$db->setQuery($query);
			$catId = $db->loadResult();
		}

		return $allCat;
	}

	/**
	 * Retrieve plans links that need to be append to the content
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlanlinks($returnAsArray = false)
	{
		$plans = $this->app->getPlans();

		$links = '<strong>' . JText::_('COM_PAYPLANS_CONTENTACL_SUBSCRIBE_PLAN') . '</strong> ( ';
		$plans = $this->isValidPlan($plans);

		// If plans are not vaild then show subscribe page link
		if ($plans == false) {
			$planLinks = '<a href="' . $this->getPlanUrl() . '">' . JText::_('COM_PAYPLANS_CONTENTACL_SUBSCRIBE_PLAN') . '</a>';

			if ($returnAsArray) {
				$planLinks = array($planLinks);
			}

			return $planLinks;
		}

		$planLinks = array();

		foreach ($plans as $plan) {
			$planName = $plan->title;
			$planLinks[] = '<a href="' . PPR::_('index.php?option=com_payplans&task=plan.subscribe&plan_id=' . $plan->plan_id) . '&tmpl=component">' . $planName . '</a>';
		}

		if ($returnAsArray) {
			return $planLinks;
		}

		$planLinks = implode(" , ", $planLinks);
		$links .= $planLinks;
		$links .= ' )';

		return $links;
	}

	/**
	 * Determine if the given plan ids is a valid plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isValidPlan($plans)
	{
		$model = PP::model('Plan');
		$records = $model->getPlans($plans);

		if (!empty($records)) {
			return $records;
		}

		return false;
	}
}