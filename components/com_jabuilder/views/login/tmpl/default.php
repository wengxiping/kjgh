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

$input = JFactory::getApplication()->input;

$return = $input->get->get('return');
?>

<form action="<?php echo JRoute::_('index.php?option=com_jabuilder&task=login.login'); ?>" method="post" class="form-validate form-horizontal well">

	<fieldset>
		<?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
			<?php if (!$field->hidden) : ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>


		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary">
					<?php echo JText::_('JLOGIN'); ?>
				</button>
			</div>
		</div>
		<?php echo $this->form->renderField('return', null, $return) ?>
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
	
</form>