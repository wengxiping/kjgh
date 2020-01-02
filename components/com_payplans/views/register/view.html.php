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

PP::import('site:/views/views');

class PayPlansViewRegister extends PayPlansSiteView
{
	/**
	 * Handles the registration request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$planId = $this->input->get('plan_id', 0, 'int');

		if (!$planId) {
			PP::info()->set('Plan id not provided');
			return $this->redirectToView('plan');
		}

		$registration = PP::registration();
		$adapter = $registration->getAdapter();

		// Set the plan id into the session
		$adapter->setPlanId($planId);
		$adapter->beforeStartRedirection();
		
		return $adapter->redirect();
	}
}
