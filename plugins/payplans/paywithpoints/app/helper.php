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

jimport('joomla.filesystem.file');

require_once(__DIR__ . '/lib/abstract.php');

class PPHelperPaywithpoints extends PPHelperPayment
{
	/**
	 * Retrieve point type to be used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPointsType()
	{
		static $type = null;

		if (is_null($type)) {
			$type = $this->params->get('pointsType', '');
		}

		return $type;
	}

	/**
	 * Retrieves the points adapter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAdapter(PPUser $user)
	{
		static $adapter = null;

		if (is_null($adapter)) {
			$type = $this->getPointsType();

			$file = __DIR__ . '/lib/' . $type . '.php';

			require_once($file);

			$className = 'PPPaywithpoints' . ucfirst($type);
			$adapter = new $className($user);
		}

		return $adapter;
	}

	/**
	 * Retrieves points for a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPoints(PPUser $user)
	{
		$adapter = $this->getAdapter($user);

		if ($adapter === false) {
			$this->setError('Invalid points source');
			return false;
		}

		$points = $adapter->getPoints();

		return $points;
	}

	/**
	 * Retrieves the cost for points
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPointsCost()
	{
		static $cost = null;

		if (is_null($cost)) {
			$type = $this->getPointsType();

			if ($type == 'aup') {
				$cost = $this->params->get('aup_points', 0);
			}

			if ($type == 'karma') {
				$cost = $this->params->get('karma_points', 0);
			}

			if ($type == 'easysocial') {
				$cost = $this->params->get('easysocial_points', 0);
			}
		}

		return $cost;
	}
	
	/**
	 * Determines if a user has sufficient points
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasSufficientPoints(PPUser $user)
	{
		static $sufficient = null;

		if (is_null($sufficient)) {
			$points = $this->getPoints($user);
			$cost = $this->getPointsCost();

			$sufficient = ($points >= $cost);
		}
		return $sufficient;
	}

	/**
	 * Process deduction of points
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process(PPPayment $payment, $data, $pendingRecurrence = null)
	{
		$invoice = $payment->getInvoice();

		// Need to apply discount as a way to offset the payment (100% discount)
		$modifier = PP::createModifier($invoice, -100, true, 'pay_with_points', JText::_('COM_PAYPLANS_PAY_WITH_POINTS_MESSAGE'));
		$modifier->save();

		// Refresh the invoice
		$invoice->refresh()->save();
		
		$transaction = PP::createTransaction($invoice, $payment, 0, 0, 0, $data);
		$transaction->amount = 0;
		$transaction->message = 'COM_PAYPLANS_APP_PAY_WITH_POINTS_TRANSACTION_CREATED_FOR_INVOICE';
		$transaction->save();

		//update recurrence count in payment gateway params
		if ($invoice->isRecurring()) {
			if ($pendingRecurrence == null) {
				$pendingRecurrence = $this->getRecurrenceCount($invoice) - 1;
			}

			$params = new JRegistry();
			$params->set('pending_recur_count', $pendingRecurrence);

			$payment->gateway_params = $params->toString();
			$payment->save();
		}
	
		// Now we need to deduct user's points
		$adapter = $this->getAdapter($invoice->getBuyer());
		
		$cost = $this->getPointsCost();
		$state = $adapter->deduct(-$cost);

		return $state;
	}

	/**
	 * Retrieves the recurrence count
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceCount(PPInvoice $invoice)
	{
		$count = $invoice->getRecurrenceCount();

		// for lifetime recurrence
		if (intval($count) === 0) {
			return 9999;
		}
		
		$recurring = $invoice->isRecurring();
		if ($recurring) {
			
			// Recurrence Count For Regular Recurring Plan
			if($recurring == PP_RECURRING){
				$recurrence_count = $count;
			}
			// Recurrence Count For Recurring + Trial 1 Plan
			if($recurring == PP_RECURRING_TRIAL_1){
				$recurrence_count = $count + 1;
			}
			// Recurrence Count For Recurring + Trial 2 Plan
			if($recurring == PP_RECURRING_TRIAL_2){
				$recurrence_count = $count + 2;
			}
		}
		return $recurrence_count;
	}

}