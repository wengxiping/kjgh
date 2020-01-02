EasySocial.require()
.script('admin/profiles/form')
.done(function($){

	$('[data-profile-form]').addController('EasySocial.Controller.Profiles.Profile', {
		id: <?php echo !empty( $profile->id ) ? $profile->id : 0; ?>
	});

	$('[data-label-font]').on('change', function() {
		var checked = $(this).is(':checked');
		var wrapper = $('[data-label-font-wrapper]');

		if (checked) {
			wrapper.removeClass('t-hidden');
			return;
		}

		wrapper.addClass('t-hidden');
		return;
	});

	$('[data-label-background]').on('change', function() {
		var checked = $(this).is(':checked');
		var wrapper = $('[data-label-background-wrapper]');

		if (checked) {
			wrapper.removeClass('t-hidden');
			return;
		}

		wrapper.addClass('t-hidden');
		return;
	});

	$.Joomla('submitbutton', function(task) {

		<?php if ($profile->id) { ?>
		var performSave = function(id) {
			$.Joomla('submitform', [task]);
			return;
		}

		var validateUploadSize = function() {

			var hasError = false;

			$('[data-maxupload-check]').each(function(idx, ele) {

				var maxvalue = $(this).data('maxupload');
				var key = $(this).data('maxupload-key');
				var curvalue = $(this).val();

				if (curvalue > maxvalue) {

					hasError = true;

					EasySocial.dialog({
						content: EasySocial.ajax('admin/views/profiles/getAclErrorDialog', {"key": key})
					});
				}
			});

			if (hasError) {
				return false;
			}

			return true;
		}

		if (task == 'save' || task == 'savenew' || task == 'apply') {
			if (validateUploadSize()) {
				performSave(<?php echo $profile->id; ?>);
			}

			return false;
		}

		if (task == 'savecopy') {
			// Make ajax call to create copy of profile
			EasySocial.ajax('admin/controllers/profiles/createBlankProfile')
				.done(function(id) {

					// lets update the form element cid value.
					var input = $('input[name="cid"]');
					input.attr( 'value', id );
					performSave(id);
				});

			return false;
		}
		<?php } ?>

		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=profiles';
			return;
		}

		$.Joomla('submitform', [task]);
	});
});
