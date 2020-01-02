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

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansidevaffiliate extends PPPlugins
{
	/**
	 * System trigger, triggered by Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		$option = $this->input->get('option', '');
		$view = $this->input->get('view', '');
		$id = $this->input->get('idev_id', '');

		if (!$id) {
			return;
		}

		if ($option != 'com_payplans') {
			return;
		}

		if ($view != 'plan') {
			return;
		}

		$session = PP::session();
		$session->set('idev_id', $id);
	}

	/**
	 * For Tracking "Upgrade" "Invoice Paid","refund" 
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
		if ($prev->getStatus() == $new->getStatus()){
			return true;
		}

		// Do nothing if iDevaffiliate tracker url not set
		if ( !$this->params->get('url')) {
			return true;
		}
		
		//Status of Invoice
		$status = $new->getStatus();
		$user = $new->getBuyer();
		$userParams = $user->getParams();
		$helper = $this->getAppHelper();

		//Which Event to track, Decision made on status of invoice from $new
		if ($new->isConfirmed()) {
			$id = $new->getParam('idev_id');
			
			if (!$id) {
				$session = PP::session();
				$id = $session->get('idev_id');

				if ($id) {
					$new->setParam('idev_id', $id);
					$new->save();
				}
			}

			$order = $new->getReferenceObject();
	
			if ($order instanceof PPOrder) {
				$idevUserKey = 'idevaffiliate' . $new->getId();
				$idevUser = $userParams->get($idevUserKey, '');

				if (!$idevUser) {
					$userParams->set($idevUserKey, $helper->getUserIp());

					$user->params = $userParams->toString();
					$user->save();
				}
			}
		}

		if ($new->isPaid()) {
			$isUpgrade = $helper->isUpgrade($prev, $new);

			if ($isUpgrade) {
				$helper->trackUpgrade($prev, $new, $this->params->get('url'));
			}

			$helper->trackPaid($new, $isUpgrade, $this->params->get('url'));
		}

		if ($new->isRefunded() && $this->params->get('track_refund', 0)) {
			$helper->trackRefund($new, $this->params->get('url'));
		}

		return true;	
	}
}