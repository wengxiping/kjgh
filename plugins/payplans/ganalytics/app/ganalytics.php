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

class PPAppGanalytics extends PPApp
{
	public function isApplicable($refObject = null, $eventName='')
	{
		if ($refObject === null || !($refObject instanceof PayPlansViewThanks) || $eventName != 'onPayplansViewAfterRender') {
			return false;
		}

		$input = JFactory::getApplication()->input;
		$invoiceId = PP::getIdFromKey($input->get('invoice_key', ''));

		$newRefObject = PP::invoice($invoiceId);
		
		return parent::isApplicable($newRefObject, $eventName);
	}

	/**
	 * Triggered when rendering the page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansViewAfterRender($view, $task, $output)
	{
		// Since this is only called when view=thanks, we can safely add the analytics codes
		$invoiceId = PP::getIdFromKey($this->input->get('invoice_key'));
		$invoice = PP::invoice($invoiceId);
		$order = $invoice->getReferenceObject();

		if (!($order instanceof PPOrder)) {
			return '';
		}

		$plan = $order->getPlan();
		$planTitle = $plan->getTitle();
		$invoiceTitle = $invoice->getTitle();
		$invoiceKey = $invoice->getKey();
		$total = $invoice->getTotal();
		$price = $invoice->getParams()->get('price');
		$analyticsId = $this->helper->getAnalyticsId();

		$this->set('analyticsId', $analyticsId);
		$this->set('invoiceKey', $invoiceKey);
		$this->set('total', $total);
		$this->set('price', $price);
		$this->set('planTitle', $planTitle);
		$this->set('invoiceTitle', $invoiceTitle);
		
		$script = $this->display('script');

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		return true;
	}
}