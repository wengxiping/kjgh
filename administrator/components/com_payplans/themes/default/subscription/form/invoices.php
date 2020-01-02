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
					<?php echo JText::_('COM_PP_TABLE_COLUMN_KEY');?>
				</th>
				<th width="15%" class="center">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_STATUS');?>
				</th>
				<th width="15%" class="center">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_SUBTOTAL');?>
				</th>
				<th width="15%" class="center">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_TOTAL');?>
				</th>
				<th width="1%" class="center">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php if ($invoices) { ?>
				<?php foreach ($invoices as $invoice) { ?>
				<tr>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=invoice&layout=form&id=' . $invoice->getId());?>">
							<?php echo PP::encryptor()->encrypt($invoice->getId()); ?>
						</a>
					</td>
					<td class="center">
						<label class="o-label <?php echo $invoice->getStatusLabelClass();?>"><?php echo $invoice->getStatusName();?></label>
					</td>
					<td class="center">
						<?php echo $this->html('html.amount', $invoice->getSubtotal(), $invoice->getCurrency()); ?>
					</td>
					<td class="center">
						<?php echo $this->html('html.amount', $invoice->getTotal(), $invoice->getCurrency()); ?>
					</td>
					<td class="center">
						<?php echo $invoice->getId();?>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>

			<?php if (!$invoices) { ?>
				<?php echo $this->html('grid.emptyBlock', 'COM_PP_INVOICES_EMPTY', 6); ?>
			<?php } ?>
		</tbody>
	</table>
</div>