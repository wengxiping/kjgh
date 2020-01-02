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
if(defined('_JEXEC')===false) die();
?>


<script type="text/javascript">
payplans.jQuery(document).ready(function()
{
		 setTimeout(function paygateSubmit(){
				document.forms["site_app_<?php echo $this->getName(); ?>_form"].submit();
		   }, 1000);
});
</script>
<form action="<?php echo $post_url ?>"
	  method="post" name="site_app_<?php echo $this->getName(); ?>_form" >
	  <?php $checksum = md5($version."|".$paygateid."|".$payment_key."|".$amount."|".$currency."|".$return_url."|".$transaction_date."|".$subscription_start_date."|".$subscription_end_date."|".$frequency."|"."YES"."|".$amount."|".$encryption_key);?>	  
	  
	<input type="hidden" name="VERSION" 			value="<?php echo $version?>">
	<input type="hidden" name="PAYGATE_ID"			value="<?php echo $paygateid;?>" >
	<input type="hidden" name="REFERENCE" 			value="<?php echo $payment_key;?>" >
	<input type="hidden" name="AMOUNT"				value="<?php echo $amount;?>" >
	<input type="hidden" name="CURRENCY" 			value="<?php echo $currency;?>" >
	<input type="hidden" name="RETURN_URL" 		 	value="<?php echo $return_url;?>" >
	<input type="hidden" name="TRANSACTION_DATE" 	value="<?php echo $transaction_date;?>" >
	<input type="hidden" name="SUBS_START_DATE" 	value="<?php echo $subscription_start_date;?>">
	<input type="hidden" name="SUBS_END_DATE" 		value="<?php echo $subscription_end_date;?>">
	<input type="hidden" name="SUBS_FREQUENCY" 		value="<?php echo $frequency;?>">
	<input type="hidden" name="PROCESS_NOW" 		value="YES">
	<input type="hidden" name="PROCESS_NOW_AMOUNT" 	value="<?php echo $amount;?>" >
	<input type="hidden" name="CHECKSUM" 			value="<?php echo $checksum;?>" >

	
	<div id="payment-paygate" class="pp-payment-pay-process">
		<div id="payment-redirection">
			<h4>
				<?php echo JText::_('COM_PAYPLANS_APP_PAYGATE_PAYMENT_REDIRECTION'); ?>
			</h4>
			<div class="loading"></div>
		</div>
		 <br>
		<div id="payment-submit">
			<input 	type="submit" class="btn btn-primary btn-largeult" id="payplans-app-paygate-payment"
					name="payplans_payment_btn" value="<?php echo JText::_('COM_PAYPLANS_PAYGATE_PAYMENT')?>"/>
		</div>
	</div>
</form>
<?php 