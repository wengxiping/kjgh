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

class PPOrder extends PPAbstract implements PPAppTriggerableInterface, PPMaskableInterface
{
	protected $subscription = null;
	protected $invoices = null;
	private $useCache = false;

	public static function factory($id = null)
	{
		return new self($id);
	}

	/**
	 * Resets the data from the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reset($options = array())
	{
		$this->table->order_id = 0;
		$this->table->buyer_id = 0;
		$this->table->total = 0.0000;

		// Load default currency from configuration
		$this->table->currency = $this->config->get('currency');
		$this->table->status = PP_SUBSCRIPTION_NONE;

		$this->table->params = new JRegistry();

		//XITODO : Is it ok to store current timestamp ?
		$this->table->created_date = PP::date();


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
	 * Loads a subscription to associate with the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function loadSubscription($id = null, $debug = false, $reload = false)
	{
		static $cache = array();

		if (!$id) {
			$id = $this->getId();
		}

		if (!isset($cache[$id]) || $reload) {
			$model = PP::model('Subscription');
			$items = $model->loadRecords(array('order_id' => $id));
			$item = array_shift($items);

			$cache[$id] = $item;
		}

		$this->subscription = null;

		$item = $cache[$id];

		if (!$item) {
			return $this;
		}

		$subscription = PP::subscription();
		if ($this->useCache) {
			$subscription->setAfterBindLoad(false);
			$subscription->toggleUseCache();
		}

		$subscription->bind($item);

		$this->subscription = $subscription;
		$this->table->total = $this->subscription->getPrice(PP_PRICE_FIXED, $debug);

		if ($debug) {
			// dump('yay');
		}
		return $this;
	}

	/**
	 * Loads invoices to associate with the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function loadInvoices($id = null)
	{
		if ($this->getId()) {
			$id = $this->getId();
		}

		$options = array('object_id' => $id);
		$model = PP::model('Invoice');
		$rows = $model->loadRecords($options);

		$this->invoices = array();

		if ($rows) {
			foreach ($rows as $row) {

				$invoice = PP::invoice();

				if ($this->useCache) {
					$invoice->setAfterBindLoad(false);
					$invoice->toggleUseCache();
				}

				$invoice->bind($row);

				$this->addInvoice($invoice);
			}
		}

		return $this;
	}

	public function afterBind($id = 0)
	{
		if (!$id) {
			return $this;
		}

		if ($this->afterBindLoad) {
			return $this->loadSubscription($id)->loadInvoices($id);
		}

		return $this;
	}

	/**
	 * Adding of invoice into the object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addInvoice(PPInvoice $invoice)
	{
		// save it on payment list
		$this->invoices[$invoice->getId()] = $invoice;

		return $this;
	}

	/**
	 * Retrieves invoices attached to the current order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getInvoices($status = null, $reload = null)
	{
		static $loaded = null;

		if (is_null($this->invoices) || $reload) {
			$this->loadInvoices();
		}

		if ($status && $this->invoices) {

			if (!is_array($status)) {
				$status = array($status);
			}

			$invoices = array();

			foreach ($this->invoices as $invoice) {

				if (in_array($invoice->getStatus(), $status)) {
					$invoices[$invoice->getId()] = $invoice;
				}
			}

			return $invoices;
		}

		return $this->invoices;
	}

	/**
	 * Gets the invoice attached on the order with specified counter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getInvoice($counter = null)
	{
		$invoices = $this->getInvoices();

		if ($counter == null) {
			return array_shift($invoices);
		}

		foreach ($invoices as $invoice) {
			if ($invoice->getCounter() == $counter) {
				return $invoice;
			}
		}

		// no invoice exists with this number
		return false;
	}

	/**
	 * Creates the first invoice for the order
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function createFirstInvoice(PPSubscription $subscription)
	{
		$invoice = PP::invoice();
		$invoice->table->object_id = $this->getId();
		$invoice->table->object_type = get_class($this);
		$invoice->table->user_id = $this->getBuyer()->getId();
		$invoice->table->counter = 1;
		$invoice->table->currency = $this->getCurrency('isocode');
		$invoice->table->subtotal = $subscription->getPriceForInvoice(1);

		$subscriptionParams = $subscription->getParams();
		$paramProperties = array('expirationtype', 'expiration', 'recurrence_count', 'price', 'trial_price_1', 'trial_time_1', 'trial_price_2', 'trial_time_2');

		$params = new JRegistry();
		$params->set('title', $subscription->getTitle());

		foreach ($paramProperties as $property) {
			$params->set($property, $subscriptionParams->get($property));
		}

		$invoice->table->params = $params->toString();

		return $invoice;
	}

	/**
	 * Creates a child invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function createChildInvoice($invoiceCount)
	{
		$invoice = PP::invoice();
		$invoice->object_id = $this->getId();
		$invoice->object_type = 'PPOrder';
		$invoice->user_id = $this->getBuyer()->getId();
		$invoice->counter = $invoiceCount + 1;
		$invoice->currency = $this->getCurrency('isocode');

		// Get the last invoice
		$masterInvoice = $this->getInvoice($invoiceCount);

		if(!($masterInvoice instanceof PPInvoice)) {
			return false;
		}

		$amount = $masterInvoice->getPrice($invoiceCount + 1);
		$params = $masterInvoice->getParams();

		$invoice->subtotal = $amount;
		$invoice->params = $params->toString();
		$recurring = $masterInvoice->isRecurring();

		if ($recurring) {
			// XITODO : use Data structure instead of of if else
			// like load the expiration type and then remve the frist element, and use next element
			// like trial_time_11, 10,9,8,7,6....and so on
			$expirationType = 'recurring';

			if ($recurring == PP_PRICE_RECURRING_TRIAL_2) {
				$expirationType = 'recurring_trial_1';

				$invoice->setParam('trial_price_1', $masterInvoice->getParam('trial_price_2', '0.00'));
				$invoice->setParam('trial_time_1', $masterInvoice->getParam('trial_time_2', '000000000000'));
			}

			$invoice->setParam('expirationtype', $expirationType);
		}

		$invoice->setParam('title', $this->getTitle());

		return $invoice;
	}

	/**
	 * Determines if an order can be updated
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateable()
	{
		if (in_array($this->getStatus(), array(PP_ORDER_HOLD, PP_ORDER_CANCEL, PP_ORDER_EXPIRED))) {
			return false;
		}

		return true;
	}

	/**
	 * Creates a new invoice for an order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createInvoice($subscription = null)
	{
		$invoices = $this->getInvoices(array(PP_INVOICE_PAID, PP_INVOICE_REFUNDED));
		$totalInvoice = count($invoices);
		// First invoice
		if ($totalInvoice <= 0) {

			if (is_null($subscription)) {
				$subscription = $this->getSubscription();
			}

			$invoice = $this->createFirstInvoice($subscription);
		} else {
			sort($invoices);

			// Get the last invoice
			$lastInvoice = array_pop($invoices);
			$invoiceCount = $lastInvoice->getCounter();

			$invoice = $this->createChildInvoice($invoiceCount);

			if (!($invoice instanceof PPInvoice)) {
				return false;
			}
		}

		// Total need to be updated
		$invoice->refresh();
		$invoice->save();

		// Once invoice is created, we need to determine if this is the first invoice
		if (!$this->getFirstInvoice()) {

			$params = $this->getParams();
			$params->set('first_invoice_id', $invoice->getId());
			$params->set('last_master_invoice_id', $invoice->getId());

			$this->table->params = $params->toString();
		}

		$this->save();

		// Explicitly refreshed the already cached object.
		PP::order($this->getId())->refresh();

		return $invoice;
	}

	/**
	 * Creates a blank invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function createEmptyInvoice()
	{
		$invoiceCount = count($this->getInvoices(array(PP_INVOICE_PAID, PP_INVOICE_REFUNDED)));

		// $invoice count should be incremented by one
		$invoiceCount++;

		$invoice = PP::invoice();

		$invoice->object_id = $this->getId();
		$invoice->object_type = get_class($this);
		$invoice->user_id = $this->getBuyer()->id;
		$invoice->counter = $invoiceCount;
		$invoice->currency = $this->getCurrency('isocode');

		$params = new JRegistry();
		$params->set('expirationtype', 'None');
		$params->set('expiration', '000000000000');
		$params->set('recurrence_count', 0);
		$params->set('price', '0.00');
		$params->set('trial_price_1', '0.00');
		$params->set('trial_time_1', '000000000000');
		$params->set('trial_price_2', '0.00');
		$params->set('trial_time_2', '000000000000');
		$params->set('title', JText::_('COM_PAYPLANS_DEFAULT_TITLE'));

		$invoice->params = $params->toString();

		return $invoice;
	}

	/**
	 * Load all the subscription and invoices attached on the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refresh($debug = false)
	{
		$this->loadSubscription($this->getId(), $debug);
		$this->loadInvoices($this->getId(), $debug);

		return $this;
	}

	/**
	 * Determines if the order is confirmed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isConfirmed()
	{
		return $this->getStatus() == PP_ORDER_CONFIRMED;
	}

	/**
	 * Determines if the order is unconfirmed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUnconfirmed()
	{
		return $this->getStatus() == PP_NONE;
	}

	/**
	 * Determines if the order is cancelled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isCancelled()
	{
		return $this->getStatus() == PP_ORDER_CANCEL;
	}

	/**
	 * Determines if the order expired
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isExpired()
	{
		return $this->getStatus() == PP_ORDER_EXPIRED;
	}

	/**
	 * Determines if the order is on hold status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isHold()
	{
		return $this->getStatus() == PP_ORDER_HOLD;
	}

	/**
	 * Determines if the order is a recurring subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRecurring()
	{
		return $this->getSubscription()->isRecurring();
	}

	/**
	 * Determines if the order is completed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isCompleted()
	{
		return $this->getStatus() == PP_ORDER_COMPLETE;
	}

	/**
	 * Renew the subscription. Extend the subscription time to the specified time
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renewSubscription($expiration)
	{
		$subscription = $this->getSubscription(true);

		// When there is no subscription exists
		if (empty($subscription)) {
			return $this;
		}

		$this->subscription->renew($expiration);

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

		if (!isset($users[$this->buyer_id])) {
			$users[$this->buyer_id] = PP::user($this->buyer_id);
		}

		return $users[$this->buyer_id];
	}

	/**
	 * Saves the order via the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		parent::save();

		$this->updatePlanStatus();

		return $this;
	}

	/**
	 * Update the status of associated plan to this order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updatePlanStatus()
	{
		if ($this->getStatus() == PP_ORDER_COMPLETE) {
			$plan = $this->getPlan();
			$plan->updateSubscriberCount('add');
		}
	}

	/**
	 * Sets the purchaser for the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setBuyer($userId = 0)
	{
		$this->table->buyer_id = $userId;

		return $this;
	}

	/**
	 * Sets an order as expired
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setExpired()
	{
		$this->setStatus(PP_ORDER_EXPIRED);

		return $this->save();
	}

	/**
	 * Gets the status of the Order
	 *
	 * @see PayplansIfaceApiOrder::getStatus()
	 *
	 * @return integer  The order status
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Sets the status of the order
	 *
	 * @param integer  $status   The value of the status
	 *
	 *  @return object PayplansOrder
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Gets the status name
	 * @return string  The order status name
	 */
	public function getStatusName()
	{
		return JText::_('COM_PAYPLANS_STATUS_'.$this->status);
	}

	/**
	 * Gets the total of the order
	 *
	 * Order total is exclusive of discount or
	 * tax or any other kind of amount modification.
	 *
	 * @see PayplansIfaceApiOrder::getTotal()
	 *
	 * @return float  Total of the order
	 */
	public function getTotal()
	{
		return PPFormats::price($this->total);
	}

	/**
	 * Retrieves the plan associated with the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlan()
	{
		$subscription = $this->getSubscription();

		if ($subscription) {
			return $subscription->getPlan();	
		}
		

		return array();
	}

	/**
	 * Implementing interface Apptriggerable
	 * @return array
	 */
	public function getPlans($requireInstance = false)
	{
		if ($this->subscription !== null) {
			return $this->getSubscription()->getPlans($requireInstance);
		}

		return array();
	}

	/**
	 * Retrieves the subscription of the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscription($reload = false)
	{
		if (!$this->subscription || $reload) {
			$this->loadSubscription(null, false, true);
		}

		return $this->subscription;
	}

	/**
	 * Gets the currency of the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCurrency($format = null)
	{
		return PPFormats::currency(PP::getCurrency($this->currency), array(), $format);
	}


	/**
	 * Gets the created date of the order
	 *
	 * @see PayplansIfaceApiOrder::getCreatedDate()
	 *
	 * @return object XiDate
	 */
	public function getCreatedDate()
	{
		return $this->created_date;
	}

	/**
	 * Injects into the orders params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setParam($key, $value)
	{
		$params = $this->getParams();
		$params->set($key, $value);

		$this->table->params = $params->toString();

		return $this;
	}

	/**
	 * Returns a key of the order object or the default value if the key is not set.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParam($key, $default = null)
	{
		$params = $this->getParams();

		return $params->get($key, $default);
	}

	/**
	 * Gets the title of the order (title of the subscription attached with the order)
	 *
	 * @return string  Title
	 */
	public function getTitle()
	{
		return $this->getSubscription()->getTitle();
	}

	/**
	 * Gets the expiration time from the attached subscription record
	 *
	 * Order does not have any expiration time of its owm.
	 * Its attached subscription has expiration time and type related data.
	 *
	 * @param   integer $for An integer constant indicating expiration type
	 *
	 * @return  array   An array containing expiration values for year, month, day and so on
	 */
	public function getExpiration($for = PAYPLANS_SUBSCRIPTION_FIXED )
	{
		return $this->getSubscription()->getExpiration($for);
	}

	/**
	 * Gets the recurrence count of the order
	 *
	 * @return integer  Recurrence count value of the subscription attached with the order
	 */
	public function getRecurrenceCount()
	{
		// XITODO: handle when no subscription exists for all the functions
		return $this->getSubscription()->getRecurrenceCount();
	}

	/**
	 * Retrieves a list of tokens available for token rewriting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRewriterTokens()
	{
		// We do not generate any data for orders in token rewriting
		return false;
	}

	/**
	 * Gets the price of the order
	 * @see components/com_payplans/libraries/iface/PayplansIfaceOrderable::getPrice()
	 *
	 * If type is not set then return the regular/normal price
	 * if it is set to RECURRING_TRIAL_1 then return first trial price
	 * if it is set to RECURRING_TRIAL_2 then return second trial price
	 *
	 * @param integer $for  A constant indicating expiration type
	 *
	 * @return float  Value of the price
	 */
	public function getPrice($for = PAYPLANS_SUBSCRIPTION_FIXED)
	{
		$subscription = $this->getSubscription();
		//when there is no subscription exists
		if(!$subscription){
			return false;
		}

		return $subscription->getPrice($for);
	}

	/**
	 * Refund the subscription amount and mark order on Hold
	 *
	 * @return object PayplansOrder
	 */
	public function refund()
	{
		$subscription = $this->getSubscription();
		$subscription->setStatus(PP_SUBSCRIPTION_HOLD);
		$subscription->save();

		return $this;
	}

	/**
	 * Cancels a recurring subscription order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function terminate()
	{
		$invoice = $this->getLastMasterInvoice();

		//if last_master_invoice id is not set
		if (!$invoice) {
			$invoice = $this->getFirstInvoice();
		}

		if (!$invoice) {
			$invoices = $this->getInvoices();
			$invoice = array_shift($invoices);
		}

		if (!$invoice) {
			return false;
		}

		return $invoice->terminate();
	}

	/**
	 * Gets the first invoice id on which payment has been received for the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFirstInvoice()
	{
		$params = $this->getParams();
		$id = $params->get('first_invoice_id', 0);

		if (!$id) {
			return false;
		}

		$invoice = PP::invoice($id);

		return $invoice;
	}

	/**
	 * Gets the last invoice id on which payment has been received for the order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLastMasterInvoice()
	{
		$id = $this->getParam('last_master_invoice_id', 0);

		$invoices = $this->getInvoices();
		$invoice = array_shift($invoices);

		if ($id) {
			$invoice = PP::invoice();

			if ($this->useCache) {
				$invoice->setAfterBindLoad(false);
				$invoice->toggleUseCache();
			}

			$invoice->load($id);
		}

		if (!$invoice) {
			return false;
		}

		return $invoice;
	}

	/**
	 * Gets the object link to be displayed on Invoice edit screen
	 *
	 * @return string  Url link of the attached subscription record
	 */
	public function getObjectLink()
	{
		$subscription = $this->getSubscription();
		if(empty($subscription)){
			return JText::_('COM_PAYPLANS_INVOICE_EDIT_OBJECT_DELETED');
		}

		return PayplansHtml::link(XiRoute::_("index.php?option=com_payplans&view=subscription&task=edit&id=".$subscription->getId(), false),$subscription->getId().'('.XiHelperUtils::getKeyFromId($subscription->getId()).')');
	}

	/**
	 * Gets the count of the completed recurrence cycle
	 * @return integer  Number indicating completed recurring cycle
	 */
	public function getRecurringInvoiceCount()
	{
		$status = array(PP_INVOICE_PAID, PP_INVOICE_REFUNDED);

		// get counter of last master invoice
		$last_master_invoice = $this->getLastMasterInvoice();
		$counter = 0;
		if($last_master_invoice){
			$counter = $last_master_invoice->getCounter();
		}

		$totalInvoices    = $this->getInvoices($status);
		sort($totalInvoices);
		$lastInvoice      = array_pop($totalInvoices);

		if ($lastInvoice && $lastInvoice instanceof PPInvoice) {
			$lastCounter = $lastInvoice->getCounter(); 
			return $lastCounter - ($counter - 1);
		}

		return false;
	}
}
