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
	<height>250</height>
	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{submitButton}": "[data-submit-button]",
		"{form}": "[data-pp-refund-form]"
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
	<title><?php echo JText::_('Refund Transaction'); ?></title>
	<content type="html">
		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-pp-refund-form>

			<p class="t-lg-mb--xl">
				<?php if ($paymentApp->supportForRefund()) { ?>
					<?php echo JText::sprintf('COM_PP_REFUND_TRANSACTION_CONFIRMATION', '<b>' .  $this->html('html.amount', $transaction->getAmount(), $transaction->getCurrency()) . '</b>'); ?>
				<?php } else { ?>
					<?php echo JText::sprintf('COM_PP_REFUND_TRANSACTION_CONFIRMATION_MANUAL', '<b>' .  $this->html('html.amount', $transaction->getAmount(), $transaction->getCurrency()) . '</b>'); ?>
				<?php } ?>
			</p>

			<?php if ($paymentApp->supportForRefund()) { ?>
			<p><?php echo JText::_('COM_PP_REFUND_TRANSACTION_CONFIRMATION_DISCLAIMER'); ?></p>
			<?php } ?>

			<?php echo $this->html('form.action', 'invoice', 'refund'); ?>
			<?php echo $this->html('form.hidden', 'transactionId', $transaction->getId()); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-pp-default-o"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-danger-o"><?php echo JText::_('COM_PP_REFUND_BUTTON'); ?></button>
	</buttons>
</dialog>
