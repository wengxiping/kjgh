<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="pp-result-container">
	<form action="https://www.moneybookers.com/app/payment.pl" method="post" data-pp-skrill-form>

		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'pay_to_email', $merchant); ?>
		<?php echo $this->html('form.hidden', 'language', $languageCode); ?>

		<?php if (!$invoice->isRecurring()) { ?>
			<?php echo $this->html('form.hidden', 'amount', $amount); ?>
		<?php } ?>
		
		<?php echo $this->html('form.hidden', 'currency', $invoice->getCurrency('isocode')); ?>

		<?php echo $this->html('form.hidden', 'recipient_description', $merchant); ?>

		<?php echo $this->html('form.hidden', 'status_url', $callbackUrls['notify']); ?>
		<?php echo $this->html('form.hidden', 'return_url', $callbackUrls['return']); ?>
		<?php echo $this->html('form.hidden', 'cancel_url', $callbackUrls['cancel']); ?>

		<?php echo $this->html('form.hidden', 'transaction_id', $payment->getKey()); ?>

		<?php echo $this->html('form.hidden', 'merchant_fields', 'invoice_key, payment_key'); ?>
		<?php echo $this->html('form.hidden', 'invoice_key', $invoice->getKey()); ?>
		<?php echo $this->html('form.hidden', 'payment_key', $payment->getKey()); ?>

		<?php echo $this->html('form.hidden', 'detail1_description', "Order"); ?>
		<?php echo $this->html('form.hidden', 'detail1_text', $invoice->getTitle()); ?>
		<?php echo $this->html('form.hidden', 'status_url2', "mailto:" . $merchant); ?>

		<?php if ($invoice->isRecurring()) { ?>
			<?php echo $this->html('form.hidden', 'rec_amount', $amount); ?>
			<?php echo $this->html('form.hidden', 'rec_cycle', $recurringCycle); ?>
			<?php echo $this->html('form.hidden', 'rec_end_date', $recurringEndDate); ?>
			<?php echo $this->html('form.hidden', 'rec_period', $recurringPeriod); ?>
		<?php } ?>
	</form>

</div>