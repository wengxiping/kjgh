<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<a href="javascript:void(0);"
	class="saveorder btn btn-pp-default-o btn-xs"
	data-original-title="<?php echo JText::_('JLIB_HTML_SAVE_ORDER');?>"
	data-pp-provide="tooltip"
	data-table-grid-saveorder
	data-total="<?php echo $total;?>"
	data-task="<?php echo $task;?>"
>
<i class="fas fa-save"></i>
</a>
