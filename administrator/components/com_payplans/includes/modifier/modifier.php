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

require_once(PP_LIB . '/abstract.php');

class PPModifier extends PPAbstract
{
	public $trigger = false;

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

	public static function factory($id = null)
	{
		return new self($id);
	}
	
	/**
	 * Resets the object values (for new instances)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reset($config = array())
	{
		$this->table->modifier_id = 0;
		$this->table->user_id = 0;
		// $this->table->order_id = 0;
		$this->table->invoice_id = 0;
		$this->table->amount = 0.00;
		$this->table->type = '';
		$this->table->reference = '';
		$this->table->message = '';
		$this->table->percentage = 1;
		$this->table->serial = null;
		
		return $this;
	}

	/**
	 * Retrieve the amount stored in the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * Compute the amount and return to the caller as a number instead.
	 * This is because the amount stored in the modifier table could be storing a % of tax or discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getComputedAmount()
	{
		$invoice = $this->getInvoice();
		$invoiceTotal = $invoice->getSubtotal();

		$amount = $this->getAmount();
		$computed = $amount;

		if ($this->isPercentage()) {
			$computed = ($invoiceTotal * $amount) / 100;
		}

		return $computed;
	}

	/**
	 * Determines if the modifier is a discount type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isDiscount()
	{
		$discounts = array(PP_MODIFIER_FIXED_DISCOUNTABLE, PP_MODIFIER_PERCENT_DISCOUNTABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE, PP_MODIFIER_FIXED_DISCOUNT,PP_MODIFIER_PERCENT_DISCOUNT);

		if (in_array($this->getSerial(), $discounts)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the modifier is negative
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isNegative()
	{
		if ($this->getAmount() < 0) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the modifier is positive
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPositive()
	{
		if ($this->getAmount() > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the modifier is a non taxable type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isNonTaxable()
	{
		$taxes = array(PP_MODIFIER_FIXED_NON_TAXABLE,PP_MODIFIER_PERCENT_NON_TAXABLE,PP_MODIFIER_PERCENT_OF_SUBTOTAL_NON_TAXABLE);

		if (in_array($this->getSerial(), $taxes)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the modifier type is a tax type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isTax()
	{
		$taxes = array(PP_MODIFIER_PERCENT_TAXABLE,PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE, PP_MODIFIER_FIXED_TAX,PP_MODIFIER_PERCENT_TAX);

		if (in_array($this->getSerial(), $taxes)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the modifier is a percentage
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPercentage()
	{
		return $this->percentage;
	}
	
	public function getSerial()
	{
		return $this->serial;
	}
	
	public function getFrequency()
	{
		return $this->frequency;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getMessage()
	{
		return $this->message;
	}
	
	public function getReference()
	{
		return $this->reference;
	}
	
	/**
	 * Retrieve the invoice associated with the modifier
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getInvoice()
	{
		$invoice = PP::invoice($this->invoice_id);

		return $invoice;
	}
}