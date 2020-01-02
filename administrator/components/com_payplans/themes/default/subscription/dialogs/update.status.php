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
	<width>500</width>
	<height>280</height>
	<selectors type="json">
	{
		"{close}": "[data-close-button]",
		"{form}": "[data-dialog-form]",
		"{submit}": "[data-submit-button]",
		"{statusInput}": "[data-update-status-input]",
		"{messageWrapper}": "[data-message-wrapper]",
		"{noneMessage}": "[data-none-message]",
		"{activeMessage}": "[data-active-message]",
		"{inactiveMessage}": "[data-inactive-message]",
		"{expireMessage}": "[data-expire-message]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{close} click": function() {
			this.parent.close();
		},

		"{submit} click": function() {
			this.form().submit();
		},

		"{statusInput} change": function(element) {
			var value = element.val();

			this.messageWrapper().addClass('t-hidden');
			this.activeMessage().addClass('t-hidden');
			this.inactiveMessage().addClass('t-hidden');

			if (value == <?php echo PP_SUBSCRIPTION_ACTIVE;?>) {
				this.activeMessage().removeClass('t-hidden');
				this.messageWrapper().removeClass('t-hidden');
			}

			if (value == <?php echo PP_SUBSCRIPTION_HOLD;?>) {
				this.inactiveMessage().removeClass('t-hidden');
				this.messageWrapper().removeClass('t-hidden');
			}
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_UPDATE_STATUS_DIALOG_TITLE'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="o-form-horizontal" data-dialog-form>
			<p class="t-lg-mb--xl">
				<?php echo JText::_('You will be able to update the status of the selected subscriptions by choosing the new state in the dropdown below:');?>
			</p>
			
			<div class="o-form-group t-lg-mb--xl">
				<?php echo $this->html('form.label', 'New Status'); ?>

				<?php echo $this->html('form.status', 'status', '', 'subscription', 'status', false, array('data-update-status-input' => ""), array(PP_SUBSCRIPTION_NONE)); ?>
			</div>

			<div class="o-form-group o-alert o-alert--warning t-lg-mt--xl t-hidden" data-message-wrapper>
				<div class="t-hidden" data-active-message>
					<?php echo JText::_('By changing the status to active, the system will automatically create a new invoice for the subscription and will mark it as paid.'); ?>
				</div>

				<div class="t-hidden" data-inactive-message>
					<?php echo JText::_('By changing the status to inactive, you will no longer be able to make any changes on the subscription'); ?>
				</div>
			</div>

			<?php echo $this->html('form.ids', 'cid', $ids); ?>
			<?php echo $this->html('form.action', 'subscription', 'updateStatus'); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default-o"><?php echo JText::_('COM_PP_CANCEL_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-primary-o"><?php echo JText::_('COM_PP_UPDATE_BUTTON'); ?></button>
	</buttons>
</dialog>