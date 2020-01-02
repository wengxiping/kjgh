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

PP::import('admin:/includes/model');

class PayPlansModelSubscriptions extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('subscriptions');
	}

	/**
	 * Returns a list of menus for the admin sidebar.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionCounts($firstDate, $lastDate, $status)
	{
		$db = $this->db;
		$query = $db->getQuery(true);

		// $status = array(PP_SUBSCRIPTION_EXPIRED,PP_SUBSCRIPTION_ACTIVE);
		$query->select('count(subscription_id) as count, status')
			->from('#__payplans_subscription')
			->where('status  IN ('.implode(',', $status).')')
			->where('date(subscription_date) >= '. "date('".$firstDate."')")
			->where('date(subscription_date) <= '. "date('".$lastDate."')")
			->group('status');

		$db->setQuery($query);
		$results = $db->loadObjectList('status');

		return $results;
	}

}
