<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
	<height>250</height>
	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{submitButton}": "[data-submit-button]",
		"{form}": "[data-pp-cancel-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},
		
		"{submitButton} click": function() {
			this.form().submit();
		}
	}
	</bindings>

	<title><?php echo JText::_('Cancel Subscription'); ?></title>
	<content type="html">
		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-pp-cancel-form>
			<p><?php echo JText::_('COM_PP_SUBSCRIPTION_CANCEL_CONFIRM_MESSAGE'); ?></p>

			<?php echo $this->html('form.action', 'subscription', 'terminate'); ?>
			<?php echo $this->html('form.hidden', 'orderId', $order->getId()); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-pp-default-o"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-danger-o"><?php echo JText::_('COM_PP_CANCEL_BUTTON'); ?></button>
	</buttons>
</dialog>
