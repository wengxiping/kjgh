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
		"{closeButton}": "[data-close-button]",
		"{submitButton}": "[data-submit-button]",
		"{quantity}": "[data-pp-gift-quantity]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_GIFT_DIALOG_FORM_TITLE'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="o-form-horizontal">

			<p class="t-lg-mb--xl">
				<?php echo JText::_('COM_PP_GIFT_DIALOG_FORM_DESC'); ?>
			</p>

			<div class="o-form-group">

				<label class="o-control-label" for="subject">
					<?php echo JText::_('COM_PP_QUANTITY'); ?>
				</label>

				<div class="o-control-input">
					<?php echo $this->html('form.text', 'quantity', '1', '', array('data-pp-gift-quantity' => ''), array('postfix' => 'Plans', 'size' => 8, 'class' => 'text-center')); ?>
				</div>
			</div>

			<div class="t-text--danger" data-pp-gift-error></div>
			
			<?php echo $this->html('form.hidden', 'invoice_id', $invoice->getId()); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-primary-o btn-sm"><?php echo JText::_('COM_PP_ADD_BUTTON'); ?></button>
	</buttons>
</dialog>