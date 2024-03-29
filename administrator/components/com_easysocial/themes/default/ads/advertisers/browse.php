<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>760</width>
	<height>500</height>
	<selectors type="json">
	{
		"{cancelButton}"	: "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function()
		{
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_ADS_BROWSE_ADVERTISERS'); ?></title>
	<content type="text"><?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=ads&layout=advertisers&tmpl=component&jscallback=<?php echo $callback;?></content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
	</buttons>
</dialog>
