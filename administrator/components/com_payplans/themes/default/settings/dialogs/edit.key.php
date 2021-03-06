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
	<width>400</width>
	<height>200</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}": "[data-submit-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_CONFIRM_UPDATE_KEY'); ?></title>
	<content>
		<p><?php echo JText::_('COM_PP_CONFIRM_UPDATE_KEY_NOTICE'); ?></p>
	</content>
	<buttons>
		<button type="button" class="btn btn-pp-default btn-sm" data-close-button><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button type="button" class="btn btn-pp-primary-o btn-sm" data-submit-button><?php echo JText::_('COM_PP_UPDATE_BUTTON'); ?></button>
	</buttons>
</dialog>