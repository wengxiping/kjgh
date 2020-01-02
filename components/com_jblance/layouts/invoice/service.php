<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	15 November 2015
 * @file name	:	layouts/invoice/service.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Invoice layout for service  (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 $items   = $displayData['display'];
 $escrows = $displayData['escrow'];
 
 $sign = ($items->usertype =='freelancer') ? '' : '-';
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
		<?php 
		$totalPaid = 0;
		for($i=0, $n=count($escrows); $i < $n; $i++){
			$escrow = $escrows[$i];
			$totalPaid += $escrow->amount;
		?>
		<tr>
			<td nowrap="nowrap"><?php echo JHtml::_('date', $escrow->date_accept, $items->dformat, true); ?></td>
			<td>
				<?php echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); ?>
			</td>
			<td><?php echo '-'; ?></td>
			<td style="text-align:right;"><?php echo $sign.JblanceHelper::formatCurrency($escrow->amount); ?></td>
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
				$subtotalAmt = $totalPaid;
				echo $sign.JblanceHelper::formatCurrency($subtotalAmt); ?>
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
				$total = $totalPaid;
				echo '<b>'.$sign.JblanceHelper::formatCurrency($total, true, true).'</b>'; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
	</tfoot>
</table>
