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

class PayplansControllerPlan extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('plans');
		
		// Map the alias methods here.
		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('apply', 'store');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('visible', 'toggleVisible');
		$this->registerTask('invisible', 'toggleVisible');

		$this->registerTask('close', 'cancel');
		$this->registerTask('remove', 'delete');

	}

	/**
	 * Display plan edit form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');

		$editLink = 'index.php?option=com_payplans&view=plan&layout=form';
		if ($id) {
			$editLink .= '&id=' . $id;
		}

		return $this->app->redirect($editLink);
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=plan');
	}

	/**
	 * Method to decorate the data for passing in to bind
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function decorateData($values)
	{
		$config = PP::config();

		// decorate the data
		$data = array();
		$details = array();
		$params = array();

		$data['plan_id'] = isset($values['plan_id']) ? $values['plan_id'] : $this->input->get('plan_id', 0, 'int');
		$data['title'] = $values['title'];
		$data['published'] = $values['published'];
		$data['visible'] = $values['visible'];
		$data['description'] = $values['description'];
		// $data['planapps'] = isset($values['planapps']) ? $values['planapps'] : '';
		$data['groups'] = isset($values['groups']) ? $values['groups'] : '';

		// details
		$details['expirationtype'] = $values['expirationtype'];
		$details['expiration'] = $values['expiration'];
		$details['recurrence_count'] = $values['recurrence_count'];
		$details['price'] = $values['price'];
		$details['trial_price_1'] = $values['trial_price_1'];
		$details['trial_time_1'] = $values['trial_time_1'];
		$details['trial_price_2'] = $values['trial_price_2'];
		$details['trial_time_2'] = $values['trial_time_2'];
		// currency should default to configuration
		$details['currency'] = $config->get('currency');
		// make sure the expiration for 'forever' is set to 0
		if ($details['expirationtype'] == 'forever') {
			$details['expiration'] = '000000000000';
		}

		$data['details'] = $details;

		// params
		$params['teasertext'] = $values['teasertext'];
		$params['redirecturl'] = $values['redirecturl'];
		$params['badgeVisible'] = $values['badgeVisible'];
		$params['badgePosition'] = $values['badgePosition'];
		$params['badgeTitle'] = $values['badgeTitle'];
		$params['badgeTitleColor'] = $values['badgeTitleColor'];
		$params['badgebackgroundcolor'] = $values['badgebackgroundcolor'];
		$params['planHighlighter'] = $values['planHighlighter'];
		$params['limit_count'] = $values['limit_count'];
		$params['scheduled'] = $values['scheduled'];
		$params['start_date'] = $values['start_date'];
		$params['end_date'] = $values['end_date'];
		$params['total_count'] = $values['total_count'];
		$params['moderate_subscription'] = $values['moderate_subscription'];
		$params['parentplans'] = PP::normalize($values, 'parentplans', '');
		$params['displaychildplanon'] = $values['displaychildplanon'];
		$params['expiration_date'] = $values['expiration_date'];
		$params['subscription_from'] = $values['subscription_from'];
		$params['subscription_to'] = $values['subscription_to'];
		$params['enable_fixed_expiration_date'] = $values['enable_fixed_expiration_date'];

		$data['params'] = $params;

		return $data;
	}

	/**
	 * Method to process plan saving
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$id = $this->input->get('plan_id', 0, 'int');

		$data = $this->input->post->getArray();

		//don't save the html tags in the plan title as it will create issue in further processing in the payment gateway.
		$data['title'] = strip_tags($data['title']);

		$data['description'] = $this->input->get('description', $data['description'], 'raw');

		// decarate the data so that binding will work.
		$data = $this->decorateData($data);

		$plan = PP::plan($id);
		$plan->bind($data);

		// Free plans can never be recurred
		if ($plan->isRecurring() && $plan->isFree()) {
			$this->info->set('Recurring plans should never be free. Please set a price for your recurring plan', 'error');

			if ($plan->getId()) {
				return $this->redirectToView('plan', 'form', 'id=' . $plan->getId());
			}

			return $this->redirectToView('plan', 'form');
		}

		$libObj = $plan->save();

		if ($libObj === false) {

			$error = $plan->getError();
			$this->info->set($error->text, $error->type);

			if ($plan->getId()) {
				return $this->redirectToView('plan', 'form', 'id=' . $plan->getId());
			}

			return $this->redirectToView('plan', 'form');
		}

		$message = JText::_('COM_PAYPLANS_ITEM_SAVED_SUCCESSFULLY');
		$this->info->set($message);

		if ($this->task == 'saveNew') {
			return $this->redirectToView('plan', 'form');
		}

		if ($this->task == 'apply') {
			return $this->redirectToView('plan', 'form', 'id=' . $plan->getId());
		}

		return $this->redirectToView('plan');
	}

	/**
	 * Method to publish / unpublish
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function togglePublish()
	{
		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			$message = JText::_('Invalid IDs.');
			$this->info->set($message);
			return $this->redirectToView('plan');
		}

		$task = $this->getTask();

		$state = $task == 'publish' ? 1 : 0;

		$model = PP::model('plan');
		$model->publish($ids, $state);

		$msg = JText::_('COM_PP_PLAN_PUBLISHED_SUCCESSFULLY');

		if ($task != 'publish') {
			$msg = JText::_('COM_PP_PLAN_UNPUBLISHED_SUCCESSFULLY');
		}

		$this->info->set($msg);
		return $this->redirectToView('plan');
	}

	/**
	 * Method to toggle plan's visibility
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toggleVisible()
	{
		$ids = $this->input->get('cid', 0, 'int');

		if (!$ids) {
			$message = JText::_('Invalid IDs.');
			$this->info->set($message);
			return $this->redirectToView('plan');
		}

		$task = $this->getTask();
		$state = $task == 'visible' ? 1 : 0;

		foreach ($ids as $id) {
			$table = PP::table('plan');
			$table->load($id);
			$table->visible($state);
		}

		$msg = JText::_('COM_PP_PLAN_VISIBLE_SUCCESSFULLY');

		if ($task != 'visible') {
			$msg = JText::_('COM_PP_PLAN_INVISIBLE_SUCCESSFULLY');
		}

		$this->info->set($msg);
		return $this->redirectToView('plan');
	}


	/**
	 * Method to process plan deletion
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			$message = JText::_('Invalid IDs.');
			$this->info->set($message);
			return $this->redirectToView('plan');
		}

		foreach ($ids as $id) {
			$plan = PP::plan($id);
			$state = $plan->delete();

			if ($state === false) {
				$error = $plan->getError();

				$this->info->set($error->text, $error->type);
				return $this->redirectToView('plan');
			}
		}

		$this->info->set(JText::_('COM_PP_PLAN_DELETED_SUCCESSFULLY'));
		return $this->redirectToView('plan');
	}

	/**
	 * Method to process plan copying
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function copy()
	{
		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			$message = JText::_('Invalid IDs.');
			$this->info->set($message);
			return $this->redirectToView('plan');
		}

		foreach ($ids as $id) {
			$plan = PP::plan($id);
			$plan->setId(0);
			$plan->setTitle(JText::sprintf('COM_PP_COPY_OF', $plan->getTitle()));

			// reset ordering
			$plan->setOrdering(0);
			$state = $plan->save();

			if ($state === false) {

				$error = $plan->getError();
				$this->info->set($error->text, $error->type);

				return $this->redirectToView('plan');
			}
		}

		$this->info->set(JText::_('COM_PP_PLAN_COPIED_SUCCESSFULLY'));
		return $this->redirectToView('plan');
	}

	public function recurrencevalidation()
	{
		return true;
	}

	/**
	 * Method to update the ordering of plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		$cid = $this->input->get('cid', array(), 'array');
		$ordering = $this->input->get('order', array(), 'array');

		if (!$cid) {
			$message = JText::_('Invalid IDs.');
			$this->info->set($message);
			return $this->redirectToView('plan');
		}

		$model = PP::model('plan');

		for($i = 0; $i < count($cid); $i++) {

			$id = $cid[$i];
			$order = $ordering[$i];

			$model->updateOrdering($id, $order);
		}

		$this->info->set(JText::_('COM_PP_PLAN_ORDERED_SUCCESSFULLY'));
		return $this->redirectToView('plan');
	}

	/**
	 * Move up the ordering
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function moveUp()
	{
		$direction = $this->input->get('direction', 'asc');
		if ($direction == 'desc') {
			return $this->move(1);
		}

		return $this->move(-1);
	}

	/**
	 * Move down the ordering
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function moveDown()
	{
		$direction = $this->input->get('direction', 'asc');

		if ($direction == 'desc') {
			return $this->move(-1);
		}

		return $this->move(1);
	}

	/**
	 * Allow caller to move the ordering up/down 
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	private function move($index)
	{
		$layout = $this->input->get('layout', '', 'cmd');

		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			$message = JText::_('Invalid IDs.');
			$this->info->set($message);
			return $this->redirectToView('plan');
		}

		foreach ($ids as $id) {
			$table = PP::table('plan');
			$table->load($id);

			$table->move($index);
		}

		$this->info->set(JText::_('COM_PP_PLAN_ORDERED_SUCCESSFULLY'));
		return $this->redirectToView('plan');
	}
}
