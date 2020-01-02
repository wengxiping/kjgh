<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
				<th width="10%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_NAME'); ?>
				</th>
				<th class="center">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_USERNAME');?>
				</th>
				<th class="center" width="20%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_EMAIL'); ?>
				</th>
				<th class="center">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_INVOICE'); ?>
				</th>
				<th class="center" width="20%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_CREATED'); ?>
				</th>				
				<th class="center" width="1%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php if ($consumersData) { ?>
				<?php foreach ($consumersData as $data) {

					$user = PP::user($data->user_id);
					$invoice = PP::invoice($data->invoice_id);
				 ?>
				<tr>
					<td>
						<a href="index.php?option=com_payplans&view=user&layout=form&id=<?php echo $user->getId();?>">
							<?php echo $user->getName(); ?>
						</a>
					</td>

					<td class="center">
						<?php echo $user->getUsername(); ?>
					</td>

					<td class="center">
						<?php echo $user->getEmail(); ?>
					</td>

					<td class="center">
						<a href="index.php?option=com_payplans&view=invoice&layout=form&id=<?php echo $data->invoice_id; ?>">
							<?php echo $data->invoice_id; ?>
						</a>
					</td>

					<td class="center">
						<?php echo PP::date($data->created_date)->format(JText::_('DATE_FORMAT_LC2')); ?>
					</td>

					<td class="center">
						<?php echo $data->modifier_id; ?>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>

			<?php if (!$consumersData) { ?>
				<?php echo $this->html('grid.emptyBlock', 'COM_PP_DISCOUNT_CONSUMED_EMPTY', 6); ?>
			<?php } ?>
		</tbody>
	</table>
</div>