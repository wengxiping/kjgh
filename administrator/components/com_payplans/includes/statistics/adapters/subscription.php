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

PP::import('admin:/includes/statistics/adapters/statistics');

class PayplansStatisticsSubscription extends PayplansStatistics
{
	protected $_purpose_id = '2001';
	public $_statistics_type = 'subscription';
	
	/**
	 * Store subscriptions details
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setDetails($data = array(), $dates_to_process = array())
	{
		$model = PP::model('Subscription');

		list($firstDate, $lastDate) = $this->getFirstAndEndDates($dates_to_process);

		$activeSubscription	= $model->getActiveSubscription($firstDate, $lastDate);
		$expiredSubscription = $model->getExpireSubscription($firstDate, $lastDate);

		// set cart statistics details
		foreach ($dates_to_process as $id => $date) {
			$key = $date->toUnix();

			$process_date = $date->toMySQL(false, "%Y-%m-%d");

			$data[$key]['statistics_type'] = $this->_statistics_type;
			$data[$key]['purpose_id_1'] = $this->_purpose_id;
			$data[$key]['count_1'] = isset($activeSubscription[$process_date]->count) ? $activeSubscription[$process_date]->count : 0;
			$data[$key]['count_2'] = isset($expiredSubscription[$process_date]->count) ? $expiredSubscription[$process_date]->count : 0;
		
			$data[$key]['statistics_date'] = $date;
		}

		return parent::setDetails($data);
	}
}
