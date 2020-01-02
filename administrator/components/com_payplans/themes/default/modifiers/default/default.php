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
<form method="post" name="adminForm" id="adminForm" data-table-grid>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkall'); ?>
					</th>

					<th width="25%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
					</th>

					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_DESCRIPTION'); ?>
					</th>

					<th width="20%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STATUS'); ?>
					</th>

					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($modifiers) { ?>
					<?php $i = 0; ?>
					<?php foreach ($modifiers as $modifier) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i++, $modifier->app_id); ?>
						</td>

						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=modifiers&layout=form&id=' . $modifier->app_id);?>"><?php echo $modifier->title;?></a>
						</td>

						<td>
							<?php if ($modifier->description) { ?>
								<?php echo $modifier->description;?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						
						<td class="center">
							<?php echo $this->html('grid.published', $modifier, 'modifiers', 'published');?>
						</td>

						<td class="center">
							<?php echo $modifier->app_id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } ?>


				<?php if (!$modifiers) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_MODIFIERS_BLANK', 5, true); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 5); ?>

		</table>
	</div>

	<?php echo $this->html('form.action', 'modifiers'); ?>


</form>
