<?php 

/*------------------------------------------------------------------------
# com_invoices - Invoices for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2012 JoomlaInvoices.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaInvoices.com
# Technical Support:	Forum - http://www.JoomlaFinances.com/forum
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');
if ( ! function_exists('plg_affiliates_escape')) {

	function plg_affiliates_escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}
}

$currencycodes=array('AUD'=>'Australian Dollar','BRL'=>'Brazilian Real','CAD'=>'Canadian Dollar','CZK'=>'Czech Koruna','DKK'=>'Danish Krone','EUR'=>'Euro','HKD'=>'Hong Kong Dollar','HUF'=>'Hungarian Forint','ILS'=>'Israeli New Sheqel','JPY'=>'Japanese Yen','MYR'=>'Malaysian Ringgit','MXN'=>'Mexican Peso','NOK'=>'Norwegian Krone','NZD'=>'New Zealand Dollar','PHP'=>'Philippine Peso','PLN'=>'Polish Zloty','GBP'=>'Pound Sterling','SGD'=>'Singapore Dolla','SEK'=>'Swedish Krona','CHF'=>'Swiss Franc','TWD'=>'Taiwan New Dollar','THB'=>'Thai Baht','TRY'=>'Turkish Lira','USD'=>'U.S. Dollar');

if($vars->receiver_email){ ?>
<script type="text/javascript">
var theamount = "<?php echo $vars->row->payment_amount; ?>";
var theamount_no_decimals = "<?php echo (int)($vars->row->payment_amount); ?>";

function checkamountformat(){
	var currency_code = jQuery('#currency_code').val();
	var no_decimals = new Array('JPY', 'HUF', 'TWD');

	var is_no_decimals = jQuery.inArray( currency_code, no_decimals );
	if(is_no_decimals >= 0){
		jQuery('#amount').val(theamount_no_decimals);
	}
	else jQuery('#amount').val(theamount);
}

jQuery( document ).ready(function() {
	checkamountformat();
});

</script>
<form action="<?php echo plg_affiliates_escape($vars->action_url) ?>" method="post">
	<dl class="dl-horizontal">
		<dt><?php echo JText::_( 'AMOUNT' ); ?></dt>
		<dd><?php echo $vars->row->payment_amount; ?></dd>
		<dt><?php echo JText::_( 'PAY_TO_EMAIL' ); ?></dt>
		<dd><input type="text" class="inputbox" name="business" value="<?php echo plg_affiliates_escape($vars->receiver_email) ?>" /></dd>
		<dt><?php echo JText::_( 'Currency' ); ?></dt>
		<dd>
			<select name="currency_code" id="currency_code" onchange="checkamountformat()">
				<?php foreach($currencycodes AS $code => $name){ ?>
					<option value="<?php echo $code; ?>" <?php if($code == $vars->currency) echo "selected"; ?>><?php echo $name; ?></option>
				<?php } ?>
			</select>
		</dd>

		<dt></dt>
		<dd style="padding-top:5px;">			
			<input type="hidden" name="custom" value="<?php echo plg_affiliates_escape($vars->custom) ?>" />
			<input type="hidden" name="item_number" value="<?php echo plg_affiliates_escape($vars->row->id) ?>" />
			<input type="hidden" name="item_name" value="<?php echo plg_affiliates_escape($vars->row->payment_description) ?>" />
			<input type="hidden" name="custom_1" value="<?php echo plg_affiliates_escape($vars->user->get('id')) ?>" />
			<input type="hidden" name="custom1" value="<?php echo plg_affiliates_escape($vars->user->get('id')) ?>" />
			<input type="hidden" name="return" value="<?php echo plg_affiliates_escape($vars->return_url) ?>" />
			<input type="hidden" name="cancel_return" value="<?php echo plg_affiliates_escape($vars->cancel_url) ?>" />
			<input type="hidden" name="notify_url" value="<?php echo plg_affiliates_escape($vars->notify_url) ?>" />				
			
			<input type="hidden" name="no_note" value="1" />
			<input type="hidden" name="rm" value="2" />

			<input type="hidden" name="amount" id="amount" value="<?php echo plg_affiliates_escape($vars->row->payment_amount) ?>" />
			<input type="hidden" name="cmd" value="_xclick" />

			<input type="hidden" name="lc" value="GB" />

			<button class="btn"><?php echo JText::_( 'PAY_WITH' ); ?> <?php echo JHTML::image('plugins/affiliates/payment_paypal/payment_paypal/tmpl/paypal_button_32.png', JText::_('PAYPAL')); ?></button>

		</dd>
	</dl>
</form>

<?php } else echo JText::_('NO_PAYPAL_EMAIL_AVAILABLE'); ?>
