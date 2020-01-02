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
	<width>600</width>
	<height>350</height>
	<selectors type="json">
	{
		"{close}" : "[data-close-button]",
		"{form}": "[data-extend-form]",
		"{submit}" : "[data-submit-button]",
		"{lifetimeToggle}": "[data-extend-lifetime]",
		"{extendPeriod}": "[data-extend-period]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{submit} click": function() {
			this.form().submit();
		},

		"{close} click": function() {
			this.parent.close();
		},

		"{lifetimeToggle} change": function (element) {
			var checked = element.is(':checked');

			if (!checked) {
				this.extendPeriod().removeClass('t-hidden');
				return;
			}

			this.extendPeriod().addClass('t-hidden');
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_EXTEND_SUBSCRIPTION_DIALOG_TITLE'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="o-form-horizontal" data-extend-form>
			<p>
				<?php echo JText::_('Set a time frame to extend the selected subscriptions. Any expired subscriptions would be active again from now up till the selected time frame.');?>
			</p>

			<div class="o-form-group t-lg-mt--xl">
				<?php echo $this->html('form.label', 'Extension Time'); ?>

				<div class="o-control-input">
					<?php echo $this->html('form.timer', 'extend_time', '000000000000'); ?>
				</div>
			</div>

			<?php echo $this->html('form.ids', 'cid', $ids); ?>
			<?php echo $this->html('form.action', 'subscription', 'extend'); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default-o"><?php echo JText::_('COM_PP_CANCEL_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-primary-o"><?php echo JText::_('COM_PP_EXTEND_BUTTON'); ?></button>
	</buttons>
</dialog>