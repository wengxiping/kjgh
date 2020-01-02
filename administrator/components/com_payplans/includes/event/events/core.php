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

class PPEventCore extends PayPlans
{
	/**
	 * Triggered after an order is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansOrderAfterSave($previous, $current)
	{
		// Consider Previous State also
		if (isset($previous) && $previous->getStatus() == $current->getstatus()) {
			return true;
		}

		// if there is change in status of order
		switch ($current->getStatus()) {
			case PP_NONE:
				$subsStatus = PP_NONE;
				break;

			case PP_ORDER_CONFIRMED:
				$subsStatus = PP_NONE;
				break;

			case PP_ORDER_COMPLETE:
				$subsStatus = PP_SUBSCRIPTION_ACTIVE;
				break;

			case PP_ORDER_HOLD:
				$subsStatus = PP_SUBSCRIPTION_HOLD;
				break;

			case PP_ORDER_EXPIRED:
				$subsStatus = PP_SUBSCRIPTION_EXPIRED;
				break;

			case PP_ORDER_PAID:
			default:
				$subsStatus = PP_NONE;
		}

		$subs = $current->getSubscription(true);

		if (is_a($subs, 'PPSubscription')) {
			$subs->load($subs->getId());

			// no change in status then need not to update
			if ($subs->getStatus() == $subsStatus || !$subsStatus) {
				return true;
			}

			$subs->setStatus($subsStatus)->save();
		}
		return true;
	}

	/**
	 * Triggered before an order is deleted
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansOrderBeforeDelete($order)
	{
		$subscription = $order->getSubscription();

		// delete all the subscriptions linked with this order
		if (!empty($subscription)) {
			$subscription->delete();
		}

		$invoices = $order->getInvoices();

		if (!empty($invoices)) {
			foreach ($invoices as $invoice) {
				$invoice->delete();
			}
		}

		return true;
	}

	/**
	 * Triggered before an invoice is deleted
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansInvoiceBeforedelete($invoice)
	{
		$payments = $invoice->getPayment();

		// delete all the payment linked with this order
		if (!empty($payments)) {  
			$payments->delete();
		}

		//get all the transaction records
		//related to tranaction and then delete transaction
		$transactions = $invoice->getTransactions();

		if (!empty($transactions)) {
			self::deleteTransaction($transactions);
		}

		//delete all modifier related to invoice
		$modifiers = $invoice->getModifiers();

		if (!empty($modifiers)) {
			self::deleteModifiers($modifiers);
		}

		return true;
	}

	protected static function deleteTransaction($transactions = array())
	{
		foreach ($transactions as $transaction) {
			$transaction->delete();
		}
	}
	
	protected static function deleteModifiers($modifiers = array())
	{
		foreach ($modifiers as $modifier) {
			$modifier->delete();
		}
	}

	/**
	 * Triggered by cronjob
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansCron()
	{
		$cron = PP::cron();

		// Process recurring and expired subscriptions
		$cron->processSubscriptions();

		// Delete orphan orders
		$cron->deleteOrphanOrders();

		// Process Plan Scheduling
		$cron->processPlanScheduling();

		// Update the statistics data
		$cron->processStatistics();

		// Purge expired download requests
		$cron->purgeExpiredDownloads();
		
		// Process download requests
		$cron->processDownloadRequests();

		// Delete pdf invoices folder
		$cron->deletePdfInvoices();

		return true;
	}
	
	/**
	 * Append module positions based on views
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansViewAfterRender($view, $task, &$output)
	{
		$position = 'payplans-';
		$app = JFactory::getApplication();

		if ($app->isAdmin()==true) {
			$position .= 'admin-';
		}

		$name = $view->getName();

		if (isset($name)) {
			$position .=  $name . '-';
		}

		if (isset($task)) {
			$position .= $task . '-';
		}

		$theme = PP::themes();

		// Append modules
		$modulehtmlTop = $theme->renderModule($position . 'top');
		$modulehtmlBottom = $theme->renderModule($position . 'bottom');

		// update output variable
		$output = $modulehtmlTop . $output . $modulehtmlBottom;

		return true;
	}

	/**
	 * Before deleting subscription changed its status to expired
	 * so as to trigger all the app which are set on status "Subscription-expired" 
	 * and do what thay are expected to on subscription expired status before the subscription gets deleted
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionBeforeDelete($object)
	{
		// set deleteing  to true so that it won't ask for payment on order deletion
		$object->deleting = true;

		// Expire only when it is already active
		if ($object->isActive() || $object->isOnHold()) {
			$object->setStatus(PP_SUBSCRIPTION_EXPIRED);
			$object->save();
		}

		// IMP : Trigger event for resource cleaning 
		// so that app can work on this to remove the assigned resource
		$args = array($object);

		PPEvent::trigger('onPayplansSubscriptionCleanResource', $args);
		return true;
	}

	/**
	 * Internal trigger to replace tokens with proper values
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansRewriterReplaceTokens($refObject, $rewriter)
	{
		$user = false;
		
		if (method_exists($refObject, 'getBuyer')) {
			$user = $refObject->getBuyer(PP_INSTANCE_REQUIRE);
		}
		
		if (!$user && !($refObject instanceof PPUser)) {
			return;
		}
		
		$param = (!$user) ? $refObject->getParams() : $user->getParams();
		$data = $param->toArray();
		
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key]= implode("\n",$value);
			}
		}
		
		$data = (object)$data;
		$data->name = 'Userdetail';
		$rewriter->setMapping($data, false);
		return ;
	}
}
