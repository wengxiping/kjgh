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
<div class="app-order-group" data-grid-column>
	<div class="app-order-group__item">
		<input name="order[]" value="<?php echo $ordering;?>" class="order-value" type="text" size="3">
	</div>
	<div class="app-order-group__item">
		<?php if ($showOrdering == 'ordering') { ?>
			<span class="order-up">
				<?php if ($current != 1) { ?>
				<a class="btn btn-pp-default-o" href="javascript:void(0);"
					title="<?php echo $this->html('string.escape', JText::_('COM_PP_GRID_MOVE_UP'));?>"
					data-pp-provide="tooltip"
					data-task="<?php echo $controller.".moveUp";?>"
					data-grid-order-up
				>
					<i class=""></i>
				</a>
				<?php } ?>
			</span>

			<span class="order-down">
				<?php if( $current != $total){ ?>
				<a class="btn btn-pp-default-o" href="javascript:void(0);"
					data-original-title="<?php echo $this->html('string.escape', JText::_('COM_PP_GRID_MOVE_DOWN'));?>"
					data-pp-provide="tooltip"
					data-task="<?php echo $controller.".moveDown";?>"
					data-grid-order-down
				>
					<i class=""></i>
				</a>
				<?php } ?>
			</span>
		<?php } ?>
	</div>
</div>
