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

class PPHelperBasicTax extends PPHelperStandardApp
{
	/**
	 * Determines if app is really enabled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isEnabled()
	{
		if (!$this->app->getId()) {
			return false;
		}

		if (!$this->app->published) {
			return false;
		}

		return true;
	}

	public function doTaxRequest($invoiceKey, $country)
	{
		$invoiceId = PP::getIdFromKey($invoiceKey);
		$invoice = PP::invoice($invoiceId);

		$args = array($invoice, $country, 0, false);
		$results = PPEvent::trigger('onPayplansApplyTax', $args, '', $invoice);

		$error = '';
		$taxes = array();

		foreach ($results as $result=>$result_val) {
			if (!is_null($result_val)) {
				$taxes[]= $result_val;
			}
		}

		// Delete existing basic tax modifier
		$model = PP::model('modifier');
		$model->deleteTypeModifiers($invoiceId, 'basictax');

		if ($taxes) {

			foreach ($taxes as $ind => $tax) {

				$modifierParams = new stdClass();
				$modifierParams->type = 'basictax';
				$modifierParams->percentage	= true;
				$modifierParams->serial = PP_MODIFIER_PERCENT_TAX;
				$modifierParams->amount = $tax->rate;
				$modifierParams->message = $tax->title;
				$modifierParams->reference = $country;
				$modifierParams->frequency = PP_MODIFIER_FREQUENCY_EACH_TIME;

				$invoice->addModifier($modifierParams);
			}

			// update user country
			$user = $invoice->getBuyer(PP_INSTANCE_REQUIRE);
			$user->setCountry($country);
			$user->save();
		}

		// update invoice
		$invoice->refresh();
		$invoice->save();

		return array($invoice, $error);
	}
}
