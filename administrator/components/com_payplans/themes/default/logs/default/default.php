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
<?php if ($form) { ?>
<form method="post" name="adminForm" id="adminForm" data-table-grid>
<?php } ?>

	<?php if ($renderFilterBar) { ?>
		<?php echo $this->output('admin/logs/default/filter', array('states' => $states)); ?>
	<?php } ?>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<?php if ($editable) { ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkall'); ?>
					</th>
					<?php } ?>

					<th>
						<?php if ($sortable) { ?>
							<?php echo $this->html('grid.sort', 'message', 'COM_PP_TABLE_COLUMN_MESSAGE', $states); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_PP_TABLE_COLUMN_MESSAGE'); ?>
						<?php } ?>
					</th>

					<th width="5%" class="center">
						&nbsp;
					</th>

					<th class="center" width="10%">
						<?php if ($sortable) { ?>
							<?php echo $this->html('grid.sort', 'owner_id', 'COM_PP_TABLE_COLUMN_OWNER', $states); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_PP_TABLE_COLUMN_OWNER'); ?>
						<?php } ?>
					</th>

					<th class="center" width="10%">
						<?php if ($sortable) { ?>
							<?php echo $this->html('grid.sort', 'user_ip', 'COM_PP_TABLE_COLUMN_LOG_LEVEL', $states); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_PP_TABLE_COLUMN_LOG_LEVEL'); ?>
						<?php } ?>
					</th>

					<th class="center" width="10%">
						<?php if ($sortable) { ?>
							<?php echo $this->html('grid.sort', 'user_ip', 'COM_PP_TABLE_COLUMN_IP', $states); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_PP_TABLE_COLUMN_IP'); ?>
						<?php } ?>
					</th>

					<th class="center" width="15%">
						<?php if ($sortable) { ?>
							<?php echo $this->html('grid.sort', 'created_date', 'COM_PP_TABLE_COLUMN_CREATED', $states); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_PP_TABLE_COLUMN_CREATED'); ?>
						<?php } ?>
					</th>

					<th class="center" width="5%">
						<?php if ($sortable) { ?>
							<?php echo $this->html('grid.sort', 'log_id', 'COM_PP_TABLE_COLUMN_ID', $states);?>
						<?php } else { ?>
							<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
						<?php } ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($logs) { ?>
					<?php $i = 0; ?>
					<?php foreach ($logs as $log) { ?>
					<tr>
						<?php if ($editable) { ?>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $log->log_id); ?>
						</th>
						<?php } ?>

						<td>
							<?php echo JText::_($log->message); ?>
						</td>

						<td class="center">
							<a href="javascript:void(0);" data-pp-log-details data-id="<?php echo $log->log_id;?>" class="btn btn-xs btn-pp-primary-o t-lg-pull-right">
								<?php echo JText::_('COM_PP_DETAILS');?>
							</a>
						</td>

						<td class="center">
							<?php if ($log->owner_id > 0) { ?>
								<?php echo PP::log()->getOwner($log->owner_id)->getUsername(); ?>
							<?php } else { ?>
								<?php echo PP::log()->getOwner($log->owner_id)->username; ?>
							<?php } ?>
						</td>

						<td class="center">
							<?php echo PP::logger()->getLevelText($log->level);?>
						</td>

						<td class="center">
							<?php echo $log->user_ip ;?>
						</td>

						<td class="center">
							<?php echo PP::date($log->created_date)->format(JText::_('DATE_FORMAT_LC2')); ?>
						</td>

						<td class="center">
							<?php echo $log->log_id;?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>

				<?php if (!$logs) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_LOGS_EMPTY', 11); ?>
				<?php } ?>
			</tbody>

			<?php if ($pagination && ($pagination instanceof PPPagination)) { ?>
				<?php echo $this->html('grid.pagination', $pagination, 11); ?>
			<?php } ?>
		</table>
	</div>

<?php if ($form) { ?>
	<?php echo $this->html('form.action', 'log'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
</form>
<?php } ?>