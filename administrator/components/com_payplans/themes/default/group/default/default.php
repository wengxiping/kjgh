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

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search', $states->search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'published', $states->published); ?>
			</div>
		</div>
		
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.lists', array(
							array('value' => 'all', 'title' => 'COM_PAYPLANS_FILTERS_SELECT_VISIBLE_STATE'),
							array('value' => '0', 'title' => 'COM_PAYPLANS_FILTERS_OFF_VISIBLE'),
							array('value' => '1', 'title' => 'COM_PAYPLANS_FILTERS_ON_VISIBLE')
						), 'visible', $states->visible); ?>

			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.group', 'parent', $states->parent, array(), array('none' => JText::_('COM_PP_SELECT_PARENT'))); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $states->limit); ?>
			</div>
		</div>
	</div>


	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkall'); ?>
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'title', 'COM_PP_GROUP_GRID_GROUP_TITLE', $states);?>
					</th>

					<th width="5%" class="hidden-phone center">
						<?php echo $this->html('grid.sort', 'published', 'COM_PP_GROUP_GRID_GROUP_PUBLISHED', $states);?>
					</th>
					
					<th width="5%" class="hidden-phone center">
						<?php echo $this->html('grid.sort', 'visible', 'COM_PP_GROUP_GRID_GROUP_VISIBLE', $states);?>
					</th>

					<th width="5%" class="hidden-phone center">
						<?php echo $this->html('grid.sort', 'ordering', 'COM_PP_GROUP_GRID_GROUP_ORDERING', $states); ?>
						<?php echo $this->html('grid.order' , $rows, 'group'); ?>
					</th>

					<th width="1%" class="hidden-phone center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($rows) { ?>
					<?php $i = 0; ?>
					<?php foreach ($rows as $row) { ?>
						<tr>
							<th class="center">
								<?php echo $this->html('grid.id', $i, $row->group_id); ?>
							</th>

							<td class="hidden-phone pp-word-wrap">
								<a href="index.php?option=com_payplans&view=group&layout=form&id=<?php echo $row->group_id;?>"><?php echo JText::_($row->title);?></a>
							</td>

							<td width="15%" class="hidden-phone center">
								<?php echo $this->html('grid.published', $row, 'group', 'published'); ?>
							</td>

							<td width="15%" class="hidden-phone center">
								<?php echo $this->html('grid.published', $row, 'group', 'visible', array(0 => 'visible', 1 => 'invisible')); ?>
							</td>

							<td class="center order">
								<?php echo $this->html('grid.ordering', count($rows), ($i + 1), $states->ordering,  $row->ordering, 'group'); ?>
							</td>

							<td class="center">
								<?php echo $row->group_id;?>
							</td>
						</tr>
				<?php $i++; ?>
					<?php } ?>
				<?php } ?>

				<?php if (!$rows) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_GROUPS_EMPTY', 6); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 6); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'group'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
</form>
