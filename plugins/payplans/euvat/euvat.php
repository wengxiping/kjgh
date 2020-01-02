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

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';
if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansEuvat extends PPPlugins
{
	/**
	 * Event trigger on checkout page
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function onPayplansViewBeforeExecute($view, $task)
	{
		if (!($view instanceof PayPlansViewCheckout) && !($view instanceof PayPlansViewInvoice && $this->app->isAdmin())) {
			return true;
		}

		if ($this->app->isAdmin()) {
			$layout = $this->input->get('layout', '', 'default');
			if ($layout != 'form') {
				return true;
			}
		}

		$helper = $this->getAppHelper();

		if (!$helper->isEnabled()) {
			return true;
		}

		$invoiceId = $view->getKey('invoice_key');

		// in backend we use ID instead of key
		if (!$invoiceId && $this->app->isAdmin()) {
			$invoiceId = $this->input->get('id', 0, 'int');
		}

		$invoice = PP::Invoice($invoiceId);

		$country = 0;
		// default to personal
		$purpose = 1;
		$businessName = '';
		$businessVatno = '';
		$invoiceKey = $invoice->getKey();
		$userId = $invoice->getBuyer()->getId();

		//only apply the tax when invoice is in none or confirmed status.
		if (!in_array($invoice->getStatus(), array(PP_NONE, PP_INVOICE_CONFIRMED))) {
			return true;
		}


		if (!empty($userId)) {
			$user = PP::user($userId);

			if ($userId != PP::getDummyUserId()) {
				$country = $user->getCountry();

				$userPref = $user->getPreferences();

				$businessVatno = $userPref->get('tin');
				$businessName = $userPref->get('business_name');
				$purpose = $userPref->get('business_purpose', 1);

				// Apply tax as per user country
				$helper->doTaxRequest($invoiceKey, $country, $purpose, $businessVatno, $businessName);
			} else {
				// reset country if user not logged in
				$user->setCountry(0);
				$user->save();
			}	
		}

		$this->set('country', $country);
		$this->set('invoiceId', $invoiceId);
		$this->set('invoice_key', $invoiceKey);
		$this->set('purpose', $purpose);
		$this->set('business_name', $businessName);
		$this->set('business_vatno', $businessVatno);

		$purposeOptions = $helper->getPurposeOptions();
		$this->set('purposeOptions', $purposeOptions);

		$namespace = 'form';
		$position = 'pp-checkout-options';

		if ($this->app->isAdmin()) {
			$namespace = 'admin.' . $namespace;
			$position = 'pp-invoice-details';
		}

		$output = $this->output($namespace);
		$result = array($position => $output);

		return $result;
	}

	/**
	 * Event trigger on checkout page on ajax verification
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function onPayplansTaxRequest($invoiceKey, $country, $purpose = 0, $businessNo = '', $businessName = '')
	{
		$helper = $this->getAppHelper();

		if (!$helper->isEnabled()) {
			return true;
		}

		list($invoice, $error, $proceed) = $helper->doTaxRequest($invoiceKey, $country, $purpose, $businessNo, $businessName);

		$ajax = PP::ajax();

		// if ($error) {
		// 	// display error.
		// 	return $ajax->reject($error);
		// }

		// TODO: check how the tax work from backend.
		//
		// this is for when appliying discount through admin panel
		// if(XiFactory::getApplication()->isAdmin()){
		// 	$response->addScriptCall('payplans.jQuery(\'input[name="discount"]\').val', $invoice->getDiscount());
		// 	$response->addScriptCall('payplans.jQuery(\'input[name="taxamount"]\').val', $invoice->getTaxAmount());
		// 	$response->addScriptCall('payplans.jQuery(\'input[name="total"]\').val', $invoice->getTotal());
		// 	$response->sendResponse();
		// }

		$modifiers= $invoice->getModifiers();
		$modifiers  = PPHelperModifier::rearrange($modifiers);

		// Get all modifiers
		// Generate the output for the modifier row
		$theme = PP::themes();
		$theme->set('modifiers', $modifiers);
		$theme->set('invoice', $invoice);
		$html = $theme->output('site/checkout/default/modifier');

		$total = $theme->html('html.amount', $invoice->getTotal(), $invoice->getCurrency());

		return $ajax->resolve($html, $total, $error);

	}
}
