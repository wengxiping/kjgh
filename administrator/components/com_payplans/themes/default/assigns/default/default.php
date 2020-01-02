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

					<th width="10%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TYPE'); ?>
					</th>

					<th width="40%" class="t-text--center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PLANS'); ?>
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
				<?php if ($assigns) { ?>
					<?php $i = 0; ?>
					<?php foreach ($assigns as $assign) { ?>
					<tr>
						<td class="t-text--center">
							<?php echo $this->html('grid.id', $i++, $assign->app_id); ?>
						</td>

						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_payplans&view=assigns&layout=form&id=' . $assign->app_id);?>"
								<?php if ($assign->description) { ?>
								data-pp-provide="tooltip"
								data-title="<?php echo $assign->description;?>"
								<?php } ?>
								><?php echo $assign->title;?></a>
						</td>

						<td class="t-text--center">
							<?php if ($assign->source == 'joomla_usertype') { ?>
								<?php echo JText::_('Joomla Usergroup'); ?>
							<?php } ?>

							<?php if ($assign->source == 'easysocial_profiletype') { ?>
								<?php echo JText::_('EasySocial Profile'); ?>
							<?php } ?>

							<?php if ($assign->source == 'jomsocial_profiletype') { ?>
								<?php echo JText::_('JomSocial Profile'); ?>
							<?php } ?>
						</td>

						<td class="t-text--center">
							<?php if ($assign->plans) { ?>
								<?php foreach ($assign->plans as $plan) { ?>
									<span class="o-label o-label--primary"><?php echo $plan->getTitle();?></span>
								<?php } ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						
						<td class="t-text--center">
							<?php echo $this->html('grid.published', $assign, 'assigns', 'published');?>
						</td>

						<td class="t-text--center">
							<?php echo $assign->app_id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } ?>


				<?php if (!$assigns) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PP_ASSIGNS_BLANK', 6, true); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, 6); ?>

		</table>
	</div>

	<?php echo $this->html('form.action', 'assigns'); ?>


</form>
