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
?>
<div class="pp-result-container">
	<form action="<?php echo $post_url ?>" method="post" data-paypal-form>

		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'charset', 'utf-8'); ?>
		<?php echo $this->html('form.hidden', 'order_id', $invoice->getKey()); ?>
		<?php echo $this->html('form.hidden', 'invoice', $payment->getKey()); ?>
		<?php echo $this->html('form.hidden', 'item_name', $invoice->getTitle()); ?>
		<?php echo $this->html('form.hidden', 'item_number', $invoice->getKey()); ?>
		
		<?php if(!$invoice->isRecurring()) { ?>
			<?php echo $this->html('form.hidden', 'amount', $invoice->getTotal()); ?>
		<?php } ?>

		<?php echo $this->html('form.hidden', 'cmd', $cmd); ?>
		<?php echo $this->html('form.hidden', 'business', $merchant_email); ?>
		<?php echo $this->html('form.hidden', 'return', $return_url); ?>
		<?php echo $this->html('form.hidden', 'cancel_return', $cancel_url); ?>
		<?php echo $this->html('form.hidden', 'notify_url', $notify_url); ?>
		<?php echo $this->html('form.hidden', 'currency_code', $invoice->getCurrency('isocode')); ?>

		<?php echo $this->html('form.hidden', 'no_note', '1'); ?>
		<?php echo $this->html('form.hidden', 'bn', 'ReadyBytes_SP'); ?>

		<!-- TRIAL SUBSCRIPTION -->
		<?php if ($invoice->isRecurring()) { ?>
			<?php if ($invoice->hasRecurringWithFreeTrials()) { ?>
				<?php echo $this->html('form.hidden', 'a1', $a1); ?>
				<?php echo $this->html('form.hidden', 'p1', $p1); ?>
				<?php echo $this->html('form.hidden', 't1', $t1); ?>
				
				<?php if ($invoice->getRecurringType() == PP_PRICE_RECURRING_TRIAL_2) { ?>
					<?php echo $this->html('form.hidden', 'a2', $a2); ?>
					<?php echo $this->html('form.hidden', 'p2', $p2); ?>
					<?php echo $this->html('form.hidden', 't2', $t2); ?>
				<?php } ?>
			<?php } ?>

			<!-- Some variables for Recurring Payment   -->
			<?php echo $this->html('form.hidden', 'a3', $a3); ?>
			<?php echo $this->html('form.hidden', 'p3', $p3); ?>
			<?php echo $this->html('form.hidden', 't3', $t3); ?>
			<?php echo $this->html('form.hidden', 'src', 1); ?>
			<?php echo $this->html('form.hidden', 'sra', $sra); ?>
			<?php echo $this->html('form.hidden', 'srt', $srt); ?>
			
			<!--	METHOD in which data to be post from paypal 0-get,1,2-post -->
			<?php echo $this->html('form.hidden', 'rm', 2); ?>
		<?php } ?>
	</form>
</div>