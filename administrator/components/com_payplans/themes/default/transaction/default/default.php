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
				<?php echo $this->html('filter.daterange', $states->dateRange, 'dateRange', 'COM_PP_FILTER_TRANSACTION_DATE'); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.gateways', 'app_id', (int) $states->app_id, array('none' => JText::_('COM_PP_SELECT_PAYMENT_METHOD'))); ?>
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
					
					<th width="5%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TRANSACTION'); ?>
					</th>

					<th class="center" width="15%">
						<?php echo $this->html('grid.sort', 'user_id', 'COM_PP_TABLE_COLUMN_USER', $states); ?>
					</th>
					
					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'amount', 'COM_PP_TABLE_COLUMN_AMOUNT', $states); ?>
					</th>
					
					<th class="center" width="15%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_GATEWAY'); ?>
					</th>
					
					<th class="center" width="15%">
						<?php echo $this->html('grid.sort', 'created_date', 'COM_PP_TABLE_COLUMN_CREATED', $states); ?>
					</th>

					<th class="center" width="1%">
						<?php echo $this->html('grid.sort', 'transaction_id', 'COM_PP_TABLE_COLUMN_ID', $states); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($transactions) { ?>
					<?php $i = 0; ?>
					<?php foreach ($transactions as $transaction) { ?>
					<tr>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $transaction->transaction_id); ?>
						</th>

						<td>
							<a href="index.php?option=com_payplans&view=transaction&layout=form&id=<?php echo $transaction->transaction_id;?>"
								<?php if ($transaction->message) { ?>
								data-pp-provide="tooltip"
								data-title="<?php echo JText::_($transaction->message);?>"
								<?php  }?>
							>
								<?php echo PP::encryptor()->encrypt($transaction->invoice_id); ?>
							</a>
						</td>

						<td class="center">
							<?php echo $transaction->buyer->getName();?> (<?php echo $transaction->buyer->getEmail();?>)
						</td>

						<td class="center">
							<?php echo PPFormats::price($transaction->amount); ?>
						</td>

						<td class="center">
							<?php if ($transaction->payment_id && ($transaction->payment instanceof PPPayment)) { ?>
								<?php echo $transaction->payment->getApp()->getTitle();?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<?php echo PP::date($transaction->created_date)->format(JText::_('DATE_FORMAT_LC2')); ?>
						</td>

						<td class="center">
							<?php echo $transaction->transaction_id;?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>

				<?php if (!$transactions) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_TRANSACTIONS_EMPTY', 7); ?>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="11" class="center">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo $this->html('form.action', 'transaction'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
</form>
 
