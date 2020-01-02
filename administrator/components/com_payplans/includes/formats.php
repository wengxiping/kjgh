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

class PPFormats
{
	/**
	 * Formats a given country record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function country($item, $config = array())
	{
		if (!is_object($item)) {

			if (is_array($item) && is_object(array_shift($item))) {
				return JText::_('COM_PAYPLANS_COUNTRY_NONE_SELECTED');
			}

			if ($item == PAYPLANS_CONST_NONE) {
				return JText::_('COM_PAYPLANS_COUNTRY_NONE');
			}

			if ($item == PAYPLANS_CONST_ALL) {
				return JText::_('COM_PAYPLANS_COUNTRY_ALL');
			}

			return XiError::assert(false, 'INVALID_COUNTRY_CODE');
		}

		return $item->title;
	}

	/**
	 * Formats a currency string
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static function currency($item, $config = array(), $format = null)
	{
		$config = PP::config();
		$format = ($format === null) ? $config->get('show_currency_as') : $format;

		if (!is_object($item)) {
			$item = PP::getCurrency($config->get('currency'));
		}

		if ($item === false) {
			return false;
		}

		if (!isset($format) || $format == 'fullname') {
			return $item->title . ' ('. $item->currency_id . ')';
		}

		if ($format == 'isocode') {
			return $item->currency_id;
		}

		if ($format == 'symbol') {
			return $item->symbol;
		}

		return false;
	}

	/**
	 * Formats a date
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function date($date, $format=null)
	{
		$date = PP::date();
		$config = PP::config();
		$format = ($format === null) ? $config->get('date_format') : $format;

		if (is_string($date)) {
			$date = PP::date($date);
		}

		return $date->toFormat($format);
	}

	public static function amount($amount, $currency, $config=array())
	{
		$config = PP::config();

		// Standard way of formatting amount display
		$amount = self::price($amount);
		$currency = self::currency($currency, $config);

		if ($config->get('show_currency_at') == 'before') {
			return $currency . $amount;
		} else {
			return $amount . $currency;
		}
	}

	/**
	 * Use this formatter only in case of amount display.
	 * Do not use it when some calculation is required to be done on the returned amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function displayAmount($amount, $config = array())
	{
		$config = PP::config();
		$fraction = $config->get('fractionDigitCount');
		$separator = $config->get('price_decimal_separator');

		$amount = number_format(round($amount, $fraction), $fraction, $separator, '');

		return $amount;
	}

	public static function price($amount)
	{
		$config = PP::config();
		$fractionDigitCount = $config->get('fractionDigitCount');

		// XITODO : configuration for rounding the value or not
		return number_format(round($amount, $fractionDigitCount), $fractionDigitCount, '.', '');
	}

	static function user($item, $config=array())
	{
		if(!empty($config)){
			return self::_ops('user', $item, $config);
		}

		return $item->realname.' ( '.$item->username.' ) ';
	}

	static function app($item, $config=array())
	{
		return $item->title;
	}

	/**
	 * Renders the plan time. Deprecated. See @html.plantime instead
	 *
	 * @deprecated	4.0.0
	 */
	public static function planTime($time, $config=array())
	{
	}

	static function order($item, $config=array('prefix'=>false, 'link'=>false, 'admin'=>false, 'attr'=>''))
	{
		return self::_ops('order', $item, $config);
	}

	static function subscription($item, $config=array('prefix'=>false, 'link'=>false, 'admin'=>false, 'attr'=>''))
	{
		return self::_ops('subscription', $item, $config);
	}

	static function payment($item, $config=array('prefix'=>false, 'link'=>false, 'admin'=>false, 'attr'=>''))
	{
		return self::_ops('payment', $item, $config);
	}

	static function invoice($item, $config=array('prefix'=>false, 'link'=>false, 'admin'=>false, 'attr'=>''))
	{
		return self::_ops('invoice', $item, $config);
	}

	private static function _ops($entity, $item, $config)
	{
		$str = $config['prefix'] ? JText::_('COM_PAYPLANS_SEARCH_'.JString::strtoupper($entity)).' # ' : '' ;

		// show ID in admin
		$id = array('var'=>'key', 'value'=>$item->getKey());
		if($config['admin']){
			$id = array('var'=>'id', 'value'=>$item->getId());
		}

		// add ID in string
		$str .= $id['value'];

		// do we need to create link
		if($config['link']){
			$link = XiRoute::_('index.php?option=com_payplans&'."view={$entity}&task=edit&{$id['var']}={$id['value']}");
			$str = PayplansHtml::link($link, $str, $config['attr']);
		}

		return $str;
	}

	static function plan($item, $config=array())
	{
		if(!empty($config)){
			return self::_ops('plan', $item, $config);
		}

		return $item->title;
	}

	static function group($item, $config=array())
	{
		if(!empty($config)){
			return self::_ops('group', $item, $config);
		}

		return $item->title;
	}
}
