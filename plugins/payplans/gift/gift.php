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

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansGift extends PPPlugins
{
	/**
	 * Renders the output
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansViewBeforeExecute($view, $task)
	{
		if (!($view instanceof PayPlansViewCheckout)) {
			return true;
		}

		// Get the invoice
		$id = $view->getKey('invoice_key');
		$invoice = PP::invoice($id);
		
		if ($invoice->isRecurring() || $invoice->isFree()) {
			return true;
		}

		if (!$this->isPlanApplicable($invoice)) {
			return true;
		}

		$plan = $invoice->getPlan();
		$order = $invoice->getReferenceObject(true);
		$subscription = $order->getSubscription();

		// $count = $subscription->getParams()->get('gift_count' , 0);
		$this->set('plan', $plan);
		$this->set('invoice', $invoice);

		$output = $this->output('form');

		return array('pp-checkout-options' => $output);
	}

	/**
	 * Triggered when a subscription is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		// Do not do anything for non active and recurring subscriptions
		if (!$new->isActive() || $new->isRecurring()) {
			return true;
		}

		$plan = $new->getPlan();
		$params = $new->getParams();
		$giftCount = (int) $params->get('gift_count', 0);

		if (!$giftCount) {
			return true;
		}

		$coupons = $this->createCoupons($new, $plan, $giftCount);

		$this->notify($new, $coupons);
	}

	/**
	 * Renders the dialog contents for gifts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansGiftShowDialog($invoiceKey)
	{
		$id = PP::getIdFromKey($invoiceKey);
		$invoice = PP::invoice($id);

		if (!$invoice->validateBuyer()) {
			die('Invalid');
		}

		$this->set('invoice', $invoice);
		$output = $this->output('dialogs/form');

		$ajax = PP::ajax();

		return $ajax->resolve($output);
	}

	/**
	 * Adds modifier item when user adds the item
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAddItemRequest($invoiceKey, $quantity)
	{
		$id = PP::getIdFromKey($invoiceKey);
		$invoice = PP::invoice($id);

		if (!$invoice->validateBuyer()) {
			die('Invalid');
		}

		$ajax = PP::ajax();

		$quantity = (int) $quantity;

		if (!is_numeric($quantity) || $quantity <= 0) {
			return $ajax->reject(JText::_('COM_PP_GIFT_INVALID_QUANTITY'));
		}

		$limit = $this->getGiftsLimit();

		if ($quantity > $limit) {
			return $ajax->reject(JText::sprintf('COM_PP_GIFT_LIMIT_EXCEEDED', $limit));
		}

		$order = $invoice->getReferenceObject(true);
		$subscription = $order->getSubscription();

		$params = $subscription->getParams();
		$params->set('gift_count', $quantity);

		$subscription->params = $params->toString();
		$subscription->save();	
		
		$plan = $invoice->getPlan();

		// Delete existing modifier records
		$db = PP::db();

		$options = array(
			'type' => $db->Quote(JText::_('gift')),
			'reference' => $db->Quote('PLG_PAYPLANS_GIFT_ITEM:' . $plan->getId()),
			'invoice_id' => $db->Quote($invoice->getId())
		);

		$model = PP::model('Modifier');
		$model->deleteMany($options);

		// Insert modifier
		$price = $plan->getPrice() * $quantity;

		$applicability = $this->params->get('applicability');
		$applicability = constant('PP_MODIFIER_' . $applicability);

		$modifier = $this->getModifier($invoice, $plan);

		$user = PP::user();
		$modifier->user_id = $user->getId();
		$modifier->amount = $price;
		$modifier->frequency = PP_MODIFIER_FREQUENCY_ONE_TIME;
		$modifier->invoice_id = $invoice->getId();
		$modifier->percentage = false;
		$modifier->message = JText::_('COM_PP_GIFT_ADDITIONAL_CHARGES');
		$modifier->serial = $applicability;
		$modifier->type = JText::_('gift');
		$modifier->reference = 'PLG_PAYPLANS_GIFT_ITEM:' . $plan->getId();
		$modifier->save();

		PP::info()->set('COM_PP_GIFT_CHECKOUT_FORM_UPDATED');
		return $ajax->resolve();
	}

	/**
	 * Creates new gift coupon codes
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function createCoupons(PPSubscription $subscription, PPPlan $plan, $giftCount)
	{
		$key = 0;

		// amount of discount to be given. As 100% discount is to be given, we have set it 100.
		$amount = 100.00000;

		while ($key < $giftCount) {
			//generate a 6 digit random code. Time is included so, no duplicate can exist.
			$code = substr(number_format(time() * rand(), 0, '', ''), 0, 6); 
			$code = $this->getGiftsCouponPrefix() . $code . $subscription->getId();
			
			$codes[] = $code;

			$table = PP::table('Discount');
			$table->title = $code;
			$table->coupon_code = $code;
			$table->coupon_type = 'gift';
			$table->core_discount = 0;
			$table->coupon_amount = $amount;
			$table->plans = $plan->getId();
			$table->core_discount = 1;
			$table->published = 1;

			$params = new JRegistry();
			$params->set('extend_time_discount', '000000000000');
			$params->set('coupon_amount_type', 'percentage');
			$params->set('allowed_quantity', 1);
			$params->set('reusable', false);
			$params->set('allow_clubbing', false);

			$table->params = $params->toString();
			$table->store();

			$key++;
		}

		return $codes;
	}

	/**
	 * Retrieve plans that should be associated with the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getAssociatedPlans()
	{
		$plans = $this->params->get('applyPlan', 0);

		if (!$plans) {
			return array();
		}

		if ($plans && !is_array($plans)) {
			$plans = array($plans);
		}

		return $plans;
	}

	/**
	 * Retrieve the limit allowed by the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getGiftsLimit()
	{
		$limit = (int) $this->params->get('giftLimit', 0);

		return $limit;
	}

	/**
	 * Retrieve the limit allowed by the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getGiftsCouponPrefix()
	{
		$prefix = $this->params->get('prefix', 'GIFT_');

		return $prefix;
	}

	/**
	 * Retrieve plans that should be associated with the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getModifier(PPInvoice $invoice, PPPlan $plan)
	{
		$db = PP::db();

		$options = array(
			'invoice_id' => $db->Quote($invoice->getId()),
			'type' => $db->Quote(JText::_('gift')),
			'reference' => $db->Quote('PLG_PAYPLANS_GIFT_ITEM:' . $plan->getId())
		);

		$model = PP::model('Modifier');
		$row = $model->loadRecords($options);

		if (!$row) {
			return PP::modifier();
		}

		$modifier = PP::modifier($row);

		return $modifier;
	}

	/**
	 * Determines if the invoice plan is applicable to purchase gifts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function isPlanApplicable(PPInvoice $invoice)
	{
		$plan = $invoice->getPlan();
		$associated = $this->getAssociatedPlans();

		if (!$associated) {
			return false;
		}

		if (!in_array($plan->getId(), $associated)) {
			return false;
		}

		return true;
	}

	/**
	 * Private method to send e-mails out
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function notify(PPSubscription $subscription, $codes)
	{
		$subject = JText::_('COM_PP_GIFT_EMAIL_SUBJECT');
		$namespace = 'emails/gift/user';

		// Send notification to the buyer
		$user = $subscription->getBuyer();

		$data = array(
			'codes' => $codes
		);

		$mailer = PP::mailer();
		$mailer->send($user->getEmail(), $subject, 'emails/gift/user', $data);

		// Determines if we should notify the admins as well
		if (!$this->shouldNotifyAdmins()) {
			return;
		}
		
		// Get admin e-mails
		$emails = $mailer->getAdminEmails();
		$subject = JText::_('COM_PP_GIFT_EMAIL_SUBJECT_ADMIN');

		foreach ($emails as $email) {
			$mailer->send($email, $subject, 'emails/gift/admin', $data);
		}

		PPLog::log(PPLogger::LEVEL_INFO, JText::_("COM_PP_GIFT_LOG_MESSAGE_SEND_SUCCESSFULLY"));

		return true;
	}

	/**
	 * Determines if we should notify site admins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function shouldNotifyAdmins()
	{
		return (bool) $this->params->get('sendMailTOAdmin', 0);
	}
}