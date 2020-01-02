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
					&nbsp;
				</th>

				<th class="center" width="10%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_SERIAL'); ?>
				</th>

				<th class="center" width="10%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_STATE'); ?>
				</th>

				<th class="center" width="15%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_SUBTOTAL'); ?>
				</th>

				<th class="center" width="15%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_TOTAL'); ?>
				</th>

				<th class="center" width="15%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_PAYMENT_DATE'); ?>
				</th>
				
				<th class="center" width="1%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php if ($invoices) { ?>
				<?php foreach ($invoices as $invoice) { ?>
				<tr>
					<td>
						<a href="index.php?option=com_payplans&view=invoice&layout=form&id=<?php echo $invoice->getId();?>">
							<?php echo $invoice->getKey(); ?>
						</a>
					</td>

					<td class="center">
						<?php echo $invoice->getSerial();?>
					</td>
					
					<td class="center">
						<span class="o-label <?php echo $invoice->getStatusLabelClass();?>"><?php echo $invoice->getStatusName();?></span>
					</td>

					<td class="center">
						<?php echo $this->html('html.amount', $invoice->getSubtotal(), $invoice->getCurrency()); ?>
					</td>

					<td class="center">
						<?php echo $this->html('html.amount', $invoice->getTotal(), $invoice->getCurrency()); ?>
					</td>

					<td class="center">
						<?php echo $invoice->getPaidDate(); ?>
					</td>

					<td class="center">
						<?php echo $invoice->getId();?>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>

			<?php if (!$subscriptions) { ?>
				<?php echo $this->html('grid.emptyBlock', 'COM_PP_USER_EMPTY_INVOICES', 7); ?>
			<?php } ?>
		</tbody>
	</table>
</div>