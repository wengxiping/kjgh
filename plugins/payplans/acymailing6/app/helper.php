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

class PPHelperAcymailing6 extends PPHelperStandardApp
{
	protected $_location = __FILE__;
	protected $_resource = 'com_acym.list';

	/**
	 * Determines if Acymailing exists on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			$enabled = JComponentHelper::isEnabled('com_acym');
			$file = JPATH_ROOT . '/administrator/components/com_acym/helpers/helper.php';

			$fileExists = JFile::exists($file);
			$exists = false;

			if ($enabled && $fileExists) {
				$exists = true;
				require_once($file);
			}
		}

		return $exists;
	}

	/**
	 * Retrieve a list of list name from Acymailing
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function listsName($value)
	{
		$db = PP::db();
		$query = 'SELECT `name` FROM `#__acym_list` WHERE `id` = "' . $value . '"';
		$db->setQuery($query);
		$result = $db->loadResult();
		
		return $result;
	}

	/**
	 * Retrieve a list of listid from Acymailing
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getAcymailingList()
	{
		$db = PP::db();

		$query = 'SELECT `id` as list_id, `name` FROM `#__acym_list`';
		$db->setQuery($query);
		$result = $db->loadObjectList('list_id');

		return $result;
	}

	/**
	 * Forcefully remove the user from provided mailing list, irrespective of plan subscription.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeForcefully($userId, $removalList) 
	{
		$removeFromListActive = is_array($removalList) ? $removalList : array($removalList);

		$lib = acym_get('class.user');
		$newSubscription = array();

		foreach ($removeFromListActive as $remListId) {
			$newList = null;
			$newList['status'] = 0;
			$newSubscription[$remListId] = $newList;
		}

		//this function returns the ID of the user stored in the
		//AcyMailing table from a Joomla User ID or an e-mail address
		$subid = $lib->getUserSubscriptionById($userId);
		$result = $lib->subscribe($subid, $newSubscription);
		
		return $result;
	}

	/**
	 * Proceed add or remove subscription from Acymailing list
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addOrRemoveFromAcymailingList($userid, $addToList, $removeFromList, $subscriptionId)
	{
		$lib = acym_get('class.user');
		$newSubscription = array();

		$user = PP::user($userid);

		$userExist = $lib->getOneByEmail($user->email);
		$subid = $userExist->id;

		if (!$subid) {
			$myUser = new stdClass();
			$myUser->email = $user->email;
			$myUser->name = $user->name;
		
			$subid = $lib->save($myUser);
		}

		// user subscribe to this list id
		$subscribe = is_array($addToList) ? $addToList : array($addToList); 

		// user who want to remove this list id
		$removeListIds = is_array($removeFromList) ? $removeFromList : array($removeFromList);

		if (!empty($removeListIds)) {

			foreach ($removeListIds as $listId) {
				
				if (is_array($listId)){
					$this->addOrRemoveFromAcymailingList($userid, array(), $listId, $subscriptionId);

				} else {

					$status = $this->removeResource($subscriptionId, $userid, $listId, $this->_resource);
					// we didn't find the user in the AcyMailing tables
					if (empty($subid)) {
						return false;
					}

					$result = $lib->unsubscribe($subid, $listId);
				}
			}
		}

		if (!empty($subscribe)) {

			foreach ($subscribe as $listId) {
				$newList = null;
				$newList['status'] = 1;
				$newSubscription[$listId] = $newList;

				$this->addResource($subscriptionId, $userid, $listId, $this->_resource);
			}
		}

		if (empty($newSubscription)) {
			return;
		}

		// we didn't find the user in the AcyMailing tables
		if (empty($subid)) {
			return false;
		}

		$result = $lib->subscribe($subid, $subscribe);

		return $result;
	}	
}