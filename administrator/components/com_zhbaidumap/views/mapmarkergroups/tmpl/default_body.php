<?php
/*------------------------------------------------------------------------
# com_zhbaidumap - Zh BaiduMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php 
	$user = JFactory::getUser();
	$userId = $user->id;

	$listOrder	= $this->escape($this->state->get('list.ordering'));
	$listDirn	= $this->escape($this->state->get('list.direction'));
    
	foreach($this->items as $i => $item): 
	$ordering  = ($listOrder == 'ordering');
	$saveOrder	= $listOrder == 'ordering';
    
	$canDo = ZhBaiduMapHelper::getMarkerGroupActions($item->id);
	
	$canEdit    = $canDo->get('core.edit');
	$canEditOwn = $canDo->get('core.edit.own') && 1==2; //$item->createdbyuser == $userId;
	$canChange  = $canDo->get('core.edit.state');
	
?>
	<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">
        <td class="order nowrap center hidden-phone">
		<?php if ($canChange) :
			$disableClassName = '';
			$disabledLabel	  = '';
			if (!$saveOrder) :
				$disabledLabel    = JText::_('JORDERINGDISABLED');
				$disableClassName = 'inactive tip-top';
			endif; ?>
			<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
				<i class="icon-menu"></i>
			</span>
			<input type="text" style="display:none" name="order[]" size="5"
				value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
		<?php else : ?>
			<span class="sortable-handler inactive" >
				<i class="icon-menu"></i>
			</span>
		<?php endif; ?>
		</td>
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<?php if ($canEdit || $canEditOwn) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_zhbaidumap&task=mapmarkergroup.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
					<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
		</td>
		<td align="center">
			<?php echo '<img src="'.JURI::root() .'administrator/components/com_zhbaidumap/assets/icons/'.str_replace("#", "%23", $item->icontype).'.png" alt="" />'; ?>
		</td>
		<td align="center">
			<?php 
				echo JHtml::_('jgrid.published', $item->published, $i, 'mapmarkergroups.', $canChange, 'cb', $item->publish_up, $item->publish_down); 
				//echo '<img src="'.JURI::root() .'administrator/components/com_zhbaidumap/assets/utils/published'.$item->published.'.png" alt="" />'; 
			?>			
		</td>
		<td>
			<?php echo $this->escape($item->category); ?>
		</td>
		<td>
			<?php echo $this->escape($item->userorder); ?>
		</td>        
	</tr>
<?php endforeach; ?>

