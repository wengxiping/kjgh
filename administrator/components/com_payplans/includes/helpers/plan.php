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

jimport('joomla.filesystem.file');

require_once(JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php');

class PPHelperPlan
{
	public static function isValidPlan($planId)
	{
		// is exist and published
		$record = PP::model('plan')->loadRecords(array('plan_id'=>$planId, 'published'=>1), array('limit'));

		// is accessible to current user
		if (isset($record[$planId]) && !empty($record[$planId])) {
			return true;
		}

		return false;
	}

	public static function getName($planId)
	{
		if(PayplansPlan::getInstance($planId) == false){
			return JText::_("COM_PAYPLANS_SUBSCRIPTION_PLAN_DOES_NOT_EXIST");
		}
		return PayplansPlan::getInstance($planId)->getTitle();
	}

	/**
	 * @deprecated since 2.1.6
	 */
	static function convertExpirationTime($period, $unit)
	{
		$days = $months= $years = 0;
		switch($unit)
		{
			case 'Y':
				$years	= $period ;
				break;

			case 'M' :
				if($period >= 12 ){
					$years	= $period / 12 ;
					$period = $period % 12 ;
				}

				$months  = $period % 12 ;
				break;

			case 'W' :
				//convert into number of days
				// let days system handle it.
				$period = $period * 7 ;

			case 'D' :
				if($period >= 365 ){
					$years	= $period / 365 ;
					$period = $period % 365 ;
				}

				if($period >= 30 ){
					$months = $period / 30 ;
					$period = $period % 30 ;
				}

				$days 	= $period % 30 ;

				break;
		}

		$time =  ($years<10  ? '0':'').number_format($years, 0)
				.($months<10 ? '0':'').number_format($months, 0)
				.($days<10   ? '0':'').number_format($days, 0)
				."000000";
		return $time;
	}

	static function convertIntoTimeArray($rawTime)
	{
		$time['year']    = "00";
		$time['month']   = "00";
		$time['day']     = "00";
		$time['hour']    = "00";
		$time['minute']  = "00";
		$time['second']  = "00";

		if($rawTime != 0)
		{
			$rawTime = str_split($rawTime, 2);
			$time = array();
			$time['year']    =  array_shift($rawTime);
			$time['month']   = array_shift($rawTime);
			$time['day']     =  array_shift($rawTime);
			$time['hour']    =  array_shift($rawTime);
			$time['minute']  =  array_shift($rawTime);
			$time['second']  =  array_shift($rawTime);
		}

		return $time;
	}

	/**
	 * Convert time array into days
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function convertTimeArrayToDays($timeArray)
	{
		if (is_string($timeArray)) {
			$timeArray = PPHelperPlan::convertIntoTimeArray($timeArray);
		}

		$days = (!empty($timeArray['day'])) ? intval($timeArray['day']) : 0;
		$days += (!empty($timeArray['month'])) ? intval($timeArray['month']) * 30 : 0;
		$days += (!empty($timeArray['year'])) ? intval($timeArray['year']) * 365 : 0;

		return $days;
	}

	static function buildPlanCloumnClasses($rowPlans,$planCount)
	{
		//setup defaults
		if(empty($rowPlans) || in_array(0,$rowPlans)){
			if($planCount%5 == 0){
				$rowPlans = array(3,2);
			}elseif($planCount%4 == 0){
				$rowPlans = array(4);
			}elseif($planCount%3 == 0){
				$rowPlans = array(3);
			}else{
				$rowPlans = array(2);
			}
		}


		$planClasses = array();


		//set default 3
		$columns = 3;

		//calculate span classes for plans
		for($totalCount = $planCount,$rows=array(); $totalCount > 0; $totalCount=($totalCount-$columns)){
			if(!empty($rowPlans)){
				$columns = array_shift($rowPlans);
			}

			$span = (int)(12/$columns);
			$columns = ($columns > $totalCount)?$totalCount:$columns;

			for($i=1;$i <= $columns; $i++){
				$planClasses[] =' span'.$span;
			}

			$rows[] = $columns;
		}

		return array($planClasses,$rows);
	}

	/**
	 * @deprecated it, use get function
	 */
	static function getPlans($filter = array('published' => 1, 'visible' => 1), $instanceRequire = true)
	{
		return self::get($filter, $instanceRequire);
	}

	public static function get($filter, $instanceRequire = true)
	{
		$plans = PP::model('plan')->loadRecords($filter);

		if ($instanceRequire !== PP_INSTANCE_REQUIRE) {
			return array_keys($plans);
		}

		$instances = array();

		foreach ($plans as $plan) {
			$instances[$plan->plan_id] = PP::plan($plan->plan_id);
		}

		return $instances;
	}

	/**
	 * For getting Subscription Stats i.e. plan_id, number of subscription and status.
	 *
	 * @return stdclass object and every object contains above specified details.
	 */

	public static function getSubscriptionStats()
	{
		$query = new XiQuery();
		$query->select('count(*) AS count, `plan_id`, `status`')
			  ->from('`#__payplans_subscription`')
			  ->group("`plan_id` , `status`");
		return $query->dbLoadQuery()->loadObjectList();
	}
}