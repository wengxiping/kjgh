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
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkall'); ?>
					</th>

					<th width="25%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
					</th>

					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_DESCRIPTION'); ?>
					</th>

					<th width="20%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STATUS'); ?>
					</th>

					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($limitsubscriptions) { ?>
					<?php $i = 0; ?>
					<?php foreach ($limitsubscriptions as $limitsubscription) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i++, $limitsubscription->app_id); ?>
						</td>

						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=limitsubscription&layout=form&id=' . $limitsubscription->app_id);?>"><?php echo $limitsubscription->title;?></a>
						</td>

						<td>
							<?php if ($limitsubscription->description) { ?>
								<?php echo $limitsubscription->description;?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						
						<td class="center">
							<?php echo $this->html('grid.published', $limitsubscription, 'limitsubscription', 'published');?>
						</td>

						<td class="center">
							<?php echo $limitsubscription->app_id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } ?>


				<?php if (!$limitsubscriptions) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_LIMITSUBSCRIPTION_BLANK', 5, true); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 5); ?>

		</table>
	</div>

	<?php echo $this->html('form.action', 'limitsubscription'); ?>
</form>
