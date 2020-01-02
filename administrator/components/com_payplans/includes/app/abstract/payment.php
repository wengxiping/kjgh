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

require_once(dirname(__DIR__) . '/helpers/payment.php');

abstract class PPAppPayment extends PPApp
{
	const CREDIT_TRANSACTION = 'CREDIT';
	const DEBIT_TRANSACTION  = 'DEBIT';

	public function __construct($data = array())
	{
		parent::__construct($data);
		
		$this->input = JFactory::getApplication()->input;
	}

	/**
	 * Determine if we need to implement if plugin is applicable or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		// if not with reference to payment then return
		if ($refObject === null || !($refObject instanceof PPAppTriggerableInterface)) {
			return false;
		}

		$object = false;

		// if ref object is instance of plan
		if ($refObject instanceof PPPlan) {
			
			// if apply all then return true
			if ($this->getParam('applyAll',false) == true) {
				return true;
			}

			 // else check in app plans
			if (in_array($refObject->getId(),$this->getPlans())) {
				return true;
			}
			return false;
		}

		// Transactions
		if ($refObject instanceof PPTransaction) {
			$object = $refObject->getPayment();
		}

		// Invoices
		if ($refObject instanceof PPInvoice) {

			if($this->getParam('applyAll',false) == true){
				return true;
			}

			// if reference object has the getPlans function
			if (method_exists($refObject, 'getPlans')) {
				$plans = $refObject->getPlans();

				// if object is of interest as per plans selected
				$ret = array_intersect($this->getPlans(), $plans);
				if (count($ret) > 0 ) {
					return true;
				}
			}

			$object = $refObject->getPayment();
		}

		// If reference object is payment then check then app id only
		if ($object instanceof PPPayment) {
			$app = $object->getApp();

			if ($app && $app->getId() == $this->getId()) {
				return true;
			}

			return false;
		}

		// Payments
		if ($refObject instanceof PPPayment) {
			$app = $refObject->getApp();

			if ($app && ($app->getId() == $this->getId())) {
				return true;
			}

			return false;
		}


		
		// if($refObject instanceof PayplansInvoice){
		// 	if($this->getParam('applyAll',false) == true){
		// 		return true;
		// 	}
			
		// 	// if reference object has the getPlans function 
		// 	if(method_exists($refObject, 'getPlans')){
		// 		$plans = $refObject->getPlans();
			
		// 		// if object is of interest as per plans selected
		// 		$ret = array_intersect($this->getPlans(), $plans);
		// 		if(count($ret) > 0 ){
		// 			return true;
		// 		}
		// 	}	
		// }

		return false;
	}


	/**
	 * Determine if app accept recurrring payment or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRecurring(PPPayment $payment)
	{
		$plans = $payment->getPlans(PP_INSTANCE_REQUIRE);

		// TODO : need to change in concept when multiple subscription support will be available
		// if any one plans if recurring then return true
		foreach ($plans as $plan) {
			if ($plan->getRecurring()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if app support payment cancel.
	 *
	 * @since 4.0.0
	 * @access	public
	 */
	public function isSupportPaymentCancellation($invoice)
	{
		return false;
	}

	/**
	 * Just before going to display payments form
	 *
	 * @since 4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentBefore(PPPayment $payment, $data=null)
	{
		return true;
	}

	/**
	 *
	 * @since 4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentDisplay(PPPayment $payment, $data=null)
	{
		return true;
	}

	/**
	 * Render Payment Forms
	 *
	 * @since 4.0.0
	 * @access	public
	 */
	abstract public function onPayplansPaymentForm(PPPayment $payment, $data=null);

	/**
	 * Render Payment Forms at Admin Panel
	 *
	 * @since 4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentFormAdmin(PPPayment $payment, $data=null)
	{
		return true;
	}

	/**
	 * Generic method to render payment records
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function onPayplansTransactionRecord(PPTransaction $transaction =null)
	{
		$params = $transaction->getParams();

		if ($params) {
			$this->set('transaction_html', $params->toArray());

			return $this->display('transaction');
		}
	}

	/**
	 * Payment collection is complete
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'error') {

			$errors = array();
			$log_id = $this->input->get('log_id', 0, 'int');

			if ($log_id && !empty($log_id)) {
				$record = PP::model('log')->loadRecords(array('id'=>$log_id));
				$errors = unserialize(base64_decode($record[$log_id]->content));
				$errors = unserialize(base64_decode($errors['content']));
			} else {
				$errorLog = PPLog::getLog($payment, PPLogger::LEVEL_ERROR);
				if ($errorLog) {
					$record = array_pop($errorLog);
					$errors = unserialize(base64_decode($record->content));
					$errors = unserialize(base64_decode($errors['content']));
				}
			}

			$this->assign('errors', $errors);

			// set error template
			$controller->setTemplate('complete_'.$action);
			return $this->_render('error');
		}

		return true;
	}

	/**
	 * A trigger comes from payment service.
	 * Verify Payment Details, all sanity checks
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data=null, $controller)
	{
		return true;
	}

	/**
	 * A trigger for cancelled payment
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		$order = $controller->getReferenceObject(PP_INSTANCE_REQUIRE);

		if (!is_a($order, 'PPOrder')) {
			return true;
		}

		$order->status = PP_ORDER_CANCEL;
		$order->save();

		return true;
	}

	/**
	 * If plugin need some special event
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function onPayplansPaymentCustom(PPPayment $payment, $data=null)
	{
		return true;
	}

	/**
	 * Trigger for before payment being saved
	 * @since 4.0.0
	 * @access public
	 */
	public function onPayplansPaymentBeforeSave(PPPayment $previous = null, PPPayment $new = null)
	{
		return true;
	}

	/**
	 * Trigger for after payment saved
	 * @since 4.0.0
	 * @access public
	 */
	public function onPayplansPaymentAfterSave(PPPayment $previous = null, PPPayment $new = null)
	{
		return true;
	}

	/**
	 * Retrieve existing transactions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function getExistingTransaction($invoiceId, $transactionId, $subscriptionId, $parentTransaction)
	{
		if (empty($txn_id) && empty($subscr_id) && empty($parent_txn)) {
			return false;
		}

		$model = PP::model('Transaction');
		$options = array(
			'invoice_id' => $invoiceId,
			'gateway_txn_id' => $transactionId,
			'gateway_subscr_id' => $subscriptionId,
			'gateway_parent_txn' => $parentTransaction
		);

		$result = $model->loadRecords($options);

		if (count($result)) {
			return $result;
		}
		
		return false;
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function normalize($data, $key, $default = null)
	{
		return PP::normalize($data, $key, $default);
	}
	
	/**
	 * this function should be override by app if it supports the refund from backend
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function supportForRefund()
	{
		return false;
	}


	/**
	 * this function should be overide by and app if it want to do some refund action after admin confirm for refund
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function refundRequest(PPTransaction $transaction,$refundAmouont)
	{
		return false;
	}
}

class PayplansPaymentFormatter extends PayplansFormatter
{

	public $template	= 'view_log';
	
	function getIgnoredata()
	{
		$ignore = array('_trigger', '_component', '_errors', '_name','_blacklist_tokens','_transactions');
		return $ignore;
	}
	
	function getVarFormatter()
	{
		$rules = array('app_id'        => array('formatter'=> 'PayplansAppFormatter',
											   'function' => 'getAppName'));
		return $rules;
		
	}
}
