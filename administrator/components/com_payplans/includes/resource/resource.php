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

class PPResource extends PayPlans
{
	/**
	 * Adds a new record into the resource
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function add($subscriptionId, $userId, $groupId, $title, $count = 0)
	{
		$resource = $this->get($userId, $groupId, $title);
		$id = 0;

		$data = array(
			'subscription_ids' => $subscriptionId,
			'value' => $groupId,
			'title' => $title,
			'user_id' => $userId,
			'count' => $count
		);

		if ($resource->resource_id) {
			$id = $resource->resource_id;

			// Add the new subscription id into it's resource
			$resource->subscription_ids = !$resource->subscription_ids ? array() : explode(',', $resource->subscription_ids);
			$resource->subscription_ids[] = $subscriptionId;

			$data['subscription_ids'] = implode(',', $resource->subscription_ids);
			$data['count'] = $resource->count + $count;
		}

		// each subscription id should be packed with comma (,)
		$data['subscription_ids'] = ',' . $data['subscription_ids'] . ',';

		$model = PP::model('Resource');
		$state = $model->save($data, $id);

		return $state;
	}

	/**
	 * Retrieves the resource record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function get($userId, $groupId, $title)
	{
		$resource = PP::table('Resource');

		$state = $resource->load(array(
								'user_id' => $userId,
								'title' => $title,
								'value' => $groupId
							));

		if ($resource->subscription_ids) {
			$resource->subscription_ids = JString::trim($resource->subscription_ids, ',');
		}

		return $resource;
	}

	/**
	 * Removes resource from PayPlans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remove($subscriptionId, $userId, $groupId, $title, $count = 0)
	{
		$resource = $this->get($userId, $groupId, $title);

		// should not remove from this group, if resource is not available
		if (!$resource->resource_id) {
			return false;
		}

		$resource->subscription_ids = explode(',', $resource->subscription_ids);

		// do not remove from this group if user was not added by this subscription
		if (!in_array($subscriptionId, $resource->subscription_ids)) {
			return false;
		}

		$data = array(
			'value' => $groupId,
			'title' => $title,
			'user_id' => $userId,
			'count' => $resource->count - $count
		);

		// if count becomes negative then set it 0
		if ($data['count'] < 0) {
			$data['count'] = 0;
		}

		// remove the currenct sub id from ids
		$resource->subscription_ids = array_diff($resource->subscription_ids, array($subscriptionId));
		$data['subscription_ids'] = implode(',', $resource->subscription_ids);

		$model = PP::model('Resource');
		$remove = false;

		// if ids are empty then return true, and remove from group
		// and delete the resource
		if (empty($data['subscription_ids'])) {
			$model->delete($resource->resource_id);
			$remove = true;
		}

		// each subscription id should be packed with comma (,)
		$data['subscription_ids'] = ','.$data['subscription_ids'].',';

		// do not remove if any ids are there
		$resource->bind($data);
		$resource->store();

		return $remove;
	}
}