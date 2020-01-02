<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansBasictax extends PPPlugins
{

	// on render of order, display output
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

		// Get the invoice
		$country = 0;
		$invoiceId = $view->getKey('invoice_key');

		// in backend we use ID instead of key
		if (!$invoiceId && $this->app->isAdmin()) {
			$invoiceId = $this->input->get('id', 0, 'int');
		}

		$invoice = PP::Invoice($invoiceId);
		$invoiceKey = $invoice->getKey();
		$user = $invoice->getBuyer();
		$userId = $user->getId();

		//only apply the tax when invoice is in none or confirmed status.
		if (!in_array($invoice->getStatus(), array(PP_NONE, PP_INVOICE_CONFIRMED))) {
			return true;
		}

		// No need to apply tax for free plan
		if ($invoice->isFree()) {
			return true;
		}

		if ($userId) {

			// reset country if user not logged-in (dummy user)
			if ($userId == PP::getDummyUserId()) {
				$user->setCountry(0);
				$user->save();

			} else {

				$country = $user->getCountry();

				if ($country) {
					$helper->doTaxRequest($invoiceKey, $country);
				}
			}
		}

		$this->set('country', $country);
		$this->set('invoiceId', $invoiceId);
		$this->set('invoice_key', $invoiceKey);

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

	// on render of order, display output
	public function onPayplansTaxRequest($invoiceKey, $country)
	{
		$helper = $this->getAppHelper();

		if (!$helper->isEnabled()) {
			return true;
		}

		list($invoice, $error) = $helper->doTaxRequest($invoiceKey, $country);

		$ajax = PP::ajax();

		if ($error) {
			// display error.
			return $ajax->reject($error);
		}


		$modifiers= $invoice->getModifiers();
		$modifiers  = PPHelperModifier::rearrange($modifiers);

		// Get all modifiers
		// Generate the output for the modifier row
		$theme = PP::themes();
		$theme->set('modifiers', $modifiers);
		$theme->set('invoice', $invoice);
		$html = $theme->output('site/checkout/default/modifier');

		$total = $theme->html('html.amount', $invoice->getTotal(), $invoice->getCurrency());

		return $ajax->resolve($html, $total);
	}
}
