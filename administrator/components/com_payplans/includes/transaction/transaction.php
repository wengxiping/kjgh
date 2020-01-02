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

class PPTransaction extends PPAbstract implements PPAppTriggerableInterface, PayplansIfaceApiTransaction
{
	private $useCache = false;

	public static function factory($id = null)
	{
		return new self($id);
	}

	public function reset($config=array())
	{
		$this->table->transaction_id = 0;
		$this->table->invoice_id = 0;
		$this->table->user_id = 0;
		$this->table->payment_id = 0;
		$this->table->gateway_txn_id = 0;
		$this->table->gateway_parent_txn = 0;
		$this->table->gateway_subscr_id = 0;
		$this->table->amount = 0.00;
		$this->table->reference = '';
		$this->table->message = '';
		$this->table->created_date = PP::date();
		$this->table->params = new JRegistry();
		
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
	 * Retrieves the purchaser of the subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBuyer()
	{
		$user = PP::user($this->table->user_id);

		return $user;
	}

	/**
	 * Gets the amount of the transaction. This amount is the actual amount received from the payment gateway
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAmount()
	{
		return PPFormats::price($this->amount);
	}
		
	/**
	 * Retrieves the invoice attached to the transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getInvoice()
	{
		static $_cache = array();

		$key = $this->invoice_id;

		if (!isset($_cache[$key])) {

			$invoice = PP::invoice();

			if ($this->useCache) {
				$invoice->setAfterBindLoad(false);
				$invoice->toggleUseCache();
			}

			$invoice->load($this->invoice_id);

			$_cache[$key] = $invoice;
		}

		return $_cache[$key];
	}
	
	/**
	 * @deprecated 2.2
	 */
	public function getCurrentInvoice($requireinstance=false)
	{
		if($requireinstance == PP_INSTANCE_REQUIRE){
			return PayplansInvoice::getInstance($this->current_invoice_id);
		}
		
		return $this->current_invoice_id;
	}

	/**
	 * Retrieves this transaction payment id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentId()
	{
		return $this->payment_id;
	}
	
	/**
	 * Retrieve the payment record attached to the transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayment()
	{
		$payment = PP::payment();

		if ($this->useCache) {
			$payment->setAfterBindLoad(false);
		}

		$payment->load($this->payment_id);
		
		return $payment;
	}
	
	/**
	 * Gets the payment gateway transaction id of the transaction
	 * Gateway Txn id is the unique identifier(reference) passed from 
	 * payment gateway indicating the transaction record at payment gateway end    
	 * 
	 * @see PayplansIfaceApiTransaction::getGatewayTxnId()
	 * 
	 * @retun string  Unique Identifier
	 */
	public function getGatewayTxnId()
	{
		return $this->gateway_txn_id;
	}
	
	/**
	 * Gets the parent gateway transaction id of the transaction
	 * 
	 * @retun string  Unique Identifier referring to a parent transaction record
	 */
	public function getGatewayParentTxn()
	{
		return $this->gateway_parent_txn;
	}
	
	/**
	 * Gets the gateway subscription id of the transaction
	 * 
	 * This parameter is available in recurring payments only.
	 * Gateway subscription id is the unique identifier referring
	 * to the profile id created at payment gateway end for the recurring subscription
	 * 
	 * @see PayplansIfaceApiTransaction::getGatewaySubscriptionId()
	 * 
	 * @return string
	 */
	public function getGatewaySubscriptionId()
	{
		return $this->gateway_subscr_id;
	}
	
	/**
	 * Retrieves the transaction message
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMessage()
	{
		return JText::_($this->message);
	}
	
	/**
	 * Gets the created date of the transaction
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getCreatedDate()
	{
		if ($this->created_date == '0000-00-00 00:00:00') {
			return false;
		}

		$date = PP::date($this->created_date);

		return $date;
	}
	
	/**
	 * Modifies a key of the transaction, creating it if it does not already exist.
	 *
	 * @param   string  $key      The name of the key.
	 * @param   mixed   $value    The value of the key to set.
	 *
	 * @return  object  PayplansTransaction
	 */
	public function setParam($key, $value)
	{
		XiError::assert($this);
		$this->getParams()->set($key,$value);
		return $this;
	}
	
	/**
	 * Returns a key of the transaction object or the default value if the key is not set.
	 *
	 * @param string  $key       The name of the property.
	 * @param mixed   $default   The default value.
	 * 
	 * @return  mixed   The value of the key.
	 */
	public function getParam($key,$default=null)
	{
		XiError::assert($this);
		return $this->getParams()->get($key,$default);
	}

	/**
	 * Retrieves the params of a transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParams()
	{
		$params = new JRegistry($this->params);
		
		return $params;
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
		$unwanted = array('message', 'reference', 'params', 'gateway_txn_id', 'gateway_parent_txn', 'gateway_subscr_id', 'current_invoice_id');

		foreach ($unwanted as $key) {
			if (isset($data[$key])) {
				unset($data[$key]);
			}
		}
		
		$data['created_date'] = $this->getCreatedDate() ? $this->getCreatedDate()->toSql() : '0000-00-00 00:00:00';

		return $data;
	}

	/**
	 * Implementing interface Apptriggerable
	 * @return array
	 */
	public function getPlans($requireInstance = false)
	{
		return $this->getInvoice()->getPlans($requireInstance);
	}
	
	/**
	 * Gets the currency of the transaction
	 * 
	 * @see PayplansIfaceApiTransaction::getCurrency()
	 * 
	 * @param string $format
	 * @return string
	 */
	public function getCurrency($format = null)
	{
		 return $this->getInvoice()->getCurrency($format);
	}

	/**
	 * Retrieves the transaction owner name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOwnerName($userId)
	{
		if (!$userId) {
			$guest = new stdClass();
			$guest->id = 0;
			$guest->name = '';
			$guest->username = '';
			
			return $guest;
		}

		static $users = array();

		if (!isset($users[$userId])) {
			$users[$userId] = PP::user($userId);
		}

		$username = $users[$userId]->getUsername();
		
		return $username;
	}

	/**
	 * Executes a refund request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refund()
	{
		$payment = $this->getPayment();

		if (!$payment) {
			throw new Exception('Unable to locate payment provider');
		}

		$app = $payment->getApp();

		if (!$app->getId()) {
			throw new Exception('Unable to retrieve the payment library');
		}

		// If app does not support for refunds, we'll create a new transaction with a negative amount
		if (!$app->supportForRefund()) {
			$invoice = $this->getInvoice();
			$params = $this->getParams();

			$refundTransaction = PP::createTransaction($invoice, $payment, $params->get('gateway_txn_id'), $params->get('gateway_subscr_id'), $params->get('gateway_parent_txn'), $params->toString());
			$refundTransaction->message = JText::_('COM_PAYPLANS_TRANSACTION_TRANSACTION_MADE_FOR_REFUND');
			$refundTransaction->amount = ($this->getAmount() * -1);
			return $refundTransaction->save();
		}

		$state = $app->refundRequest($this, $this->getAmount());

		return $state;
	}

}