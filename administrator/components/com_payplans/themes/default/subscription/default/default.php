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
			<div class="app-filter-bar__search-input-group">
				<?php echo $this->html('filter.daterange', $states->dateRange, 'dateRange', 'COM_PP_FILTER_SUBSCRPTION_DATE'); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.plans', 'plan_id', $states->plan_id, array()); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php $attr['none'] = JText::_('COM_PAYPLANS_STATUS_SELECT');?>
				<?php echo $this->html('filter.status', 'status', $states->status, 'subscription', '', $attr); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $states->limit); ?>
			</div>
		</div>
	</div>
	
	<div class="panel-table">
		<table class="app-table table table-pp" data-table>
			<thead>
				<tr>
					<?php if ($this->tmpl != 'component') { ?>
					<th class="center"  width="1%">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>

					<th width="10%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_SUBSCRIPTION'); ?>
					</th>

					<th class="center">
						<?php echo $this->html('grid.sort', 'user_id', 'COM_PP_TABLE_COLUMN_USER', $states); ?>
					</th>

					<th width="15%" class="center">
						<?php echo $this->html('grid.sort', 'plan_id', 'COM_PP_TABLE_COLUMN_PLAN', $states); ?>
					</th>

					<th width="10%" class="center">
						<?php echo $this->html('grid.sort', 'total', 'COM_PP_TABLE_COLUMN_AMOUNT', $states); ?>
					</th>

					<th width="10%" class="center">
						<?php echo $this->html('grid.sort', 'status', 'COM_PP_TABLE_COLUMN_STATE', $states); ?>
					</th>
	
					<?php if ($this->tmpl != 'component') { ?>
					
					<th width="15%" class="center">
						<?php echo $this->html('grid.sort', 'expiration_date', 'COM_PP_TABLE_COLUMN_EXPIRE_DATE', $states); ?>	
					</th>
					<?php } ?>

					<?php if ($this->tmpl != 'component') { ?>
					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'subscription_id', 'COM_PP_TABLE_COLUMN_ID', $states); ?>
					</th>
					<?php } ?>
				</tr>
			</thead>

			<tbody>
				<?php if ($subscriptions) { ?>
					<?php $i = 0; ?>
					<?php foreach ($subscriptions as $subscription) { ?>
					<tr>
						<?php if ($this->tmpl != 'component') { ?>
						<td class="center">
							<?php echo $this->html('grid.id', $i, $subscription->getId()); ?>
						</td>
						<?php } ?>

						<td>
							<a href="index.php?option=com_payplans&view=subscription&layout=form&id=<?php echo $subscription->getId();?>"
								data-pp-row
								data-id="<?php echo $subscription->getId();?>"
								data-title="<?php echo $subscription->getTitle();?>"
								data-order-id="<?php echo $subscription->order->getId();?>"
								>
								<?php echo $subscription->getKey(); ?>
							</a>
						</td>

						<td class="center">
							<?php echo $subscription->buyer->getName();?> (<?php echo $subscription->buyer->getEmail();?>)
						</td>

						<td class="center">
							<?php echo JText::_($subscription->getTitle());?>
						</td>

						<td class="center">
							<?php echo $this->html('html.amount', $subscription->getTotal(), $subscription->order->getCurrency()); ?>
						</td>

						<td class="center">
							<span class="o-label <?php echo $subscription->getStatusLabelClass();?>"><?php echo $subscription->getLabel(); ?></span>
						</td>

						<?php if ($this->tmpl != 'component') { ?>

						<td class="center">
							<?php if ($subscription->getExpirationDate()) { ?>
								<?php echo PP::date($subscription->expiration_date)->format(JText::_('DATE_FORMAT_LC2')); ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						
						<td class="center">
							<?php echo $subscription->getId(); ?>
						</td>
						<?php } ?>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>


				<?php if (!$subscriptions) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_SUBSCRIPTIONS_EMPTY', 9); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 9); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'subscription'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
</form>