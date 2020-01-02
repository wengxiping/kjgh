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

class PPEventAddon extends PayPlans
{
	/**
	 * Triggered when an invoice is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansInvoiceAfterSave($previousInvoice, $newInvoice)
	{
		// only change the consumed quantity only after invoice paid
		if ($newInvoice->getStatus() != PP_INVOICE_PAID) {
			return true;
		}

		// we only increase the consumed count one time
		if ($previousInvoice->getStatus() != PP_INVOICE_PAID && $newInvoice->getStatus() == PP_INVOICE_PAID) {

			$model = PP::model('Addons');
			$services = $model->getPurchasedServices($newInvoice->getId());

			if ($services) {
				foreach ($services as $service) {
					$addon = PP::table('Addon');
					$addon->load($service->planaddons_id);

					if ($addon->planaddons_id) {

						$cosumed = (int) $addon->consumed;
						$cosumed++;

						$addon->consumed = $cosumed;
						$addon->store();
					}
				}
			}
		}

		return true;
	}

}
