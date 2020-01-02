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
	<form action="<?php echo $formUrl;?>" method="post" data-pp-payzen-form>

		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'vads_action_mode', $payload['vads_action_mode']); ?>
		<?php echo $this->html('form.hidden', 'vads_amount', $payload['vads_amount']); ?>
		<?php echo $this->html('form.hidden', 'vads_ctx_mode', $payload['vads_ctx_mode']); ?>
		<?php echo $this->html('form.hidden', 'vads_currency', $payload['vads_currency']); ?>

		<?php if ($isRecurring) { ?>
			<?php echo $this->html('form.hidden', 'vads_cust_email', $payload['vads_cust_email']); ?>
		<?php } ?>

		<?php echo $this->html('form.hidden', 'vads_page_action', $payload['vads_page_action']); ?>
		<?php echo $this->html('form.hidden', 'vads_payment_config', $payload['vads_payment_config']); ?>
		<?php echo $this->html('form.hidden', 'vads_site_id', $payload['vads_site_id']); ?>

		<?php if ($isRecurring) { ?>
			<?php echo $this->html('form.hidden', 'vads_sub_amount', $payload['vads_sub_amount']); ?>
			<?php echo $this->html('form.hidden', 'vads_sub_currency', $payload['vads_sub_currency']); ?>
			<?php echo $this->html('form.hidden', 'vads_sub_desc', $payload['vads_sub_desc']); ?>
			<?php echo $this->html('form.hidden', 'vads_sub_effect_date', $payload['vads_sub_effect_date']); ?>
		<?php } ?>

		<?php echo $this->html('form.hidden', 'vads_trans_date', $payload['vads_trans_date']); ?>
		<?php echo $this->html('form.hidden', 'vads_trans_id', $payload['vads_trans_id']); ?>
		<?php echo $this->html('form.hidden', 'vads_order_id', $payload['vads_order_id']); ?>
		<?php echo $this->html('form.hidden', 'vads_version', $payload['vads_version']); ?>
		<?php echo $this->html('form.hidden', 'vads_return_mode', $payload['vads_return_mode']); ?>
		<?php echo $this->html('form.hidden', 'signature', $payload['signature']); ?>
	</form>
</div>

