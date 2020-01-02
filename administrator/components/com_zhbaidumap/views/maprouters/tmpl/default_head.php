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
	<th width="20" class="hidden-phone">
		<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
	</th>			
	<th class="title">
		<?php echo JHtml::_('grid.sort', 'COM_ZHBAIDUMAP_MAPROUTER_HEADING_TITLE', 'h.title', $listDirn, $listOrder); ?>
	</th>
	<th>
		<?php echo JText::_('COM_ZHBAIDUMAP_MAPROUTER_HEADING_MAPTITLE'); ?>
	</th>
	<th width="5" class="nowrap center">
		<?php echo JHtml::_('grid.sort', 'COM_ZHBAIDUMAP_MAPROUTER_HEADING_PUBLISHED', 'h.published', $listDirn, $listOrder); ?>
	</th>
	<th>
		<?php echo JText::_('COM_ZHBAIDUMAP_MAPROUTER_HEADING_CATEGORY'); ?>
	</th>
</tr>


