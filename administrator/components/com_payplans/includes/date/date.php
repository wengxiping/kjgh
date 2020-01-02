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

require_once(__DIR__ . '/abstract.php');

class PPDate extends PPDateAbstract
{
	const INVOICE_FORMAT = '%A %d %b, %Y';
	const SUBSCRIPTION_PAYMENT_FORMAT = '%d %b %Y';
	const SUBSCRIPTION_PAYMENT_FORMAT_HOUR = '%d %b %Y %R%p';
	const YYYY_MM_DD_FORMAT = '%Y-%b-%d';
	const YYYY_MM_DD_FORMAT_WITHOUT_COMMA = '%Y%n%d';
	const YYYY_MM_DD_HH_MM = '%Y-%m-%d %H:%M';

	/**
	 * @param mixed $date optional the date this XiDate will represent.
	 * @param int $tzOffset optional the timezone $date is from
	 * 
	 * @return XiDate
	 */
	public static function factory($date = 'now', $tzOffset = null)
	{
		return new self($date, $tzOffset);	
	}
	
	/**
	 * Expiration time should be in the format of YYMMDDHHMMSS
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addExpiration($expirationTime)
	{
		$timerElements = array('year', 'month', 'day', 'hour', 'minute', 'second');
		$date = date_parse($this->toString());
		
		if ($this->_date == false) {
			return $this;
		}

		$count = count($timerElements);

		$this->_date = false;

		if ($expirationTime != 0) {
			for ($i=0; $i<$count ; $i++) {
				$date[$timerElements[$i]] += intval(JString::substr($expirationTime, $i*2, 2), 10);
			}

			$this->_date = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);

			parent::__construct($this->_date);
		}

		return $this;
	}
	
	public function subtractExpiration($expirationTime)
	{
		$timerElements = array('year', 'month', 'day', 'hour', 'minute', 'second');
		$date = date_parse($this->toString());
		
		$count = count($timerElements);
		for($i=0; $i<$count ; $i++){
			//XITODO : convert to integer before adding
			$date[$timerElements[$i]] -= JString::substr($expirationTime, $i*2, 2);
		}
		
		$result= mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);

		$this->_date = $result; 
		parent::__construct($this->_date);

		return $this;
	}

	/**
	 * Returns the lapsed time since NOW
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toLapsed()
	{
		$now = PP::date();
		$time = $now->toUnix(true) - $this->toUnix(true);

		$tokens = array (
							31536000 => 'COM_PP_LAPSED_YEARS_COUNT',
							2592000 => 'COM_PP_LAPSED_MONTHS_COUNT',
							604800 => 'COM_PP_LAPSED_WEEKS_COUNT',
							86400 => 'COM_PP_LAPSED_DAYS_COUNT',
							3600 => 'COM_PP_LAPSED_HOURS_COUNT',
							60 => 'COM_PP_LAPSED_MINUTES_COUNT',
							1 => 'COM_PP_LAPSED_SECONDS_COUNT'
						);

		if ($time == 0) {
			return JText::_('COM_PP_LAPSED_NOW');
		}

		foreach ($tokens as $unit => $key) {
			if ($time < $unit) {
				continue;
			}

			$units = floor($time / $unit);

			$text = PP::string()->computeNoun($key , $units);
			$text = JText::sprintf($text , $units);

			return $text;
		}

	}
}
