<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php foreach ($sections as $section) { ?>
<div class="panel">
	<?php echo $this->html('panel.heading', $section->title, $section->desc); ?>

	<div class="panel-body">
		<?php foreach ($section->items as $field) { ?>
		<div class="o-form-group" data-section="<?php echo $section->key;?>">
			<?php if ($field->type == 'rewriter') { ?>
				<label class="o-control-label"></label>
			<?php } else { ?>
				<?php echo $this->html('form.label', $field->title, $field->tooltip); ?>
			<?php } ?>

			<div class="o-control-input col-md-7">
				<?php if ($field->type == 'custom') { ?>
					<?php echo $this->output($field->path, array('field' => $field, 'name' => $section->key . '[' . $field->name . ']', 'value' => $field->value, 'id' => $field->id, 'attributes' => $field->attributes)) ?>
				<?php } else { ?>
					<?php echo $this->html('form.' . $field->type, $section->key . '[' . $field->name . ']', $field->value, $section->key . '[' . $field->name . ']', $field->attributes, $field->options, $field->dependents); ?>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php } ?>