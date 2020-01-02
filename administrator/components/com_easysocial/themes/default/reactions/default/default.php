<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form name="adminForm" id="adminForm" method="post" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div class="panel-table">
		<table class="app-table table" data-stream-list>
			<thead>
				<tr>
				<th width="1%">
					<input type="checkbox" name="toggle" class="checkAll" data-table-grid-checkall />
				</th>
				<th>
					<?php echo JText::_('COM_ES_TABLE_COLUMN_USER'); ?>
				</th>
				<th width="10%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_REACTION'); ?>
				</th>
				<th width="15%" class="center">
					<?php echo JText::_('COM_EASYSOCIAL_STREAM_TITLE_CONTENT'); ?>
				</th>
				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
				</th>
				<th width="5%" class="center">
					<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
				</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($reactions) { ?>
					<?php $i = 0; ?>
					<?php foreach ($reactions as $reaction) { ?>
					<tr data-id="<?php echo $reaction->id;?>">
						<td class="center">
							<?php echo $this->html('grid.id', $i, $reaction->id); ?>
						</td>
						<td>
							<?php echo ES::user($reaction->created_by)->getName(); ?>
						</td>
						<td class="center">
							<div class="es-icon-reaction es-icon-reaction--sm es-icon-reaction--<?php echo $reaction->reaction;?>"></div>	
						</td>
						<td class="center">
							<?php if ($reaction->permalink) { ?>
								<a href="<?php echo $reaction->permalink;?>" target="_blank"><?php echo JText::_('COM_ES_VIEW_ITEM');?></a>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						<td class="center">
							<?php echo ES::date($reaction->created)->toSql();?>
						</td>
						<td class="center">
							<?php echo $reaction->id; ?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>

				<?php } else { ?>
				<tr class="is-empty">
					<td colspan="8" class="empty">
						<?php echo JText::_( 'COM_EASYSOCIAL_STREAM_NO_ITEM_FOUND' ); ?>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8">
						<div class="footer-pagination">
						<?php echo $pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />

	<?php echo $this->html('form.action', 'reactions'); ?>
</form>
