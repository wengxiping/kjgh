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
		<li class="active"><a href="#tab1" data-toggle="tab"><?php echo JText::_( 'COM_ZHBAIDUMAP_MAPTYPE_DETAIL' ); ?></a></li>
		<li><a href="#tab2" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAPTYPE_DETAIL_PROJECTION_GLOBAL_LABEL'); ?></a></li>
		<li><a href="#tab3" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAPTYPE_DETAIL_PROJECTION_DEFINITION_LABEL'); ?></a></li>
		<li><a href="#tab4" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAPTYPE_DETAIL_PROJECTION_LLP_LABEL'); ?></a></li>
		<li><a href="#tab5" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAPTYPE_DETAIL_PROJECTION_PLL_LABEL'); ?></a></li>
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
					?>
				</div>
				<?php endforeach; ?>

			
		</fieldset>
	</div>
	<div class="tab-pane" id="tab2">

		<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('projectionglobal') as $field): ?>
				<div class="control-group">
					<?php 
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
					?>
				</div>
				<?php endforeach; ?>

			
		</fieldset>
	</div>
	<div class="tab-pane" id="tab3">
		
		<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('projectiondefinition') as $field): ?>
				<div class="control-group">
					<?php 
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
					?>
				</div>
				<?php endforeach; ?>

			
		</fieldset>
	</div>
	<div class="tab-pane" id="tab4">
		
		<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('projectionllp') as $field): ?>
				<div class="control-group">
					<?php 
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
					?>
				</div>
				<?php endforeach; ?>

			
		</fieldset>
	</div>
	<div class="tab-pane" id="tab5">
		
		<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('projectionpll') as $field): ?>
				<div class="control-group">
					<?php 
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
					?>
				</div>
				<?php endforeach; ?>

			
		</fieldset>
	</div>

	<?php echo $this->loadTemplate('params'); ?>

</div>


<div class="row-fluid">
	<input type="hidden" name="task" value="maptype.edit" />
	<?php echo JHtml::_('form.token'); ?>
</div>


</div>

</form>


