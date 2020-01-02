<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class PPString
{
	private $adapter = null;

	public function __construct()
	{

	}

	public static function factory()
	{
		return new self();
	}

	public function __call($method , $arguments)
	{
		if (method_exists($this->adapter , $method)) {
			return call_user_func_array(array($this->adapter , $method) , $arguments);
		}

		return false;
	}

	/**
	 * Legacy implementation of PayPlansStatus::getName()
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStatusName($code)
	{
		$code = JString::trim($code);

		if ($code == '') {
			return '';
		}
		
		if ($code == '1601') {
			return 'SUBSCRIPTION_ACTIVE';
		}

		if ($code == '1602') {
			return 'SUBSCRIPTION_HOLD';
		}

		if ($code == '1603') {
			return 'SUBSCRIPTION_EXPIRED';
		}

		if ($code == '301') {
			return 'ORDER_CONFIRMED';
		}

		if ($code == '302') {
			return 'ORDER_PAID';
		}

		if ($code == '303') {
			return 'ORDER_COMPLETE';
		}

		if ($code == '304') {
			return 'ORDER_HOLD';
		}

		if ($code == '305') {
			return 'ORDER_EXPIRED';
		}

		if ($code == '306') {
			return 'ORDER_CANCEL';
		}

		if ($code == '401') {
			return 'INVOICE_CONFIRMED';
		}

		if ($code == '402') {
			return 'INVOICE_PAID';
		}

		if ($code == '403') {
			return 'INVOICE_REFUNDED';
		}

		if ($code == '404') {
			return 'INVOICE_WALLET_RECHARGE';
		}
	}

	/**
	 * Our own implementation of only allowing safe html tags
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function filterHtml($str)
	{
		// We can't use JComponentHelper::filterText because by default registered users aren't allowed html codes
		$filter = JFilterInput::getInstance(array(), array(), 1, 1);
		$str = $filter->clean($str, 'html');

		return $str;
	}

	/**
	 * Converts color code into RGB values
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function hexToRGB($hex)
	{
		$hex = str_ireplace('#', '', $hex);
		$rgb = array();
		$rgb['r'] = hexdec(substr($hex, 0, 2));
		$rgb['g'] = hexdec(substr($hex, 2, 2));
		$rgb['b'] = hexdec(substr($hex, 4, 2));

		$str = $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'];
		return $str;
	}

	/**
	 * Determines if a given string is in ascii format
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isAscii($text)
	{
		return (preg_match('/(?:[^\x00-\x7F])/', $text) !== 1);
	}

	/**
	 * Computes a noun given the string and count
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function computeNoun($string , $count)
	{
		$zeroAsPlural = false;

		// Always use plural
		$text = $string . '_PLURAL';

		if ($count == 1 || $count == -1 || ($count == 0 && !$zeroAsPlural)) {
			$text 	= $string 	. '_SINGULAR';
		}

		return $text;
	}


	/**
	 * Convert special characters to HTML entities
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * An alternative to encodeURIComponent equivalent on javascript.
	 * Useful when we need to use decodeURIComponent on the client end.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function encodeURIComponent($contents)
	{
		$revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');

		return strtr(rawurlencode($contents), $revert);
	}

	/**
	 * Format timer value to human readable form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatTimer($value)
	{
		if (!$value) {
			$value = '000000000000';
		}

		$values = str_split($value, 2);

		// split the values into correct segments
		list($year,$month,$day,$hour,$minute,$second) = $values;

		// in case when plan expiration time is not set
		$lifetime = true;
		$count = 0;

		$timers = array('year', 'month', 'day', 'hour', 'minute', 'second');

		foreach ($timers as $key) {
			$value = (int) $$key;

			if($value > 0){
				$lifetime = false;
			}

			$count += $value ? 1 : 0;
		}

		if ($lifetime){
			return JText::_('COM_PAYPLANS_PLAN_LIFE_TIME');
		}

		$counter = 0;
		$str = '';

		foreach ($timers as $key) {

			$value = (int) $$key;

			$lang = JString::strtoupper($key);

			// show values if they are greater than zero only
			if(!$value){
				continue;
			}

			$lang = PP::string()->computeNoun('COM_PP_TIMER_' . $key, $value);
			$valueStr = JText::sprintf($lang, $value);


			$concatStr = $counter ? ' ' . JText::_('COM_PAYPLANS_PLANTIME_CONCATE_STRING_AND') . ' ' : '';
			$str .= $concatStr.$valueStr;
			$counter++;
		}

		return $str;
	}
}
