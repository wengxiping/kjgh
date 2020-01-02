<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form action="index.php" id="adminForm" method="post" name="adminForm" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left"></div>

		<?php if($this->tmpl != 'component'){ ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
		<?php } ?>
	</div>

	<div id="pendingUsersTable" class="panel-table">
		<table class="app-table table" data-pending-users>
			<thead>
				<tr>
					<th width="5">
						<input type="checkbox" name="toggle" value="" data-table-grid-checkall />
					</th>
					<th style="text-align: left;">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_NAME'); ?>
					</th>
					<th width="15%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_MESSAGE'); ?>
					</th>
					<th width="25%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_IP'); ?>
					</th>
					<th width="1%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if ($users) { ?>
				<?php $i = 0; ?>
				<?php foreach ($users as $user) { ?>
				<tr>
					<td>
						<?php echo $this->html('grid.id', $i++, $user->request->id); ?>
					</td>
					<td align="left">
						<a href="<?php echo $user->getPermalink(true, true); ?>" target="_blank"><?php echo $user->getName(); ?></a>
					</td>
					<td class="center">
						<a href="javascript:void(0);" data-verify-message data-id="<?php echo $user->request->id; ?>"><?php echo JText::_('COM_ES_VIEW_MESSAGE');?></a>
					</td>
					<td class="center">
						<?php echo $user->request->ip;?>
					</td>
					<td class="center">
						<?php echo $user->id;?>
					</td>
				</tr>
				<?php } ?>

			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="5" class="center empty">
						<div>
							<?php echo JText::_('COM_ES_USERS_NO_VERIFICATION_REQUESTS'); ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="5">
						<div class="footer-pagination">
							<?php echo $pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>

		</table>
	</div>

	<?php echo $this->html('form.action', 'users', '', 'users'); ?>
	<?php echo $this->html('form.hidden', 'layout', 'verifications'); ?>
</form>
