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


</script>

<form method="post" autocomplete="off" action="<?php echo $post_url;?>" id="checkout_form">
	<legend><?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CARD_DETAILS'); ?></legend>
				<div class="form-horizontal">
					<div  class="text-info">    
		<?php  $currency = $invoice->getCurrency();
						echo $this->_render('partial_amount', compact('currency', 'amount'), 'default'). JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_PAYMENT_AMOUNT');
				 ?>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<label class="required">
							<?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CREDIT_CARD_NUMBER')?>
						</label>
					</div>
						<div class="controls">
							<input type="number" data-braintree-name="number" class="required pp-validate-creditcard" value="" id=card-number/>
						</div>
				</div>

				<div class="control-group">
					<div class="control-label"><label class="required"><?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CCV')?></label></div>
					<div class="controls"><input type="number" class="required pp-validate-cvclength" id=card-cvc  data-pp-validate='#card-number' data-braintree-name="cvv" value=""/></div>
				</div>
				
				<div class="control-group">
					<div class="control-label"><label class="required"><?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_EXPIRATION_DATE')?></label></div>
			<div class="controls"><?php
					/*** array of months ***/
						$months = array(
										1=>JText::_('JANUARY'),
										2=>JText::_('FEBRUARY'),
										3=>JText::_('MARCH'),
										4=>JText::_('APRIL'),
										5=>JText::_('MAY'),
										6=>JText::_('JUNE'),
										7=>JText::_('JULY'),
										8=>JText::_('AUGUST'),
										9=>JText::_('SEPTEMBER'),
										10=>JText::_('OCTOBER'),
										11=>JText::_('NOVEMBER'),
										12=>JText::_('DECEMBER'));
		
						/*** current month ***/
						$select = '<select data-braintree-name="expiration_month" style="width:93px;">'."\n";
						foreach($months as $key=>$mon)
						{
								$select .= "<option value=".$key;
								$select .= ">$mon</option>\n";
						}
						$select .= '</select>';
						echo $select;
					
						/*** the current year ***/
				$start_year = date('Y');
				$end_year = $start_year + 20;
				
						/*** range of years ***/
						$rangeOfYear = range($start_year, $end_year);
		
						/*** create the select ***/
						$select = '<select data-braintree-name="expiration_year" style="width:93px;">';
						foreach( $rangeOfYear as $year )
						{
								$select .= "<option value=".$year;
								$select .= ">$year</option>\n";
						}
						$select .= '</select>';
						
						echo $select;
				 ?>
				 </div>
				</div>
						
			</div>

			<fieldset class="form-horizontal row-fluid">
				<div class="offset4 span8">             
							<button id="pp-payment-app-buy" type="submit" name="buy" class="btn btn-primary btn-large" data-loading-text="<?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_BUY');?>...">
								<?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_BUY')?>
							</button>
							<div class="btn btn-large"><a id="paypalpro-pay-cancel" href="<?php echo $cancel_url; ?>"><?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CANCEL')?></a></div>
								</div>
			</fieldset>
</form>
<br/>
<br/>
<br/>

<form method="post" autocomplete="off" action="<?php echo $post_url;?>" id="paypal_form">
<div id="paypal-container"> 
</div>

	<fieldset class="form-horizontal row-fluid">
			<div class="offset4 span8">             
					<button id="pp-payment-app-buy" type="submit" name="buy" class="btn btn-primary btn-large" data-loading-text="<?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_BUY');?>...">
						<?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_BUY')?>
					</button>
					<div class="btn btn-large"><a id="paypalpro-pay-cancel" href="<?php echo $cancel_url; ?>"><?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CANCEL')?></a></div>
				</div>
		</fieldset>
</form>

<script src="https://js.braintreegateway.com/v2/braintree.js"></script>
<script>
payplans.jQuery(document).ready(function()
{
	payplans.jQuery('form#checkout_form').find("input,textarea,select");
	payplans.jQuery('#pp-payment-app-buy').click(function(){
			payplans.jQuery(this).button('loading');
	});

	braintree.setup("<?php echo $token;?>", "custom", {id: "checkout_form"});
	braintree.setup("<?php echo $token;?>", "paypal", {
		container: "paypal-container",
			onPaymentMethodReceived: function (obj) {
				//$('#paypal_form')
			}
	});

// We generated a client token for you so you can test out this code
// immediately. In a production-ready integration, you will need to
// generate a client token on your server (see section below).
// var clientToken = "<?php echo $token;?>";

// braintree.setup(clientToken, "dropin", {
//   container: "payment-form"
});
</script>
