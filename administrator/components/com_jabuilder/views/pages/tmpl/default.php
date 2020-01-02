<?php

/**
 * ------------------------------------------------------------------------
 * JA Builder Package
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

$items = $this->items;

$user = JFactory::getUser();

$session = JFactory::getSession();

$session_id = $session->getId();

$userid = $user->id;
?>

<form action="<?php echo JRoute::_('index.php?option=com_jabuilder&view=pages'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
	
	<table class="table table-striped" id="">
		<thead>
				<tr>
					<th width="3%" class="">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="3%" class="">
						Status
					</th>
					<th width="10%">
					</th>
					<th width="40%" class="">
						Title
					</th>
					<th width="2%" class="">
						ID
					</th>
				</tr>
		</thead>

		<tfoot>
		<tr>
			<td colspan="5">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		
		<tbody>
			
		<?php if(!empty($items)): ?>
			
		<?php foreach($items as $i => $item ):	
			$link = JRoute::_('index.php?option=com_jabuilder&task=page.edit&id=' . $item->id);	
			?>
			
			<tr class="sortable-group-id">
				<td><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
				<td>
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'pages.', true, 'cb'); ?>
					
				</td>
				<td style="width: 200px">
					<a class="btn btn-micro btn-warning" href="<?php echo JURI::root().'index.php?option=com_jabuilder&task=login.autologin&user='.$userid.'&session_id='.$session_id.'&id='.$item->id?>" target="_blank" title="Live edit" >
						<span class="icon-share icon-white"></span><b>Live edit</b>
					</a>
				</td>
				<td>
					<a href="<?php echo $link; ?>" title="Edit page">								
						<?php echo $item->title; ?>
					</a>
					<br>
					<span class="small">( alias: <?php echo $item->alias ?>)</span>
				</td>
				<td><?php echo $item->id ?></td>
			</tr>
			
		<?php endforeach; ?>
			
		<?php endif; ?>
		</tbody>
		
    </table>
			
	<input type="hidden" name="task" value=""/>	
	<input type="hidden" name="boxchecked" value="0"/>	
	<?php echo JHtml::_('form.token'); ?>
</form>
