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
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search', $states->search); ?>
		</div>
		
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'published', $states->published); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.lists', $paymentTypes, 'type', $states->type, 'COM_PP_FILTER_SELECT_TYPE'); ?>
			</div>
		</div>
		
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $states->limit); ?>
			</div>
		</div>
	</div>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
				   <th width="1%" class="t-text--center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					
					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
					</th>
					
					<th width="15%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STATE'); ?>
					</th>

					<th width="20%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TYPE'); ?>
					</th>

					<th width="5%" class="t-text--center">
						<?php echo $this->html('grid.sort', 'app_id', 'COM_PP_TABLE_COLUMN_ID', $states); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($apps) { ?>
					<?php $i = 0; ?>
					<?php foreach ($apps as $app) { ?>
					<tr>
						<td class="t-text--center">
							<?php echo $this->html('grid.id', $i++, $app->app_id); ?>
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=gateways&layout=form&id=' . $app->app_id);?>"><?php echo JText::_($app->title);?></a>
						</td>
						<td class="t-text--center">
							<?php echo $this->html('grid.published', $app, 'app', 'published'); ?>
						</td>
						<td class="t-text--center"> 
							<?php echo $app->type;?>
						</td>
						<td class="t-text--center">
							<?php echo $app->app_id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } else { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_GATEWAYS_EMPTY', 5); ?>
				<?php } ?>
			</tbody>

			<tfoot>
				<?php echo $this->html('grid.pagination', $pagination, 5); ?>
			</tfoot>
		</table>
	</div>

	<?php echo $this->html('form.action', 'subscription'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
	<?php echo $this->html('form.returnUrl'); ?>
</form>
 
