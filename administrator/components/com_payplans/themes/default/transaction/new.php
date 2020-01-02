<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div>
<form action="<?php echo $uri; ?>" method="post" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<legend> <?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_DETAILS' ); ?> </legend>
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('invoice_id'); ?> </div>
				<div class="controls"><?php echo $form->getInput('invoice_id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('amount'); ?> </div>
				<div class="controls"><?php echo $form->getInput('amount'); ?></div>
			</div>
			
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('gateway_txn_id'); ?> </div>
				<div class="controls"><?php echo $form->getInput('gateway_txn_id'); ?></div>
			</div>
			
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('gateway_parent_txn'); ?> </div>
				<div class="controls"><?php echo $form->getInput('gateway_parent_txn'); ?></div>
			</div>
			
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('gateway_subscr_id'); ?> </div>
				<div class="controls"><?php echo $form->getInput('gateway_subscr_id'); ?></div>
			</div>
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('created_date'); ?>
				</div>
				<div class="controls"><?php echo XiDate::timeago($form->getValue('created_date')); ?>
				</div>
			</div>
			
			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('message'); ?>
				</div>
				<div class="controls">
					<?php echo $form->getInput('message'); ?>
				</div>
			</div>
		
			<div>
				<?php echo $this->loadTemplate('partial_user', compact('user'));?>
			</div>
		</fieldset>
	</div>
	
	<div class="span6">
		<fieldset class="form-horizontal">	
		<legend > <?php echo JText::_('COM_PAYPLANS_TRANSACTION_EDIT_PARAMS' ); ?> </legend>
					<?php echo $transaction_html; ?>
		</fieldset>
	</div>	
	
	
	<?php echo $form->getInput('transaction_id');?>
	<?php echo $form->getInput('user_id');?>
	<?php echo $form->getInput('payment_id');?>
	<input type="hidden" name="task" value="save" />
</div>
</form>
</div>
<?php
