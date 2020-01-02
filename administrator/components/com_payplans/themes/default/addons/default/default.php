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

					<th width="15%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_CONDITION'); ?>
					</th>

					<th width="10%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PRICE'); ?>
					</th>

					<th width="5%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_USAGE'); ?>
					</th>

					<th width="15%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STARTDATE'); ?>
					</th>

					<th width="15%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ENDDATE'); ?>
					</th>

					<th width="10%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_AVAILABILITY'); ?>
					</th>

					<th width="5%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STATUS'); ?>
					</th>

					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($addons) { ?>
					<?php $i = 0; ?>
					<?php foreach ($addons as $addon) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i++, $addon->planaddons_id); ?>
						</td>

						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=addons&layout=form&id=' . $addon->planaddons_id);?>"><?php echo $addon->title;?></a>
						</td>

						<td>
							<?php echo $addon->getCondition(true); ?>
						</td>

						<td>
							<?php echo $addon->getPrice(); ?>
						</td>

						<td>
							<?php echo $addon->usage; ?>
							<?php if ($addon->usage) { ?>
								(<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=addons&layout=stats&id=' . $addon->planaddons_id);?>">view</a>)
							<?php } ?>
						</td>

						<td>
							<?php echo ($addon->getStartDate()) ? PP::date($addon->getStartDate())->format(JText::_('DATE_FORMAT_LC1')) : JText::_('COM_PP_NEVER'); ?>
						</td>

						<td>
							<?php echo ($addon->getEndDate()) ? PP::date($addon->getEndDate())->format(JText::_('DATE_FORMAT_LC1')) : JText::_('COM_PP_NEVER'); ?>
						</td>

						<td>
							<?php echo $addon->getAvailability(true); ?>
						</td>

						<td class="center">
							<?php echo $this->html('grid.published', $addon, 'addons', 'published');?>
						</td>

						<td class="center">
							<?php echo $addon->planaddons_id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } ?>


				<?php if (!$addons) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_ADDONS_BLANK', 10, true); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 10); ?>

		</table>
	</div>

	<?php echo $this->html('form.action', 'addons'); ?>

</form>
