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
	<form action="<?php echo $postUrl;?>" method="post" data-pp-2co-form>

		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'sid', $sid); ?>
		<?php echo $this->html('form.hidden', 'invoice_number', $payment->getKey()); ?>
		<?php echo $this->html('form.hidden', 'payment_key', $payment->getKey()); ?>
		<?php echo $this->html('form.hidden', 'merchant_order_id', $invoice->getKey().','.$payment->getKey()); ?>
		<?php echo $this->html('form.hidden', 'currency', $invoice->getCurrency('isocode')); ?>
		<?php echo $this->html('form.hidden', 'return_url', $cancelUrl); ?>
		<?php echo $this->html('form.hidden', 'cust_id', $buyer->getId()); ?>
		<?php echo $this->html('form.hidden', 'fixed', 'Y'); ?>

		<?php if ($invoice->isRecurring()) { ?>
			<?php echo $this->html('form.hidden', 'mode', '2CO');?>
			<?php echo $this->html('form.hidden', 'is_recurring', 1); ?>

			<?php echo $this->html('form.hidden', 'li_0_type', $li_0_type);?>
			<?php echo $this->html('form.hidden', 'li_0_name', $li_0_name); ?>
			<?php echo $this->html('form.hidden', 'li_0_quantity', $li_0_quantity);?>
			<?php echo $this->html('form.hidden', 'li_0_price', $li_0_price); ?>
			<?php echo $this->html('form.hidden', 'li_0_tangible', $li_0_tangible);?>
			<?php echo $this->html('form.hidden', 'li_0_recurrence', $li_0_recurrence); ?>
			<?php echo $this->html('form.hidden', 'li_0_duration', $li_0_duration);?>
				
			<?php if (isset($li_0_startup_fee)) { ?>
				<?php echo $this->html('form.hidden', 'li_0_startup_fee', $li_0_startup_fee);?>	
			<?php } ?>

		<?php } else { ?>
			<?php echo $this->html('form.hidden', 'x_invoice_num', $invoice->getKey()); ?>
			<?php echo $this->html('form.hidden', 'total', $invoice->getTotal()); ?>
			<?php echo $this->html('form.hidden', 'cart_order_id', $invoice->getTitle()); ?>
			<?php echo $this->html('form.hidden', 'cart_brand_name', $this->config->get('sitename')); ?>
			<?php echo $this->html('form.hidden', 'cart_version_name', PP::getLocalVersion()); ?>
			<?php echo $this->html('form.hidden', 'username', $buyer->getUsername()); ?>
			<?php echo $this->html('form.hidden', 'name', $buyer->getName()); ?>
		<?php } ?>
	</form>
</div>