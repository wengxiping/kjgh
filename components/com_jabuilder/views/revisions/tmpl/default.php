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

$input = JFactory::getApplication()->input;

$page_id = $input->get->get('page_id');
?>
<table class="table table-striped" id="">
	<thead>
			<tr>
				<th width="50%" class="">
					Title
				</th>
				<th width="10%" class="">
					Type
				</th>
				<th width="40%">
					Time
				</th>
				<th width="2%" class="">
					ID
				</th>
			</tr>
	</thead>

	<tfoot>
	<tr>
		<td colspan="5">
		</td>
	</tr>
	</tfoot>
		
	<tbody>
		<?php if (!empty($items)): ?>
		
		<?php foreach($items as $i => $item ):	?>

			<tr class="sortable-group-id">
				<td>
					<?php echo $item->title; ?>
				</td>
				<td><?php echo $item->type ?> </td>
				<td>
					<a href="index.php?option=com_jabuilder&task=edit.revert&revision_id=<?php echo $item->id ?>&id=<?php echo $page_id ?>" title="Edit page" >								
						<?php echo $item->modified_date; ?>
					</a>
				</td>
				<td><?php echo $item->id ?></td>
			</tr>

		<?php endforeach; ?>
			
		<?php endif; ?>

	</tbody>
		
    </table>