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
<?php if ($transaction_html) { ?>
<?php 
	$data = array('approved', 'declined', 'error', 'response_code', 'response_subcode', 'response_reason_code', 
					'response_reason_text','authorization_code', 'avs_response', 'transaction_id', 'invoice_number', 
					'description', 'amount', 'transaction_type', 'customer_id', 'first_name', 'last_name', 'company', 
					'address', 'city', 'state', 'zip_code', 'phone', 'fax', 'email_address', 'testmode','x_response_code', 
					'x_response_reason_code', 'x_response_reason_text', 'x_avs_code', 'x_auth_code', 'x_trans_id', 
					'x_first_name', 'x_last_name','x_company', 'x_address', 'x_city', 'x_state', 'x_zip', 'x_country', 
					'x_phone', 'x_fax', 'x_email', 'x_invoice_num', 'x_description', 'x_type','x_cust_id', 'x_amount',
					'x_subscription_id', 'x_subscription_paynum');
?>
<table class="app-table table table-hover">
	<thead>
		<tr>
			<td width="20%">
				<?php echo JText::_('Key'); ?>
			</td>
			<td>
				<?php echo JText::_('Value'); ?>
			</td>
		</tr>
	</thead>

	<tbody>
		<?php if (isset($transaction_html['testmode'])) { ?>
		<tr>
			<td>
				<?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_'.JString::strtoupper('testmode'));?>
			</td>
			<td>
				<b>
					<?php echo $transaction_html['testmode'] ? JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_SANDBOX_TESTING_MODE') : JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_SANDBOX_LIVE_MODE'); ?>
				</b>
			</td>
		</tr>
		<?php } ?>

		<?php foreach ($data as $key) { ?>
			<?php if (isset($transaction_html[$key])) { ?>
			<tr>
				<td>
					<?php echo $key;?>
				</td>
				<td>
					<?php echo $transaction_html[$key] ? $transaction_html[$key] : '&mdash;'; ?>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
	</tbody>
</table>
<?php } ?>