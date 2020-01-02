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
<form action="<?php echo $postUrl;?>" method="post" data-pp-paygate-form>

	<?php echo $this->html('html.redirection'); ?>

	<?php echo $this->html('form.hidden', 'PAYGATE_ID', $paygateid); ?>
	<?php echo $this->html('form.hidden', 'REFERENCE', $payment_key); ?>
	<?php echo $this->html('form.hidden', 'AMOUNT', $amount); ?>
	<?php echo $this->html('form.hidden', 'CURRENCY', $currency); ?>
	<?php echo $this->html('form.hidden', 'RETURN_URL', $return_url); ?>
	<?php echo $this->html('form.hidden', 'TRANSACTION_DATE', $transaction_date); ?>
	<?php echo $this->html('form.hidden', 'CHECKSUM', $checksum); ?>

	<?php if ($invoice->isRecurring()) { ?>
		<?php echo $this->html('form.hidden', 'VERSION', $version); ?>
		<?php echo $this->html('form.hidden', 'SUBS_START_DATE', $subscription_start_date); ?>
		<?php echo $this->html('form.hidden', 'SUBS_END_DATE', $subscription_end_date); ?>
		<?php echo $this->html('form.hidden', 'SUBS_FREQUENCY', $frequency); ?>
		<?php echo $this->html('form.hidden', 'PROCESS_NOW', 'YES'); ?>
		<?php echo $this->html('form.hidden', 'PROCESS_NOW_AMOUNT', $amount); ?>
	<?php } ?>

</form>	

