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
<div class="panel-table">
	<table class="app-table table">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('COM_PP_TABLE_COLUMN_MESSAGE');?>
				</th>
				<th class="center" width="15%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_AMOUNT'); ?>
				</th>
				<th class="center" width="15%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_CREATED'); ?>
				</th>
				<th class="center" width="1%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php if ($transactions) { ?>
				<?php foreach ($transactions as $transaction) { ?>
				<tr>
					<td>
						<a href="index.php?option=com_payplans&view=transaction&layout=form&id=<?php echo $transaction->transaction_id;?>">
							<?php echo $transaction->getMessage();?>
						</a>
					</td>
					<td class="center">
						<?php echo PPFormats::price($transaction->amount);?>
					</td>

					<td class="center">
						<?php echo PP::date($transaction->created_date)->format(JText::_('DATE_FORMAT_LC2'));?>
					</td>
					<td class="center">
						<?php echo $transaction->getId();?>
					</td>
				</tr>		
				<?php } ?>
			<?php } ?>

			<?php if (!$transactions) { ?>
				<?php echo $this->html('grid.emptyBlock', 'COM_PP_TRANSACTIONS_EMPTY', 4); ?>
			<?php } ?>
		</tbody>
	</table>
</div>