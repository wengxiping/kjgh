<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 November 2015
 * @file name	:	gateways/class.banktransfer.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

defined('_JEXEC') or die('Restricted access');

class banktransfer_class {
	var $payconfig = array();
	var $details = array();
	
	function __construct($payconfig, $details){
		$this->payconfig = $payconfig;
		$this->details = $details;
	}
	
	function banktransferPayment(){
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		
		$document = JFactory::getDocument();
		$document->addStyleSheet("components/com_jblance/css/style.css");
		
		$app  	= JFactory::getApplication();
		$buy  	= $app->input->get('buy', '', 'string');	//either buy deposit or plan
		
		$config 		= JblanceHelper::getConfig();
		$tax_name	 	= $config->taxName;
		
		$payconfig   		= $this->payconfig;
		$bank_name  		= $payconfig->btBankname;
		$acc_holder_name 	= $payconfig->btAccHoldername;
		$bank_account  		= $payconfig->btAccnum;
		$emailnotify  		= $payconfig->btNotifyEmail;
		$faxnofity  		= $payconfig->btNotifyFaxno;
		$iban				= $payconfig->btIBAN;
		$swift				= $payconfig->btSWIFT;
	
		$details 	= $this->details;
		$amount 	= $details['amount'];
		$taxrate 	= $details['taxrate'];
		$totamt 	= (float)($amount + $amount * ($taxrate/100));	$totamt = round($totamt, 2);	//the total amount is calculated as authorize.net does not support tax parameter.
		$orderid 	= $details['orderid'];
		$itemname 	= $details['itemname'];
		$item_num 	= $details['item_num'];
		$invoiceNo 	= $details['invoiceNo'];
		$user_id 	= $details['user_id'];
	
		$link_balance	= JRoute::_('index.php?option=com_jblance&view=membership&layout=transaction');
		
		if($buy == 'plan'){
			//send alert to admin and user
			$jbmail->alertAdminSubscr($orderid, $user_id);
			$jbmail->alertUserSubscr($orderid, $user_id);
		}
		elseif($buy == 'deposit') {
			//send deposit fund alert to admin
			$jbmail->sendAdminDepositFund($orderid);
		}
		
		?>
		<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PAYMENT_INFO'); ?></div>
		<div class="well well-small jbbox-gradient">
			<h2 class="jbj_manual"><?php echo JText::_('COM_JBLANCE_CART'); ?></h2>
			<table style="width: 100%;">
				<!-- ************************************************************** plan section ******************************************* -->
				<?php if($buy == 'plan') : ?>
				<thead>
				<tr>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_NAME'); ?></th>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></th>
					<th><?php echo JText::_('COM_JBLANCE_TOTAL'); ?></th>
				</tr>
				</thead>
				<tr>
					<td><?php echo $itemname; ?></td>
					<td><?php echo $invoiceNo; ?></td>
					<td class="text-right"><?php echo JblanceHelper::formatCurrency($amount, false); ?></td>
				</tr>
				<tr>
					<td colspan="2" class="text-right"><?php echo $tax_name.' '.$taxrate; ?>% :</td>
					<td colspan="1" class="text-right">
						<?php
							$taxamt = ($taxrate/100) * $amount;
							echo JblanceHelper::formatCurrency($taxamt, false);
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="text-right"> </td>
					<td colspan="1" class="text-right"><hr></td>
				</tr>
				<tr>
					<td colspan="2" class="text-right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
					<td colspan="1" class="text-right">
						<?php
							$total = $taxamt + $amount;
							echo '<b>'.JblanceHelper::formatCurrency($total).'</b>';
						?>
					</td>
				</tr>
				<tr>
					<td colspan="6"><hr></td>
				</tr>
				<!-- ************************************************************** deposit section ******************************************* -->
				<?php elseif($buy == 'deposit') : ?>
				<thead>
				<tr>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_NAME'); ?></th>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></th>
					<th><?php echo JText::_('COM_JBLANCE_TOTAL'); ?></th>
				</tr>
				</thead>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_DEPOSIT_FUNDS'); ?></td>
					<td><?php echo $invoiceNo; ?></td>
					<td class="text-right"><?php echo JblanceHelper::formatCurrency($amount, false); ?></td>
				</tr>
				<tr>
					<td colspan="2" class="text-right"> </td>
					<td colspan="1" class="text-right"><hr></td>
				</tr>
				<tr>
					<td colspan="2" class="text-right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
					<td colspan="1" class="text-right">
						<?php
							echo '<b>'.JblanceHelper::formatCurrency($amount).'</b>';
						?>
					</td>
				</tr>
				<tr>
					<td colspan="6"><hr></td>
				</tr>
				<?php endif; ?>
			</table>
		</div>
		<div class="sp20">&nbsp;</div>
		
		<table class="table table-bordered table-hover table-condensed">
			<thead>
				<tr>
					<th colspan="2"><?php echo JText::_('COM_JBLANCE_BANK_ACCOUNT_INFO'); ?> </th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_BANK_NAME'); ?>:</td>
					<td><?php echo $bank_name; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_BANK_ACCOUNT_NAME'); ?>:</td>
					<td> <?php echo $acc_holder_name; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_ACCOUNT_NO'); ?>:</td>
					<td><?php echo $bank_account; ?></td>
				</tr>
			<?php if(!empty($iban)): ?>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_IBAN'); ?>:</td>
					<td><?php echo $iban; ?></td>
				</tr>
			<?php endif; ?>
				<?php if(!empty($swift)): ?>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_SWIFT'); ?>:</td>
					<td><?php echo $swift; ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
			<thead>
				<tr>
				<th colspan="2"><?php echo JText::_('COM_JBLANCE_NOTIFICATION_INFO'); ?> </th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_EMAIL'); ?>:</td>
					<td><?php echo $emailnotify; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_FAX'); ?>:</td>
					<td><?php echo $faxnofity; ?></td>
				</tr>
			</tbody>
		</table>
	
		<div class="alert alert-info">
			<?php echo JText::_('COM_JBLANCE_MANUAL_TRANSER_WHATS_NEXT'); ?>
		</div>
		
		<div class="form-actions">
			<input type="button" onclick="location.href='<?php echo $link_balance; ?>';" value="<?php echo JText::_('COM_JBLANCE_CONTINUE'); ?>" class="btn btn-primary"/>
		</div>
		
	<?php
	}
}
?>
