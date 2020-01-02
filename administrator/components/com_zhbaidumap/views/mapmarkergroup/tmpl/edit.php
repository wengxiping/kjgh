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
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

?>
<form action="<?php echo JRoute::_('index.php?option=com_zhbaidumap&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="span12 form-horizontal">

<div class="tabbable">
    <ul class="nav nav-pills">
		<li class="active"><a href="#tab1" data-toggle="tab"><?php echo JText::_( 'COM_ZHBAIDUMAP_MAPMARKERGROUP_DETAIL' ); ?></a></li>
		<?php
		$fieldSets = $this->form->getFieldsets('params');
		foreach ($fieldSets as $name => $fieldSet) :
		?>
		<li><a href="#params-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
		<?php endforeach; ?>
    </ul>
</div>
<div class="tab-content">
	<div class="tab-pane active" id="tab1">
		<fieldset class="adminform">
				<?php foreach($this->form->getFieldset('details') as $field): ?>
				<div class="control-group">
					<?php 
						if ($field->id == 'jform_icontype')
						{
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 

								$imgpath = JURI::root() .'administrator/components/com_zhbaidumap/assets/icons/';
								$utilspath = JURI::root() .'administrator/components/com_zhbaidumap/assets/utils/';

								$iconTypeJS = " onchange=\"javascript:
								if (document.forms.adminForm.jform_icontype.options[selectedIndex].value!='') 
								{document.image.src='".$imgpath."' + document.forms.adminForm.jform_icontype.options[selectedIndex].value.replace(/#/g,'%23') + '.png'}
								else 
								{document.image.src=''}\"";


								$scriptPosition = ' name=';

								echo str_replace($scriptPosition, $iconTypeJS.$scriptPosition, $field->input);
								echo '<img name="image" src="'.$imgpath .str_replace("#", "%23", $this->item->icontype).'.png" alt="" />';
								
								echo '<div class="clr"></div>';
								echo '<a class="btn btn-primary" href="http://wiki.zhuk.cc/index.php?title=Zh_BaiduMap_Credits_Icons" target="_blank">'.JText::_( 'COM_ZHBAIDUMAP_MAP_TERMSOFUSE_ICONS' ).' <img src="'.$utilspath.'info.png" alt="'.JText::_( 'COM_ZHBAIDUMAP_MAP_TERMSOFUSE_ICONS' ).'" style="margin: 0;" /></a>';
								echo '<div class="clr"></div>';
								echo '<br />';
							?>
							</div>
							<?php 
						}
						else if ($field->id == 'jform_ordering')
						{
						}                                                
						else
						{
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								echo $field->input;
							?>
							</div>
							<?php 
						}
						?>
				</div>
				<?php endforeach; ?>

			
		</fieldset>
	</div>

	<?php echo $this->loadTemplate('params'); ?>

</div>


<div class="row-fluid">
	<input type="hidden" name="task" value="mapmarkergroup.edit" />
	<?php echo JHtml::_('form.token'); ?>
</div>


</div>

</form>


