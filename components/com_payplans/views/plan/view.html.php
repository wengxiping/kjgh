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

PP::import('site:/views/views');
PP::import('admin:/includes/limitsubscription/limitsubscription');

class PayplansViewPlan extends PayPlansSiteView
{
	// Display toolbar on this view
	protected $toolbar = true;
	
	public function display($tpl = null)
	{
		$returnUrl = '';

		$model = PP::model('Plan');

		$id = $this->input->get('plan_id', 0, 'int');
		$groupId = $this->input->get('group_id', 0, 'int');
		$from = $this->input->get('from', '', 'string');

		// If it is coming from the checkout and dashboard page, we should redirect them back to the plan listing
		// even though the display plans/groups menu item has set specific plan/group
		if (($id || $groupId) && ($from == 'checkout' || $from == 'dashboard')) {
			$id = 0;
			$groupId = 0;
		}

		// If a plan id is already provided, we need to redirect the user to the checkout page
		if ($id) {
			$plan = PP::plan($id);
			$redirect = $plan->getSelectPermalink(false);

			PP::redirect($redirect);
			return;
		}

		$options = array(
			'published' => 1,
			'visible' => 1
		);

		// Default options
		$groups = array();
		$plans = array();
		$returnUrl = false;

		// To fix legacy issues with the columns per row settings
		$columns = $this->getTotalColumns();

		// If groups is enabled then use the groups layout
		$useGroups = $this->config->get('useGroupsForPlan', false);

		if (!$useGroups) {
			$plans = $model->loadRecords($options, array('limit'), '', 'ordering');
			$plans = $this->formatPlans($plans);

			// Retrieve plan and group badge styling
			$renderBadgeStyleCss = $this->renderBadgeStyleCss($plans, $groups);

			$this->set('returnUrl', $returnUrl);
			$this->set('columns', $columns);
			$this->set('groups', $groups);
			$this->set('plans', $plans);
			$this->set('renderBadgeStyleCss', $renderBadgeStyleCss);
			
			return parent::display('site/plan/default/default');
		}

		$groupModel = PP::model('Group');
		

		// if both are not set then need to show all groups and ungrouped plans
		if (!$id && $groupId <= 0) {
			$groupOptions = array_merge($options, array('parent' => 0));
			$groups = $groupModel->loadRecords($groupOptions, array('limit'), '', 'ordering');
			$plans = $model->getUngrouppedPlans($options);
		}

		// When there is a group id in the query string, we should only retrieve plans under the group
		if ($groupId) {
			$plans = $model->getGrouppedPlans($options, $groupId);
			
			$groupOptions = array_merge($options, array('parent' => $groupId));
			$groups = $groupModel->loadRecords($groupOptions, array('limit'));

			$returnUrl = PPR::_('index.php?option=com_payplans&view=plan');

			// Check for menu item
			$active = JFactory::getApplication()->getMenu()->getActive();

			// Do not show back button when the menu item is associated with group plan. #683
			if ($active && $active->query['view'] == 'plan' && isset($active->query['group_id']) && $active->query['group_id']) {
				$returnUrl = false;
			}
		}

		$groups = $this->formatGroups($groups);
		$plans = $this->formatPlans($plans);

		// Retrieve plan and group badge styling
		$renderBadgeStyleCss = $this->renderBadgeStyleCss($plans, $groups);

		$this->set('returnUrl', $returnUrl);
		$this->set('columns', $columns);
		$this->set('link', $returnUrl);
		$this->set('groups', $groups);
		$this->set('plans', $plans);
		$this->set('renderBadgeStyleCss', $renderBadgeStyleCss);

		return parent::display('site/plan/default/default');
	}

	/**
	 * Generate badges styling for each of the plan and group.
	 *
	 * @since	4.0.11
	 * @access	public
	 */
	public function getBadgeStyleCss($items = '', $planType = 'plan')
	{
		if (!$items) {
			return false;
		}

		$badgeStyleCss = '';

		$isPlan = $planType == 'plan' ? true : false;
		$suffix = $isPlan ? 'plan-id-' : 'group-id-';

		foreach ($items as $item) {

			$itemSuffix = $suffix . $item->getId();

			if ($item->hasBadge()) {

				if ($item->getBadgeTitleColor()) {
					$badgeStyleCss .= "#pp .pp-plan-pop-label__txt." . $itemSuffix . "{color: " . $item->getBadgeTitleColor() . " !important;}";
				}

				if ($item->getBadgeBackgroundColor()) {
					$badgeStyleCss .= "#pp .pp-plan-pop-label." . $itemSuffix . "{background: " . $item->getBadgeBackgroundColor() . " !important;}";
					$badgeStyleCss .= "#pp .pp-plan-pop-label." . $itemSuffix . "::before{border-top-color: " . $item->getBadgeBackgroundColor() . " !important;}";
				}
			}
		}

		return $badgeStyleCss;
	}	

	/**
	 * Generate badges styling for each of the plan and group.
	 *
	 * @since	4.0.11
	 * @access	public
	 */
	public function renderBadgeStyleCss($plans = array(), $groups = array())
	{
		$renderBadgePlanStyleCss = '';
		$renderBadgeGroupStyleCss = '';
		$badgeStyleCss = '';

		if ($plans) {
			$renderBadgePlanStyleCss = $this->getBadgeStyleCss($plans, 'plan');	
		}
	
		if ($groups) {
			$renderBadgeGroupStyleCss = $this->getBadgeStyleCss($groups, 'group');
		}

		$badgeStyleCss = $renderBadgePlanStyleCss . $renderBadgeGroupStyleCss;

		if ($badgeStyleCss) {
			$badgeStyleCss = '<style type="text/css">' . $badgeStyleCss . '</style>';
		}

		return $badgeStyleCss;		
	}	

	/**
	 * Formats groups to it's proper object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatGroups($groups)
	{
		if (!$groups) {
			return;
		}

		foreach ($groups as &$group) {
			$group = PP::group($group);
		}

		$groups = PP::parentChild()->filterGroups($groups);

		$user = PP::user();
		$displaySubscribedPlans = $this->config->get('displayExistingSubscribedPlans');

		// unset plan if user already subscribed and display existing subscribed plan to no
		if (!$displaySubscribedPlans && $user->id) {
			$userPlans = $user->getPlans();

			foreach ($groups as $group) {
				$groupPlans = $group->getPlans();

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

				foreach ($userPlans as $plan) {
					unset($childPlans[$plan->getId()]);
				}
			}
		}

		return $groups;
	}

	/**
	 * Formats plans to it's proper object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatPlans($plans)
	{
		$appModel = PP::model('App');

		// Get modifiers that applied to all plans
		$modifiers = $appModel->getAppInstances(array('type' => 'planmodifier', 'published' => PP_STATE_PUBLISHED));

		// Get all the advanced pricing instances
		$model = PP::model('Advancedpricing');
		$advPricings = $model->getItems();

		if ($plans) {
			foreach ($plans as &$plan) {
				$plan = PP::plan($plan);
				$plan->separator = $plan->isRecurring() !== false ? JText::_('COM_PAYPLANS_PLAN_PRICE_TIME_SEPERATOR') : JText::_('COM_PAYPLANS_PLAN_PRICE_TIME_SEPERATOR_FOR');

				$planModifiers = array();

				foreach ($modifiers as $modifier) {
					$app = PP::app($modifier->app_id);
					if ($app->getCoreParams()->get('applyAll') == '1' || $appModel->isPlanRelated($app->getId(), $plan->getId())) {
						
						$tmpOption = unserialize($app->getAppParam('time_price'));
						$options = array();

						if ($tmpOption) {
							foreach ($tmpOption['title'] as $key => $value) {
								$obj = new stdClass;
								$obj->title = $value;
								$obj->price = $tmpOption['price'][$key];
								$obj->time = $tmpOption['time'][$key];

								$options[] = $obj;
							}
						}

						$app->options = $options;

						$planModifiers[] = $app;
					}
				}

				$plan->modifiers = $planModifiers;

				// Process the advanced pricing
				$plan->advancedpricing = false;

				foreach ($advPricings as $adv) {
					// If advancepricing rule disabled then do nothing
					if (!$adv->published) {
						continue;
					}

					// Check if this plan is assigned in advanced pricing
					if (in_array($plan->getId(), $adv->assignedPlans)) {
						$plan->advancedpricing = $adv;
					}
				}
			}
		}

		$plans = PP::parentChild()->filterPlans($plans);
		$plans = PPlimitsubscription::filterPlans($plans);

		$user = PP::user();
		$displaySubscribedPlans = $this->config->get('displayExistingSubscribedPlans');

		// unset plan if user already subscribed and display existing subscribed plan to no
		if (!$displaySubscribedPlans && $user->id) {
			$userPlans = $user->getPlans();
			foreach ($userPlans as $plan) {
				unset($plans[$plan->getId()]);
			}
		}

		return $plans;
	}

	/**
	 * Renders the login form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function login()
	{
		$model = PP::model('Plan');

		$planId = $model->getState('id');
		$this->set('plan', PayplansPlan::getInstance($planId));

		return parent::display('site/plan/default/login');
	}

	/**
	 * Triggers?
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trigger()
	{
		$this->setTpl('partial_position');
		return true;
	}

	/**
	 * Get total number of columns per row
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalColumns()
	{
		$columns = $this->config->get('row_plan_counter');
		$parts = explode(',', $columns);

		if (count($parts) == 1) {
			return (int) $columns;
		}

		// We only take the first one
		$columns = (int) $parts[0];

		return $columns;
	}
}
