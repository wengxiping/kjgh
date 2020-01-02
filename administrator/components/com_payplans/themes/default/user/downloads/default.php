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
	<div class="app-filter-bar">

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $states->limit); ?>
			</div>
		</div>
	</div>
		

	<div class="panel-table">
		<table class="app-table table table-striped">
			<thead>
				<tr>
				   <th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					
					<th>
						<?php echo $this->html('grid.sort', 'user_id', 'COM_PP_TABLE_COLUMN_USER', $states); ?>
					</th>
					
					<th width="15%" class="t-text--center">
						&nbsp;
					</th>

					<th width="15%" class="t-text--center">
						<?php echo $this->html('grid.sort', 'status', 'COM_PP_TABLE_COLUMN_STATUS', $states); ?>
					</th>

					<th width="15%" class="t-text--center">
						<?php echo $this->html('grid.sort', 'created', 'COM_PP_TABLE_COLUMN_CREATED', $states); ?>
					</th>

					<th width="5%" class="t-text--center">
						<?php echo $this->html('grid.sort', 'id', 'COM_PP_TABLE_COLUMN_ID', $states); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($requests) { ?>
					<?php $i = 0; ?>
					<?php foreach ($requests as $request) { ?>
					<tr>
						<th class="center">
							<?php echo $this->html('grid.id', $i, $request->download_id); ?>
						</th>

						<td>
							<?php echo $request->user->getName();?>
						</td>

						<td class="t-text--center">
							<?php if ($request->state != PP_DOWNLOAD_REQ_READY) { ?>
								&mdash;
							<?php } else { ?>
								<a href="index.php?option=com_payplans&view=user&layout=download&id=<?php echo $request->user->getId();?>" target="_blank"><?php echo JText::_('COM_PP_DOWNLOAD'); ?></a>
							<?php } ?>
						</td>

						<td class="t-text--center">
							<?php if ($request->state == PP_DOWNLOAD_REQ_READY) { ?>
								<?php echo JText::_('COM_PAYPLANS_DOWNLOAD_STATE_READY'); ?>
							<?php } else { ?>
								<?php echo JText::_('COM_PAYPLANS_DOWNLOAD_STATE_PROCESSING'); ?>
							<?php } ?>
						</td>

						<td class="t-text--center">
							<?php echo PP::date($request->created)->format(JText::_('DATE_FORMAT_LC1')); ?>
						</td>
						
						<td class="t-text--center">
							<?php echo $request->download_id;?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>

				<?php if (!$requests) { ?>
					<?php echo $this->html('grid.emptyBlock', 'No download requests initiated yet', 6); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 6); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'subscription'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
</form>
 
