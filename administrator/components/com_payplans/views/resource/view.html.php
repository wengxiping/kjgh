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

class PayplansViewResource extends PayPlansAdminView
{
	public function display($tpl = null)
	{
		$this->heading('Resources');

		JToolbarHelper::deleteList('Are you sure you want to delete the selected resources?', 'resource.delete');

		$model = PP::model('Resource');
		$model->initStates();

		$rows = $model->getItems();
		$resources = array();

		if ($rows) {
			foreach ($rows as $row) {
				$row->user = PP::user($row->user_id);
					
				$subscriptions = $row->subscription_ids;
				$subscriptions = ltrim($subscriptions, ',');
				$subscriptions = rtrim($subscriptions, ',');

				$subscriptions = explode(',', $subscriptions);

				$row->subscriptions = array();

				if ($subscriptions) {
					foreach ($subscriptions as $subscriptionId) {
						$subscription = PP::subscription($subscriptionId);

						$row->subscriptions[] = $subscription;
					}
				}

				$resources[] = $row;
			}
		}
		// Get states used in this list
		$states = $this->getStates(array('search', 'paid_date', 'app_id', 'status', 'ordering', 'direction', 'limit'));

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');

		$this->set('resources', $resources);
		$this->set('pagination', $model->getPagination());
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limitstart', $model->getState('limitstart'));
		$this->set('states', $states);

		return parent::display('resource/default/default');
	}

	/**
	 * Renders the edit form for resource
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');

		JToolbarHelper::apply('resource.apply');
		JToolbarHelper::save('resource.save');
		JToolbarHelper::cancel();

		$resource = PP::table('Resource');
		$resource->load($id);

		$user = PP::user($resource->user_id);

		$this->set('user', $user);
		$this->set('resource', $resource);

		return parent::display('resource/form/default');
	}
}