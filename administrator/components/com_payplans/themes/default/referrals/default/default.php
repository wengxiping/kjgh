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

					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
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
				<?php if ($apps) { ?>
					<?php $i = 0; ?>
					<?php foreach ($apps as $app) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i++, $app->getId()); ?>
						</td>

						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=referrals&layout=form&id=' . $app->getId());?>"><?php echo $app->title;?></a>
						</td>

						<td class="center">
							<?php echo $this->html('grid.published', $app, 'app', 'published'); ?>
						</td>

						<td class="center">
							<?php echo $app->getId();?>
						</td>
					</tr>
					<?php } ?>
				<?php } ?>


				<?php if (!$apps) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_REFERRALS_BLANK', 4); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 7); ?>

		</table>
	</div>

	<?php echo $this->html('form.action', 'referrals'); ?>
	<?php echo $this->html('form.returnUrl'); ?>
</form>
