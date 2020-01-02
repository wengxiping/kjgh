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

class PPHelperIdevAffiliate extends PPHelperStandardApp
{
	/**
	 * Connects to idev installation to track data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($url, $args)
	{
		if (!$args) {
			return false;
		}

		$url = $url . $args;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		if (!$result) {
			return false;
		}
		
		$error = curl_error($ch);
		curl_close($ch);

		return $result;
	}

	/**
	 * Retrieve details about an invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDetails(PPInvoice $invoice)
	{
		$user = $invoice->getBuyer();

		$data = array(
			'Invoice Key' => $invoice->getKey(),
			'Price' => $invoice->getPrice(),
			'Plan Name' => str_ireplace(' ', '-', $invoice->getTitle()),
			'Visitor IP' => $this->getUserIp(),
			'Affiliate ID' => $invoice->getParam('idev_id'),
			'User Id' => $invoice->getBuyer()->getId(),
			'User Name' => $user->getName(),
			'Email' => $user->getEmail()
		);

		$order = $invoice->getReferenceObject();
		
		if ($order instanceof PPOrder) {
			$invoiceId = $invoice->getId();
			$masterInvoice = $order->getLastMasterInvoice();

			$userParams = $user->getParams();
			$userIdevParam = $userParams->get('idevaffiliate' . $invoiceId, '');

			if ($masterInvoice->getId() == $invoiceId && !empty($userIdevParam)) {
				$data['Visitor IP'] = $userIdevParam;
			}
		}

		return $data;
	}

	/**
	 * Retrieves the IP address of the current viewer
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserIp()
	{
		static $ip = null;

		if (is_null($ip)) {
			$server = JFactory::getApplication()->input->server;		

			// Get IP from HTTP Proxy or load-balancer
			$ip = $server->get('HTTP_X_FORWARDED_FOR');

			if (!$ip) {
				$ip = $server->get('HTTP_CLIENT_IP');
			}
			
			if (!$ip) {
				$ip = $server->get('REMOTE_ADDR');
			}

			if (!$ip && getenv('HTTP_X_FORWARDED_FOR')) {
				$ip = getenv('HTTP_X_FORWARDED_FOR');
			}

			if (!$ip && getenv('HTTP_CLIENT_IP')) {
				$ip = getenv('HTTP_CLIENT_IP');
			}

			if (!$ip && getenv('REMOTE_ADDR')) {
				$ip = getenv('REMOTE_ADDR');
			}
		}

		return $ip;
	}		

	/**
	 * Track refunds
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trackRefund(PPInvoice $invoice, $url)
	{
		$data = $this->getDetails($invoice);

		if (isset($data['Affiliate Id'])) {
			$query = "idevaffiliate.php?id=".$data['Affiliate Id']."&tid1=invoice&tid2=refund&tid3=successful";
			$this->connect($url, $query);
		}

		$query = "sale.php?profile=72198&idev_saleamt=".$data['Price']."&idev_ordernum=".$data['Invoice Key']."&ip_address=".$data['Visitor IP']."&idev_option_1=".$data['User Name']."&idev_option_2=".$data['Email']."&idev_option_3=".$data['Plan Name'];
		
		return $this->connect($url, $query);
	}

	/**
	 * Tracks for upgrade of subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trackUpgrade(PPInvoice $oldInvoice, PPInvoice $newInvoice, $url)
	{
		$newOrder = PP::order($newInvoice->getObjectId());
		$newSubscriptionId = $newOrder->getParam('upgrading_from', null);

		$oldInvoiceKey = PP::getKeyFromId($oldInvoice->getObjectId());
		$oldOrder = PP::order($oldInvoiceKey);

		$oldplanName = $oldInvoice->getTitle();
							
		//Data From new subscription to which we are going to upgrade.
		$newSubscription = PP::subscription($newSubscriptionId);
		$newTotal = $newInvoice->getTotal();
		$newPlanName = $newInvoice->getTitle();
		$userId = $oldInvoice->getBuyer();
		$affiliateId = $newInvoice->getParam('idev_id');//$this->getAppParam('affiliate_id');

		if ($affiliateId) {
			$query = "idevaffiliate.php?id=$affiliateId"."&tid1=Upgraded_from_$oldplanName"."&tid2=Upgraded_To_$newPlanName"."&tid3=Old_invoice_$oldInvoiceKey";
			return $this->connect($url, $query);
		}
		return false;
	}

	/**
	 * Track for paid payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trackPaid($invoice, $upgrade, $url)
	{
		//Gets Details of invoice
		$data = $this->getDetails($invoice);
				
		//If Subscription is Not Upgraded then make an entry for Traffic Log on IDevAffiliates Otherwise
		if (!$upgrade && isset($data['Affiliate Id'])) {
			$query ='idevaffiliate.php?id='.$data['Affiliate Id']."&tid1=".$data['User Id']."&tid2=".$data['User Name'];
			$this->connect($url, $query);
		}
		
		$query = "sale.php?profile=72198&idev_saleamt=".$data['Price']."&idev_ordernum=".$data['Invoice Key']."&ip_address=".$data['Visitor IP']."&idev_option_1=".$data['User Name']."&idev_option_2=".$data['Email']."&idev_option_3=".$data['Plan Name'];
		return $this->connect($url, $query);
	}

	/**
	 * Determines if an order is upgraded
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUpgrade(PPInvoice $oldInvoice, PPInvoice $newInvoice)
	{	
		$invoiceKey = $newInvoice->getKey();
		$order = PP::order($newInvoice->getObjectId());
		$subscriptionId = $order->getParam('upgrading_from', null);

		if ($order && $subscriptionId) {
			$oldInvoiceKey = PP::getKeyFromId($oldInvoice->getObjectId());
			$oldOrder = PP::order($oldInvoiceKey);
			$oldSubscription = $oldOrder->getSubscription();
			return true;
		}

		return false;
	}	
}