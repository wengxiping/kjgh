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

	<div class="panel-table">
		<table class="app-table table table-eb">
			<thead>
				<tr>

					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_USER'); ?>
					</th>

					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
					</th>

					<th width="10%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PRICE'); ?>
					</th>

					<th width="15%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_CONDITION'); ?>
					</th>

					<th width="10%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PURCHASE'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STATUS'); ?>
					</th>

					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($stats) { ?>
					<?php $i = 0; ?>
					<?php foreach ($stats as $stat) { ?>
					<tr>

						<td>
							<?php echo $stat->getUserName(); ?>
						</td>

						<td>
							<?php echo $stat->title; ?>
						</td>

						<td>
							<?php echo $stat->getPrice(); ?>
						</td>

						<td>
							<?php echo $stat->getCondition(true); ?>
						</td>

						<td>
							<?php echo PP::date($stat->getPurchaseDate())->toLapsed(); ?>
						</td>

						<td class="center">
							<select data-status-list data-id="<?php echo $stat->planaddons_stats_id; ?>">
						<?php
							$statusList = $stat->getStatusList();
							foreach ($statusList as $key => $text) {
								$checked = ($stat->status == $key) ? ' selected="selected"' : '';
						?>
								<option value="<?php echo $key; ?>"<?php echo $checked; ?>><?php echo $text; ?></option>
						<?php
							} // end foreach
						?>
							</select>
						</td>

						<td class="center">
							<?php echo $stat->planaddons_stats_id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } ?>


				<?php if (!$stats) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_ADDONS_STAT_BLANK', 7, true); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 7); ?>

		</table>
	</div>

	<?php echo $this->html('form.action', 'addons'); ?>

</form>
