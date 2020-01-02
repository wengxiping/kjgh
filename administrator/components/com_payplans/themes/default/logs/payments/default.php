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
			<?php echo $this->html('filter.search', $states->search, 'search'); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__search-input-group">
				<?php echo $this->html('filter.daterange', $states->dateRange, 'dateRange', 'COM_PP_FILTER_LOG_DATE'); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.gateways', 'app_id', (int) $states->app_id, '', array('none' => JText::_('COM_PP_SELECT_PAYMENT_METHOD'))); ?>
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
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PAYMENT_KEY'); ?>
					</th>

					<th width="10%" class="center">
						&nbsp;
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_JSON_DATA'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_HTTP_DATA'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PHP_DATA'); ?>
					</th>

					<th class="center" width="15%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PROVIDER'); ?>
					</th>
				
					<th width="15%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_CREATED'); ?>
					</th>

					<th class="center" width="1%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($items) { ?>
					<?php $i = 0; ?>
					<?php foreach ($items as $item) { ?>
					<tr data-item data-id="<?php echo $item->id;?>">
						<th class="center">
							<?php echo $this->html('grid.id', $i, $item->id); ?>
						</th>

						<td>
							<?php echo $item->getPayment()->getKey();?>
						</td>

						<td class="center">
							<a href="javascript:void(0);" class="btn btn-pp-success btn-xs" data-simulate>Simulate</a>
						</td>

						<td class="center">
							<a href="javascript:void(0);" data-view-notification="json">View</a>
						</td>
						
						<td class="center">
							<a href="javascript:void(0);" data-view-notification="http">View</a>
						</td>

						<td class="center">
							<?php if ($item->php) { ?>
								<a href="javascript:void(0);" data-view-notification="php">View</a>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<?php if ($item->getProviderTitle()) { ?>
								<?php echo $item->getProviderTitle(); ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<?php echo $item->created;?>
						</td>

						<td class="center">
							<?php echo $item->id;?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>

				<?php if (!$items) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_LOGS_EMPTY', 11); ?>
				<?php } ?>
			</tbody>

			<?php if ($pagination && ($pagination instanceof PPPagination)) { ?>
				<?php echo $this->html('grid.pagination', $pagination, 11); ?>
			<?php } ?>
		</table>
	</div>

	<input type="hidden" name="option" value="com_payplans" />
	<input type="hidden" name="controller" value="log" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
