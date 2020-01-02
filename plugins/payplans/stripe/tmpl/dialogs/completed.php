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
		"{closeButton}" : "[data-close-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_PAYMENT_DETAILS_UPDATED'); ?></title>
	<content>
		<p class="t-lg-mb--xl"><?php echo JText::_('COM_PP_PAYMENT_DETAILS_UPDATED_INFO');?></p>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default-o btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>
