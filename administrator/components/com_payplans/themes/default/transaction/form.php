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
<div class="pp-transaction-edit">
<form method="post" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<legend> <?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_DETAILS' ); ?> </legend>
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_TRANSACTION_ID') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TRANSACTION_ID') ?> 
				</div>
				<div class="controls"><?php echo $transaction->getId(); ?></div>
			</div>
			
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_INVOICE_ID') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_INVOICE_ID') ?> 
				 </div>
				 <div class="controls">
				 <?php $invoice_id = $transaction->getInvoice();
					   echo PayplansHtml::link(XiRoute::_("index.php?option=com_payplans&view=invoice&task=edit&id=".$invoice_id, false),$invoice_id.'('.XiHelperUtils::getKeyFromId($invoice_id).')'); ?>
				 </div>
			</div>
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_PAYMENT_ID') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_PAYMENT_ID') ?> 
				 </div>
				 <div class="controls">
				 <?php $payment_id = $transaction->getPayment();
					   echo $payment_id.'('.XiHelperUtils::getKeyFromId($payment_id).')'; ?>
				 </div>
			 </div>
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_AMOUNT') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_AMOUNT') ?> 
				</div>
				<div class="controls"><?php $amount   = $transaction->getAmount();
							   $currency = $transaction->getCurrency();
								echo $this->output('admin/partials/amount', compact('currency', 'amount')); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_GATEWAY_TYPE') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_GATEWAY_TYPE') ?> 
				</div>
				 <?php $pament_id = $transaction->getPayment();
					   $payment = PayplansPayment::getInstance($pament_id);?>
				<div class="controls"><?php echo (!empty($pament_id) && ($payment instanceof PayplansPayment))? $payment->getAppName() : JText::_('COM_PAYPLANS_TRANSACTION_PAYMENT_GATEWAY_NONE'); ?></div>
			</div>

			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_GATEWAY_TRANSACTION_ID') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_GATEWAY_TRANSACTION_ID') ?> 
				</div>
				<div class="controls"><?php echo $transaction->getGatewayTxnId(); ?></div>
			</div>
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_GATEWAY_PARENT_TRANSACTION') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_GATEWAY_PARENT_TRANSACTION') ?> 
				 </div>
				<div class="controls"><?php echo $transaction->getGatewayParentTxn(); ?></div>
			</div>
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_GATEWAY_SUBSCRIPTION_ID') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_GATEWAY_SUBSCRIPTION_ID') ?> 
				 </div>
				<div class="controls"><?php echo $transaction->getGatewaySubscriptionId(); ?></div>
			</div>
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_CREATED_DATE') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_CREATED_DATE') ?> 
				 </div>
				<div class="controls"><?php echo XiDate::timeago($transaction->getCreatedDate()->toMySql()); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label hasTip" title="<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_TOOLTIP_MESSAGE') ?>" >
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_MESSAGE') ?> 
				 </div>
				<div class="controls"><?php echo JText::_($transaction->getMessage()); ?></div>
			</div>
		
			<div>
				<?php //echo $this->output('site/partials/partial_user', compact('user'));?>

			</div>
		</fieldset>		
	</div>

	<div class="span6">
			<fieldset class="form-horizontal">	
			<legend > <?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_PARAMS' ); ?> </legend>
						<?php echo $transaction_html; ?>
			</fieldset>
	
			<?php if(isset($show_refund_option) && $show_refund_option) : ?>
			<fieldset class="form-horizontal">	
					<legend>
						<?php echo JText::_('COM_PAYPLANS_TRANSACTION_DETAIL_REFUND' ); ?> 
					</legend>			
					<div class="pp-float-left ui-button ui-button-primary ui-widget ui-corner-all pp-button-text-only">
						<a href="" onclick="payplans.url.modal('<?php echo XiRoute::_('index.php?option=com_payplans&view=transaction&layout=refund&transaction_id='.$transaction->getId());?>'); return false;"><?php echo JText::_('COM_PAYPLANS_TRANSACTION_DETAIL_REFUND');?></a>
					</div>
			</fieldset>
			<?php endif;?>
	</div>

	<input type="hidden" name="option" value="com_payplans" />
	<input type="hidden" name="controller" value="transaction" />
	<input type="hidden" name="task" value="store" />
	<?php echo $form->getInput('transaction_id'); ?>
	<input type="hidden" name="boxchecked" value="1" />

</div>
</form>
</div>
<?php

