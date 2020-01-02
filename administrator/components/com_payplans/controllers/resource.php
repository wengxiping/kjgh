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

class PayplansControllerResource extends PayplansController
{
	public function __construct()
	{
		parent::__construct();
		
		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('apply', 'store');
	}

	/**
	 * Allows admin to delete resource
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		$ids = $this->input->get('cid', array(), 'int');

		if ($ids) {
			foreach ($ids as $id) {
				$table = PP::table('Resource');
				$table->load($id);

				$table->delete();
			}
		}

		$this->info->set('Selected resource has been deleted from the site');

		return $this->redirectToView('resource');
	}

	/**
	 * Saves a resource record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$id = $this->input->get('id', 0, 'int');
		$subscriptionIds = $this->input->get('subscription_ids', array(), 'array');
		$data = $this->input->post->getArray();

		if ($subscriptionIds) {
			$data['subscription_ids'] = ','.implode(',', $subscriptionIds).',';
		}

		$resource = PP::table('Resource');
		$resource->load($id);
		$resource->bind($data);

		$resource->store();

		$task = $this->getTask();
		$this->info->set('Resource updated successfully');

		if ($task == 'apply') {
			return $this->redirectToView('resource', 'form', 'id=' . $resource->resource_id);
		}

		return $this->redirectToView('resource');
	}

}
