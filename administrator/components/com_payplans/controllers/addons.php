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

class PayplansControllerAddons extends PayPlansController
{
	protected $_defaultOrderingDirection = 'ASC';

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
	 * Deletes addon
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function delete()
	{
		$ids = $this->input->get('cid', 0, 'int');

		foreach ($ids as $id) {
			$discount = PP::addon((int) $id);
			$discount->delete();
		}

		$this->info->set('COM_PP_SELECTED_ADDONS_DELETED_SUCCESS');

		return $this->redirectToView('addons');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=addons');
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
			$table = PP::table('Addon');
			$table->load($id);

			$table->$task();
		}

		$message = $task == 'publish' ? 'COM_PP_ITEM_PUBLISHED_SUCCESSFULLY' : 'COM_PP_ITEM_UNPUBLISHED_SUCCESSFULLY';

		$this->info->set($message);
		return $this->redirectToView('addons');
	}

	public function updateStatStatus()
	{
		$id = $this->input->get('id', 0, 'int');
		$status = $this->input->get('status', 0, 'int');

		if (!$id) {
			$message = JText::_('Invalid IDs.');;
			$this->view->setMessage($message, PP_MSG_WARNING);
			return $this->view->call(__FUNCTION__);
		}

		$table = PP::table('AddonStat');
		$table->load($id);

		$table->status = $status;
		$state = $table->store();

		$msg = JText::_('COM_PP_ADDONS_STAT_STATUS_UPDATE_SUCCESSFULLY');

		if (!$state) {
			$msg = JText::_('COM_PP_ADDONS_STAT_STATUS_UPDATE_FAILED');
		}

		$this->view->setMessage($msg);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Saves the addon
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function save()
	{
		$id = $this->input->get('id', 0, 'int');
		$data = $this->input->post->getArray();

		$addon = PP::addon($id);
		$addon->bind($data);

		// dump($data, $addon);

		// if (!$coreDiscount && isset($data['plans'])) {
		// 	$discount->plans = json_encode($data['plans']);
		// }

		// if ($coreDiscount) {
		// 	$discount->plans = json_encode(array());
		// }

		if ($addon->apply_on) {
			$addon->plans = '';
		}

		if (!$addon->apply_on && isset($data['plans'])) {
			$addon->plans = json_encode($data['plans']);
		}

		$addon->save();

		$message = 'COM_PP_ADDONS_CREATED_SUCCESS';

		if ($id) {
			$message = 'COM_PP_ADDONS_UPDATED_SUCCESS';
		}

		$this->info->set($message, 'success');

		$task = $this->getTask();

		if ($task == 'apply') {
			return $this->redirectToView('addons', 'form', 'id=' . $addon->getId());
		}

		if ($task == 'savenew') {
			return $this->redirectToView('addons', 'form');
		}

		return $this->redirectToView('addons');
	}

}
