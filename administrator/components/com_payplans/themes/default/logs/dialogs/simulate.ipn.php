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
<dialog>
	<width>860</width>
	<height>640</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}" : "[data-submit-button]",
		"{form}": "[data-ipn-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},

		"{submit} click": function(element) {
			self.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::sprintf('Simulate Instant Payment Notification'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php?option=com_payplans&view=payment&task=notify');?>" method="post" class="o-form-horizontal" data-ipn-form>
			<p>
				This allows you to simulate an instant payment notification to PayPlans. Do take note that you should not be submitting this again if the payment is already recorded.
			</p>

			<?php foreach ($data as $key => $value) { ?>
			<div class="o-form-group">

				<label class="o-control-label" for="subject">
					<?php echo $key;?>
				</label>

				<div class="o-control-input">
					<?php echo $this->html('form.textarea', $key, $value, ''); ?>
				</div>
			</div>
			<?php } ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-primary-o btn-sm"><?php echo JText::_('COM_PP_SUBMIT_BUTTON'); ?></button>
	</buttons>
</dialog>
