<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
				<?php echo $this->html('filter.group', 'group_id', $states->group_id, array(), 
					array('none' => JText::_('COM_PP_SELECT_A_GROUP'))
				); ?>
			</div>
		</div>

		<?php if ($this->tmpl != 'component') { ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.lists', array(
							array('value' => 'all', 'title' => 'COM_PAYPLANS_FILTERS_SELECT_VISIBLE_STATE'),
							array('value' => '0', 'title' => 'COM_PAYPLANS_FILTERS_OFF_VISIBLE'),
							array('value' => '1', 'title' => 'COM_PAYPLANS_FILTERS_ON_VISIBLE')
						), 'visible', $states->visible); ?>
			</div>
		</div>
		<?php } ?>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left"></div>

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
					<?php if ($this->tmpl != 'component') { ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkall'); ?>
					</th>
					<?php } ?>

					<th>
						<?php echo $this->html('grid.sort', 'title', 'COM_PAYPLANS_PLAN_GRID_PLAN_TITLE', $states); ?>
					</th>

					<?php if ($this->tmpl != 'component' && $this->config->get('useGroupsForPlan')) { ?>
					<th class="hidden-phone">
						<?php echo JText::_("PAYPLANS_PLAN_GRID_FIELD_GROUP"); ?>
					</th>
					<?php } ?>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'published', 'COM_PAYPLANS_PLAN_GRID_PLAN_PUBLISHED', $states); ?>
					</th>

					<?php if ($this->tmpl != 'component') { ?>
					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'visible', 'COM_PAYPLANS_PLAN_GRID_PLAN_VISIBLE', $states); ?>
					</th>
					<?php } ?>

					<th width="10%" class="center">
						<?php echo JText::_("COM_PAYPLANS_PLAN_GRID_PLAN_PRICE");?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_("COM_PAYPLANS_PLAN_GRID_PLAN_TYPE");?>
					</th>

					<?php if ($this->tmpl !='component') { ?>
					<th width="5%" class="hidden-phone center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ACTIVE'); ?>
					</th>

					<th width="5%" class="hidden-phone center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_EXPIRED'); ?>
					</th>

					<th width="5%" class="hidden-phone center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PENDING'); ?>
					</th>

					<th width="5%" class="hidden-phone center">
						<?php echo $this->html('grid.sort', 'ordering', 'COM_PAYPLANS_PLAN_GRID_PLAN_ORDERING', $states); ?>
						<?php echo $this->html('grid.order' , $plans, 'plan'); ?>
					</th>
					<?php } ?>

					<th width="1%" class="hidden-phone center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($plans) { ?>
					<?php $i = 0; ?>
					<?php foreach ($plans as $row) { ?>
					<tr>
						<?php if ($this->tmpl != 'component') { ?>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $row->plan_id); ?>
						</th>
						<?php } ?>

						<td class="pp-word-wrap">
							<a href="index.php?option=com_payplans&view=plan&layout=form&id=<?php echo $row->plan_id;?>"
								data-pp-plan-item
								data-title="<?php echo $this->html('string.escape', $row->title);?>"
								data-id="<?php echo $row->plan_id;?>"
								><?php echo JText::_($row->title);?></a>
						</td>

						<?php if ($this->config->get('useGroupsForPlan') && $this->tmpl != 'component') { ?>
						<td class="hidden-phone">
							<?php if ($row->groups) { ?>
								<?php foreach ($row->groups as $group) { ?>
								<div><a href="index.php?option=com_payplans&view=group&layout=form&id=<?php echo $group->getId(); ?>"><?php echo $group->getTitle(); ?></a></div>
								<?php } ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						<?php } ?>

						<td class="hidden-phone center">
							<?php echo $this->html('grid.published', $row, 'plan', 'published'); ?>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
						<td class="hidden-phone center">
							<?php echo $this->html('grid.published', $row, 'plan', 'visible', array(0 => 'visible', 1 => 'invisible')); ?>
						</td>
						<?php } ?>

						<td class="hidden-phone center">
							<?php echo $this->html('html.amount', $row->price, $row->currency); ?>
						</td>

						<td class="center">
							<?php echo ucfirst($row->getExpirationType()); ?>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
						<td class="hidden-phone center">
							<?php echo (isset($stats[$row->plan_id]) && isset($stats[$row->plan_id][PP_SUBSCRIPTION_ACTIVE])) ? $stats[$row->plan_id][PP_SUBSCRIPTION_ACTIVE] : '0'; ?>
						</td>

						<td class="hidden-phone center">
							<?php echo (isset($stats[$row->plan_id]) && isset($stats[$row->plan_id][PP_SUBSCRIPTION_EXPIRED])) ? $stats[$row->plan_id][PP_SUBSCRIPTION_EXPIRED] : '0'; ?>
						</td>

						<td class="hidden-phone center">
							<?php echo (isset($stats[$row->plan_id]) && isset($stats[$row->plan_id][PP_SUBSCRIPTION_HOLD])) ? $stats[$row->plan_id][PP_SUBSCRIPTION_HOLD] : '0'; ?>
						</td>

						<td class="center order">
							<?php echo $this->html('grid.ordering', count($plans), ($i + 1), $states->ordering,  $row->ordering, 'plan'); ?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $row->plan_id;?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>


				<?php if (!$plans) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PAYPLANS_ADMIN_BLANK_PLAN', 12); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 12); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'plan'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
</form>
