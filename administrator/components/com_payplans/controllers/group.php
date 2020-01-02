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

class PayplansControllerGroup extends PayPlansController
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

		$this->registerTask('close', 'cancel');
		$this->registerTask('remove', 'delete');

		$this->registerTask('visible', 'toggleVisible');
		$this->registerTask('invisible', 'toggleVisible');
	}

	/**
	 * Method to publish / unpublish
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function togglePublish()
	{
		$ids = $this->input->get('cid', 0, 'int');
		
		$task = $this->getTask();

		foreach ($ids as $id) {
			$table = PP::table('Group');
			$table->load($id);
			$table->$task();
		}

		$message = $task == 'publish' ? 'COM_PP_GROUP_PUBLISHED_SUCCESSFULLY' : 'COM_PP_GROUP_UNPUBLISHED_SUCCESSFULLY';

		$this->info->set(JText::_($message));
		return $this->redirectToView('group');
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
		$task = $this->getTask();

		$message = 'COM_PP_GROUP_VISIBLED_SUCCESSFULLY';
		$state = 1;

		if ($task == 'invisible') {
			$message = 'COM_PP_GROUP_INVISIBLED_SUCCESSFULLY';
			$state = 0;
		}

		foreach ($ids as $id) {
			$table = PP::table('Group');
			$table->load($id);
			$table->visible($state);
		}

		$this->info->set(JText::_($message));
		return $this->redirectToView('group');
	}

	public function delete()
	{
		$ids = $this->input->get('cid', 0, 'int');

		foreach ($ids as $id) {
			$group = PP::group($id);
			$group->delete();
		}

		$this->info->set(JText::_('COM_PP_GROUP_DELETE_SUCCESSFULLY'));
		return $this->redirectToView('group');
	}

	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=group');
	}

	/**
	 * Duplicates a group
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function copy()
	{
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$group = PP::group($id);
			$group->setId(0);
			$group->title = JText::_("COM_PAYPLANS_COPY_OF") . $group->getTitle();
			$state = $group->save();

			if ($state === false) {
				$this->info->set($group->getError(), 'error');
				return $this->redirectToView('group');
			}
		}

		$this->info->set('COM_PAYPLANS_ITEMS_COPIED');

		return $this->redirectToView('group');
	}

	/**
	 * Method to process plan saving
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$id = $this->input->get('id', 0, 'int');
		$data = $this->input->post->getArray();


		if (empty($data['title'])) {
			$this->info->set('COM_PP_TITLE_REQUIRED', 'error');
			return $this->redirectToView('group', 'form');
		}

		$data['description'] = isset($data['description']) ? $data['description'] : '';	
		$data['description'] = $this->input->get('description', $data['description'], 'raw');
		
		$group = PP::group($id);

		$params = $group->getParams();

		if ($data['params']) {
			foreach ($data['params'] as $key => $value) {
				$params->set($key, $value);
			}
		}

		$data['params'] = $params->toString();
		
		$group->bind($data);
		$state = $group->save();

		if ($state === false) {
			$this->view->setMessage($group->getError(), PP_MSG_ERROR);
			return $this->redirectToView('group', 'form');
		}

		$this->info->set('COM_PAYPLANS_ITEM_SAVED_SUCCESSFULLY', 'success');

		$task = $this->getTask();

		if ($task == 'savenew') {
			return $this->redirectToView('group', 'form');
		}

		if ($task == 'apply') {
			return $this->redirectToView('group', 'form', 'id=' . $group->getId());
		}

		return $this->redirectToView('group');
	}

	/**
	 * Method to update the ordering of group
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
			return $this->redirectToView('group');
		}

		$model = PP::model('group');

		for($i = 0; $i < count($cid); $i++) {

			$id = $cid[$i];
			$order = $ordering[$i];

			$model->updateOrdering($id, $order);
		}

		$this->info->set(JText::_('COM_PP_GROUP_ORDERED_SUCCESSFULLY'));
		return $this->redirectToView('group');
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
			return $this->redirectToView('group');
		}

		foreach ($ids as $id) {
			$table = PP::table('group');
			$table->load($id);

			$table->move($index);
		}

		$this->info->set(JText::_('COM_PP_GROUP_ORDERED_SUCCESSFULLY'));
		return $this->redirectToView('group');
	}
}