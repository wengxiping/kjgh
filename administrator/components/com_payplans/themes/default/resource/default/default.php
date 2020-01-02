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
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'resource', 'COM_PAYPLANS_RESOURCE_GRID_TITLE', $direction, $ordering);?>
					</th>

					<th width="15%" class="center">
						<?php echo $this->html('grid.sort', 'user_id', 'COM_PP_TABLE_COLUMN_USER', $direction, $ordering);?>
					</th>

					<th width="35%" class="center">
						<?php echo $this->html('grid.sort', 'subscription_ids', 'COM_PAYPLANS_RESOURCE_GRID_SUBSCRIPTION_IDS', $direction, $ordering);?>
					</th>
					
					<th width="10%" class="center">
						<?php echo $this->html('grid.sort', 'count', 'COM_PAYPLANS_RESOURCE_GRID_COUNT', $direction, $ordering);?>
					</th>

					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($resources) { ?>
					<?php $i = 0; ?>
					<?php foreach ($resources as $resource) { ?>
					<tr>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $resource->resource_id); ?>
						</th>

						<td class="pp-word-wrap">
							<a href="index.php?option=com_payplans&view=resource&layout=form&id=<?php echo $resource->resource_id;?>">
								<?php echo $resource->title; ?>
							</a>
						</td>

						<td class="center">
							<?php echo $resource->user->getName(); ?> (<?php echo $resource->user->getEmail();?>)
						</td>

						<td class="center">
							<?php if ($resource->subscriptions) { ?>
								<?php foreach ($resource->subscriptions as $subscription) { ?>
									<a href="index.php?option=com_payplans&view=subscription&layout=form&id=<?php echo $subscription->getId();?>" target="_blank" class="o-label o-label--primary">
										#<?php echo $subscription->getId();?> (<?php echo $subscription->getKey();?>)
									</a>
								<?php } ?>
							<?php } ?>
						</td>

						<td class="center">
							<?php echo $resource->count;?>
						</td>

						<td class="center">
							<?php echo $resource->resource_id;?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>


				<?php if (!$resources) { ?>
					<?php echo $this->html('grid.emptyBlock', 'No resource records available currently', 6); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 6); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'resource'); ?>
	<input type="hidden" name="filter_order" value="<?php echo $ordering;?>" />
	<input type="hidden" name="filter_order_dir" value="<?php echo $direction;?>" />
</form>
