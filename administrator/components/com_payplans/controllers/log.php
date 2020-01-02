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

class PayplansControllerLog extends PayplansController
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('logs');
	}
	
	/**
	 * Purge ipn history
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purgeIpn()
	{
		$model = PP::model('Log');
		$model->purgePaymentNotifications();

		$this->info->set('COM_PP_PURGED_PAYMENT_NOTIFICATIONS_SUCCESSFULLY');

		return $this->redirectToView('log', 'payments');
	}

	/**
	 * Purge ipn history
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purge()
	{
		$model = PP::model('Log');
		$model->purgeAll();

		$this->info->set('All logs purged from the site successfully');

		return $this->redirectToView('log');
	}

	/**
	 * Removes a log from the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remove()
	{
		$ids = $this->input->get('cid', array(), 'array');

		if ($ids) {
			foreach ($ids as $id) {
				$log = PP::table('Log');
				$log->load((int) $id);

				$log->delete();
			}
		}
		$this->info->set('Selected log records has been deleted successfully', 'success');
		return $this->redirectToView('log');
	}
	
}