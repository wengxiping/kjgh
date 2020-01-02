<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	15 November 2015
 * @file name	:	layouts/invoice/plan.php
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
			<td><?php echo JText::sprintf('COM_JBLANCE_PURCHASE_OF', $items->planname); ?></td>
			<td><?php echo JblanceHelper::getPaymentStatus($items->approved); ?></td>
			<td style="text-align:right;"><?php echo JblanceHelper::formatCurrency($items->price); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<?php 
		if($items->tax_percent > 0){ ?>
		<tr>
			<td colspan="3" align="right"><?php echo $items->taxname.' '.$items->tax_percent.' %'; ?>:</td>
			<td colspan="1" align="right">
				<?php 
				$taxamt = ($items->tax_percent / 100) * $items->price;
				echo JblanceHelper::formatCurrency($taxamt); ?>
			</td>
		</tr>
		<?php 
		}
		else {
			$taxamt = 0;
		}
		?>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
		<tr>
			<td colspan="3" align="right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
			<td colspan="1" align="right">
				<?php 
				$total = $taxamt + $items->price;
				echo '<b>'.JblanceHelper::formatCurrency($total, true, true).'</b>'; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
	</tfoot>
</table>
