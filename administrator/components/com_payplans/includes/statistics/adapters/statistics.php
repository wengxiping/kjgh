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

class PayplansStatistics extends PayPlans
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to set the statistics details from the adapters
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setDetails($data = array(), $dates_to_process = array())
	{
		foreach ($data as $value) {
			$isExists = $this->selectStatistics($value);

			if ($isExists == 0) {
				$this->insertStatistics($value);
			} else {
				$this->updateStatistics($value);
			}
		}

		return true;
	}

	/**
	 * Method to select the statistics existence
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function selectStatistics($values)
	{
		$now = PP::date('now');
		$statisticsType = $values['statistics_type'];
		$purpose_id_1 = $values['purpose_id_1'];
		$purpose_id_2 = isset($values['purpose_id_2']) ? $values['purpose_id_2'] : 0;
		$statisticsDate = $values['statistics_date']->toMySQL(false, '%Y-%m-%d');

		$table = PP::table('Statistics');
		$table->load(array('statistics_type' => $statisticsType, 'purpose_id_1' => $purpose_id_1, 'purpose_id_2' => $purpose_id_2, 'statistics_date' => $statisticsDate));

		return $table->statistics_id > 0 ? $table->statistics_id : 0;
	}

	/**
	 * Method to inset new statistics data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function insertStatistics($value)
	{
		$model = PP::model('Statistics');
		$model->insertStatisticsData($value);

		return true;
	}

	/**
	 * Method to update existing statistics data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function updateStatistics($value)
	{
		// Reformat the data
		$value['purpose_id_2'] = (isset($value['purpose_id_2']) ? $value['purpose_id_2'] : 0);

		for ($count = 1; $count <= 4; $count++) {
			$value['count_' . $count] = (isset($value['count_' . $count]) ? $value['count_' . $count] : 0);
		}

		$value['details_1']	= isset($value['details_1']) ? htmlentities($value['details_1'], ENT_QUOTES) : ' ';
		$value['details_2']	= isset($value['details_2']) ? htmlentities($value['details_2'], ENT_QUOTES) : ' ';
		$value['message'] =	isset($value['message']) ? htmlentities($value['message'], ENT_QUOTES) : ' ';

		$model = PP::model('Statistics');
		$model->updateStatiscticsData($value);

		return true;
	}

	/**
	 * Return set of dates required for the calculation to take place
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getDates($adapter = false)
	{
		if (!empty($this->dates_to_process)) {
			return $this->dates_to_process;
		}

		// Step 1 :- select latest date available in the statistics record
						// Step 1.1 :-  if statistics table do not contain any data 
						// Step 1.2 :- then go to subscription table and get the oldest date
		// Step 2 :- if latest date of statistics is equal to today's date then calculate today's data
		// Step 3 :- if latest date of statistics is less than today's date then firstly calculate previous data

		$latestDate = $this->getLatestDate($this->_statistics_type);

		// if still $latestDate == empty then return blank array()
		if (empty($latestDate)) {
			return array();
		}

		$latestDate = PP::date($latestDate);
		$today = PP::date('now');

		$datesToProcess = array();

		// Step 2
		if ($latestDate->toMySQL(false, '%Y-%m-%d') == $today->toMySQL(false, '%Y-%m-%d')) {
			$datesToProcess[] = $today;
		}

		$limit = PP::statistics()->getRebuildLimit();

		// Step 3 
		if ($latestDate->toMySQL(false, '%Y-%m-%d') < $today->toMySQL(false, '%Y-%m-%d')) {
			while (($latestDate->toUnix() < $today->toUnix()) && ($limit >= 0)) {
				$copy_date = unserialize(serialize($latestDate));
				$datesToProcess[] = $copy_date;
				$latestDate->addExpiration('000001000000');

				$limit--;
			}
		}

		$this->dates_to_process = $datesToProcess;
		return $this->dates_to_process;
	}

	/**
	 * Get the last date whose record is present in the statistics table.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getLatestDate($statistics_type)
	{
		$model = PP::model('statistics');
		
		$this->latestDate = $model->getLatestStatisticsDate($statistics_type);

		// Statistics data for specified type is not exists yet.
		// Let's get the data from beginning of the first ever subscription date.
		if (empty($this->latestDate)) {
			$this->latestDate = $this->getOldestDate();
		}

		// Ensure that the date is really valid
		if ($this->latestDate == '0000-00-00') {
			$date = PP::date('now');
			$this->latestDate = $date->format('Y-m-d');
		}

		return $this->latestDate;
	}

	/**
	 * Returns date of oldest subscription
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getOldestDate()
	{
		$model = PP::model('Statistics');
		$date = $model->getOldestDate();

		return $date;
	}
	
	/**
	* returns timestamp for starting and ending time of the day.
	*/
	public function getFirstAndLastDates(PPDate $date)
	{
		$year = $date->toMySQL(false, '%Y');
		$month = $date->toMySQL(false, '%m');
		$day = $date->toMySQL(false, '%d');
		$firstDate = PP::date(mktime(0,0,0,$month,$day,$year));
		$lastDate = PP::date(mktime(23,59,59,$month,$day,$year));

		return array(unserialize(serialize($firstDate)), unserialize(serialize($lastDate)));
	}
	
	
	public function getFirstAndEndDates($dateToProcess)
	{
		$first = reset($dateToProcess);
		$last  = end($dateToProcess);

		$yearFirst = $first->toMySQL(false, '%Y');
		$monthFirst = $first->toMySQL(false, '%m');
		$dayFirst = $first->toMySQL(false, '%d');

		$yearLast = $last->toMySQL(false, '%Y');
		$monthLast = $last->toMySQL(false, '%m');
		$dayLast = $last->toMySQL(false, '%d');

		$firstDate = PP::date(mktime(0,0,0,$monthFirst,$dayFirst,$yearFirst));
		$lastDate = PP::date(mktime(23,59,59,$monthLast,$dayLast,$yearLast));

		return array(unserialize(serialize($firstDate)), unserialize(serialize($lastDate)));
	}
}
