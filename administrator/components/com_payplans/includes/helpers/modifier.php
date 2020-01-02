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

class PPHelperModifier
{
	const CUMULATIVE_CALCULATION = 1;
	const NON_CUMULATIVE_CALCULATION = 0;

	public static $serials = array(
									PP_MODIFIER_FIXED_DISCOUNTABLE,
									PP_MODIFIER_PERCENT_DISCOUNTABLE,
									PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE,
									PP_MODIFIER_FIXED_DISCOUNT,
									PP_MODIFIER_FIXED_NON_TAXABLE,
									PP_MODIFIER_PERCENT_DISCOUNT,
									PP_MODIFIER_PERCENT_TAXABLE,
									PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE,
									PP_MODIFIER_FIXED_TAX,
									PP_MODIFIER_PERCENT_TAX,
									PP_MODIFIER_PERCENT_NON_TAXABLE,
									PP_MODIFIER_FIXED_NON_TAXABLE_TAX_ADJUSTABLE,
									PP_MODIFIER_PERCENT_OF_SUBTOTAL_NON_TAXABLE
	);

	/**
	 * Creates a new modifier
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function create($data)
	{
		$modifier = PP::modifier();
		$modifier->bind($data);
		$modifier->save();

		return $modifier;
	}

	/**
	 * Retrieve all modifiers with a given filter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function get($filter)
	{
		$model = PP::model('Modifier');
		$modifiers = $model->loadRecords($filter, array('limit'));

		if (!$modifiers) {
			return array();
		}

		foreach ($modifiers as &$modifier) {
			$modifier = PP::modifier($modifier);
		}

		return $modifiers;
	}

	/**
	 * Re-arranges modifier according to their serials
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function rearrange($modifiers)
	{
		$results = array();

		// arrage according to their serial
		$arrangeOrder = array();

		foreach ($modifiers as $modifier) {
			$arrangeOrder[$modifier->getSerial()][] = $modifier;
		}

		$arranged = array();

		foreach (self::$serials as $serial) {
			if (!isset($arrangeOrder[$serial])) {
				continue;
			}

			$arranged = array_merge($arranged, $arrangeOrder[$serial]);
		}

		return $arranged;
	}

	/**
	 * Computes the total, given the subtotal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getTotal($subTotal, $modifiers, $excludedModifiers = array())
	{
		// if not an array
		if (!is_array($modifiers)) {
			$modifiers = array($modifiers);
		}

		$modifiers = self::rearrange($modifiers);
		$modifierType = 0;
		$finalTotal = $total[0] = $subTotal;
		$modifierType = 0;

		foreach ($modifiers as $modifier) {

			if (in_array($modifier->getSerial(), $excludedModifiers)) {
				continue;
			}

			// this is the real data, will be visible, calculated everytime
			$modifier->_modificationOf = 0;

			$modificationOf = $modifier->getAmount();
			$invoiceModifier = $modifier->getSerial();

			if ($invoiceModifier == PP_MODIFIER_PERCENT_DISCOUNTABLE || $invoiceModifier == PP_MODIFIER_FIXED_DISCOUNTABLE) {
				$refAmount = self::findTotal($total, self::CUMULATIVE_CALCULATION, 0, 1,$finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[1] = $total[1]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE) {
				$refAmount = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, 0, 2,$finalTotal);
				$result    = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[2] = $total[2]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_FIXED_DISCOUNT || $invoiceModifier == PP_MODIFIER_FIXED_NON_TAXABLE || $invoiceModifier == PP_MODIFIER_PERCENT_DISCOUNT) {
				$refAmount = self::findTotal($total, self::CUMULATIVE_CALCULATION, 2, 3,$finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[3] = $total[3]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_PERCENT_TAXABLE) {
				$refAmount = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, 3, 4, $finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[4]= $total[4]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE) {
				$refAmount = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, 0, 5,$finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[5] = $total[5]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_FIXED_TAX || $invoiceModifier == PP_MODIFIER_PERCENT_TAX) {
				$refAmount = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, 5, 6, $finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[6] = $total[6]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_PERCENT_NON_TAXABLE) {
				$refAmount = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, 6, 7,$finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[7] = $total[7]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_FIXED_NON_TAXABLE_TAX_ADJUSTABLE) {
				$refAmount = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, 7, 8,$finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[8] = $total[8]+$result;
				$finalTotal += $result;
			}

			if ($invoiceModifier == PP_MODIFIER_PERCENT_OF_SUBTOTAL_NON_TAXABLE) {
				$refAmount = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, 0, 9,$finalTotal);
				$result = self::calculateAmount($refAmount,$modificationOf,$modifier);
				$modifier->_modificationOf += $result;
				$total[9] = $total[9]+$result;
				$finalTotal += $result;
			}
		}

		if ($finalTotal < 0) {
			$finalTotal = 0.0;
		}

		return $finalTotal;
	}

	/**
	 *
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function findTotal(&$total, $cumulative, $index_working, $index_updating, $finalTotal)
	{
		// We dont have working data, quickly find it out
		if (!isset($total[$index_working])) {
			$total[$index_working] = self::findTotal($total, self::NON_CUMULATIVE_CALCULATION, $index_working-1, null, $finalTotal);
		}

		// Return updated total
		if (!isset($total[$index_updating])) {
			$total[$index_updating] = $finalTotal;
		}

		$result = $total[$index_updating];

		// 	return working total
		if ($cumulative == self::NON_CUMULATIVE_CALCULATION) {
			$result = $total[$index_working];
		}

		return $result;
	}

	/**
	 *
	 * @param number $subTotal
	 * @param number $total
	 * @param array of PayplansModifier $modifier
	 * @param number $modificationOf
	 * @return number
	 * @since 3.2
	 */
	public static function calculateAmount($refAmount,$modificationOf,$modifier)
	{
		if ($modifier->isPercentage() == true ) {
			$modificationOf =  ($refAmount * $modificationOf ) / 100;
		}
		return $modificationOf;
	}


	/**
	 *
	 * Returns the modified amount by the $serials
	 * @param numeric $total
	 * @param array $modifiers
	 * @param array $serials
	 *
	 * @return float $modifiedBy
	 * @since 2.0
	 */
	static public function getModificationAmount($total, $modifiers, $serials)
	{
		if (!is_array($serials)) {
			$serials = array($serials);
		}

		$modifiedBy = 0;

		foreach ($modifiers as $modifier) {
			if (in_array($modifier->getSerial(), $serials)) {
				if (!isset($modifier->_modificationOf)) {
					$modificationOf = $modifier->getAmount();
					if($modifier->isPercentage() == true){
						$modificationOf = $total * $modificationOf / 100;
					}
					$modifier->_modificationOf = $modificationOf;
				}

				$modifiedBy += $modifier->_modificationOf;
			}
		}

		return $modifiedBy;
	}

	public static function applyConditionally($invoice, $referenceInvoice, $modifiers)
	{
		foreach($modifiers as $modifier){
			switch ($modifier->getFrequency()){
				case PP_MODIFIER_FREQUENCY_EACH_TIME :
					$newModifier = PP::modifier($modifier->getId());
					$newModifier->setId(0);
					$newModifier->invoice_id = $invoice->getId();
					$newModifier->save();

					break;

				default :
					break;
			}
		}

		return true;
	}

	public static function getTotalByFrequencyOnInvoiceNumber($modifiers, $subtotal, $invoiceNumber)
	{
		$total = $subtotal;
		$modifiers = self::rearrange($modifiers);
		$total = self::getTotal($total, $modifiers);

		return $total;
	}

	/**
	 * Retrieves the total usage of a modifier not taking into consideration the status of the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static function getTotalUsage($reference, $type)
	{
		$db = PP::db();
		$query = $db->getQuery(true);

		$query->select('*')
					 ->from('`#__payplans_modifier` as modifier')
					 ->leftJoin('`#__payplans_invoice` as invoice ON invoice.`invoice_id` = modifier.`invoice_id`')
					 ->where('modifier.`reference` = "'.$reference.'"')
					 ->where('modifier.`type` = "'.$type.'"');

		$items = $db->setQuery($query)->loadObjectList();
		return count($items);
	}

	/**
	 * Retrieve the actual usage of modifier based on the paid status of an invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getActualConsumption($reference, $type, $count = true)
	{
		$db = PP::db();
		$query = $db->getQuery(true);

		$query->select('*')
					 ->from('`#__payplans_modifier` as modifier')
					 ->leftJoin('`#__payplans_invoice` as invoice ON invoice.`invoice_id` = modifier.`invoice_id`')
					 ->where('modifier.`reference` = "'.$reference.'"')
					 ->where('modifier.`type` = "'.$type.'"')
					 ->where('(invoice.`status` = "' . PP_INVOICE_PAID . '")');

		$result = $db->setQuery($query)->loadObjectList();

		//check if all records are needed
		if ($count == false){
			return $result;
		}

		return count($result);
	}
}
