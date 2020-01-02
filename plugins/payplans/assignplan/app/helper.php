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

class PPHelperAssignplan extends PPHelperStandardApp
{
	public function setPlan($userId, $assignPlanIds, $subscribedPlanId)
	{
		if (!$userId) {
			return true;
		}

		// check if there is any plan in automatic assign plan 
		if (empty($assignPlanIds) && !(is_array($assignPlanIds))) {
			return true;
		}

		// check for the user existing plans
		$user = PP::user($userId);
		$userPlans = $user->getUserPlans($userId);

		$assignFinalPlans = array();
		$userPlanIds = array();

		// simplify the user existing plans to ids
		if ($userPlans) {
			foreach ($userPlans as $userPlan) {
				$userPlanIds[] = $userPlan->id;
			}
		}

		// Process assign plan based on the subscription status
		foreach ($assignPlanIds as $assignPlanId) {

			// if the user has exiting plans
			if ($userPlanIds) {

				// filter it here, only assign those additional plan for the user if the user doesn't have these plan
				if (!in_array($assignPlanId, $userPlanIds)) {
					$assignFinalPlans[] = $assignPlanId;
				}

			} else {
				// will set the original assigned plan ids
				$assignFinalPlans[] = $assignPlanId;
			}
		}

		// Skip here if after filtering process, it still don't have any additional plan 
		if (!$assignFinalPlans) {
			return true;
		}

		foreach ($assignFinalPlans as $planId) {
			
			// if plan to be assigned to same plan 
			// on which this event triggered then do not assign 
			// plan as it will create infinite loop
			if (!empty($planId) && ($planId != $subscribedPlanId)) {

				// validate for the subscription plan
				$isValidPlan = PPHelperPlan::isValidPlan($planId);

				// if that is not valid plan then skip it
				if (!$isValidPlan) {
					return true;
				}

				$planLib = PP::plan($planId);
				$order = $planLib->subscribe($userId);
				$state = $planLib->save();

				$invoice = $order->createInvoice();
	
				// apply 100% discount
				$modifier = PP::modifier();
				$modifier->message = JText::_('COM_PAYPLANS_ASSIGN_PLAN_TO_USER_MESSAGE');
				$modifier->invoice_id = $invoice->getId();
				$modifier->user_id = $invoice->getBuyer()->getId();
				$modifier->type = 'assign_plan';
				$modifier->amount = -100; // 100 percent Discount, discount must be negative
				$modifier->percentage = true;
				$modifier->frequency = PP_MODIFIER_FREQUENCY_ONE_TIME;
				$modifier->serial = PP_MODIFIER_FIXED_DISCOUNT;
				$modifier->save();
					  
				$invoice->refresh()->save();
				
				// create a transaction with 0 amount 
				$transaction = PP::transaction();
				$transaction->user_id = $invoice->getBuyer()->getId();
				$transaction->invoice_id = $invoice->getId();
				$transaction->message = 'COM_PAYPLANS_TRANSACTION_CREATED_FOR_ASSIGN_PLAN_TO_USER';
				$transaction->save();
			}
		}

		return true;
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
		$records = $model->getPlans(array($plans));

		if (!empty($records)) {
			return $records;
		}

		return false;
	}	
}
