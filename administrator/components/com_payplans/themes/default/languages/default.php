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
<form id="adminForm" name="adminForm" action="index.php" method="post" data-table-grid>
	<div class="panel-table">
		<div class="alert alert-warning">
			<?php echo JText::_('We could always use some help for translating language files.'); ?>
			<a href="https://stackideas.com/translators" target="_blank" class="btn btn-pp-primary-o t-lg-ml--xl">
				<i class="fa fa-external-link-alt"></i>&nbsp; <?php echo JText::_('Be a Translator');?>
			</a>
		</div>

		<table class="app-table table table-striped table-eb table-hover" data-mailer-list>
			<thead>
				<tr>
					<th width="1%">
						<?php echo $this->html('grid.checkall'); ?>
					</th>
					<th>
						<?php echo JText::_('COM_PP_TABLE_COLUMN_TITLE'); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_LOCALE'); ?>
					</th>
					<th width="15%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_STATE'); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PROGRESS'); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_LAST_UPDATED'); ?>
					</th>
					<th width="1%" class="center">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($languages) { ?>
					<?php $i = 0; ?>
					<?php foreach ($languages as $language) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i, $language->id); ?>
						</td>
						<td>
							<?php echo $language->title;?>
						</td>
						<td class="center">
							<?php echo $language->locale;?>
						</td>
						<td class="center">
							<?php if ($language->isInstalled()) { ?>
							<b class="text-success">
								<?php echo JText::_('COM_PP_INSTALLED'); ?>
							</b>
							<?php } ?>

							<?php if ($language->requiresUpdating()) { ?>
							<b class="text-danger">
								<?php echo JText::_('COM_PP_REQUIRES_UPDATING'); ?>
							</b>
							<?php } ?>

							<?php if (!$language->isInstalled()){ ?>
							<span class="">
								<?php echo JText::_('COM_PP_NOT_INSTALLED'); ?>
							</span>
							<?php } ?>
						</td>
						<td class="center">
							<?php echo !$language->progress ? 0 : $language->progress;?> %
						</td>
						<td class="center">
							<?php echo $language->updated; ?>
						</td>
						<td class="center">
							<?php echo $language->id; ?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="8">
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo $this->html('form.action'); ?>
</form>