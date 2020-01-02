<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	15 November 2015
 * @file name	:	layouts/invoice/withdraw.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Invoice deposit layout (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 $items = $displayData;
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
			<td><?php echo JText::_('COM_JBLANCE_WITHDRAW_FUNDS'); ?></td>
			<td><?php echo JblanceHelper::getPaymentStatus($items->approved); ?></td>
			<td style="text-align:right;"><?php echo JblanceHelper::formatCurrency($items->amount); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3" align="right"><?php echo JText::_('COM_JBLANCE_WITHDRAWAL_FEE'); ?>:</td>
			<td colspan="1" align="right">
				<?php 
				$fee = ($items->withdrawFeePerc / 100) * $items->amount + $items->withdrawFeeFixed;
				echo '-'.JblanceHelper::formatCurrency($fee); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
		<tr>
			<td colspan="3" align="right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
			<td colspan="1" align="right">
				<?php echo '<b>'.JblanceHelper::formatCurrency($items->finalAmount, true, true).'</b>'; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
	</tfoot>
</table>
