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

class plgPayplansAnalytics extends PPPlugins
{
	public function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->eventToTrack = $this->params->get('eventToTrack',array());

		if (!is_array($this->eventToTrack)) {
			$this->eventToTrack = array($this->eventToTrack);
		}
		
		$realDomain = JURI::getInstance()->getHost();
		$domainFilter = explode(',', $this->params->get('real_domain_filter',''));
		
		if (in_array($realDomain, $domainFilter) || empty($domainFilter)) {
			return true;
		}

		$this->helper = $this->getAppHelper();

		return false;
	}

	/**
	 * Capture email sending process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		$pptracker = $this->input->get('ppemailtracker', null, 'cmd');

		// TO use this image
		// ppemailtracker=image&wv_email=john.doe@example.com&utm_variables
		if ($pptracker === 'image') {
			$vars = array('utm_source' => '', 'utm_medium' => '', 'utm_campaign' => '', 'utm_content' => '', 'utm_term' => '');
			$args = $this->input->getArray($vars);
			$email = $this->input->getString('email');
			$event  = $this->input->get('event', '');
			$eventName	= !empty($event) ? $event : 'email.open';

			$args['email'] = $email;
			$user = $this->helper->getUserId($email);
			return $this->helper->trackEvent($user->id, $eventName, $args);

			header("Content-type: image/png");
			echo file_get_contents(__DIR__ . '/analytics/beacon.png');
			exit;
		}
	}

	/**
	 * Capture all related events after subscription is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		if ($prev == null && $new->getStatus() == PP_NONE) {

			$user = $new->getBuyer();
			$userId = $user->getId();

			// Tracks Event Add To Cart Event
			$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_SUBSCRIPTION_CREATION_EVENTNAME';

			if (in_array($eventName, $this->eventToTrack)) {
				$eventName = JText::_($eventName);
				$args = $this->helper->getDetailSubscription($new);
				$email = $new->getBuyer(true)->getEmail();

				$args['email'] = $email;
				return $this->helper->trackEvent($userId, $eventName, $args, 1);
			}
		}

		$status = $new->getStatus();
		$args = $this->helper->getDetailSubscription($new);
		$email = $new->getBuyer(true)->getEmail();

		$args['email'] = $email;

		// For Tracking Upgradation of a plan.
		if ($prev != null && $prev->getStatus() != PP_SUBSCRIPTION_ACTIVE && $new->getStatus() == PP_SUBSCRIPTION_ACTIVE) {
			// Gets the order from subscription instance $new
			$order = $new->getOrder(PP_INSTANCE_REQUIRE);
			$oldSubscriptionId = $order->getParam('upgrading_from',null);

			if ($order && isset($oldSubscriptionId) && !empty($oldSubscriptionId)) {
				$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_SUBSCRIPTION_UPGRADE_EVENTNAME';

				if (in_array($eventName, $this->eventToTrack)) {
					$eventName = JText::_($eventName);

					return $this->helper->trackEvent($userId, $eventName, $args);
				}
			}
		}

		switch ($status) {
			// Subscription active
			case PP_SUBSCRIPTION_ACTIVE :
				$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_SUBSCRIPTION_CREATION_EVENTNAME';
				break ;

			// Subscription expired
			case PP_SUBSCRIPTION_EXPIRED:
				$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_SUBSCRIPTION_EXPIRATION_EVENTNAME';
				break;

			// subscription hold
			case PP_SUBSCRIPTION_HOLD:
				$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_SUBSCRIPTION_HOLD_EVENTNAME';
				break;

			case PP_NONE:
				return true;
		}

		if (in_array($eventName, $this->eventToTrack)) {
			$eventName = JText::_($eventName);
			return $this->helper->trackEvent($userId, $eventName, $args);
		}

		return true;
	}

	/**
	 * Track events after invoices is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansInvoiceAfterSave($prev, $new)
	{
		if (!isset($prev) || !isset($new)) {
			return true;
		}

		// Before save and after save, Invoice status should not be same.
		if ($new->getStatus() == $prev->getStatus()) {
			return true;
		}

		//Initialize Tracking of KM
		$status = $new->getStatus();

		$args = $this->helper->getDetailInvoice($new);
		$email = $new->getBuyer(true)->getEmail();

		$args['email'] = $email;
		$userId = $new->getBuyer()->getId();

		switch ($status) {
			// Invoice paid
			case PP_INVOICE_PAID :
				$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_INVOICE_PAID_EVENTNAME';
				break ;

				// Invoice Refund
			case PP_INVOICE_REFUNDED:
				$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_INVOICE_REFUND_EVENTNAME';
				break;

			// Invoice Confirm or none
			case PP_INVOICE_CONFIRMED:
				$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_INVOICE_CHECKOUT_EVENTNAME';
				
				break;

			case PP_NONE:
				return true;
		}
		
		if (in_array($eventName, $this->eventToTrack)) {
			$eventName = JText::_($eventName);
			return $this->helper->trackEvent($userId, $eventName, $args);
		}
	}

	/**
	 * Track events after user renewed the subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionRenewalComplete($subscription, $new)
	{
		$eventName = 'COM_PAYPLANS_APP_ANALYTICS_SUBSCRIPTION_RENEWAL_EVENTNAME';

		if (in_array($eventName, $this->eventToTrack)) {
			$args = $this->helper->getDetailInvoice($new);
			$email = $new->getBuyer(true)->getEmail();
			
			$args['email'] = $email;
			$user = $new->getBuyer();
			$eventName = JText::_($eventName);

			return $this->helper->trackEvent($user->getId(), $eventName, $args);
		}
	}

	/**
	 * Detect events after applying a discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansDiscountAfterApply($invoice, $discountCode)
	{
		$eventName	= 'COM_PAYPLANS_APP_ANALYTICS_DISCOUNT_CONSUMED_EVENTNAME';

		if (in_array($eventName, $this->eventToTrack)) {
			$args = $this->helper->getDetailInvoice($invoice);
			$email = $invoice->getBuyer(true)->getEmail();

			$args['discount_code_utilized'] = $discountCode;
			$args['email'] = $email;

			$user = $this->helper->getUserId($email);
			$eventName = JText::_($eventName);

			return $this->helper->trackEvent($user, $eventName, $args);
		}
	}

	/**
	 * Track events after payment is made
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfterSave($prev, $new)
	{
		if (!is_null($prev) || $prev != null) {
			return;
		}

		$eventName = 'COM_PAYPLANS_APP_ANALYTICS_PAYMENT_GATEWAY_USED_EVENTNAME';

		if (in_array($eventName, $this->eventToTrack)) {
			$email = $new->getBuyer(true)->getEmail();

			$args['paymentGateway'] = $new->getAppName();
			$args['email'] = $email;

			$user = $this->helper->getUserId($email);
			$eventName = JText::_($eventName);

			return $this->helper->trackEvent($user, $eventName, $args);
		}
	}
}