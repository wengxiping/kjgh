<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="pp-result-container">
	<form action="<?php echo $postUrl;?>" method="post" data-pp-smoovpay-form>
		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'version', '2.0'); ?>
		<?php echo $this->html('form.hidden', 'action', 'pay'); ?>
		<?php echo $this->html('form.hidden', 'merchant', $merchantId); ?>
		<?php echo $this->html('form.hidden', 'ref_id', $payment->getKey()); ?>
		<?php echo $this->html('form.hidden', 'item_name_1', $invoice->getTitle()); ?>
		<?php echo $this->html('form.hidden', 'item_description_1', $invoice->getTitle()); ?>
		<?php echo $this->html('form.hidden', 'item_quantity_1', 1); ?>
		<?php echo $this->html('form.hidden', 'item_amount_1', $invoice->getTotal()); ?>
		<?php echo $this->html('form.hidden', 'currency', $invoice->getCurrency('isocode', 'USD')); ?>
		<?php echo $this->html('form.hidden', 'total_amount', $invoice->getTotal()); ?>
		<?php echo $this->html('form.hidden', 'success_url', $successUrl); ?>
		<?php echo $this->html('form.hidden', 'cancel_url', $cancelUrl); ?>
		<?php echo $this->html('form.hidden', 'signature', $signature); ?>
		<?php echo $this->html('form.hidden', 'signature_algorithm', 'sha1'); ?>
		<?php echo $this->html('form.hidden', 'skip_success_page', '1'); ?>
	</form>
</div>