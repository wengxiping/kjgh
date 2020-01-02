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
	<width>450</width>
	<height>200</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}" : "[data-submit-button]",
		"{form}": "[data-submit-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},

		"{submitButton} click": function(element) {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PAYPLANS_ORDER_TERMINATE_CONFIRM_WINDOW_TITLE'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php');?>" method="post" data-submit-form>
			<p>
				<?php echo JText::_('COM_PAYPLANS_ORDER_TERMINATE_CONFIRM_WINDOW_MSG'); ?>
			</p>
			<?php echo $this->html('form.action', 'order', 'cancelSubscription'); ?>
			<?php echo $this->html('form.hidden', 'order_key', $key); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-danger-o btn-sm"><?php echo JText::_('COM_PP_YES_BUTTON'); ?></button>
	</buttons>
</dialog>