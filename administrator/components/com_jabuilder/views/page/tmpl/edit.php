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
$isOldJoomla = version_compare(JVERSION, '3.4', '<');
if (!$isOldJoomla) {
	JHtml::_('behavior.formvalidator');
}
JHtml::_('formbehavior.chosen', 'select');
$id = JFactory::getApplication()->input->get('id',0);
?>

<form action="<?php echo JRoute::_('index.php?option=com_jabuilder&view=page&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
	
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="row-fluid">
		<div class="span6">
			<div class="form-horizontal">
				<?php echo $this->form->renderField('newmenu');?>
				<?php echo $this->form->renderField('menuid');?>
				<?php echo $this->form->renderField('menutitle');?>
			</div>
		</div>
	</div>

	<hr>
	<div class="row-fluid">
		<div class="span6">
			<div class="form-horizontal">
				<fieldset class="adminform">
					<?php foreach ($this->form->getFieldset('params') as $field): ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</fieldset>
			</div>
		</div>
		<div class="span6">
			<div class="form-vertical">
				<?php echo $this->form->renderField('menutype');?>
				<?php echo $this->form->renderField('parent_id');?>
				<?php echo $this->form->renderField('menuordering');?>
				<?php echo $this->form->renderField('state');?>
				<?php echo $this->form->renderField('access');?>
			</div>
		</div>
	</div>

    <input type="hidden" name="task" value="page.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>
<script>
	jQuery(document).ready(function ($) {
		if($('#jform_menuid').val() !== '') {
			$('#jform_menuid').prop('readonly', true).trigger('liszt:updated');
		}
		
		
		var id = <?php echo $id ?>;
		$('#jform_menuid').on('change', function () {
			var val = $(this).val();
			var menuid = '&menuid=' + val;
			var query = [];
			var query_string = '';
			if(val !== '') {
					query.push(menuid);
			}
			if (id === 0) {
				var input = {
					title: $('#jform_title').val(),
					alias: $('#jform_alias').val()
				};
				var opt =  '&opt='+btoa( JSON.stringify(input) );
				query.push(opt);
			}
			query_string = query.join('');
			document.location = 'index.php?option=com_jabuilder&view=page&layout=edit&id='+id+query_string;
		});	

		$('#jform_menutype').change(function(){
			var menutype = $(this).val();
			$.ajax({
				url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
				dataType: 'json'
			}).done(function(data) {
				$('#jform_parent_id option').each(function() {
					if ($(this).val() != '1') {
						$(this).remove();
					}
				});

				$.each(data, function (i, val) {
					var option = $('<option>');
					option.text(val.title).val(val.id);
					$('#jform_parent_id').append(option);
				});
				$('#jform_parent_id').trigger('liszt:updated');
			});
		});
	});
</script>