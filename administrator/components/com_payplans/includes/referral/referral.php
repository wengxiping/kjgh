<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(PP_LIB . '/abstract.php');

class PPReferral extends PayPlans
{
	private $referral = null;
	private $params = null;

	public function __construct($app = null)
	{	
		if ($app) {
			$this->referral = $app;
			$this->params = $this->referral->getAppParams();
		}
	}

	public static function factory($app = null)
	{
		return new self($app);
	}

	/**
	 * Determines if referrer code is allowed on the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function allowed(PPInvoice $invoice, $code)
	{
		// If there are prior referral discounts, do not allow this
		if ($this->isUsed($invoice)) {
			$this->setError(JText::_('COM_PAYPLANS_APP_REFERRAL_ERROR_ALREADY_USED'));
			return false;
		}

		// Ensure that the user id really exists on the system
		$sharerId = PP::getIdFromKey($code);
		$sharer = PP::user($sharerId);

		if (!$sharerId || !$sharer->getId()) {
			$this->setError(JText::_('COM_PAYPLANS_APP_REFERRAL_ERROR_INVALID_CODE'));
			return false;
		}

		// Referrer should not be allowed to use their own codes
		$my = PP::user();

		if ($my->id == $sharer->getId()) {
			$this->setError(JText::_('COM_PAYPLANS_APP_REFERRAL_ERROR_CANNOT_USE_OWN_REFERRAL_CODE'));
			return false;
		}

		$plan = $invoice->getPlan();

		$limit = $this->getLimit($plan);

		// Referral Code can be used infinite time
		if ($limit == 0) {
			return true;
		}

		$usage = $this->getUsage($sharer);

		if ($usage < $limit) {
			return true;
		}

		$this->setError(JText::_('COM_PAYPLANS_APP_REFERRAL_ERROR_MAXIMUM_ALLOWED_LIMIT_REACHED'));
		return false;
	}

	/**
	 * Creates a new modifier for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addModifier($code, $amount, $serial, $object)
	{
		$params = new stdClass();
		$params->message = JText::_('COM_PAYPLANS_APP_REFERRAR_DISCOUNT_MESSAGE');
		$params->type = 'referral';
		$params->reference = $code;
		$params->frequency = PP_MODIFIER_FREQUENCY_ONE_TIME;
		$params->amount = -$amount;
		$params->percentage = 'fixed';
		$params->serial = $serial;

		$object->addModifier($params);

		// Change Recurring Plan to Trial + Recurring, so referral discount will not be applicable on next recurrence
		if ($object->getRecurringType() == PP_PRICE_RECURRING) {
			$objectParams = $object->getParams();

			$recurrenceCount = $objectParams->get('recurrence_count') > 0 ? $objectParams->get('recurrence_count') - 1 : 0;

			$objectParams->set('expirationtype', 'recurring_trial_1');
			$objectParams->set('recurrence_count', $recurrenceCount);
			$objectParams->set('trial_price_1', $objectParams->get('price'));
			$objectParams->set('trial_time_1', $objectParams->get('expiration'));

			$object->params = $objectParams->toString();
		}

		return $object->save();
	}

	/**
	 * Apply discount for referral (purchaser)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function applyPurchaserDiscount(PPInvoice $invoice, $code)
	{
		$plan = $invoice->getPlan();
		$apps = $this->getReferralApps($plan);

		$sharerId = PP::getIdFromKey($code);
		$sharer = PP::user($sharerId);

		foreach ($apps as $app) {
			$app = PP::referral($app);

			$amount = $app->getPurchaserAmount();
			$amount = $app->formatAmount($invoice, $amount);

			// if amount of applied referral discount is greater than total
			if ($amount > $invoice->getTotal()) {
				$this->setError('COM_PAYPLANS_REFERRAL_ERROR_EXCEED_TOTAL_AMOUNT');
				return false;
			}

			/**
			 * V.V.IMP : this is very impotant for applying discount in which serial
			 * @see PayplansModifier
			*/
			$serial = ($app->isPercentage()) ? PP_MODIFIER_PERCENT_DISCOUNT : PP_MODIFIER_FIXED_DISCOUNT;

			$this->addModifier($code, $amount, $serial, $invoice);
		}

		return true;
	}

	/**
	 * Apply discount for referrer (code sharer)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function applySharerDiscount(PPInvoice $invoice, $code)
	{
		$plan = $invoice->getPlan();
		$apps = $this->getReferralApps($plan);

		$sharerId = PP::getIdFromKey($code);
		$sharer = PP::user($sharerId);

		foreach ($apps as $app) {
			$app = PP::referral($app);

			$amount = $app->getSharerAmount();
			$amount = $app->formatAmount($invoice, $amount);

			if (floatval($amount) == floatval(0)) {
				continue;
			}

			// Send discount code to sharer when Referral Code utilized
			$sendLater = $app->sendCouponAfterInvoicePaid();

			if ($sendLater) {
				$params = $invoice->getParams();
				$params->set('referrar_id', $sharer->getId());
				$params->set('referrar_amount', $amount);

				$invoice->params = $params->toString();

				return $invoice->save();
			}

			// Create a new coupon
			$discount = $this->createCouponCode($invoice, $sharer, $amount);

			// Notify referrer when a coupon has been generated
			if (!$discount->prodiscount_id) {
				return false;
			}

			$result = $this->notify($invoice, $sharer, $discount, $amount);

			$this->createReferralRecord($invoice, $sharer, $amount);
		}

		return true;
	}

	/**
	 * Create a new referral record in the #__payplans_referral table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createReferralRecord(PPInvoice $invoice, PPUser $sharer, $amount)
	{
		$plan = $invoice->getPlan();

		$data = new stdClass();
		$data->referrar_id = $sharer->getId();
		$data->referral_id = $invoice->getBuyer()->getId();
		$data->plan_id = $plan->getId();
		$data->amount = $amount;

		$db = PP::db();
		$state = $db->insertObject('#__payplans_referral', $data, 'referrar_id');

		return $state;
	}

	/**
	 * Create coupon code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createCouponCode(PPInvoice $invoice, PPUser $sharer, $amount)
	{
		$code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
		$code = 'REFERRAL' . $sharer->getId() . '_' . $code;

		$params = new JRegistry();
		$params->set('extend_time_discount', '000000000000');
		$params->set('coupon_amount_type', 'fixed');
		$params->set('allowed_quantity', 1);
		$params->set('reusable', false);
		$params->set('allow_clubbing', true);

		$table = PP::table('Discount');
		$table->title = $code;
		$table->coupon_code = $code;
		$table->coupon_type = 'referral';
		$table->core_discount = 1;
		$table->coupon_amount = $amount;
		$table->plans = '';
		$table->start_date = '0000-00-00 00:00:00';
		$table->end_date = '0000-00-00 00:00:00';
		$table->published = 1;
		$table->params = $params->toString();

		$table->store();

		return $table;
	}

	/**
	 * Computes and calculate amount. Eventhough percentage is used, we need to compute it to decimal value
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatAmount(PPInvoice $invoice, $amount)
	{
		$percentage = $this->isPercentage();

		if ($percentage) {
			$amount = (($amount * $invoice->getSubTotal()) / 100);
		}

		return $amount;
	}

	/**
	 * Computes and calculate amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatAmountForDisplay(PPInvoice $invoice, $amount)
	{
		$amount = $invoice->getCurrency() . $amount;
		return $amount;
	}

	/**
	 * Retrieves discount type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDiscountType()
	{
		$type = $this->params->get('referral_amount_type');

		return $type;
	}

	/**
	 * Get all referral apps
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReferralApps(PPPlan $plan)
	{
		static $apps = null;

		if (is_null($apps)) {
			$model = PP::model('Referrals');
			$apps = $model->getApplicableApps($plan);
		}

		return $apps;
	}

	/**
	 * Retrieve the consolidated limits
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLimit(PPPlan $plan)
	{
		static $limit = null;

		if (is_null($limit)) {
			$apps = $this->getReferralApps($plan);
			$limit = 0;

			foreach ($apps as $app) {
				$params = $app->getAppParams();
				$limit += (int) $params->get('referral_limit');
			}
		}

		return $limit;
	}

	/**
	 * Retrieves the usage of a code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUsage(PPUser $sharer)
	{
		// Get how many referral code utilized for Referrar
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM `#__payplans_referral` WHERE `referrar_id`=' . $db->Quote($sharer->getId());
		$db->setQuery($query);
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the amount that should be given to the referrer
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSharerAmount()
	{
		$amount = $this->params->get('referrar_amount');

		return $amount;
	}

	/**
	 * Retrieves the amount that should be given to the person using the referrer code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPurchaserAmount()
	{
		$amount = $this->params->get('referral_amount');

		return $amount;
	}

	/**
	 * Determines if discount type is percentage or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPercentage()
	{
		return $this->getDiscountType() == 'percentage';
	}

	/**
	 * Determines if a referral code was already previously applied
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUsed(PPInvoice $invoice)
	{
		// Referral Discount will not be applied if already applied
		$modifiers = $invoice->getModifiers(array('type' => 'referral'));

		if (count($modifiers) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Notifies user when a new coupon is created for them
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function notify(PPInvoice $invoice, PPUser $user, PayplansTableDiscount $discount, $amount)
	{
		$subject = JText::_('COM_PP_REFERRER_EMAIL_SUBJECT');

		$params = array(
			'amount' => $this->formatAmountForDisplay($invoice, $amount),
			'code' => $discount->coupon_code,
			'referralName' => $invoice->getBuyer()->getName(),
			'referrarName' => $user->getName()
		);

		$mailer = PP::mailer();
		$result = $mailer->send($user->getEmail(), $subject, 'emails/referral/referrer', $params);

		return $result;
	}

	/**
	 * Determines if discount type is percentage or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sendCouponAfterInvoicePaid()
	{
		return (bool) $this->params->get('after_invoice_paid');
	}

	/**
	 * Retrieves the Referral user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReferralUser()
	{
		static $users = array();

		if (!isset($users[$this->referral_id])) {
			$users[$this->referral_id] = PP::user($this->referral_id);
		}

		return $users[$this->referral_id];
	}
}
