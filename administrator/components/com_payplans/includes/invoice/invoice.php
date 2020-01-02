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

class PPInvoice extends PPAbstract implements PPAppTriggerableInterface, PPDiscountableInterface, PPMaskableInterface, PPApiInvoiceInterface
{
	private $payment = null;
	private $useCache = false;

	public static function factory($id = 0)
	{
		return new self($id);
	}

	public function reset($config = array())
	{
		$this->table->invoice_id = 0;
		$this->table->object_id = 0;
		$this->table->object_type = null;
		$this->table->user_id = 0;
		$this->table->subtotal = 0.00;
		$this->table->total = 0.00;
		$this->table->counter = 0;

		// Load default currency from configuration
		$this->table->currency = $this->config->get('currency');
		$this->table->status = PP_NONE;
		$this->table->created_date = PP::date();
		$this->table->modified_date = PP::date();
		$this->table->checked_out = 0;
		$this->table->checked_out_time	= PP::date();
		$this->table->params = new JRegistry();
		$this->table->paid_date = PP::date('0000:00:00 00:00:00');
		$this->_modifiers = null;

		return $this;
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
	 * Add default addons during first time invoice creation in checkout page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function attachDefaultServices($plan, $includePurchased = false)
	{
		if ($this->config->get('addons_enabled')) {
			// check if this is a the first invoice creation.
			$order = $this->getOrder();
			$firstInvoice = $order->getFirstInvoice();

			if ($firstInvoice) {
				if ($this->getId() == $firstInvoice->getId()) {

					$invoicePlans = $this->getPlans();

					$model = PP::model('addons');
					$defaultAddons = $model->getDefaultServices(array($invoicePlans->getId()));

					if ($includePurchased) {
						$purchasedAddons = $model->getPurchasedServices($this->getId());

						if ($purchasedAddons) {
							foreach ($purchasedAddons as $key => $stat) {
								if (! array_key_exists($key, $defaultAddons)) {
									$addon = PP::addon($stat->planaddons_id);
									$defaultAddons[$key] = $addon;
								}
							}
						}
					}

					if ($defaultAddons) {
						$model->calculateCharges($this, $defaultAddons);
					}
				}
			}
		}
	}

	/**
	 * Function used to check if the current logged user is a valid user or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validateBuyer($id = null)
	{
		$userId = $this->getBuyer()->getId();
		$my = PP::user($id);

		if ($userId != $my->id && !$my->isSiteAdmin()) {
			return false;
		}

		return true;
	}

	/**
	 * Add default addons during first time invoice creation in checkout page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateAddonServices($addonId, $updateType)
	{
		$allowed = array('add', 'remove');

		if (! in_array($updateType, $allowed)) {
			return false;
		}

		$model = PP::model('addons');
		$addon = PP::Addon($addonId);

		$state = true;

		if ($updateType == 'add') {
			$state = $model->addService($this, $addon);
		}

		if ($updateType == 'remove') {
			$state = $model->removeService($this, $addon);
		}

		return $state;
	}

	/**
	 * Loads payment records for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function loadPayment($invoiceId = null)
	{
		if (!$invoiceId) {
			$invoiceId = $this->getId();
		}
		
		$model = PP::model('Payment');
		$options = array('invoice_id' => $invoiceId);
		$rows = $model->loadRecords($options, array('limit'));

		$this->payment = null;

		if ($rows) {
			foreach ($rows as $row) {
				$this->payment = PP::payment($row);
			}
		}

		return $this;
	}

	/**
	 * This loads the payment of invoice if exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function loadModifiers($invoiceId = null)
	{
		static $_cache = array();

		if ($invoiceId === null) {
			$invoiceId = $this->getId();
		}

		if ($this->useCache && isset($_cache[$invoiceId])) {

			$this->_modifiers = $_cache[$invoiceId];
			$total = PPHelperModifier::getTotal($this->getSubtotal(), $this->_modifiers);
			$this->table->total = $total;
			return;
		}

		$this->_modifiers = PPHelperModifier::get(array('invoice_id' => $invoiceId), PP_INSTANCE_REQUIRE);
		$_cache[$invoiceId] = $this->_modifiers;

		$total = PPHelperModifier::getTotal($this->getSubtotal(), $this->_modifiers);

		$this->table->total = $total;

		return $this;
	}

	/**
	 * After binding the data on the invoice, try to load transactions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function afterBind($id = 0)
	{
		if (!$id) {
			return $this;
		}

		if ($this->afterBindLoad) {
			$this->loadModifiers($id);
			$this->loadPayment($id);
		}

		return $this;
	}

	/**
	 * Creates a new payment for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createPayment($appId)
	{
		$payment = PP::payment();

		$payment->user_id = $this->getBuyer()->getId();
		$payment->invoice_id = $this->getId();
		$payment->app_id = $appId;
		$payment->amount = $this->getTotal();
		$payment->currency = $this->getCurrency('isocode');

		$payment->save();

		$this->payment = $payment;

		return $this->payment;
	}

	/**
	 * Determines if there are any transactions associated with this invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasTransaction()
	{
		$transactions = $this->getTransactions();

		if (!$transactions) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the recurring has a free trial
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasRecurringWithFreeTrials()
	{
		if (!$this->isRecurring()) {
			return false;
		}

		$type = $this->getRecurringType();
		$trials = array(PP_PRICE_RECURRING_TRIAL_1, PP_PRICE_RECURRING_TRIAL_2);

		if (in_array($type, $trials)) {
			return true;
		}

		return false;
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
		unset($data['params']);

		$data['key'] = $this->getKey();

		return $data;
	}

	/**
	 * Get the instance of object/object_id attached with current invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReferenceObject()
	{
		$objectType = str_ireplace('PP', '', $this->table->object_type);

		// Backward compactibility.
		// Old data from db still using PayplansOrder object.
		$objectType = str_ireplace('Payplans', '', $objectType);

		if ($this->useCache) {
			$object = call_user_func(array('PP', $objectType));

			$object->setAfterBindLoad(false);
			$object->load($this->table->object_id);
			return $object;
		}

		return call_user_func(array('PP', $objectType), $this->table->object_id);
	}

	/**
	 * Retrieves the total amount on an invoice (including discounts, taxes and other modifiers)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotal($number = 0)
	{
		$total = $this->total;

		if ($number != 0 && $number != $this->getCounter()) {
			$subtotal = $this->getPrice($number);
			$total = PPHelperModifier::getTotalByFrequencyOnInvoiceNumber($this->getModifiers(array('frequency' => PP_MODIFIER_FREQUENCY_EACH_TIME)), $subtotal, $number);
		}

		return PPFormats::price($total);
	}

	/**
	 * Retrieves the subtotal of an invoice (excluding discounts and tax)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubtotal()
	{
		$amount = PPFormats::price($this->subtotal);

		return $amount;
	}

	/**
	 * Retrieves the currency that is attached with the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCurrency($format = null)
	{
		$currency = PPFormats::currency(PP::getCurrency($this->currency), array(), $format);

		return $currency;
	}

	/**
	 * returns the tax amount applied on invoice
	 *
	 * @see PayplansIfaceApiInvoice::getTaxAmount()
	 *
	 * @return integer
	 * @since 2.0
	 */
	public function getTaxAmount()
	{
		$tax = PPHelperModifier::getModificationAmount($this->getSubtotal(), $this->getModifiers(), array(PP_MODIFIER_FIXED_TAX, PP_MODIFIER_PERCENT_TAX));
		return PPFormats::price($tax);
	}

	/**
	 * returns the discount amount applied on invoice
	 *
	 * @see PayplansIfaceApiInvoice::getDiscount()
	 *
	 * @return float  Value of the discount
	 * @since 2.0
	 */
	public function getDiscount()
	{
		$discount = PPHelperModifier::getModificationAmount($this->getSubtotal(), $this->getModifiers(), array(PP_MODIFIER_FIXED_DISCOUNT, PP_MODIFIER_PERCENT_DISCOUNT));
		return PPFormats::price(-$discount);
	}

	/**
	 * The amount of invoice, on this amount discount will be applied.
	 * e.g. Plan amount + Any amount for addons (+/-)
	 *
	 * @see PayplansIfaceApiInvoice::getDiscountable()
	 *
	 * @return double
	 * @since 2.1
	 */
	public function getDiscountable()
	{
		$discount = PPHelperModifier::getModificationAmount($this->getSubtotal(), $this->getModifiers(), array(PP_MODIFIER_FIXED_DISCOUNTABLE, PP_MODIFIER_PERCENT_DISCOUNTABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE));
		return PPFormats::price($discount);
	}


	public function getTaxableAmount()
		{
			$taxable = PPHelperModifier::getModificationAmount($this->getSubtotal(), $this->getModifiers(), array(PP_MODIFIER_PERCENT_TAXABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE));
			return PPFormats::price($taxable);
		}

	/**
	 * returns the invoice amount after applying tax
	 * it will be always positive amount
	 *
	 * @see PayplansIfaceApiInvoice::getNontaxableAmount()
	 *
	 * @return double
	 * @since 2.1
	 */
	public function getNontaxableAmount()
	{
		$tax = PPHelperModifier::getModificationAmount($this->getSubtotal(), $this->getModifiers(), array(PP_MODIFIER_FIXED_NON_TAXABLE, PP_MODIFIER_PERCENT_NON_TAXABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_NON_TAXABLE));
		return PPFormats::price($tax);
	}

	/**
	 * Retrieves the current status of the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Retrieves the css class that is applied on status labels
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStatusLabelClass()
	{
		if ($this->status == PP_INVOICE_CONFIRMED) {
			return 'o-label--warning';
		}

		if ($this->status == PP_INVOICE_PAID) {
			return 'o-label--success';
		}

		if ($this->status == PP_INVOICE_REFUNDED) {
			return 'o-label--danger';
		}

		if ($this->status == PP_NONE && $this->hasTransaction()) {
			return 'o-label--warning';
		}

		return 'o-label--primary';
	}

	/**
	 * Retrieves the current state of the status (string)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStatusName()
	{
		$text = 'COM_PP_NA';

		if ($this->status == PP_INVOICE_CONFIRMED) {
			$text = 'COM_PP_PENDING_PAYMENT';
		}

		if ($this->status == PP_INVOICE_PAID) {
			$text = 'COM_PP_PAID';
		}

		if ($this->status == PP_INVOICE_REFUNDED) {
			$text = 'COM_PP_REFUNDED';
		}

		// If there is no status on the invoice and a transaction is already added, we need to label it as pending payment
		if ($this->status == PP_NONE && $this->hasTransaction()) {
			$text = 'COM_PP_PENDING_PAYMENT';
		}

		return JText::_($text);
	}

	/**
	 * Sets the status of the invoice
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
	 * return the counter of current invoice
	 *
	 * @see PayplansIfaceApiInvoice::getCounter()
	 *
	 * @return integer
	 * @since 2.0
	 */
	public function getCounter()
	{
		return $this->counter;
	}

	/**
	 * Reload modifiers, payment and transaction in current object so that modifiers can be applied
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refresh()
	{
		$id = $this->getId();

		$this->loadModifiers($id);
		$this->loadPayment($id);

		return $this;
	}

	/**
	 * return all the plans attached with reference object for which invoice was created.
	 * (In case of PayPlans, reference object is PayPlansOrder)
	 * (non-PHPdoc)
	 * @see PayplansIfaceDiscountable::getPlans()
	 * @return Array PayplansPlan
	 */
	public function getPlans($instanceRequire = false)
	{
		$refereceObject = $this->getReferenceObject(PP_INSTANCE_REQUIRE);
		if(method_exists($refereceObject, 'getPlans')){
			return $refereceObject->getPlans($instanceRequire);
		}

		return array();
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

		if (!isset($users[$this->user_id])) {
			$users[$this->user_id] = PP::user($this->user_id);
		}

		return $users[$this->user_id];
	}

	/**
	 * Sets the purchaser for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setBuyer($id)
	{
		$this->user_id = $id;
		return $this;
	}

	/**
	 * Retrieves a list of modifiers for an invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getModifiers($filters = array())
	{
		static $_cache = array();

		
		$key = $this->getId();
		$options = array('invoice_id' => $this->getId());

		if ($filters) {
			$options = array_merge($options, $filters);
		}

		if ($this->useCache && isset($_cache[$key])) {
			$modifiers = $_cache[$key];
			PPHelperModifier::getTotal($this->getSubtotal(), $modifiers);

			return $modifiers;
		}

		$model = PP::model('Modifier');
		$modifiers = $model->loadRecords($options);

		if ($modifiers) {
			foreach ($modifiers as &$modifier) {
				$modifier = PP::modifier($modifier);
			}
		}

		$_cache[$key] = $modifiers;

		// we need to run this so that the _modificationOf
		PPHelperModifier::getTotal($this->getSubtotal(), $modifiers);
		return $modifiers;
	}

	/**
	 * Retrieves the current payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayment()
	{
		$this->loadPayment($this->getId());

		return $this->payment;
	}

	/**
	 * Retrieves a list of applicable payment providers for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentProviders()
	{
		$model = PP::model('Gateways');
		$plan = $this->getPlan();
		$gateways = $model->getPaymentGateways($plan);

		// Ensure that there is a payment gateway attached to the plan
		$total = floatval($this->getTotal());

		if (floatval(0) != $total && !$gateways) {
			throw new Exception(JText::_('COM_PAYPLANS_NO_APPLICATION_AVAILABLE_FOR_PAYMENT'));
		}
		return $gateways;
	}

	/**
	 * Retrieves the plan associated with the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlan()
	{
		$order = $this->getOrder();
		$plan = $order->getPlan();

		return $plan;
	}

	/**
	 * Retrieves the subscription associated with the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscription()
	{
		$order = $this->getOrder();
		$subscription = $order->getSubscription();

		return $subscription;
	}

	/**
	 * Retrieves a list of transactions tied to the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTransactions($reload = false)
	{
		static $cache = array();

		$key = $this->getId();

		if (!isset($cache[$key]) || $reload) {

			$model = PP::model('Transaction');
			$data = $model->getItemsWithoutState(array('invoice_id' => $this->getId()));

			$transactions = array();

			if ($data) {
				foreach ($data as $row) {
					$transaction = PP::transaction($row);

					$transactions[] = $transaction;
				}
			}

			$cache[$key] = $transactions;
		}

		return $cache[$key];
	}

	/**
	 * Retrieves the latest transaction for invoice that has amount associated with it
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLatestTransactionWithAmount()
	{
		$model = PP::model('Transaction');
		$transaction = $model->getLatestTransactionWithAmount($this->getId());

		return $transaction;
	}

	/**
	 * Determines if the current viewer can view the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canView($userId)
	{
		$viewer = PP::user($userId);
		$purchaser = $this->getBuyer();

		// Get user id from session as the user may have just registered for a new account
		$id = PP::getUserIdFromSession();

		if ($viewer->isSiteAdmin() || $viewer->getId() == $purchaser->getId() || $id == (int)$purchaser->getId()) {
			return true;
		}

		return false;
	}

	/**
	 * Confirms the invoice and create the necessary payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirm($appId)
	{
		// If the invoice has 0 value because of discounts or if the plan is free, we do not need to create
		// the payment.
		if ($this->isFree()) {
			$this->table->status = PP_INVOICE_CONFIRMED;
			$this->save();

			$app = JFactory::getApplication();

			if ($app->isAdmin()) {
				return $this;
			}
			
			// Create a transaction
			$transaction = PP::transaction();
			$transaction->user_id = $this->getBuyer()->getId();
			$transaction->invoice_id = $this->getId();
			$transaction->payment_id = 0;
			$transaction->message = 'COM_PAYPLANS_TRANSACTION_OF_FREE_SUBSCRIPTION';
			$transaction->save();

			return $this;
		}

		// Create a new payment for this invoice
		$payment = $this->createPayment($appId);

		// Whatever the previous status,always save the invoice
		// Otherwise some extra details entered by users won't be updated
		$this->table->status = PP_INVOICE_CONFIRMED;
		$this->save();

		return $this;
	}

	/**
	 * Modifies a param of the invoice, creating it if it does not already exist.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setParam($key, $value)
	{
		if ($key == 'recurrence_count' && $value < 0) {
			$value = 0;
		}

		$params = $this->getParams();
		$params->set($key, $value);

		$this->params = $params->toString();

		return $this;
	}

	/**
	 * Retrieve the params object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParams()
	{
		static $items = array();

		if (!isset($items[$this->getId()])) {
			$items[$this->getId()] = new JRegistry($this->params);
		}

		return $items[$this->getId()];
	}

	/**
	 * Returns a param of the invoice object or the default value if the key is not set.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParam($key,$default=null)
	{
		$params = $this->getParams();

		return $params->get($key, $default);
	}

	/**
	 * Gets the title of the invoice set in the parameters
	 *
	 * @see PayplansIfaceApiInvoice::getTitle()
	 *
	 * @return string  Title of the invoice
	 */
	public function getTitle()
	{
		$params = $this->getParams();
		return $params->get('title', JText::_('COM_PAYPLANS_DEFAULT_TITLE'));
	}

	/**
	 * Retrieves the recurring type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurringType($string = false)
	{
		$params = $this->getParams();
		$type = $params->get('expirationtype', 'forever');

		if ($string) {
			return $type;
		}

		if ($type == 'recurring') {
			return PP_PRICE_RECURRING;
		}

		if ($type == 'recurring_trial_1') {
			return PP_PRICE_RECURRING_TRIAL_1;
		}

		if ($type == 'recurring_trial_2') {
			return PP_PRICE_RECURRING_TRIAL_2;
		}

		return false;
	}

	/**
	 * Generates the permalink for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $args = '')
	{
		$link = 'index.php?option=com_payplans&view=invoice&tmpl=component&invoice_key=' . $this->getKey();

		if ($args) {
			$link .= '&' . $args;
		}

		$link = JRoute::_($link, $xhtml);
		return $link;
	}

	/**
	 * Generates the download link for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDownloadLink($xhtml = true)
	{
		$link = 'index.php?option=com_payplans&view=invoice&layout=download&invoice_key=' . $this->getKey();
		$link = JRoute::_($link, $xhtml);

		return $link;
	}

	/**
	 * Generates the permalink for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPrintLink($xhtml = true)
	{
		return $this->getPermalink(true, 'print=1');
	}

	/**
	 * Determines if payment is really needed for this invoice
	 *
	 * @since   4.0.3
	 * @access  public
	 */
	public function isPaymentNeeded()
	{
		return $this->getTotal() > 0.00 || $this->isRecurring();
	}

	/**
	 * Retrieves the price on the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPrice($number = 1)
	{
		$counter = $this->getCounter();

		if ($number >= $counter) {
			$number = $number - $counter + 1;
		}

		$type = $this->isRecurring();

		if (in_array($type, array(PP_RECURRING_TRIAL_1, PP_RECURRING_TRIAL_2)) && $number == 1){
			$priceParam = 'trial_price_1';
		} elseif($type == PP_RECURRING_TRIAL_2 && $number == 2){
			$priceParam = 'trial_price_2';
		} else{
			$priceParam = 'price';
		}

		$params = $this->getParams();

		return PPFormats::price($params->get($priceParam, 0.00));
	}

	/**
	 * Determines if the invoice has been refunded
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRefunded()
	{
		return $this->getStatus() == PP_INVOICE_REFUNDED;
	}

	/**
	 * Displays refund button
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRefundable()
	{
		$order = $this->getReferenceObject();
		$order = ($order instanceof PPOrder)? $order : null;

		if (!$order) {
			return false;
		}

		$order->toggleUseCache();
		$subscription = $order->getSubscription();

		if (!$subscription instanceof PPSubscription) {
			return false;
		}

		$masterInvoice = $order->getLastMasterInvoice();

		if ($masterInvoice instanceOf PPInvoice) {

			if ($this->useCache) {
				$masterInvoice->toggleUseCache();
			}

			$masterInvoiceId = $masterInvoice->getId();
			$payment = $masterInvoice->getPayment();

			// if payment not available then do nothing
			if (!$payment) {
				return false;
			}			

			$app = $payment->getApp();

			if (($app instanceof PPAppPayment) && $app->supportForRefund() && $subscription->isActive()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if the invoice is a recurring invoice
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
		// $type = $params->get('expirationtype', 'forever');

		// if ($type == 'recurring') {
		// 	return PP_PRICE_RECURRING;
		// }

		// if ($type == 'recurring_trial_1') {
		// 	return PP_PRICE_RECURRING_TRIAL_1;
		// }

		// if ($type == 'recurring_trial_2') {
		// 	return PP_PRICE_RECURRING_TRIAL_2;
		// }

		// return false;
	}

	/**
	 * Determines if the invoice is marked as confirmed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isConfirmed()
	{
		return $this->getStatus() == PP_INVOICE_CONFIRMED;
	}

	/**
	 * Determines if the invoice is considered as free
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isFree()
	{
		$total = floatval($this->getTotal());

		if ($total == PP_ZERO && !$this->isRecurring()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the invoice is marked as paid
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPaid()
	{
		return $this->getStatus() == PP_INVOICE_PAID;
	}

	/**
	 * Retrieves the expiration time of an invoice
	 *
	 * Invoice has expiration time of its own.
	 * Initially its copied from subscription parameter and can be changed later
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExpiration($for = PP_PRICE_FIXED, $raw = false)
	{

		if ($for === PP_PRICE_RECURRING_TRIAL_1) {
			$rawTime = 'trial_time_1';
		} else if ($for === PP_PRICE_RECURRING_TRIAL_2) {
			$rawTime = 'trial_time_2';
		} else {
			$rawTime = 'expiration';
		}

		$params = $this->getParams();

		if ($raw == true) {
			return $params->get($rawTime, '000000000000');
		}

		return PPHelperPlan::convertIntoTimeArray($params->get($rawTime, '000000000000'));
	}

	/**
	 * Gets the expiration type of the invoice
	 * @return string fixed / recurring / recurring_trial_1 / recurring_trial_2 / forever
	 */
	public function getExpirationType()
	{
		return $this->getParams()->get('expirationtype', 'fixed');
		
	}

	/**
	 * Return current invoice expiration time
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCurrentExpiration($raw = false)
	{
		$invoiceNumber = $this->getCounter();
		$type =  $this->isRecurring();

		// in case of recurring trial to, still current expiration time should be trial time 1
		// trial time 2 is copied to next invoice's trial 1, so no need to return trial time 2
		$rawTime = 'expiration';

		if ($type === PP_RECURRING_TRIAL_1 || $type === PP_RECURRING_TRIAL_2) {
			$rawTime = 'trial_time_1';
		}

		$params = $this->getParams();

		if ($raw == true) {
			return $params->get($rawTime, '000000000000');
		}

		return PPHelperPlan::convertIntoTimeArray($params->get($rawTime, ''));
	}

	/**
	 * Retrieves the recurrence count of the invoice.
	 *
	 * i.e: How many times payment needs to be done.
	 *
	 * 0 - Represents lifetime
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceCount()
	{
		$params = $this->getParams();

		return $params->get('recurrence_count');
	}

	/**
	 * Gets the last modification date of the invoice
	 *
	 * @return object  XiDate
	 */
	public function getModifiedDate()
	{
		return $this->modified_date;
	}

	/**
	 * Retrieves the object type of the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getObjectType()
	{
		return $this->object_type;
	}

	/**
	 * Retrieves the order associated with the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOrder()
	{
		if ($this->useCache) {
			$order = PP::order()->setAfterBindLoad(false);
			$order->load($this->getObjectId());
			return $order;
		}

		$order = PP::order($this->getObjectId());
		return $order;
	}

	/**
	 * Retrieve the main invoice for recurring invoice
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getMainInvoice()
	{
		static $mainInvoice = array();

		if (!isset($mainInvoice[$this->getId()])) {
			$mainInvoice[$this->getId()] = false;

			if ($this->isRecurring()) {
				$toggleCache = false;
				if ($this->useCache) {
					$this->toggleUseCache(false);
					$toggleCache = true;
				}

				$order = $this->getOrder();
				$this->toggleUseCache($toggleCache);

				$mainInvoice[$this->getId()] = $order->getInvoice();
			}
		}

		return $mainInvoice[$this->getId()];
	}

	/**
	 * Gets the creation date of the invoice
	 *
	 * @return object  XiDate
	 */
	public function getCreatedDate()
	{
		return $this->created_date;
	}

	/**
	 * Gets the object id of the invoice
	 *
	 * Object id is the identifier which has created invoice
	 *
	 * @return integer
	 */
	public function getObjectId()
	{
		return $this->object_id;
	}

	/**
	 * Gets the regular amount including tax and discount
	 *
	 * In terms of recurring, Regular amount is the one
	 * which will be charged regularly after all the applicable trials
	 *
	 * @return float
	 */
	public function getRegularAmount()
	{
		$recurring = $this->isRecurring();

		if($recurring){
			$counter = $this->getCounter();

			if($recurring == PP_RECURRING_TRIAL_2){
				$regularAmount = $this->getTotal($counter+2);
			}

			elseif($recurring == PP_RECURRING_TRIAL_1){
				$regularAmount = $this->getTotal($counter+1);
			}

			else {
				$regularAmount = $this->getTotal();
			}
		}

		else {
			$regularAmount = $this->getTotal();
		}

		return $regularAmount;
	}

	/**
	 * Cancels the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function terminate()
	{
		$payment = $this->getPayment();

		// Trigger apps
		$args = array($payment, $this);

		$output = PP::event()->trigger('onPayplansPaymentTerminate', $args, 'payment', $payment);

		return $output;
	}

	/**
	 * Request the payment from payment gateway
	 *
	 * @param   integer $invoiceCount  Invoice counter specifies the invoice number to be processed further
	 * @return  mixed  value returned from payment gateway app after processing recurring cycle
	 */
	public function requestPayment($invoiceCount = 1)
	{
		//XITODO : Generate Error logs for proper debugging.
		$payment = $this->getPayment();
		if(!isset($payment) || empty($payment)){
			return true;
		}
		$instance = $payment->getApp(PP_INSTANCE_REQUIRE);
		if(method_exists($instance, 'processPayment')){
			$instance->processPayment($this->getPayment(), $invoiceCount);

			//if payment is done through payment gateway then always returen true
			return true;
		}

		// error in processing
		return false;
	}

	/**
	 * Adds a transaction on the invoice
	 *
	 * 1. Finds payment gateway for invoice (default to admin payment)
	 * 2. Adds a transaction on the payment record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addTransaction($parameters = '')
	{
		$payment = $this->getPayment();

		// Use default adminpay payment method
		if (!$payment) {

			$table = PP::table('App');
			$exists = $table->load(array('type' => 'adminpay'));

			// Make sure adminpay plugin is installed and enabled from Payplans 3.3 adminpay is available as installable plugin
			if (!$exists || !$table->app_id) {
				throw new Exception(JText::_('COM_PAYPLANS_INVOICE_ADD_TRANSACTION_PAYMENT_GATEWAY_NOT_FOUND'));
			}

			$payment = $this->createPayment($table->app_id);
		}

		$transactionParams = array(
			'invoice_id' => $this->getId(),
			'user_id' => (int) $this->getBuyer()->getId(),
			'amount' => $this->getTotal(),
			'payment_id' => $payment->getId(),
			'message' => 'COM_PAYPLANS_TRANSACTION_ADDED_FOR_INVOICE',
			'params' => ''
		);

		$transaction = PP::transaction();

		foreach ($transactionParams as $key => $value) {
			$transaction->$key = isset($parameters->$key) ? $parameters->$key : $value;

			// For params column, we need the json string
			if ($key == 'params') {
				$data = isset($parameters->$key) ? $parameters->$key : $value;

				$transaction->$key = is_array($data) ? json_encode($data) : $data;
			}
		}

		$transaction->save();

		return $transaction;
	}

	/**
	 * Add modifier by considering the given params
	 *
	 * 1. Create Modifier as per params
	 * 2. Attach to the current invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addModifier($data = '')
	{
		$columns = array(
						'message' => JText::_('COM_PAYPLANS_INVOICE_FREE_COMPLETION'),
						'invoice_id' => $this->getId(),
						'user_id' => $this->getBuyer()->getId(),
						'type' => 'admin-discount',
						'amount' => 0.00,
						'reference' => 'admin-discount',
						'percentage' => false,
						'frequency' => PP_MODIFIER_FREQUENCY_ONE_TIME,
						'serial' => PP_MODIFIER_FIXED_DISCOUNT
		);

		$modifier = PP::modifier();

		foreach ($columns as $key => $value) {

			if (!isset($data->$key)) {
				$modifier->$key = $value;
				continue;
			}

			$modifier->$key = $data->$key;
		}


		$modifier->save();

		$this->refresh();

		return $modifier;
	}

	/**
	 * Retrieves the serial number for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSerial()
	{
		if (is_null($this->serial)) {
			return JText::_('COM_PP_NA');
		}

		return $this->serial;
	}

	public function getPaidDate()
	{
		if ($this->paid_date == '0000-00-00 00:00:00') {
			return JText::_('COM_PAYPLANS_NEVER');
		}

		return $this->paid_date;
	}

	/**
	 * Generate content for pdf
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPdfContent($invoices)
	{
		// Load the dompdf lib
		require_once(dirname(__FILE__) . '/dompdf/autoload.inc.php');

		$contents = '';

		foreach ($invoices as $invId) {

			$invoice = PP::invoice($invId);

			$buyer = $invoice->getBuyer();

			$modifier = PP::modifier();

			$discountablesSerials = array(PP_MODIFIER_FIXED_DISCOUNTABLE, PP_MODIFIER_PERCENT_DISCOUNTABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE);
			$discountables = $invoice->getModifiers(array('serial' => $discountablesSerials, 'invoice_id' => $invoice->getId()));
			// $discountables = $modifier->rearrange($discountables);

			$nonTaxesSerials = array(PP_MODIFIER_FIXED_NON_TAXABLE, PP_MODIFIER_PERCENT_NON_TAXABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_NON_TAXABLE);
			$nonTaxables = $invoice->getModifiers(array('serial' => $nonTaxesSerials, 'invoice_id'=>$invoice->getId()));
			// $nonTaxables = $modifier->rearrange($nonTaxables);

			$taxableSerials = array(PP_MODIFIER_PERCENT_TAXABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE);
			$taxables = $invoice->getModifiers(array('serial'=> $taxableSerials, 'invoice_id'=>$invoice->getId()));
			// $taxables = $modifier->rearrange($taxables);

			$digitAfterDecimal = $this->config->get('fractionDigitCount');
			$separator = $this->config->get('price_decimal_separator');

			$total = round($invoice->getTotal(), $digitAfterDecimal);
			$amount = number_format($total, $digitAfterDecimal, $separator, '');

			$modifiers = $invoice->getModifiers();
			// $modifiers = $modifier->rearrange($modifiers);

			$discountablesSerials = array_merge($discountablesSerials, array(PP_MODIFIER_FIXED_DISCOUNT,PP_MODIFIER_PERCENT_DISCOUNT));
			$taxableSerials = array_merge($taxableSerials, array(PP_MODIFIER_FIXED_TAX,PP_MODIFIER_PERCENT_TAX));

			$payment = $invoice->getPayment();

			$theme = PP::themes();
			$theme->set('invoice', $invoice);
			$theme->set('user', $buyer);
			$theme->set('discountables', $discountables);
			$theme->set('nonTaxables', $nonTaxables);
			$theme->set('taxables', $taxables);
			$theme->set('amount', $amount);
			$theme->set('modifiers', $modifiers);
			$theme->set('discountablesSerials', $discountablesSerials);
			$theme->set('taxableSerials', $taxableSerials);
			$theme->set('nonTaxesSerials', $nonTaxesSerials);
			$theme->set('config', PP::config());
			$theme->set('payment', $payment);
			$contents .= $theme->output('site/invoice/pdf_content');
		}

		$theme = PP::themes();
		$theme->set('contents', $contents);
		$output = $theme->output('site/invoice/pdf');

		$pdf = new Dompdf\Dompdf();
		$pdf->set_paper("a4", "portrait");
		$pdf->set_option("isHtml5ParserEnabled", true);
		$pdf->load_html($output);
		$pdf->render();

		return $pdf;
	}

	/**
	 * Updates the purchaser
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updatePurchaser($user)
	{
		// Update the buyer
		$this->setBuyer($user->getId());
		$this->save();

		// Update the modifiers
		$modifiers = $this->getModifiers();

		if ($modifiers) {
			foreach ($modifiers as $modifier) {
				$modifier->user_id = $user->getId();
				$modifier->save();
			}
		}

		// Update the order
		$order = $this->getOrder();
		$order->setBuyer($user->getId());
		$order->save();

		// Update the subscription
		$subscription = $order->getSubscription();
		$subscription->setBuyer($user->getId());
		$subscription->save();

		return true;
	}

	/**
	 * Retrieve the new serial number
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function setNewSerial()
	{
		$serialFormate = $this->config->get('expert_invoice_serial_format', '[[number]]');
		$lastCounter = $this->config->get('expert_invoice_last_serial', 0);

		$lastCounter++;

		$invoicePaidOn = PP::date($this->paid_date);

		$paidYear = PPFormats::date($invoicePaidOn, '%Y');
		$paidMonth = PPFormats::date($invoicePaidOn, '%m');
		$paidDate = PPFormats::date($invoicePaidOn, '%d');
		$paidDay = PPFormats::date($invoicePaidOn, '%A');

		$search  = array('[[number]]', '[[date]]', '[[month]]', '[[year]]','[[day]]');
		$replace = array($lastCounter, $paidDate, $paidMonth, $paidYear, $paidDay);

		$newSerial = str_replace($search, $replace, $serialFormate);

		if (strstr($serialFormate , '[[number]]')) {
			$model = PP::model('config');
			$model->save(array('expert_invoice_last_serial' => $lastCounter));

			// need to also update the local cache copy too.
			$this->config->set('expert_invoice_last_serial', $lastCounter);
		}

		$this->serial = $newSerial;
	}

	/**
	 * Check Invoice id for renewal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRenewalInvoice()
	{
		$order = $this->getReferenceObject();
		if (($order instanceof PayplansOrder) == false) {
			return false;
		}

		$subscription = $order->getSubscription();

		if (!$subscription->isRenewal()) {
			return false;
		}
		
		if ($order->getFirstInvoice() == $this->getId()) {
			return false;
		}
		
		$payment = $this->getPayment();
		if (($payment instanceof PayplansPayment) == false) {
			
			// Check for free plan
			$total = $this->getTotal();
			if ( number_format($total, 2) == 0.00 ) {
				return true;
			}
			return false;
		}
		
		return true;
	}

	/**
	* Check same user can not use his own referral code
	*
	* @since 4.0.0
	* @access  public
	*/
	public function isReferralApplicable()
	{
		$userId = $this->getBuyer()->getId();

		// get user referral code
		$userReferralCode = PP::getKeyFromID($userId);

		// remove referral discount from invoice
		$options = array('type' => 'referral', 'invoice_id' => $this->getId());
		$modifiers = $this->getModifiers($options);
		$modifier = array_pop($modifiers);

		if (!$modifier) {
			return true;
		}

		$referralCode = $modifier->getReference();

		if ($referralCode == $userReferralCode) {
			if ($modifier) {
				// Delete existing referral modifier
				$model = PP::model('modifier');
				$model->deleteTypeModifiers($this->getId(), 'referral');

				return false;
			}
		}

		return true;
	}

}