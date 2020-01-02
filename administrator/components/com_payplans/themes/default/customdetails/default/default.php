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
		<table class="app-table table table-striped">
			<thead>
				<tr>
				   <th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					
					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
					</th>
					
					<th width="20%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PUBLISHED'); ?>
					</th>

					<th width="20%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_CREATED'); ?>
					</th>

					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($items) { ?>
					<?php $i = 0; ?>
					<?php foreach ($items as $item) { ?>
					<tr>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $item->id); ?>
						</th>

						<td>
							<a href="index.php?option=com_payplans&view=<?php echo $view; ?>&layout=customdetailsform&id=<?php echo $item->id; ?>">
								<?php echo $item->title; ?>
							</a>
						</td>

						<td class="center">
							<?php echo $this->html('grid.published', $item, 'customdetails', 'published', array(0 => 'publish', 1 => 'unpublish')); ?>
						</td>

						<td class="center">
							<?php echo $item->created;?>
						</td>

						<td class="center">
							<?php echo $item->id; ?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>

				<?php if (!$items) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_ADMIN_EMPTY_CUSTOM_DETAILS', $this->tmpl == 'component' ? 4 : 5); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, $this->tmpl == 'component' ? 4 : 5); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'customdetails'); ?>
	<?php echo $this->html('form.hidden', 'ordering', $states->ordering); ?>
	<?php echo $this->html('form.hidden', 'direction', $states->direction); ?>
	<?php echo $this->html('form.returnUrl'); ?>
</form>