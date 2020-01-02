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

class PayPlansViewThanks extends PayPlansSiteView
{
	/**
	 * Renders the thank you page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$invoiceId = $this->getKey('invoice_key');
		$invoice = PP::invoice($invoiceId);
		$buyer = $invoice->getBuyer();

		// Check if the current viewer can view the thank you page
		if (!$invoice->canView($this->my->id)) {
			die('Not allowed to view');
		}

		$plan = $invoice->getPlan();
		$modifiers = $invoice->getModifiers();
		$registration = PP::registration();
		$redirectUrl = $plan->getRedirecturl();

		$this->set('registration', $registration);
		$this->set('modifiers', $modifiers);
		$this->set('plan', $plan);
		$this->set('invoice', $invoice);
		$this->set('user', $buyer);
		$this->set('redirectUrl', $redirectUrl);

		return parent::display('site/thanks/default/default');
	}
}
