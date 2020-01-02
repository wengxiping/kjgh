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

class PPThemesHelperHtml extends PPThemesHelperAbstract
{
	/**
	 * Renders html output for amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function amount($amount, $currency)
	{
		$fractionDigitCount = $this->config->get('fractionDigitCount');
		$separator = $this->config->get('price_decimal_separator');

		$amount = number_format(round($amount, $fractionDigitCount), $fractionDigitCount, $separator, '');

		$theme = PP::themes();
		$theme->set('amount', $amount);
		$theme->set('currency', $currency);
		$output = $theme->output('site/helpers/html/amount');

		return $output;
	}

	/**
	 * Renders the html output for plan timer
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function plantime($timer)
	{
		$lifetime = true;
		$count = 0;
		
		foreach ($timer as $key => $value) {
			$value = (int) $value;

			if ($value > 0) {
				$lifetime = false;
			}

			$count += $value ? 1 : 0;
		}

		if ($lifetime) {
			return JText::_('COM_PAYPLANS_PLAN_LIFE_TIME');
		}

		$counter = 0;
		$str = '';

		foreach ($timer as $key => $value) {
			$value = (int) $value;
			$key = JString::strtoupper($key);
			
			// show values if they are greater than zero only
			if (!$value) {
				continue;
			}
				
			$key .= ($value > 1) ? 'S':'';
			$valueStr = $value ." ";
			
			$concatStr = $counter ? ' ' . JText::_('COM_PAYPLANS_PLANTIME_CONCATE_STRING_AND') . ' ' : '';
			$str .= $concatStr.$valueStr . JText::_("COM_PAYPLANS_PLAN_" . $key); 
			
			$counter++;
		}

		return $str;
	}

	/**
	 * Renders a redirection notice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirection($title = '', $desc = '', $button = '')
	{
		$title = !$title ? 'COM_PP_REDIRECT_TO_MERCHANT_HEADING' : $title;
		$desc = !$desc ? 'COM_PP_REDIRECT_TO_MERCHANT' : $desc;
		$button = !$button ? 'COM_PP_PROCEED_TO_PAYMENT_BUTTON' : $button;

		$theme = PP::themes();
		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('button', $button);

		$output = $theme->output('site/helpers/html/redirection');

		return $output;
	}
}
