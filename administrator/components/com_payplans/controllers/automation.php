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

class PayplansControllerAutomation extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('automation');

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');

		$this->registerTask('close', 'cancel');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=automation');
	}

	/**
	 * Saves a payment gateway
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$id = $this->input->get('id', 0, 'int');
		$data = $this->input->post->getArray();
		$coreParams = $this->input->get('core_params', array(), 'array');
		$appParams = $this->input->get('app_params', array(), 'array');

		$app = PP::app($id);
		$app->setCoreParams($coreParams);
		$app->setAppParams($appParams);

		// All payment gateways should have an instance of "payment"
		if (!$id) {
			$data['group'] = 'automation';
		}

		$app->bind($data);

		try {
			$app->save();
		} catch (Exception $e) {
			$this->info->set($e->getMessage(), 'error');

			return $this->redirectToView('automation', 'form', 'id=' . $app->getId());
		}

		$this->info->set('COM_PP_AUTOMATION_SCRIPT_UPDATED_SUCCESSFULLY');

		if ($this->getTask() == 'apply') {
			return $this->redirectToView('automation', 'form', 'id=' . $app->getId());
		}
		return $this->redirectToView('automation');
	}

	/**
	 * Allows remote caller to delete an app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		$ids = $this->input->get('cid', array(), 'int');

		foreach ($ids as $id) {
			$id = (int) $id;

			$app = PP::app($id);

			if (!$app->canDelete()) {
				$this->info->set('Unable to delete automation rule', 'error');
				return $this->redirectToView('automation');
			}

			$app->delete();
		}

		$this->info->set('COM_PP_AUTOMATION_SCRIPT_DELETED_SUCCESS', 'success');

		return $this->redirectToView('automation');
	}
}
