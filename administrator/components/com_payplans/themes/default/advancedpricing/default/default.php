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
					<th width="1%" class="t-text--center">
						<?php echo $this->html('grid.checkall'); ?>
					</th>

					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
					</th>

					<th width="20%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PLANS'); ?>
					</th>

					<th width="30%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PRICE_SET'); ?>
					</th>

					<th width="20%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STATUS'); ?>
					</th>

					<th width="1%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($items) { ?>
					<?php $i = 0; ?>
					<?php foreach ($items as $item) { ?>
					<tr>
						<td class="t-text--center">
							<?php echo $this->html('grid.id', $i++, $item->advancedpricing_id); ?>
						</td>

						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=advancedpricing&layout=form&id=' . $item->advancedpricing_id);?>"
								<?php if ($item->description) { ?>
								data-pp-provide="tooltip"
								data-title="<?php echo $item->description;?>"
								<?php } ?>
								><?php echo $item->title;?></a>
						</td>

						

						<td class="t-text--center">
							<?php if ($item->plans) { ?>
								<?php foreach ($item->plans as $plan) { ?>
									<span class="o-label o-label--primary"><?php echo $plan->getTitle();?></span>
								<?php } ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>

						<td class="t-text--center">
							<?php if ($item->priceset) { ?>
								<?php foreach ($item->priceset as $set) { ?>
									<div><b><?php echo $this->html('html.amount', $set['price'], $this->config->get('currency')); ?> - <?php echo PP::string()->formatTimer($set['duration']); ?></b></div>
								<?php } ?>
							<?php } ?>
						</td>
						
						<td class="t-text--center">
							<?php echo $this->html('grid.published', $item, 'advancedpricing', 'published');?>
						</td>

						<td class="t-text--center">
							<?php echo $item->advancedpricing_id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } ?>


				<?php if (!$items) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_ASSIGNS_BLANK', 6, true); ?>
				<?php } ?>
			</tbody>
		</table>
	</div>

	<?php echo $this->html('form.action', 'advancedpricing'); ?>
</form>
