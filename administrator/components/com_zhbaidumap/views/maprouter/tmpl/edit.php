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
		<li class="active"><a href="#tab1" data-toggle="tab"><?php echo JText::_( 'COM_ZHBAIDUMAP_MAPROUTER_DETAIL' ); ?></a></li>
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
							if ($field->id == 'jform_mapid')
						{
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								array_unshift($this->mapList, JHTML::_('select.option', '', JText::_( 'COM_ZHBAIDUMAP_MAPROUTER_FILTER_MAP'), 'value', 'text')); 
								echo JHTML::_( 'select.genericlist', $this->mapList, 'jform[mapid]',  'class="inputbox required" size="1"', 'value', 'text', (int)$this->item->mapid, 'jform_mapid');
								//echo $field->label;
								//echo $field->input;
							?>
							</div>
							<?php 
						}
						else if ($field->id == 'jform_descriptionhtml')
						{
							?>
							<div class="control-label">
							<?php 
								echo '<div class="clr"></div>';
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								echo '<div class="clr"></div>';
								echo $field->input;
							?>
							</div>
							<?php 
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
	<input type="hidden" name="task" value="maprouter.edit" />
	<?php echo JHtml::_('form.token'); ?>
</div>


</div>

</form>


