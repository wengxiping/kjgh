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

require_once(__DIR__ . '/formatter.php');

class PPAppAupDiscount extends PPAppDiscounts
{
	//inherited properties
	protected $_location = __FILE__;
	protected $_discountCode, $_user, $_aupuser, $_aupuserpoints;

	// Entry Function
	public function onPayplansApplyDiscount(PPInvoice $object, $discountCode)
	{
	}

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		if (!isset($prev) || $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		if ($new->isActive()) {
			$reference = 'aup_'.$this->getId();
			$this->setAppParam('totalConsumption' , PPHelperModifier::getActualConsumption($reference,'aupdiscount'));
			$this->save();
		}
	}

	//Check if current discount should be applied as per discount purpose
	public function doCheckAllowed(PPInvoice $object, $discountCode)
	{
	}

	public function doCalculateDiscount(PPInvoice $subscription, $price, $discount)
	{
	}

	public function onAupDiscountTooltipFetch()
	{
		$data = new stdClass();
		$data->ratio    = $this->getAppParam('ratio', 1);
		$data->min      = $this->getAppParam('min_aup', 0);
		$data->max      = $this->getAppParam('max_aup', 1);
		$data->round    = $this->getAppParam('round', 1);
		$data->end      = $this->getAppParam('publish_end','');

		return $data;
	}

	protected function _discountAupPoints($object)
	{

	}
}