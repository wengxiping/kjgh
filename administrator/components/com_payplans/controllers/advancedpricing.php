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

class PayplansControllerAdvancedpricing extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('plans');
		
		$this->registerTask('save', 'save');
		$this->registerTask('savenew', 'save');
		$this->registerTask('apply', 'save');

		$this->registerTask('close', 'cancel');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');
	}

	/**
	 * Delete a list of advancedpricing from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		$ids = $this->input->get('cid', 0, 'int');

		foreach ($ids as $id) {
			$table = PP::table('Advancedpricing');
			$table->load($id);
			$table->delete();
		}

		$this->info->set('COM_PP_ITEM_DELETED_SUCCESS');
		return $this->redirectToView('advancedpricing');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=advancedpricing');
	}

	/**
	 * Allow caller to toggle published state
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function togglePublish()
	{

		$ids = $this->input->get('cid', 0, 'int');
		$task = $this->getTask();

		foreach ($ids as $id) {
			$table = PP::table('Advancedpricing');
			$table->load($id);

			$table->$task();
		}

		$message = $task == 'publish' ? 'COM_PP_ITEM_PUBLISHED_SUCCESSFULLY' : 'COM_PP_ITEM_UNPUBLISHED_SUCCESSFULLY';

		$this->info->set($message);
		return $this->redirectToView('advancedpricing');
	}

	/**
	 * Saves a advanced pricing item
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		$id = $this->input->get('id', 0, 'int');
		$data = $this->input->post->getArray();

		if (empty($data['title'])) {
			$this->info->set('COM_PP_TITLE_REQUIRED', 'error');
			return $this->redirectToView('advancedpricing', 'form');
		}

		if (empty($data['units_min']) || empty($data['units_max'])) {
			$this->info->set('COM_PP_ADV_PRICING_MIN_MAX_INVALID', 'error');
			return $this->redirectToView('advancedpricing', 'form');
		}

		$prices = $data['price'];
		$durations = $data['duration'];

		if (!$prices || !$durations) {
			$this->info->set('COM_PP_AT_LEAST_ONE_PRICESET_REQUIRED', 'error');
			return $this->redirectToView('advancedpricing', 'form');
		}

		$table = PP::table('Advancedpricing');
		$table->load($id);
		$table->bind($data);

		$plans = $title = PP::normalize($data, 'plans', array());

		// Save plans
		$table->plans = json_encode($plans);

		// Save price set
		$priceSet = new stdClass();
		$priceSet->price = $prices;
		$priceSet->expiration_time = $durations;

		$table->params = json_encode($priceSet);
		
		if (!$id) {
			$table->created_date = PP::date()->toSql();
		}

		$state = $table->store();

		if ($state === false) {
			$this->info->set('COM_PP_MODIFIER_SAVED_FAILED', 'error');
			return $this->redirectToView('advancedpricing', 'form');
		}

		$message = $id ? 'COM_PP_ADV_PRICING_SAVED_SUCCESS' : 'COM_PP_ADV_PRICING_CREATED_SUCCESS';

		$this->info->set($message, 'success');

		$task = $this->getTask();

		if ($task == 'apply') {
			return $this->redirectToView('advancedpricing', 'form', 'id=' . $table->getId());
		}

		if ($task == 'savenew') {
			return $this->redirectToView('advancedpricing', 'form');
		}

		return $this->redirectToView('advancedpricing');
	}

}