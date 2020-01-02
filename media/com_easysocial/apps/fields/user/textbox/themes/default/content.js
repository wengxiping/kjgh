EasySocial
.require()
.script('apps/fields/user/textbox/content')
.done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Textbox', {
		required: <?php echo $field->required ? 1 : 0; ?>
	});
});