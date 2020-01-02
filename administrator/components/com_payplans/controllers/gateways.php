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

class PayplansControllerGateways extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('gateways');
		
		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		
		$this->registerTask('remove', 'delete');
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
				$this->info->set('COM_PP_UNABLE_TO_DELETE_PAYMENT_METHOD', 'error');
				return $this->redirectToView('gateways');
			}

			$app->delete();
		}

		$this->info->set('COM_PP_PAYMENT_METHODS_DELETED_SUCCESS', 'success');

		return $this->redirectToView('gateways');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=gateways');
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

		if ($id) {
			$app = PP::app()->getAppInstance($id);

			$data['core_params'] = $app->collectCoreParams($data);
			$data['app_params']  = $app->collectAppParams($data);
		} else {
			$app = PP::app();
		}

		$title = PP::normalize($data, 'title', '');

		if (!$title) {
			$this->info->set('Please set a title for your payment method', 'error');

			if ($app->getId()) {
				return $this->redirectToView('gateways', 'form', 'id=' . $app->getId());
			}

			return $this->redirectToView('gateways', 'create', 'element=' . $data['type']);
		}
		
		$app->setCoreParams($coreParams);
		$app->setAppParams($appParams);

		// All payment gateways should have an instance of "payment"
		if (!$id) {
			$data['group'] = 'payment';
		}

		$app->bind($data);

		try {
			$app->save();
		} catch (Exception $e) {
			$this->info->set($e->getMessage(), 'error');

			return $this->redirectToView('gateways', 'form', 'id=' . $app->getId());
		}

		$this->info->set('COM_PP_PAYMENT_METHOD_UPDATED_SUCCESSFULLY');

		if ($this->getTask() == 'apply') {
			return $this->redirectToView('gateways', 'form', 'id=' . $app->getId());
		}
		return $this->redirectToView('gateways');
	}
}
