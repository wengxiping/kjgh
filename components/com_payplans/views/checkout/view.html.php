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

PP::import('site:/views/views');

class PayPlansViewCheckout extends PayPlansSiteView
{
	/**
	 * Renders the check out page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$invoiceKey = $this->input->get('invoice_key', '', 'default');
		$invoice = null;

		$planId = $this->input->get('plan_id', 0, 'int');
		$plan = PP::plan($planId);

		$subscriptionParams = new JRegistry();

		if (!$invoiceKey) {
			return $this->redirectToView('plan');
		}

		if ($invoiceKey) {
			$invoiceId = (int) PP::encryptor()->decrypt($invoiceKey);
			$invoice = PP::invoice($invoiceId);

			if (!$invoice->getId()) {
				return $this->redirectToView('plan');
			}

			$plan = $invoice->getPlan();
			$planId = $plan->getId();
			$subscription = $invoice->getSubscription();
			$subscriptionParams = $subscription->getParams();
		}

		if (!$plan->getId()) {
			throw new Exception('COM_PAYPLANS_PLAN_PLEASE_SELECT_A_VALID_PLAN');
		}

		// Trigger event after a plan has been selected
		$args = array(&$planId, $this);

		PP::event()->trigger('onPayplansPlanAfterSelection', $args, '', $plan);

		// add any default addons if available
		if ($this->config->get('addons_enabled')) {
			$invoice->attachDefaultServices($plan, true);
		}

		// Get referral apps
		$referrals = false;

		if ($this->config->get('discounts_referral')) {
			$referralsModel = PP::model('Referrals');
			$referralApp = $referralsModel->getApplicableApp($plan);
			$referral = PP::referral($referralApp);

			// We should not display the referral form again if a referral discount is already applied
			if (!$referral->isUsed($invoice) && $referralApp) {
				$referrals = true;
			}
		}

		// Get a list of applicable payment methods for the plan
		$providers = $invoice->getPaymentProviders();
		$provider = null;

		if (count($providers) == 1) {
			$provider = $providers[0];
		}

		$user = PP::user();

		$addons = array();
		$purchasedAddons = array();

		if ($this->config->get('addons_enabled')) {
			$addonModel = PP::model('addons');
			$addons = $addonModel->getAvailableServices(array($planId));
			$purchasedAddons = $addonModel->getPurchasedServices($invoiceId);
		}

		// Retrieves a list of customdetails
		$customDetailsModel = PP::model('Customdetails');

		$userCustomDetails = $customDetailsModel->getPlanCustomDetails($plan, 'user');
		$subsCustomDetails = $customDetailsModel->getPlanCustomDetails($plan, 'subscription');

		$payment = $invoice->getPayment();

		if ($payment) {
			$provider = $payment->getApp();
		}

		$modifiers = $invoice->getModifiers();
		$total = PPHelperModifier::getTotal($invoice->getSubtotal(), $modifiers);

		// Get registration provider and set the necessary data
		$registration = PP::registration();

		$registration->setInvoiceKey($invoice->getKey());
		$registration->setSessionKey('PP_CHECKOUT_REGISTRATION');

		$socialDiscount = PP::socialdiscount();
		$defaultAccountType = $this->config->get('default_form_order', 'login');

		$accountType = $this->input->get('account_type', $defaultAccountType, 'string');
		$skipInvoice = $this->input->get('skipInvoice', 0, 'int');

		if ($skipInvoice && (!$invoice->isFree() || $this->my->id)) {
			$skipInvoice = false;
		}

		$registrationOnly = false;

		// Determine if we should hide unnecessary things initially
		if (!$this->my->id && !$registration->isBuiltIn()) {

			// There might be user coming from new registration. #601
			if ($accountType == 'register' && !$registration->getNewUserId()) {
				$registrationOnly = true;
			} else if ($accountType == 'login' && $registration->getNewUserId()) {
				$accountType = 'register';
			}
		}

		$this->set('registrationOnly', $registrationOnly);
		$this->set('accountType', $accountType);
		$this->set('socialDiscount', $socialDiscount);
		$this->set('registration', $registration);
		$this->set('referrals', $referrals);
		$this->set('userCustomDetails', $userCustomDetails);
		$this->set('subsCustomDetails', $subsCustomDetails);
		$this->set('subscriptionParams', $subscriptionParams);
		$this->set('user', $user);
		$this->set('step', 'info');
		$this->set('modifiers', $modifiers);
		$this->set('plan', $plan);
		$this->set('provider', $provider);
		$this->set('providers', $providers);
		$this->set('invoice', $invoice);
		$this->set('addons', $addons);
		$this->set('purchasedAddons', $purchasedAddons);


		$country = $user->getCountry();
		$userPref = $user->getPreferences();
		$businessVatno = $userPref->get('tin', '');
		$businessName = $userPref->get('business_name', '');
		$businessAddress = $userPref->get('business_address', '');
		$businessCity = $userPref->get('business_city', '');
		$businessState = $userPref->get('business_state', '');
		$businessZip = $userPref->get('business_zip', '');
		$purpose = $userPref->get('business_purpose', 0);
		$purposeValue = 'none';

		if ($purpose == PP_EUVAT_PURPOSE_BUSINESS) {
			$purposeValue = 'business';
		}

		if ($purpose == PP_EUVAT_PURPOSE_PERSONAL) {
			$purposeValue = 'personal';
		}

		$this->set('country', $country);
		$this->set('businessVatno', $businessVatno);
		$this->set('businessName', $businessName);
		$this->set('businessAddress', $businessAddress);
		$this->set('businessCity', $businessCity);
		$this->set('businessState', $businessState);
		$this->set('businessZip', $businessZip);
		$this->set('purpose', $purpose);
		$this->set('purposeValue', $purposeValue);
		$this->set('skipInvoice', $skipInvoice);

		return parent::display('site/checkout/default/default');
	}
}
