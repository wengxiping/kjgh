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

class PPAppPap extends PPApp
{
	public function isApplicable($refObject = null, $eventName = '')
	{
		// if not with reference to payment then return
		if ($eventName === 'onPayplansInvoiceAfterSave' || $eventName === 'onPayplansInvoiceBeforeSave') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Triggered before an invoice is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPayplansInvoiceBeforeSave($prev, $new)
	{
		if ($prev != null) {
			return true;
		}

		$session = PP::session();
		$affiliateId = $session->get('PAPAffiliateId');

		if ($affiliateId) {
			$new->setParam('PAPAffiliateId', $affiliateId);
			$new->setParam('PAPVisitorId', @$_COOKIE['PAPVisitorId']);
		}

		return true;
	}
	
	/**
	 * Triggered after an invoice is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansInvoiceAfterSave($prev, $new) 
	{
		// Before save and after save, Invoice status should not be same. 
		if ($prev != null && $new->getStatus() == $prev->getStatus()) {
			return true;
		}
		
		// If it isn't paid, we don't need to do anything
		if (!$new->isPaid()) {
			return true;
		}
		
		$user = $new->getBuyer();
		$order = $new->getReferenceObject();
		
		$orderKey = $order->getKey();
		$invoiceKey = $new->getKey();
		
		$visitorId = $new->getParam('PAPVisitorId', false);
		$affiliateId = $new->getParam('PAPAffiliateId', $this->helper->getAccountId());

		// Tracking enabled for recurring invoices
		if ($new->isRecurring() && ($new->getCounter() > 1)) {
			$object = $new->getReferenceObject();

			if ($object instanceof PPOrder) {
				$masterInvoice = $object->getLastMasterInvoice();
				$visitorId = $masterInvoice->getParam('PAPVisitorId', false);
				$affiliateId = $masterInvoice->getParam('PAPAffiliateId', false);
			}
		}
		
		$tracker = $this->helper->getSalesTracker();
		$tracker->setAccountId($this->helper->getAccountId());
		$tracker->setVisitorId($visitorId);
		
		$sale = $tracker->createSale();
		$sale->setTotalCost($new->getTotal());
		$sale->setOrderID($orderKey);
		$sale->setProductID($invoiceKey);
		$sale->setAffiliateId($affiliateId);
		
		$sale->setData1($user->getEmail());
		$sale->setData2($user->getName());
		
		$tracker->register();

		return true;
	}
}
