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

class PPPayment extends PPAbstract implements PPAppTriggerableInterface, PPApiPaymentInterface, PPMaskableInterface
{
	protected $_transactions = null;

	// skip these tokens in token rewriter
	public  $_blacklist_tokens = array('gateway_params');

	private $useCache = false;

	public static function factory($id = null)
	{
		return new self($id);
	}

	public function reset($option = array())
	{
		$this->table->payment_id = 	0;
		$this->table->user_id =	0;
		$this->table->invoice_id =	0;
		$this->table->app_id =	0;
		$this->table->created_date = PP::date();
		$this->table->modified_date = PP::date();
		$this->table->params = null;
		$this->table->gateway_params = null;
		$this->table->_transactions	= array();

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
	 * Triggered after a payment record is binded to the lib
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function afterBind($id = 0)
	{
		if (!$id) {
			return $this;
		}

		//load dependent records
		if ($this->afterBindLoad) {
			return $this->_loadTransactions($id);
		}

		return $this;
	}

	/**
	 * Retrieves the purchaser (user account)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBuyer($requireInstance = false)
	{
		if ($requireInstance) {
			return PP::user($this->user_id);
		}

		return $this->user_id;
	}

	/**
	 * Implementing interface Apptriggerable
	 * @return array
	 */
	public function getPlans($requireInstance = false)
	{
		return $this->getInvoice(PP_INSTANCE_REQUIRE)->getPlans($requireInstance);
	}

	public function setApp($app)
	{
		$this->app_id = is_a($app,'PayplansApp') ? $app->getId() : $app;
		return $this;
	}

	/**
	 * Gets the invoice linked with the current payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getInvoice()
	{
		$invoice = PP::invoice();

		if ($this->useCache) {
			$invoice->setAfterBindLoad(false);
			$invoice->toggleUseCache();
		}

		$invoice->load($this->table->invoice_id);

		return $invoice;
	}

	/**
	 * Gets the payment-gateway app name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAppName()
	{
		$app = PP::app( $this->app_id);
		
		if ($app instanceof PPApp) {
			return JText::_($app->getTitle());
		}

		return "";
	}

	/**
	 * Gets the creation date of the payment
	 * @see PayplansIfaceApiPayment::getCreatedDate()
	 * @return object XiDate
	 */
	public function getCreatedDate()
	{
		return $this->created_date;
	}

	/**
	 * Gets the modified date of the payment
	 * @see PayplansIfaceApiPayment::getModifiedDate()
	 * @return object  XiDate
	 */
	public function getModifiedDate()
	{
		return $this->modified_date;
	}

	/**
	 * Retrieves the app used for payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApp()
	{
		$app = PP::app()->getAppInstance($this->app_id);

		return $app;
	}

	/**
	 * Returns a key of the payment object or the default value if the key is not set.
	 *
	 * @param string  $key       The name of the property.
	 * @param mixed   $default   The default value.
	 *
	 * @return  mixed   The value of the key.
	 */
	public function getParam($key,$default=null)
	{
		return $this->getParams()->get($key,$default);
	}

	/**
	 * Gets all the parameters of the Order
	 *
	 * @return object XiParameter
	 */
	public function getParams()
	{
		$registry = new JRegistry($this->params);

		return $registry;

	}

	/**
	 * Retrieves the gateway params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getGatewayParams()
	{
		$registry = new JRegistry($this->gateway_params);

		return $registry;
	}

	/**
	 * Gets the property of the gateway params of the payment
	 *
	 * @param   string  $key       The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed  The value of the key.
	 */
	public function getGatewayParam($key, $default=null)
	{
		return $this->getGatewayParams()->get($key,$default);
	}

	/**
	 * Retrieves a list of tokens available for token rewriting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRewriterTokens()
	{
		return false;
	}

	/**
	 * Gets the transaction attached with the payment
	 *
	 * @see PayplansIfaceApiPayment::getTransactions()
	 *
	 * @return array  Array of transaction object (PayplansTransaction)
	 */
	public function getTransactions()
	{
		$this->_loadTransactions($this->getId());
		
		return $this->_transactions;
	}

	/**
	 * Refer the payment record
	 * Load all the transactions attached to the payment
	 * @return object PayplansPayment
	 */
	public function refresh()
	{
		// get all transactions
		$this->_loadTransactions($this->getId());

		// save update payment
		return $this;
	}

	protected function _loadTransactions($payment_id)
	{
		// get all transaction records of this payment
		$records = PP::model('transaction')->loadRecords(array('payment_id' => $payment_id));

		foreach ($records as $record){
			// $this->_transactions[$record->transaction_id] = PayplansTransaction::getInstance($record->transaction_id, null, $record);
			$this->_transactions[$record->transaction_id] = PP::transaction($record->transaction_id);

		}

		return $this;
	}
}

