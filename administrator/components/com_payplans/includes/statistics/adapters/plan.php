<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Payplans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/includes/statistics/adapters/statistics');

class PayplansStatisticsPlan extends PayplansStatistics
{
	public $_statistics_type = 'plan';

	/**
	 * Retrieve plan's subscription count and its status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionStats()
	{
		// return all plan's subscriptions and it status
		$model = PP::model('plan');
		$results = $model->getAllSubscriptionStats();

		$stats = array();

		if ($results) {
			foreach ($results as $stat) {
				$stats[$stat->plan_id][$stat->status] = isset($stat->count) ? $stat->count : 0;
			}
		}

		return $stats;
	}

	/**
	 * Calculate statistics details for plan
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setDetails($data = array(), $datesToProcess = array())
	{
		list($first, $last) = $this->getFirstAndEndDates($datesToProcess);
		$planModel = PP::model('Plan');
		$subscriptionModel = PP::model('Subscription');
		$transactionModel = PP::model('Transaction');

		$plans = $planModel->loadRecords(array());
		$salesOfPlans = $subscriptionModel->getSalesOfPlans($first, $last);
		$revenueOfPlans = $transactionModel->getRevenuesOfPlans($first, $last);
		$upgradesOfPlans = $subscriptionModel->getUpgradesOfPlans($first, $last);

		foreach ($datesToProcess as $processDate) {
			list($firstDate, $lastDate) = $this->getFirstAndLastDates($processDate);
			$key = $processDate->toUnix();
			$processDateUnformat = $processDate->toMySQL(false, "%Y-%m-%d");

			$renewSubscriptions = $planModel->getTotalRenewalPerPlan($firstDate, $lastDate); //Renewal per plan

			foreach ($plans as $pid => $plan) {
				$key .= $pid;
				$data[$key]['purpose_id_1'] = $pid;
				$data[$key]['statistics_type'] = $this->_statistics_type;
				$data[$key]['count_1'] = isset($salesOfPlans[$pid][$processDateUnformat]) ? $salesOfPlans[$pid][$processDateUnformat] : 0; // Sales Per Plan
				$data[$key]['count_2'] = isset($revenueOfPlans[$pid][$processDateUnformat]) ? $revenueOfPlans[$pid][$processDateUnformat] : 0; // Revenue Per Plan
				//added this code to save the renewal per plan
				$data[$key]['count_3'] = isset($renewSubscriptions[$pid]) ? $renewSubscriptions[$pid]:0;
				$data[$key]['count_4'] = isset($upgradesOfPlans[$pid][$processDateUnformat]) ? $upgradesOfPlans[$pid][$processDateUnformat] : 0; // Upgrades Per Plan
				$data[$key]['details_1'] = $plan->title;
				$data[$key]['statistics_date'] = $processDate;
			}
		}

		return parent::setDetails($data);
	}
}
