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

class PPAppVirtueMart extends PPApp
{
	/**
	 * Triggered after subscription save
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		if ($prev != null && ($prev->getStatus() == $new->getStatus())) {
			return true;
		}
		
		$active = $this->getAppParam('addToShoppersGroupOnActive', 0);
		$hold = $this->getAppParam('addToShoppersGroupOnHold', 0);
		$expire = $this->getAppParam('addToShoppersGroupOnExpire', 0);

		$subId = $new->getId();
		$user = $new->getBuyer();	
		$userId = $user->getId();	

		if ($new->isActive()) {
			$this->helper->addToShoppersGroup($userId, $active, $subId);
		}
		
		if ($new->isOnHold()) {
			$this->helper->addToShoppersGroup($userId, $hold, $subId);
		}
		
		if ($new->isExpired()) {
			$this->helper->addToShoppersGroup($userId, $expire, $subId);
		}
	
		return true;
	}

	/**
	 * Renders widget html
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderWidgetHtml()
	{   
		$shopperGroups = array();

		$userId = $this->my->id;

		if ($userId) {
			$shopperGroups = $this->getUserShopperGroup($userId);
		}
	 
	    $this->assign('shopper_group',$shopperGroups);
        $data = $this->_render('widgethtml');
        return $data;
	}

}