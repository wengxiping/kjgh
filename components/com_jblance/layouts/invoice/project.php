<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	15 November 2015
 * @file name	:	layouts/invoice/project.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Invoice deposit layout (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 $items   = $displayData['display'];
 $escrows = $displayData['escrow'];
?>

<table style="width: 100%;">
	<thead>
		<tr>
			<th align="left"><?php echo JText::_('COM_JBLANCE_DATE'); ?></th>
			<th align="left"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?></th>
			<th align="left"><?php echo JText::_('COM_JBLANCE_STATUS'); ?></th>
			<th align="right"><?php echo JText::_('COM_JBLANCE_AMOUNT'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo JHtml::_('date', $items->invoiceDate, $items->dformat, true); ?></td>
			<td><?php echo JText::sprintf('COM_JBLANCE_PROJECT_COMMISSION_FOR_PROJECT_NAME', '<b>'.$items->project_title.'</b>'); ?></td>
			<td><?php echo JblanceHelper::getPaymentStatus(1); ?></td>
			<td style="text-align:right;"><?php echo '-'.JblanceHelper::formatCurrency($items->commission_amount); ?></td>
		</tr>
		<?php 
		$totalPaid = 0;
		for($i=0, $n=count($escrows); $i < $n; $i++){
			$escrow = $escrows[$i];
			$totalPaid += $escrow->amount;
		?>
		<tr>
			<td nowrap="nowrap"><?php echo JHtml::_('date', $escrow->date_accept, $items->dformat, true); ?></td>
			<td>
				<?php 
				if($escrow->project_type == 'COM_JBLANCE_FIXED')
					echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); 
				if($escrow->project_type == 'COM_JBLANCE_HOURLY')
					echo JText::sprintf('COM_JBLANCE_PAYMENT_FOR_HOURS', $escrow->pay_for);
				?>
			</td>
			<td><?php echo '-'; ?></td>
			<td style="text-align:right;"><?php echo JblanceHelper::formatCurrency($escrow->amount); ?></td>
		</tr>
		<?php 
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3" align="right"><?php echo JText::_('COM_JBLANCE_SUBTOTAL'); ?>:</td>
			<td colspan="1" align="right">
				<?php 
				$subtotalAmt = -$items->commission_amount + $totalPaid;
				echo JblanceHelper::formatCurrency($subtotalAmt); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
		<tr>
			<td colspan="3" align="right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
			<td colspan="1" align="right">
				<?php 
				$total = -$items->commission_amount + $totalPaid;
				echo '<b>'.JblanceHelper::formatCurrency($total, true, true).'</b>'; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
	</tfoot>
</table>
