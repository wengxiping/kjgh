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

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

?>
<tr>
	<th width="1%" class="nowrap center hidden-phone">
		<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
	</th>
	<th width="10" class="nowrap center hidden-phone">
		<?php echo JHtml::_('grid.sort', 'COM_ZHBAIDUMAP_MAPMARKER_HEADING_ID', 'h.id', $listDirn, $listOrder); ?>
	</th>
	<th width="20" class="hidden-phone">
		<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
	</th>			
	<th class="title">
		<?php echo JHtml::_('grid.sort', 'COM_ZHBAIDUMAP_MAPMARKER_HEADING_TITLE', 'h.title', $listDirn, $listOrder); ?>
	</th>
	<th>
		<?php echo JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_MAPTITLE'); ?>
	</th>
	<th width="5">
		<?php echo JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_ICONTYPE'); ?>
	</th>
	<th width="5" class="nowrap center">
		<?php echo JHtml::_('grid.sort', 'COM_ZHBAIDUMAP_MAPMARKER_HEADING_PUBLISHED', 'h.published', $listDirn, $listOrder); ?>
	</th>
	<th>
		<?php echo JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_MARKERGROUP'); ?>
	</th>
	<th>
		<?php echo JHtml::_('grid.sort', 'COM_ZHBAIDUMAP_MAPMARKER_HEADING_CATEGORY', 'category_title', $listDirn, $listOrder); ?>
	</th>
	<th width="10%" class="nowrap hidden-phone">
		<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'h.access', $listDirn, $listOrder); ?>
	</th>	
	<th>
		<?php echo JText::_('COM_ZHBAIDUMAP_MAPMARKER_HEADING_USER'); ?>
	</th>
	<th width="5">
		<?php echo JHtml::_('grid.sort', 'COM_ZHBAIDUMAP_MAPMARKER_HEADING_USERORDER', 'h.userorder', $listDirn, $listOrder); ?>
	</th>    
</tr>


