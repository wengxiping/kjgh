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

class PayplansControllerReferrals extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('referrals');
		
		$this->registerTask('saveFile', 'storeFile');
		$this->registerTask('applyFile', 'storeFile');
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
				$this->info->set('Unable to delete referral rule', 'error');
				return $this->redirectToView('referrals');
			}

			$app->delete();
		}

		$this->info->set('Referral rules deleted successfully', 'success');

		return $this->redirectToView('referrals');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->redirectToView('referrals');
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
			$app = PP::app($id);

			$data['core_params'] = $app->collectCoreParams($data);
			$data['app_params']  = $app->collectAppParams($data);
		} else {
			$app = PP::app();
		}

		// All referrals should have the group of "referral"
		$data['group'] = 'referral';
		$data['type'] = 'referral';
		
		$app->bind($data);

		$app->setCoreParams($coreParams);
		$app->setAppParams($appParams);

		try {
			$app->save();
		} catch (Exception $e) {
			$this->info->set($e->getMessage(), 'error');

			return $this->redirectToView('referrals', 'form', 'id=' . $app->getId());
		}

		$this->info->set('Referral rule updated successfully');

		if ($this->getTask() == 'apply') {
			return $this->redirectToView('referrals', 'form', 'id=' . $app->getId());
		}
		return $this->redirectToView('referrals');
	}
}
