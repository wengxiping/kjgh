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

interface PayplansIfaceApiTransaction
{
	/**
	 * Gets the currency of the transaction
	 * @param string $format
	 * @return string
	 */
	public function getCurrency($format = null);
	
	/**
	 * Gets all tha parameter of the transaction
	 * @return object XiParameter
	 */
	public function getParams();
	
	/**
	 * Gets the gateway subscription id of the transaction
	 * 
	 * This parameter is available in recurring payments only.
	 * Gateway subscription id is the unique identifier referring
	 * to the profile id created at payment gateway end for the recurring subscription
	 * 
	 * @return string
	 */
	public function getGatewaySubscriptionId();
	
	/**
	 * Gets the payment gateway transaction id of the transaction
	 * Gateway Txn id is the unique identifier(reference) passed from 
	 * payment gateway indicating the transaction record at payment gateway end    
	 * 
	 * @retun string  Unique Identifier
	 */
	public function getGatewayTxnId();
	
	/**
	 * Gets the payment record attached to the transaction
	 * 
	 * @param boolean $requireinstance  Optional parameter to get the instance of the payment rather than payment id
	 * @return interger|object PaymentId or object of PayplansPayment for PaymentId
	 */
	public function getPayment();
	
	/**
	 * Gets the invoice attached to the transaction
	 * 
	 * @param   integer $requireinstance  Optional parameter to get the instance of the Invoice
	 * @return  mixed  InvoiceId or object of PayplansInvoice for InvoiceId
	 */
	public function getInvoice();
	
	/**
	 * Gets the amount of the transaction
	 * This amount is the actual amount received from the payment gateway
	 * 
	 * @return float  Value of the amount
	 */
	public function getAmount();
	
	/**
	 * Gets the buyer of the transaction
	 * 
	 * @param boolean $requireinstance  If True return PayplansUser instance else Userid 
	 * @return mixed Userid or instance of PayplansUser attached with the transaction
	 */
	public function getBuyer();
}