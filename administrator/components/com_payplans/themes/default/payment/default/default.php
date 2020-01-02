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
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'payment_id', 'COM_PP_TABLE_COLUMN_INVOICE', $filter_order_dir, $filter_order);?>
					</th>
					
					<th class="center">
						<?php echo $this->html('grid.sort', 'app_id', 'COM_PP_TABLE_COLUMN_GATEWAY', $filter_order_dir, $filter_order);?>
					</th>
					
					<th class="center">
						<?php echo $this->html('grid.sort', 'created_date', 'COM_PP_TABLE_COLUMN_CREATED', $filter_order_dir, $filter_order);?>
					</th>
					
					<th class="center">
						<?php echo $this->html('grid.sort', 'modified_date', 'COM_PP_TABLE_COLUMN_MODIFIED', $filter_order_dir, $filter_order);?>
					</th>

					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID');?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($payments) { ?>
					<?php $i = 0; ?>
					<?php foreach ($payments as $payment) { ?>
					<tr>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $payment->getId()); ?>
						</th>

						<td class="pp-word-wrap">
							<a href="index.php?option=com_payplans&view=payment&layout=form&id=<?php echo $payment->getId();?>">
								<?php echo $payment->getKey();?>
							</a>
						</td>

						<td class="center">
							<?php echo $payment->gateway->getTitle();?>
						</td>

						<td class="center">
							<?php echo $payment->getCreatedDate();?>
						</td>

						<td class="center">
							<?php echo $payment->getModifiedDate();?>
						</td>

						<td class="center">
							<?php echo $payment->getId();?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>


				<?php if (!$payments) { ?>
					<?php echo $this->html('grid.emptyBlock', 'No payment records available currently', 6); ?>
				<?php } ?>
			</tbody>
				
			<?php echo $this->html('grid.pagination', $pagination, 6); ?>
		</table>
	</div>
	<?php echo $this->html('form.action', 'payment'); ?>
	<input type="hidden" name="filter_order" value="<?php echo $filter_order;?>" />
	<input type="hidden" name="filter_order_dir" value="<?php echo $filter_order_dir;?>" />
</form>