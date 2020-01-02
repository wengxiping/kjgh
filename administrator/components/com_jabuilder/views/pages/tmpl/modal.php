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

$app = JFactory::getApplication();

if ($app->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('formbehavior.chosen', 'select');

$function  = $app->input->getCmd('function', 'jSelectArticle');

$items = $this->items;
?>

<form action="<?php echo JRoute::_('index.php?option=com_jabuilder&view=pages&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>" 
	  method="post" name="adminForm" id="adminForm">
	
	<div class="clearfix"></div>

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
	
	<table class="table table-striped" id="">
		<thead>
				<tr>
					<th width="5%" class="">
						Status
					</th>
					<th width="50%" class="">
						Title
					</th>
					<th width="40%">
						Alias
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
			
		<?php foreach($items as $i => $item ):	
			?>
			
			<tr class="sortable-group-id">
				<td>
					<a class="btn btn-micro disabled hasTooltip" href="javascript:void(0);" title="">
						<span class="icon-<?php echo $item->state ? 'publish':'unpublish' ?>"></span>
					</a>
				
				</td>
				<td>
					<a href="javascript:void(0);" title="Edit page"
					   accesskey=""onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>', '', null, '', '', null);" >								
						<?php echo $item->title; ?>
					</a>
					<span class="small">( alias: <?php echo $item->alias ?>)</span>
				</td>
				<td><?php echo $item->alias ?> </td>
				<td><?php echo $item->id ?></td>
			</tr>
			
		<?php endforeach; ?>
			
		</tbody>
		
    </table>
	
	<?php endif; ?>
			
	<input type="hidden" name="task" value=""/>	
	<input type="hidden" name="boxchecked" value="0"/>	
	<?php echo JHtml::_('form.token'); ?>
</form>
