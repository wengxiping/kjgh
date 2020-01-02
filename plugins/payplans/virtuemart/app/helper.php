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

class PPHelperVirtueMart extends PPHelperStandardApp
{
	private $_resource = 'com_virtuemart.group';

	/**
	 * Add shopper to group
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addToShoppersGroup($userId, $group, $subId)
	{
		if (!$group) {
			$this->deleteShopperToGroup($userId);
			return true;
		}
		
		$shoppergroupId = $this->_getShopperGroup($userId);

		// Ensure this current user id doesn't have add into any shopper group then only add it
		if (!$shoppergroupId || is_null($shoppergroupId)) {
			$this->insertShopperToGroup($userId, $group);
		} else {
			$this->removeResource($subId, $userId, $group, $this->_resource);
			$this->updateShopperToGroup($userId, $group);
		}

		$this->addResource($subId, $userId, $group, $this->_resource);
		
		return true;
	}

	// /**
	//  * Remove shopper from group
	//  *
	//  * @since	4.0.0
	//  * @access	public
	//  */
	// public function removeFromShoppersGroup($userId, $group, $subId)
	// {
	// 	if (!$group) {
	// 		return true;
	// 	}
		
	// 	$shoppergroupId = $this->_getShopperGroup($userId);

	// 	if ($shoppergroupId && !is_null($shoppergroupId)) {
	// 		$this->addResource($subId, $userId, $group, $this->_resource); 
	// 		$this->_deleteShopperToGroup($userId);

	// 		return true;
	// 	}
	// }
	
	public function _getShopperGroup($userId)
	{
		$db = PP::db();
		$query = 'SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_vmuser_shoppergroups` WHERE `virtuemart_user_id` = '.$userId;

		$db->setQuery($query);
		
		return $db->loadResult();
	}
	
	public function insertShopperToGroup($userId, $groupId)
	{
		$db = PP::db();
		$query = 'INSERT INTO `#__virtuemart_vmuser_shoppergroups` (`virtuemart_user_id`, `virtuemart_shoppergroup_id`) VALUES (' . $userId . ',' . $groupId . ')';
		
		$db->setQuery($query);

		return $db->query();
	}
	
	public function updateShopperToGroup($userId, $groupId)
	{
		$db = PP::db();
		$query = 'UPDATE `#__virtuemart_vmuser_shoppergroups` SET `virtuemart_shoppergroup_id` = ' . $groupId . ' WHERE `virtuemart_user_id` = ' . $userId;
		
		$db->setQuery($query);
		
		return $db->query(); 
	}
	
	public function deleteShopperToGroup($userId)
	{
		$db = PP::db();
		$query = 'DELETE FROM `#__virtuemart_vmuser_shoppergroups`  WHERE `virtuemart_user_id` = ' . $userId;

		$db->setQuery($query);
		
		return $db->query();
	}
	
	public static function getShopperGroups()
	{
		$db = PP::db();
		$query = 'SELECT * FROM `#__virtuemart_shoppergroups`';
	 	$db->setQuery( $query );

	 	$groups = $db->loadObjectList('virtuemart_shoppergroup_id'); 

	 	return $groups; 
	}

	public function getUserShopperGroup($userId)
	{
		$db = PP::db();

		$query = 'SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_vmuser_shoppergroups` WHERE `virtuemart_user_id` = ' . $userId;
		$db->setQuery( $query );
		
		$shopperGroupId = $db->loadResult();

		$query = 'SELECT `shopper_group_name` FROM `#__virtuemart_shoppergroups` WHERE `virtuemart_shoppergroup_id` = ' . $shopperGroupId;
	
		$db->setQuery( $query );
		$shopperGroup = $db->loadResult();
		
		return $shopperGroup;
	}
}