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
PP::import('admin:/includes/upgrade/upgrade');
PP::import('admin:/includes/renewal/renewal');

class PPSubscription extends PPAbstract implements PPAppTriggerableInterface, PayplansIfaceApiSubscription, PPMaskableInterface
{
	private $plan = null;
	private $useCache = false;

	public static function factory($id = null)
	{
		return new self($id);
	}

	/**
	 * Activates the current subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function activate($order = null)
	{
		// Get the order
		if (is_null($order)) {
			$order = $this->getOrder();
		}

		// Get invoices for the order
		$invoices = $order->getInvoices();

		// Get Proper Object of Invoice.
		$recentInvoiceId = !empty($invoices)? max(array_keys($invoices)) : 0;
		$recentInvoice = !empty($recentInvoiceId) ? $invoices[$recentInvoiceId] : PP::invoice();

		$invoiceStatus = array(PP_INVOICE_CONFIRMED, PP_NONE);

		// Case 1 : Subscription already have an invoice in PAID, REFUND or RECHARGE then no need to create a new invoice just add a Transaction on Recent Invoice.
		// Case 2 : Add Invoice and Transaction.
		if (($recentInvoice->getId() != 0) && in_array($recentInvoice->getStatus(), $invoiceStatus)) {
			$transaction = $recentInvoice->addTransaction();

			return $transaction;
		}

		// Create an invoice for the order
		$invoice = $order->createInvoice($this);

		// Add a new transaction for the invoice
		$transaction = $invoice->addTransaction();

		return $transaction;
	}

	/**
	 * Activates the current subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toggleUseCache($flag = true)
	{
		$this->useCache = (bool) $flag;
	}

	/**
	 * Check if this subscription is upgradable or not
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isUpgradable()
	{
		if (!$this->isActive()) {
			return false;
		}

		$upgrades = PPUpgrade::loadUpgrades();
		if (!$upgrades) {
			return false;
		}

		// check plans
		$plan = $this->getPlan();
		$planId = $plan->getId();

		foreach ($upgrades as $upgrade) {

			if ($upgrade->getApplyAll()) {
				return true;
			}

			$appPlans = $upgrade->getPlans();
			if (in_array($planId, $appPlans)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if renewal applicable for subscription or not
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isRenewable()
	{
		// check subscription status for which renewal applicable
		$recurring = $this->isRecurring();

		if ($recurring) {
			if (!$this->isExpired()) {
				return false;
			}

		} else {

			// renewal not applicable for forever/life time plan
			if ( ($this->getExpirationType() != 'fixed') || $this->isOnHold() || $this->isNotActive()) {
				return false;
			}
		}

		$renewals = PPRenewal::loadRenewals();
		if (!$renewals) {
			return false;
		}

		// check plans
		$plan = $this->getPlan();
		$planId = $plan->getId();

		foreach ($renewals as $renewal) {

			if ($renewal->getApplyAll()) {
				return true;
			}

			$appPlans = $renewal->getPlans();
			if (in_array($planId, $appPlans)) {
				return true;
			}
		}

		return false;
	}



	/**
	 * Triggered after parent binds an event
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function afterBind($id = 0)
	{
		static $loadedPlans = array();

		if (!$id) {
			return $this;
		}

		if ($this->afterBindLoad) {

			$idx = $this->plan_id;

			if (isset($loadedPlans[$idx])) {

				$this->plan = $loadedPlans[$idx];
				return $this;
			}

			$loadedPlans[$idx] = PP::plan($this->plan_id);
			$this->plan = $loadedPlans[$idx];
		}

		return $this;
	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canCancel()
	{
		$order = $this->getOrder();

		if (!$order->isConfirmed() && !$order->isCancelled() && !$order->isExpired() && !$this->isExpired() && $this->isRecurring()) {
			$masterInvoice = $order->getLastMasterInvoice();

			if ($masterInvoice instanceOf PPInvoice) {
				$masterInvoiceId = $masterInvoice->getId();

				$payment = $masterInvoice->getPayment();
				$app = ($payment instanceof PPPayment) ? $payment->getApp() : false;

				// Determine if the app allows cancellation
				if (($app instanceof PPAppPayment) && $app->getAppParam('allow_recurring_cancel', false)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Determines if the current subscribed plan can be upgraded
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canUpgrade($newPlanId = null)
	{
		// Non active subscriptions cannot be upgraded
		if (!$this->isActive()) {
			return false;
		}

		// Get a list of available plans to be upgraded to
		$model = PP::model('Plan');
		$plans = $model->getAvailableUpgrades($this->getPlan()->getId());

		// this is to check if a specific new plans can be upgrade from this subscription or not.
		if ($plans && $newPlanId && in_array($newPlanId, $plans)) {
			return true;
		}

		if ($plans) {
			return true;
		}

		return false;
	}

	/**
	 * Extends the current subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function extend($timeframe)
	{
		if ($this->isExpired()) {
			$now = PP::date();
			$this->table->expiration_date = $now->toSql();
			$this->table->status = PP_SUBSCRIPTION_ACTIVE;
		}

		// Set to life time by default unless explicitly stated otherwise
		if ($timeframe == '000000000000') {
			$this->expiration_date = '0000-00-00 00:00:00';
		}

		if ($timeframe != '000000000000') {
			$expireDate = $this->getExpirationDate();

			if ($expireDate === false) {
				return false;
			}

			$this->expiration_date = $expireDate->addExpiration($timeframe)->toSql();
		}

		$this->save();
	}

	/**
	 * Retrieves a list of subscription actions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getActions()
	{
		$order = $this->getOrder();
		
		if (!$this->isActive() || !$this->isRecurring() || !$order->isCompleted() || $order->isCancelled() || $order->isExpired() || $this->isExpired()) {
			return false;
		}

		$masterInvoice = $order->getLastMasterInvoice();
		$masterInvoiceId = $masterInvoice->getId();

		if (!$masterInvoiceId) {
			return false;
		}

		$payment = $masterInvoice->getPayment();
		$app = ($payment instanceof PPPayment) ? $payment->getApp() : false;

		if (!$app || !method_exists($app, 'getSubscriptionActions')) {
			return false;
		}

		$actions = array();
		$actions[] = $app->getSubscriptionActions($this);

		return $actions;	
	}

	/**
	 * Gets the expiration type of the subscription
	 *
	 * @return  string
	 */
	public function getExpirationType()
	{
		return  $this->getParams()->get('expirationtype', '');
	}

	/**
	 * Retrieves the title of the subscription. Subscription title is always the title of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTitle()
	{
		// Plan-modifier can change the title so if title is set then return that else plan title
		$params = $this->getParams();
		$title = $params->get('title', '');

		if (!empty($title)) {
			return $title;
		}

		// If plan isn't set, we can't return a value for it
		if (!isset($this->plan)) {
			return '';
		}

		if ($this->plan == false) {
			return JText::_("COM_PAYPLANS_SUBSCRIPTION_PLAN_DOES_NOT_EXIST");
		}

		return $this->plan->getTitle();
	}

	/**
	 * Retrieves the permalink for a subscription (Used in frontend)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true)
	{
		static $items = array();

		if (!isset($items[$this->getKey()])) {
			$link = 'index.php?option=com_payplans&view=dashboard&layout=subscription&subscription_key=' . $this->getKey();
			$link = PPR::_($link, $xhtml);

			$items[$this->getKey()] = $link;
		}

		return $items[$this->getKey()];
	}

	/**
	 * Returns the price of subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPrice($type = PP_PRICE_FIXED, $debug = false)
	{
		$params = $this->getParams();
		$price = PPFormats::price($params->get('price', 0.00));

		if ($type === PP_PRICE_RECURRING_TRIAL_1) {
			$price = PPFormats::price($params->get('trial_price_1', 0.00));
		}

		if ($type === PP_PRICE_RECURRING_TRIAL_2) {
			$price = PPFormats::price($params->get('trial_price_2', 0.00));
		}

		if ($debug) {
			// dump($price);
		}

		return $price;
	}

	/**
	 * Sets the price of the subscription. Change the price parameter of the subscription
	 * Subscription total will be update to the value passed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setPrice($price)
	{
		$params = $this->getParams();
		$params->set('price', $price);

		$this->table->params = $params->toString();

		$this->getTotal();
		return $this;
	}

	/**
	 * Gets the total amount of the subscription. The total is exclusive of tax and discounts.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotal()
	{
		//always ensure it to be calculated
		$this->total = $this->getPrice();

		return PPFormats::price($this->total);
	}

	/**
	 * Retrieves a list of tokens available for token rewriting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRewriterTokens()
	{
		$data = $this->table->toArray();

		// Remove unwanted data
		unset($data['checked_out']);
		unset($data['checked_out_time']);
		unset($data['modified_date']);
		unset($data['params']);

		$data['key'] = $this->getKey();

		/*$subDetails = $this->getCustomDetails();
		foreach ($subDetails as $details) {
			var_dump($details);die;
		}*/

		// If no 'units' param, means this subscription is for 1 unit only
		$data['advancedpricing_units'] = $this->getParams()->get('units', '1');

		return $data;
	}

	/**
	 * Gets the status of the subscription
	 *
	 * @see PayplansIfaceApiSubscription::getStatus()
	 *
	 * @return integer  Value of the status
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Sets the status of the subscription.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Sets the buyer for the subscription
	 * @see PayplansIfaceApiSubscription::setBuyer()
	 *
	 * @param  integer $userId  UserId to which the subscription will be attached
	 * @return object  PayplansSubscription
	 */
	public function setBuyer($userId=0)
	{
		$this->user_id = $userId;
		return $this;
	}

	/**
	 * Retrieves the purchaser of the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBuyer()
	{
		static $users = array();

		if (!isset($users[$this->table->user_id])) {
			$user = PP::user($this->table->user_id);

			$users[$this->table->user_id] = $user;
		}

		return $users[$this->table->user_id];
	}

	/**
	 * Assigns an order to the current subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setOrder(PPOrder $order)
	{
		$this->setBuyer($order->getBuyer()->getId());
		$this->table->order_id = $order->getId();

		return $this;
	}

	/**
	 * Retrieves the plan object that is tied to this subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlan()
	{
		static $plans = array();

		$key = $this->table->plan_id;

		if (!isset($plans[$key])) {
			$plan = PP::plan($key);

			$plans[$key] = $plan;
		}

		return $plans[$key];
	}

	/**
	 * Deprecated. Use @getPlan instead.
	 *
	 * @deprecated	4.0.0
	 */
	public function getPlans($requireInstance = false)
	{
		return $this->getPlan();
		// $plan = PayPlansPlan::getInstance($this->plan_id);

		// if ($requireInstance === PP_INSTANCE_REQUIRE) {
		// 	return array(PayplansPlan::getInstance($this->plan_id));
		// }
		// //get all subscription's plans
		// return array($this->plan_id);
	}

	/**
	 * Retrieves an order object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOrder()
	{
		static $_cache = array();

		$key = $this->table->order_id;

		if ($this->useCache && isset($_cache[$key])) {
			return $_cache[$key];
		}

		$order = PP::order();

		if ($this->useCache) {
			$order->setAfterBindLoad(false);
			$order->toggleUseCache();
		}
		$order->load($this->table->order_id);

		if (!$this->useCache) {
			$order->loadSubscription($this->table->subscription_id);
		}

		$_cache[$key] = $order;

		return $_cache[$key];
	}

	/**
	 * Gets the cancellation date of the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancellationDate()
	{
		if ($this->table->cancel_date == '0000-00-00 00:00:00') {
			return false;
		}

		$date = PP::date($this->table->cancel_date);

		return $date;
	}

	/**
	 * Gets the subscription date. Subscription date is the activation date of the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionDate()
	{
		if ($this->table->subscription_date == '0000-00-00 00:00:00') {
			return false;
		}

		$date = PP::date($this->table->subscription_date);

		return $date;
	}

	/**
	 * Gets the expiration date of the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExpirationDate()
	{
		if ($this->table->expiration_date == '0000-00-00 00:00:00') {
			return false;
		}

		$date = PP::date($this->table->expiration_date);

		return $date;
	}

	/**
	 * Set the subscription Expiration date
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function setExpirationDate($date)
	{
		if(!is_a($date, 'XiDate')){
			$date = PP::date($date);
		}

		$this->expiration_date = $date;
		return $this;
	}

	/**
	 * Set the subscription date
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function setSubscriptionDate($date)
	{
		if(!is_a($date, 'XiDate')){
			$date = PP::date($date);
		}
		
		$this->subscription_date = PP::date($date);
		return $this;
	}

	/**
	 * Gets the name of the buyer of the subscription
	 * @return string  Name
	 */
	public function getBuyerName()
	{
		return PayplansHelperUser::getName($this->user_id);
	}

	/**
	 * Gets the username of the buyer of the subscription
	 * @return string  Username
	 */
	public function getBuyerUsername()
	{
		return PayplansHelperUser::getUserName($this->user_id);
	}

	/**
	 * Deprecated. Use @getLabel
	 *
	 * @deprecated	4.0.0
	 */
	public function getStatusName()
	{
		return $this->getLabel();
	}

	/**
	 * Determines if a subscription is active
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isActive()
	{
		return $this->status == PP_SUBSCRIPTION_ACTIVE;
	}

	/**
	 * Determines if a subscription is on hold
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isOnHold()
	{
		return $this->status == PP_SUBSCRIPTION_HOLD;
	}

	/**
	 * Determines if a subscription is expired
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isExpired()
	{
		return $this->status == PP_SUBSCRIPTION_EXPIRED;
	}

	/**
	 * Determines when the subscription is not active
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isNotActive()
	{
		return $this->status == PP_SUBSCRIPTION_NONE;
	}

	/**
	 * Determines if the subscription should be processed during cron
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRequiredToProcessByCron()
	{
		// Only process recurring subscriptions
		if (!$this->isRecurring()) {
			return false;
		}

		$recurrenceCount = $this->getRecurrenceCount();

		// for 0 reccurrence count, always create new invoice
		if ($recurrenceCount == 0) {
			return true;
		}

		$order = $this->getOrder();
		$invoiceCount = $order->getRecurringInvoiceCount();

		$type = $this->getExpirationType();

		if ($type == PP_RECURRING && $invoiceCount >= $recurrenceCount){
			return false;
		}

		if ($type == PP_RECURRING_TRIAL_1 && $invoiceCount >= ($recurrenceCount + 1)) {
			return false;
		}

		if ($type == PP_RECURRING_TRIAL_2 && $invoiceCount >= ($recurrenceCount + 2)) {
			return false;
		}

		return true;

	}

	/**
	 * Deletes a subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		return parent::delete();
	}

	/**
	 * Activate the subscription and add the given expiration time to the existing expiration time of the subscription.
	 * Extends subscription if it is already active
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renew($expiration)
	{
		// Initialize subscription date
		$subscriptionDate = $this->getSubscriptionDate();

		if (!$subscriptionDate) {
			$this->table->subscription_date = PP::date();
		}

		// Initialize expiration date
		$expirationDate = $this->getExpirationDate();

		if (!$expirationDate) {
			$this->table->expiration_date = PP::date();
		}

		$from = $this->getExpirationDate();
		$now = PP::date();

		// Extend the subscription time from the date which is greater not from the expiration date always
		// as in some cases user renew the subscription after few days of expiration
		if ($now->toSql() > $from->toSql()) {
			$from = $now;
		}

		$this->table->expiration_date = $from->addExpiration($expiration);
		

		$this->table->status = PP_SUBSCRIPTION_ACTIVE;
		$this->save();

		return $this;
	}

	/**
	 * Determines if the subscription is recurring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRecurring()
	{
		$params = $this->getParams();
		$type = $params->get('expirationtype', 'forever');
		$recurringTypes = array(PP_RECURRING, PP_RECURRING_TRIAL_1, PP_RECURRING_TRIAL_2);

		if (in_array($type, $recurringTypes)) {
			return $type;
		}

		return false;
	}

	/**
	 * This function will calculate the price for different invoices
	 * Enter description here ...
	 * @param integer $invoiceNumber : this is the number of invoice for which price has been asked
	 * 								   default is 1
	 */
	public function getPriceForInvoice($invoiceNumber)
	{
		$recurringType = $this->isRecurring();

		// if subscription is recurring trial 1/2
		// and invoice number is 1 then return first trial price
		if((PP_RECURRING_TRIAL_1 === $recurringType
				|| PP_RECURRING_TRIAL_2 === $recurringType)
				&& $invoiceNumber === 1){
			return $this->getPrice(PP_PRICE_RECURRING_TRIAL_1);
		}

		// if subscription is recurring trial 2
		// and invoice number is 2 then return second trial price
		if(PP_RECURRING_TRIAL_2 === $recurringType && $invoiceNumber === 2){
			return $this->getPrice(PP_PRICE_RECURRING_TRIAL_2);
		}

		// else return regular price
		return $this->getPrice();
	}

	/**
	 * Gets the expiration time of the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExpiration($for = "")
	{
		$rawTime = $this->getParams()->get('expiration', '000000000000');

		if ($for === PP_RECURRING_TRIAL_1) {
			$rawTime = $this->getParams()->get('trial_time_1', '000000000000');
		}

		if ($for === PP_RECURRING_TRIAL_2) {
			$rawTime = $this->getParams()->get('trial_time_2', '000000000000');
		}

		return PPHelperPlan::convertIntoTimeArray($rawTime);
	}

	/**
	 * Generates the status label of the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLabel()
	{
		$label = 'COM_PP_SUBSCRIPTION_NONE';

		if ($this->isActive()) {
			$label = 'COM_PP_SUBSCRIPTION_ACTIVE';
		}

		if ($this->isOnHold()) {
			$label = 'COM_PP_SUBSCRIPTION_HOLD';
		}

		if ($this->isExpired()) {
			$label = 'COM_PP_SUBSCRIPTION_EXPIRED';
		}

		$label = JText::_($label);

		return $label;
	}

	/**
	 * Retrieves the css class that is applied on status labels
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStatusLabelClass()
	{
		if ($this->isExpired()) {
			return 'o-label--danger';
		}

		if ($this->isActive()) {
			return 'o-label--success';
		}

		if ($this->isOnHold()) {
			return 'o-label--warning';
		}

		return 'o-label--primary';
	}

	/**
	 * Sets the expiration time of the invoice
	 *
	 * @param  string   $rawExpiration  12 digits numeric string each 2 digits denotes the value for year, month, day, hour, minute and second in the same sequence, starting from year(starting 2 digits indicate year)
	 * @param  integer  $for            Integer constant indicating the expiration type for which expiration time is to be set
	 *
	 * @return object PayplansPlan
	 */
	public function setExpiration($rawExpiration, $for = PP_SUBSCRIPTION_FIXED)
	{
		$varName = 'expiration';
		if($for === PP_RECURRING_TRIAL_1){
			$varName = 'trial_time_1';
		}
		elseif($for === PP_RECURRING_TRIAL_2){
			$varName = 'trial_time_2';
		}

		$this->getParams()->set($varName, $rawExpiration);

		return $this;
	}

	/**
	 * Gets the recurrence count of the subscription
	 * @return integer
	 */
	public function getRecurrenceCount()
	{
		return $this->getParams()->get('recurrence_count');
	}

	/**
	 * Gets the currecny of the subscription
	 *
	 * Subscription does not stores the currency in its own parameter
	 * It is saved in the attached order
	 *
	 * @param string $format  An optional parameter to get the currency in different format.
	 * Available formats are isocode, symbol, fullname
	 *
	 * @return  currency of the subscription
	 */
	public function getCurrency($format = null)
	{
		return $this->getOrder(PP_INSTANCE_REQUIRE)->getCurrency($format);
	}

	/**
	 * Refunds a subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refund()
	{
		$this->status = PP_SUBSCRIPTION_HOLD;
		return $this->save();
	}

	/**
	 * Expired a subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function expired()
	{
		$this->table->status = PP_SUBSCRIPTION_EXPIRED;

		return $this->save();
	}


	/**
	 * Inactive a subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function inactive()
	{
		$this->status = PP_SUBSCRIPTION_NONE;
		return $this->save();
	}

	/**
	 * Resets the data from the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reset($options = array())
	{
		$this->table->subscription_id = 0;
		$this->table->order_id = 0;
		$this->table->user_id = 0;
		$this->table->plan_id = 0;
		$this->table->total = 0.0000;
		$this->table->status = PP_SUBSCRIPTION_NONE;
		$this->table->subscription_date = PP::date();
		$this->table->expiration_date = PP::date();
		$this->table->cancel_date = PP::date();

		return $this;
	}

	/**
	 * Overrides the standard save behavior as subscriptions would need to have a different workflow
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		// if subscription status is active and expiration time is not set/valid it means activate subscription now
		if ($this->isActive() && (!$this->getSubscriptionDate() && !$this->getExpirationDate())) {

			$this->subscription_date = PP::date();
			$plan = $this->getPlan();

			// Add expiration to current timestamp
			$expireDate = PP::date();
			$this->expiration_date = $expireDate->addExpiration($plan->getRawExpiration());
		}

		$this->checkActivationDate();

		return parent::save();
	}

	public function checkActivationDate()
	{
		if (!$this->plan->isFixedExpirationDate()) {
			return true;	
		}

		//if recurring plan and free plan skip the process
		if ($this->plan->isRecurring() || $this->plan->isFree()) {
			return;
		}
		
		if (!$this->isActive()) {
			return;
		}

		$expirationDate = $this->plan->getExpirationOnDate();
		$subscriptionDate = $this->getSubscriptionDate();
		$actualExpiration = $this->getExpirationDate();
		$planIsLifeTime = !$actualExpiration ? true : $actualExpiration->toUnix();

		$from = $this->plan->getSubscriptionFromExpirationDate();
		$to = $this->plan->getSubscriptionEndExpirationDate();

		// when range is set and current subscription does not lie within that range
		if (!empty($from) && !empty($to) && (($subscriptionDate->toUnix() < $from->toUnix()) || ($subscriptionDate->toUnix() > $to->toUnix()))) {
			return;
		}

		// when range is not set then change the expiration date anyway 
		if (empty($from) && empty($to)) {
			return $this->changeExpirationDate($expirationDate);
		}

		// when range is set then check subscription date whether lies in that range
		if (!empty($from) && !empty($to) && ($subscriptionDate->toUnix() >= $from->toUnix()) && ($subscriptionDate->toUnix() <= $to->toUnix())) {
			return $this->changeExpirationDate($expirationDate);
		}

		// when start date is set
		if (!empty($from) && ($subscriptionDate->toUnix() >= $from->toUnix())) {
			return $this->changeExpirationDate($expirationDate);
		}

		// when end date is set
		if (!empty($to) && ($subscriptionDate->toUnix() <= $to->toUnix())) {
			return $this->changeExpirationDate($expirationDate);
		}
	}

	public function changeExpirationDate($expirationDate)
	{
		$currentDate = PP::date();

		// do nothing when current date is greater than the expiration date
		if ($currentDate->toMySQL() > $expirationDate->toMySQL()) {
			return true;
		}

		$this->setExpirationDate($expirationDate);
	}

	/**
	 * Determines if this subscription needs moderation
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function requireModeration()
	{
		// If this action done from backend, we skip the moderation
		if (JFactory::getApplication()->isAdmin()) {
			return false;
		}

		$previousSubscription = call_user_func_array(array('PP', $this->getName()), array($this->getId()));
		$plan = $this->getPlan();

		if ($plan->requireModeration() && $this->isActive()) {
			return true;
		}

		return false;
	}

	/**
	 * Process subscription moderation
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function setAsPending()
	{
		// Set the status to onhold
		$this->setStatus(PP_SUBSCRIPTION_HOLD);

		// Stop the subscription time. Wait for it to be approved later
		$this->expiration_date = '0000-00-00 00:00:00';

		$this->save();
		
		// Next we need to send the moderation email to admin and the user
		$namespace = 'emails/subscription/moderate';
		$subject = JText::_('COM_PAYPLANS_SUBSCRIPTION_MODERATION_REQUIRED_SUBJECT');

		$mailer = PP::mailer();
		$emails = $mailer->getAdminEmails();

		foreach ($emails as $email) {
			$mailer->send($email, $subject, $namespace, array('type' => 'ADMIN'));
		}

		$subject = JText::_('COM_PAYPLANS_SUBSCRIPTION_UNDER_MODERATION_SUBJECT');

		$buyerEmail = $this->getBuyer()->getEmail();
		$mailer->send($buyerEmail, $subject, $namespace, array('type' => 'USER'));

		return true;
	}

	/**
	 * Allow caller to call for process the moderation
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function processModeration()
	{
		if (!$this->requireModeration()) {
			return;
		}

		return $this->setAsPending();
	}

	/**
	 * Marks a subscription as active
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setActive()
	{
		$invoices = $order->getInvoices();

		// Get Proper Object of Invoice.
		$recentInvoiceId = !empty($invoices)? max(array_keys($invoices)) : 0;
		$recentInvoice	 = !empty($recentInvoiceId) ? $invoices[$recentInvoiceId] : PayplansInvoice::getInstance();

		$invoiceStatus	 = array(PP_INVOICE_CONFIRMED, PP_SUBSCRIPTION_NONE);

		// Case 1 : Subscription already have an invoice in PAID, REFUND or RECHARGE then no need to create a new invoice just add a Transaction on Recent Invoice.
		// Case 2 : Add Invoice and Transaction.
		if (($recentInvoiceId != 0) && in_array($recentInvoice->getStatus(), $invoiceStatus)){
			$transaction = $recentInvoice->addTransaction();
		} else {
			$transaction = $order->createInvoice()->addTransaction();
		}

		return $transaction;
	}

	/**
	 * Sets the plan to the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setPlan($plan)
	{
		// support passing a plan-id.
		if (is_a($plan,'PPPlan') === false) {
			$plan = PP::plan($plan);
		}

		// To make this clearer, we should assign it directly to the table
		$this->table->plan_id = $plan->getId();
		$this->table->status = PP_NONE;
		$this->table->cancel_date = null;

		// current timestamp
		$this->table->subscription_date = PP::date('0000:00:00 00:00:00');
		$this->table->expiration_date = PP::date('0000:00:00 00:00:00');

		$planDetails = $plan->getDetails();

		if ($plan->getModifier()) {
			list($title, $price, $time) = explode('_', $plan->getModifier());

			$planDetails->set('price', $price);
			$planDetails->set('title', $title);
			$planDetails->set('expiration', $time);
		}

		if ($plan->getAdvPricing()) {
			list($totalUnit, $price, $time) = explode('_', $plan->getAdvPricing());

			$planDetails->set('units', $totalUnit);
			$planDetails->set('price', $price);
			$planDetails->set('expiration', $time);
		}

		// set time params of Plan to params of subscription
		$this->table->params = $planDetails->toString();
		$this->table->total = $this->getTotal();

		$this->plan = $plan;
		return $this;
	}

	/**
	 * Retrieves the custom details for this subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomDetails()
	{
		static $items = array();

		if (!isset($items[$this->getId()])) {
			$model = PP::model('Customdetails');
			$customDetails = $model->getSubscriptionCustomDetails($this);

			$items[$this->getId()] = $customDetails;
		}

		return $items[$this->getId()];
	}

	/**
	 * Allows remote caller to set preferences for the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setParams($paramsArray = array())
	{	
		$params = $this->getParams();

		foreach ($paramsArray as $key => $value) {
			$params->set($key, $value);
		}

		$this->table->params = $params->toString();
	}

}
