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
	<height>250</height>
	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{submitButton}": "[data-submit-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('Mark Invoice as Paid'); ?></title>
	<content>
		<p>
			<?php echo JText::_('Are you sure you want to mark this invoice as paid? Once an invoice is marked as paid, a new transaction will be added into the system and their subscription will be activated automatically');?>
		</p>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-pp-default-o"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-primary-o"><?php echo JText::_('COM_PP_MARK_AS_PAID'); ?></button>
	</buttons>
</dialog>
