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
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansAupDiscount extends PPPlugins
{
	public function onPayplansViewBeforeExecute($view, $task)
	{
		if (!($view instanceof PayPlansViewCheckout)) {
			return true;
		}

		if ($this->app->isAdmin()) {
			return;
		}

		$lib = PP::aup();

		if (!$lib->exists() || !$this->my->id) {
			return;
		}

		// Get the invoice
		$id = $view->getKey('invoice_key');
		$invoice = PP::invoice($id);

		$helper = $this->getAppHelper();

		// Do not show the form if it has been used before
		if (!$helper->isEnabled() || $helper->isUsed($invoice)) {
			return;
		}

		$ratio = $helper->getRatio();
		$minimum = $helper->getMinimumPoints();
		$maximum = $helper->getMaximumPoints();
		$rounded = $helper->shouldRoundPoints();
		$end = $helper->getEndDate();

		$this->set('invoice', $invoice);
		$this->set('points', $lib->getPoints());
		$this->set('ratio', $ratio);
		$this->set('minimum', $minimum);
		$this->set('maximum', $maximum);
		$this->set('rounded', $rounded);
		$this->set('end', $end);

		$output = $this->output('form');

		$result = array('payplans_order_confirm_payment' => $output);

		return $result;
	}

	/**
	 * Triggered by plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAupDiscountRequest($invoiceId, $points)
	{
		// Only logged in users are able to perform this
		PP::requireLogin();

		$ajax = PP::ajax();
	
		$helper = $this->getAppHelper();

		$invoiceId = (int) $invoiceId;
		$points = (int) $points;

		$invoice = PP::invoice($invoiceId);
		$invoiceOwner = $invoice->getBuyer();

		if ($this->my->id != $invoiceOwner->getId()) {
			die('Not allowed');
		}
		
		$state = $helper->apply($invoice, $points);

		if (!$state) {
			return $ajax->reject($helper->getError());
		}

		PP::info()->set('COM_PP_AUP_POINTS_DEDUCTED_AND_APPLIED_ON_INVOICE');

		return $ajax->resolve();
	}
}