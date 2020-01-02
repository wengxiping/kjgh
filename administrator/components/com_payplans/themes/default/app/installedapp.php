<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();?>
<form action="<?php echo XiRoute::_('index.php?option=com_payplans&view=app', false); ?>" method="post" name="adminForm" id="adminForm">
	<?php echo $this->loadTemplate('filter'); ?>
	
	<table class="table table-striped">
		<thead>
		<!-- ROW HEADER START -->
			<tr>
				
				<th class="default-grid-chkbox hidden-phone">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>
				<th class="hidden-phone"><?php echo PayplansHtml::_('grid.sort', "COM_PAYPLANS_APP_GRID_APP_ID", 'app_id', $filter_order_Dir, $filter_order);?></th>
				<th><?php echo PayplansHtml::_('grid.sort', "COM_PAYPLANS_APP_GRID_APP_TITLE", 'title', $filter_order_Dir, $filter_order);?></th>
				<th><?php echo PayplansHtml::_('grid.sort', "COM_PAYPLANS_APP_GRID_APP_TYPE", 'type', $filter_order_Dir, $filter_order);?></th>
				<th><?php echo PayplansHtml::_('grid.sort', "COM_PAYPLANS_APP_GRID_APP_PUBLISHED", 'published', $filter_order_Dir, $filter_order);?></th>
				<th class="hidden-phone"><?php echo PayplansHtml::_('grid.sort', "COM_PAYPLANS_APP_GRID_APP_ORDERING", 'ordering', $filter_order_Dir, $filter_order);?></th>
			</tr>
		<!-- ROW HEADER END -->
		</thead>
		
		
		<!-- TABLE BODY START -->
			<?php $count= $limitstart;
				$cbCount = 0;
				foreach ($records as $record):?>
					<tr class="<?php echo "row".$count%2; ?>">
						<?php if(isset($app_names[$record->type])) :?>	 
							<th class="default-grid-chkbox hidden-phone">
								<?php echo PayplansHtml::_('grid.id', $cbCount, $record->{$record_key} ); ?>
							</th>
							<td class="hidden-phone"><?php echo $record->app_id;?></td>
							<td style="width:40%;">
								<div><?php echo PayplansHtml::link($uri.'&task=edit&id='.$record->{$record_key}, $record->title);?></div>
								<div class="hidden-phone"><?php echo $record->description;?></div>
							</td>   					
							<td><?php echo JText::_($app_names[$record->type]);?></td>
							<td><?php echo PayplansHtml::_("boolean.grid", $record, 'published', $cbCount);?></td>
							<td class="hidden-phone">
								<span><?php echo $pagination->orderUpIcon( $count , true, 'orderup', 'Move Up'); ?></span>
								<span><?php echo $pagination->orderDownIcon( $count , count($records), true , 'orderdown', 'Move Down', true ); ?></span>
							</td>
						<?php else : ?>
							<th class="default-grid-chkbox hidden-phone">
							</th>
							<td class="hidden-phone"><?php echo $record->app_id;?></td>
							<td style="width:40%;">
								<div><?php echo $record->title;?></div>
								<div class="hidden-phone"><?php echo $record->description;?></div>
							</td>
							<td colspan="3" class="hidden-phone"><?php echo sprintf(JText::_('COM_PAYPLANS_APP_GRID_APP_PLUGIN_DISABLE'), $record->type);?></td>
						<?php endif;?>
						
					</tr>
			<?php $count++;?>
			<?php $cbCount++;?>
			<?php endforeach; ?>
		
		<!-- TABLE BODY END -->

		<tfoot>
		<!-- TABLE FOOTER START -->
			<tr>
				<td colspan="5">
					<?php echo $pagination->getListFooter(); ?>
				</td>
				<td >
					<?php echo $pagination->getLimitBox();?>	
				</td>
			</tr>
		<!-- TABLE FOOTER END -->
		</tfoot>
	</table>

	<input type="hidden" name="filter_order" value="<?php echo $filter_order;?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $filter_order_Dir;?>" />
	<input type="hidden" name="active_tab" value="manageapps" />
		<input type="hidden" name="active_tab_content" value="ppmanage" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>