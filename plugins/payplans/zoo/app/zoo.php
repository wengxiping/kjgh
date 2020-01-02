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

require_once(__DIR__ . '/lib.php');

class PPAppZoo extends PPApp
{
	protected $_resource = 'com_zoo.submission';

	/**
	 * Determine if it's applicable for the event to trigger this app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventname='')
	{
		$lib = $this->helper->getLib();

		if (!$lib->exists()) {
			return false;
		}

		return true;
	}

	/**
	 * Trigger after subscription is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// no need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		$newStatus  = $new->getStatus();
		
		$restrictionOn = $this->getAppParam('controlOn','on_view');
		if ($restrictionOn != 'on_submit' && $restrictionOn != 'on_both') {
			return true;
		}

		$zooCategory = array();
		$postInCategory	= $this->getAppParam('add_entry_in','any_category');

		if ($postInCategory == 'any_category') {
			$zooCategory[] = 0;
		} else{	
			$zooCategory = $this->getAppParam('zoo_category', $zooCategory);
		}

		$allowedEntries = $this->getAppParam('allowedSubmissions',0);
		$allowedSubmissions = explode(',', $allowedEntries);
		$subId = $new->getId();
		$userId = $new->getBuyer()->getId();

		if ($newStatus == PP_SUBSCRIPTION_ACTIVE) {
			// action is publish/feature
			$action = 1;

			for ($i = 0; $i < count($zooCategory); $i++) {
				if (!isset($allowedSubmissions[$i])) {
					$allowedSubmissions[$i]= $allowedSubmissions[count($allowedSubmissions) - 1];
				}

				$this->_addToResource($subId, $userId, $zooCategory[$i], $this->_resource . $zooCategory[$i], $allowedSubmissions[$i]);
				$this->helper->publishOrUnpublishEntry($userId, $allowedSubmissions[$i], $action, $zooCategory[$i]);
			}
		}

		if (($prev != null && $prev->getStatus() == PP_SUBSCRIPTION_ACTIVE) && ($newStatus == PP_SUBSCRIPTION_EXPIRED || $newStatus == PP_SUBSCRIPTION_HOLD)) {
			$action = 0;

			for ($i = 0; $i < count($zooCategory); $i++) {
				if (!isset($allowedSubmissions[$i])) {
					$allowedSubmissions[$i] = $allowedSubmissions[count($allowedSubmissions) - 1];
				}

				$this->_removeFromResource($subId, $userId, $zooCategory[$i], $this->_resource . $zooCategory[$i], $allowedSubmissions[$i]);
				$this->helper->publishOrUnpublishEntry($userId, $allowedSubmissions[$i], $action,$zooCategory[$i]);
			}
		}

		return true;
	}
}
