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

class PPHelperEasyDiscussBadges extends PPHelperStandardApp
{
	protected $_resource = 'com_easydiscuss.badge';
	
	/**
	 * Retrieves the list of badges to be assigned to the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAssignedBadges()
	{
		$badges = $this->params->get('badges', array());

		if (!is_array($badges)) {
			$badges = array($badges);
		}

		return $badges;
	}
	
	/**
	 * Assign badge to a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function assign($userId, $badges)
	{
		$ids = array();
		$db = PP::db();

		foreach ($badges as $id) {
			
			$query = array();
			$query[] = 'SELECT `badge_id` FROM `#__discuss_badges_users` WHERE `user_id`='.$db->Quote($userId);
			$query[] = 'AND `badge_id`='.$db->Quote($id);

			$query = implode(' ', $query);
			$db->setQuery($query);
			$result = $db->loadColumn();

			// Check badges already assigned to user
			if ($result) {
				continue;
			}

			$query1 = array();
			$createdDate = JFactory::getDate()->toSql();

			$query1[] = 'INSERT INTO `#__discuss_badges_users` (`badge_id`, `user_id`, `created`, `published`) VALUES';
			$query1[] = '(' . $db->Quote($id) . ',' . $db->Quote($userId) . ','. $db->Quote($createdDate).', 1)';

			$query1 = implode(' ', $query1);
			$db->setQuery($query1);
			$db->Query();
			
			$ids[] = $db->insertid();
		}
		
		return $ids;
	}	

	/**
	 * Get stored badge values
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBadgeValues($userId, $subscriptionId)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT `value` FROM `#__payplans_resource` WHERE `user_id`=' . $db->Quote($userId);
		$query[] = 'AND `subscription_ids`=' . $db->Quote("'," . $subscriptionId . ",'");
		
		$query = implode(' ', $query);
		$db->setQuery($query);
		
		return $db->loadColumn();         
	}
	
	/**
	 * Remove badges from user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remove($userId, $values)
	{
		if (!$values) {
			return true;
		}

		$values = implode(',', $values);
		
		$db = PP::db();
		$query = 'DELETE FROM `#__discuss_badges_users` WHERE `id` IN ('. $badgevalues .')';
		$db->setQuery($query);
		return $db->query();
	}

	public function renderWidgetHtml()
	{	
		$userid = PP::user()->id;

		if (!$userid) {
			return '';
		}

		//get user's easydiscuss categories
		$easydiscuss_badges = $this->getAllowedBadges($userid);

		if (empty($easydiscuss_badges)) {
			return '';
		}

		$this->assign('easydiscuss_badges', $easydiscuss_badges);
		
		$data = $this->_render('widgethtml');
		return $data;
	}	
	
	public function getAllowedBadges($userid)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT a.`id`, a.`title`';
		$query[] = 'FROM `#__discuss_badges` AS a';
		$query[] = 'LEFT JOIN `#__discuss_badges_users` AS b';
		$query[] = 'ON a.`id` = b.`badge_id`';
		$query[] = 'WHERE b.`user_id` = ' . $db->Quote($userid);

		$query = implode(' ', $query);
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
