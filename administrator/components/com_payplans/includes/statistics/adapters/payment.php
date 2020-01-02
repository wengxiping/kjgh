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

class PayplansStatisticsPayment extends PayplansStatistics
{
	protected $_purpose_id = '3001';
	public $_statistics_type = 'payment';
	
	public function setDetails($data = array(), $dates_to_process = array())
	{
		foreach ($dates_to_process as $id => $date) {
			list($firstDate, $lastDate) = $this->getFirstAndLastDates($date);

			$records = $this->getGatewayDetails($firstDate, $lastDate);
			foreach ($records as $app_id => $record) {

				// set cart statistics details
				$key = $date->toUnix();
				$key = $key + $app_id;
				$data[$key]['statistics_type'] = $this->_statistics_type;
				$data[$key]['purpose_id_1'] = $this->_purpose_id;
				$data[$key]['purpose_id_2'] = $record['app_id'];
				$data[$key]['count_1'] = $record['used'];
				$data[$key]['details_1'] = $record['title'];
				$data[$key]['statistics_date'] = $date;
			}
		}

		return parent::setDetails($data);
	}
	
	protected function getGatewayDetails($firstDate, $lastDate)
	{
		$start = $firstDate->format('Y-m-d');
		$end = $lastDate->format('Y-m-d');
		$data = array();

		$query = 'SELECT payment.`app_id` as app_id, count(payment.`payment_id`) as used';
		$query .= ' FROM `#__payplans_payment` as payment';
		$query .= ' INNER JOIN `#__payplans_transaction` as transaction';
		$query .= ' ON (';
		$query .= ' payment.`payment_id` = transaction.`payment_id`';
		$query .= ' AND transaction.`amount` <> 0';
		$query .= ' AND date(transaction.`created_date`) >= ' . $db->Quote($start);
		$query .= ' AND date(transaction.`created_date`) <= ' . $db->Quote($end);
		$query .= ' )';
		$query .= ' GROUP BY app_id ORDER BY used DESC';

		$db->setQuery($query);
		$payments = $db->loadObjectList('app_id');
		
		if (!is_array($payments)) {
			return $data;
		}

		$appsModel = PP::model('app');
		$apps = $appsModel->loadRecords();

		foreach ($payments as $app_id => $payment) {
			$data[$app_id]['app_id'] = $app_id;
			$data[$app_id]['title'] = $apps[$app_id]->title;
			$data[$app_id]['used'] = $payment->used;
		}

		return $data;
	}
}