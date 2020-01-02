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

<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
            
<table>

<tr>
	<td>
		<?php echo JText::_('COM_ZHBAIDUMAP_ABOUT_AUTHOR'); ?>
	</td>
	<td>Dmitry Zhuk</td>			
</tr>
<tr>
	<td>
		<?php echo JText::_('COM_ZHBAIDUMAP_ABOUT_SITE'); ?>
	</td>
	<td><a href="http://zhuk.cc" target="_blank">zhuk.cc</a></td>			
</tr>
<tr>
	<td>
		<?php echo JText::_('COM_ZHBAIDUMAP_ABOUT_LICENSE'); ?>
	</td>
	<td><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU/GPLv2 or later</td>			
</tr>
</table>
<br />
<br />
<h2>
<?php echo JText::_('COM_ZHBAIDUMAP_THANKS'); ?>
</h2>
<table class="adminlist">
	<thead><?php echo $this->loadTemplate('head');?></thead>
	<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
	<tbody><?php echo $this->loadTemplate('body');?></tbody>
</table>

</div>