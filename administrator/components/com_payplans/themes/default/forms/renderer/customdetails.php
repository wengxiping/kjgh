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
	<?php echo $this->html('panel.heading', 'COM_PP_CUSTOM_DETAILS_HEADING'); ?>

	<div class="panel-body">
		<?php foreach ($section->items as $field) { ?>
		<div class="o-form-group" data-section="<?php echo $section->key;?>">
			<?php echo $this->html('form.label', $field->title, $field->tooltip, 3, $field->tooltip ? true : false); ?>

			<div class="o-control-input">
				<?php echo $this->html('form.' . $field->type, $section->key . '[' . $field->name . ']', $this->html('string.escape', $field->value), $field->id, $field->attributes, $field->options); ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php } ?>