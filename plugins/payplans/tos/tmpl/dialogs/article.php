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
	<width>750</width>
	<height>450</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}": "[data-submit-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_TERMS_AND_CONDITIONS'); ?></title>
	<content type="text"><?php echo $link;?></content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-danger-o btn-sm"><?php echo JText::_('COM_PP_I_DISAGREE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-primary-o btn-sm"><?php echo JText::_('COM_PP_I_AGREE_BUTTON'); ?></button>
	</buttons>
</dialog>
