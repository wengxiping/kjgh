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
	<height>500</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}" : "[data-submit-button]",
		"{textarea}": "[data-textarea]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},

		"{textarea} click": function(element) {
			element.focus();
			element.select();
		}
	}
	</bindings>
	<title><?php echo JText::sprintf('Payment Notification'); ?></title>
	<content>
		<div class="o-form-group">
			<?php if ($type == 'json') { ?>
			<textarea class="o-form-control" rows="20" data-textarea><?php echo json_encode(json_decode($ipn->json), JSON_PRETTY_PRINT);?></textarea>
			<?php } ?>

			<?php if ($type == 'http') { ?>
			<textarea class="o-form-control" rows="20" data-textarea><?php echo $ipn->query;?></textarea>
			<?php } ?>

			<?php if ($type == 'php') { ?>
			<textarea class="o-form-control" rows="20" data-textarea><?php echo $ipn->php;?></textarea>
			<?php } ?>
		</div>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>
