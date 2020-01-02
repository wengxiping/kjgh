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
				<?php echo $this->html('filter.daterange', $states->dateRange, 'dateRange', 'COM_PP_FILTER_PAYMENT_DATE'); ?>
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
				<?php echo $this->html('filter.status', 'status', $states->status, 'invoice', 'none', $attr); ?>
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
					<?php if ($this->tmpl != 'component') { ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkall'); ?>
					</th>
					<?php } ?>

					<th width="10%">
						<?php echo $this->html('grid.sort', 'invoice_id', 'COM_PP_TABLE_COLUMN_INVOICE', $states);?>
					</th>

					<?php if ($this->tmpl != 'component') { ?>
					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'serial', 'COM_PP_INVOICE_INVOICE_SERIAL', $states); ?>
					</th>
					<?php } ?>
					
					<th class="center" width="20%">
						<?php echo $this->html('grid.sort', 'user_id', 'COM_PP_TABLE_COLUMN_USER', $states);?>
					</th>
					
					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'subtotal', 'COM_PP_TABLE_COLUMN_SUBTOTAL', $states);?>
					</th>
					
					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'total', 'COM_PP_TABLE_COLUMN_TOTAL', $states);?>
					</th>
					
					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'status', 'COM_PP_TABLE_COLUMN_STATE', $states);?>
					</th>
					
					<?php if ($this->tmpl != 'component') { ?>
					<th class="center" width="20%">
						<?php echo $this->html('grid.sort', 'paid_date', 'COM_PP_TABLE_COLUMN_PAYMENT_DATE', $states);?>
					</th>

					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'invoice_id', 'COM_PP_TABLE_COLUMN_ID', $states);?>
					</th>
					<?php } ?>
				</tr>
			</thead>

			<tbody>
				<?php if ($invoices) { ?>
					<?php $i = 0; ?>
					<?php foreach ($invoices as $invoice) { ?>
					<tr>
						<?php if ($this->tmpl != 'component') { ?>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $invoice->getId()); ?>
						</th>
						<?php } ?>

						<td>
							<a href="index.php?option=com_payplans&view=invoice&layout=form&id=<?php echo $invoice->getId();?>" data-pp-row data-id="<?php echo $invoice->getId();?>">
								<?php echo $invoice->getKey();?>
							</a>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
						<td class="center">
							<?php echo $invoice->getSerial();?>
						</td>
						<?php } ?>

						<td class="hidden-phone center">
							<?php echo $invoice->buyer->getName();?> (<?php echo $invoice->buyer->getEmail();?>)
						</td>

						<td class="center">
							<?php echo $invoice->getSubtotal();?>
						</td>

						<td class="center">
							<?php echo $invoice->getTotal();?>
						</td>

						<td class="hidden-phone center">
							<span class="o-label <?php echo $invoice->getStatusLabelClass();?>">
								<?php echo $invoice->getStatusName();?>
							</span>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
						<td class="hidden-phone center">
							<?php if ($invoice->isPaid()) { ?>
								<?php echo PP::date($invoice->paid_date)->format(JText::_('DATE_FORMAT_LC2')); ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>

						<td class="pp-word-wrap center">
							<?php echo $invoice->getId();?>
						</td>
						<?php } ?>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>


				<?php if (!$invoices) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_INVOICES_EMPTY', 9); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 9); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'invoice'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
</form>
