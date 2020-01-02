<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	15 March 2012
 * @file name	:	views/admconfig/tmpl/editpaymode.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Edit the Payment Gateway(jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');

 $model = $this->getModel();
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 
 $config = JblanceHelper::getConfig();
 $currencysym = $config->currencySymbol;
?>
<script type="text/javascript">
<!-- 
Joomla.submitbutton = function(task){
	if (task == 'admconfig.cancelpaymode' || document.formvalidator.isValid(document.getElementById('editpaymode-form'))) {
		Joomla.submitform(task, document.getElementById('editpaymode-form'));
	}
	else {
		alert('<?php echo $this->escape(JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY'));?>');
	}
}
//-->
</script>
<form action="index.php" name="adminForm" id="editpaymode-form" method="post" enctype="multipart/form-data" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span6">
		<?php
		// Iterate through the fields and display them.
		if($this->form){ ?>
			<fieldset>
				<legend><?php echo $this->paymode->gateway_name; ?></legend>
				<div class="control-group">
		    		<label class="control-label" for="title"><?php echo JText::_('COM_JBLANCE_GATEWAY_NAME'); ?>:</label>
					<div class="controls">
						<input class="input-xlarge input-large-text required" type="text" name="gateway_name" id="gateway_name" value="<?php echo $this->paymode->gateway_name; ?>" />
					</div>
		  		</div>
			<?php
			// Iterate through the fields and display them.
			foreach($this->form->getFieldset('settings') as $field):
			    // If the field is hidden, only use the input.
			    if ($field->hidden):
			        echo $field->input;
			    else:
			    ?>
			<div class="control-group">
				<label class="control-label"><?php echo $field->label; ?></label>
				<div class="controls">
			        <?php echo $field->input; ?>
				</div>
			</div>
			    <?php
			    endif;
			endforeach;
			?>
	    </fieldset>
	    <?php
	    //endforeach;
	    ?>
	<?php 
	}
	?>
		</div>
		<div class="span6">
		<!-- Identify if withdrawal is allowed -->
		<?php
		$isWithdrawEnabled = false;
		foreach($this->form->getFieldset('withdraw') as $field){
			$isWithdrawEnabled = true;
		}
		?>
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_JBLANCE_FIELDSET_MORE_OPTIONS_LABEL'); ?></legend>
				<div class="control-group">
					<?php $tip = JHtml::tooltipText(JText::_('COM_JBLANCE_ENABLE_SUBSCRIPTION_EXAMPLE')); ?>
		    		<label class="control-label hasTooltip" for="is_subscription" title="<?php echo $tip; ?>"><?php echo JText::_('COM_JBLANCE_ENABLE_SUBSCRIPTION'); ?>:</label>
					<div class="controls">
						<?php $enable_subscription = $select->YesNoBool('is_subscription', $this->paymode->is_subscription);
						echo  $enable_subscription; ?>
					</div>
		  		</div>
				<div class="control-group">
					<?php $tip = JHtml::tooltipText(JText::_('COM_JBLANCE_ENABLE_DEPOSIT_EXAMPLE')); ?>
		    		<label class="control-label hasTooltip" for="is_deposit" title="<?php echo $tip; ?>"><?php echo JText::_('COM_JBLANCE_ENABLE_DEPOSIT'); ?>:</label>
					<div class="controls">
						<?php $enable_deposit = $select->YesNoBool('is_deposit', $this->paymode->is_deposit);
						echo  $enable_deposit; ?>
					</div>
		  		</div>
				<?php if($isWithdrawEnabled) : ?>
				<div class="control-group">
					<?php $tip = JHtml::tooltipText(JText::_('COM_JBLANCE_ENABLE_WITHDRAW_EXAMPLE')); ?>
		    		<label class="control-label hasTooltip" for="withdraw" title="<?php echo $tip; ?>"><?php echo JText::_('COM_JBLANCE_ENABLE_WITHDRAW'); ?>:</label>
					<div class="controls">
						<?php $enable_withdraw = $select->YesNoBool('is_withdraw', $this->paymode->is_withdraw);
						echo  $enable_withdraw; ?>
					</div>
		  		</div>
				<div class="control-group">
					<?php $tip = JHtml::tooltipText(JText::_('COM_JBLANCE_WITHDRAW_FEE_FIXED_EXAMPLE')); ?>
		    		<label class="control-label hasTooltip" for="withdrawFeeFixed" title="<?php echo $tip; ?>"><?php echo JText::_('COM_JBLANCE_WITHDRAW_FEE_FIXED'); ?>:</label>
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on"><?php echo $currencysym; ?></span>
							<input type="text" class="input-small required" name="withdrawFeeFixed" id="withdrawFeeFixed" value="<?php echo $this->paymode->withdrawFeeFixed; ?>" />
						</div>
					</div>
		  		</div>
				<div class="control-group">
					<?php $tip = JHtml::tooltipText(JText::_('COM_JBLANCE_WITHDRAW_FEE_PERCENT_EXAMPLE')); ?>
		    		<label class="control-label hasTooltip" for="withdrawFeePerc" title="<?php echo $tip; ?>"><?php echo JText::_('COM_JBLANCE_WITHDRAW_FEE_PERCENT'); ?>:</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="input-small required" name="withdrawFeePerc" id="withdrawFeePerc" value="<?php echo $this->paymode->withdrawFeePerc; ?>" />
							<span class="add-on">%</span>
						</div>
					</div>
		  		</div>
		  		<?php endif; ?>
				<div class="control-group">
					<?php $tip = JHtml::tooltipText(JText::_('COM_JBLANCE_DEPOSIT_FEE_FIXED_EXAMPLE')); ?>
		    		<label class="control-label hasTooltip" for="depositfeeFixed" title="<?php echo $tip; ?>"><?php echo JText::_('COM_JBLANCE_DEPOSIT_FEE_FIXED'); ?>:</label>
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on"><?php echo $currencysym; ?></span>
							<input type="text" class="input-small required" name="depositfeeFixed" id="depositfeeFixed" value="<?php echo $this->paymode->depositfeeFixed; ?>" />
						</div>
					</div>
		  		</div>
				<div class="control-group">
					<?php $tip = JHtml::tooltipText(JText::_('COM_JBLANCE_DEPOSIT_FEE_PERCENT_EXAMPLE')); ?>
		    		<label class="control-label hasTooltip" for="depositfeeFixed" title="<?php echo $tip; ?>"><?php echo JText::_('COM_JBLANCE_DEPOSIT_FEE_PERCENT'); ?>:</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="input-small required" name="depositfeePerc" id="depositfeePerc" value="<?php echo $this->paymode->depositfeePerc; ?>" />
							<span class="add-on">%</span>
						</div>
					</div>
		  		</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="gateway" value="<?php echo $this->paymode->gwcode; ?>" />
	<input type="hidden" name="id" value="<?php echo $this->paymode->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
