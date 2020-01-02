<?php
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');

?>

<div id="accordion1" class="accordion">
	<div class="accordion-group">
		<div class="accordion-heading">
			<a href="#batch" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
				<?php echo JText::_('COM_INVITEX_BATCH_OPTIONS');?>
			</a>
		</div>
		<div class="accordion-body collapse" id="batch" style="height: 0px;">
			<div class="accordion-inner">
				<fieldset class="batch form-inline">
					<legend><?php echo JText::_('COM_INVITEX_BATCH_OPTIONS');?></legend>
					<div class="combo control-group" id="batch-choose-action">
						<input type="text" value="" name="batch_inv_limit" id="batch_inv_limit" placeholder="<?php echo JText::_('COM_INVITEX_IL_ADD_SUBPLACE');?>"/>
					</div>

					<button onclick="batch_process();" type="button" class="btn btn-primary">
						<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>	</button>
				</fieldset>
			</div>
		</div>
	</div>
</div>
