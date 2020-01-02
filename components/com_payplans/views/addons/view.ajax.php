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

class PayPlansViewAddons extends PayPlansSiteView
{
	/**
	 * add / remove addons into invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateCharges()
	{
		// Ensure that addons is really enabled from Payplans
		if (!$this->config->get('addons_enabled')) {
			throw new Exception('Addons has been disabled.');
		}

		// Get the plan the user is trying to purchase
		$invoiceKey = $this->input->get('invoice_key', '', 'default');
		$updateType = $this->input->get('update_type', '', 'default');
		$planAddon = $this->input->get('plan_addons', '', 'default');

		if (!$invoiceKey || !$updateType || !$planAddon) {
			throw new Exception('Invalid data.');
		}

		$invoiceId = (int) PP::encryptor()->decrypt($invoiceKey);
		$invoice = PP::invoice($invoiceId);

		if (!$invoice->getId()) {
			throw new Exception('Invalid invoice.');
		}

		$state = $invoice->updateAddonServices($planAddon, $updateType);

		if ($state === false) {
			return $this->reject(JText::_('Something went wrong. Please contact site administrator.'));
		}

		$invoice->refresh();
		$invoice->save();

		// Get new total for the invoice
		$theme = PP::themes();
		$total = $theme->html('html.amount', $invoice->getTotal(), $invoice->getCurrency());
		$html = '';

		// get all the modifiers again.
		$modifiers = $invoice->getModifiers();
		PPHelperModifier::getTotal($invoice->getSubtotal(), $modifiers);

		$theme->set('modifiers', $modifiers);
		$theme->set('invoice', $invoice);
		$html = $theme->output('site/checkout/default/modifier');

		return $this->resolve($html, $total, $invoice->getTotal(), $invoice->isRecurring());
	}
}
