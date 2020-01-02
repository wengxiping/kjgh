
EasySocial.require()
.done(function($) {

	$('[data-image-restore]').on('click', function() {
		var parent = $(this).parent();
		var type = $(this).data('type');
		var image = parent.siblings('[data-image-source]');

		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/settings/confirmRestoreImage', {type: type}),
			bindings: {
				'{restoreButton} click': function() {

					EasySocial.ajax('admin/controllers/settings/restoreImage', {type: type}).done(function() {
						parent.addClass('t-hidden');

						image.attr('src', image.data('default'));
						EasySocial.dialog().close();
					});
				}
			}
		});
	});
	
	// Bind the active tab so that we know which page to redirect the user to
	$(document)
	.on('click.settings.tabs', '[data-bs-toggle]', function() {
		var tab = $(this);
		var id = tab.attr('href').replace('#', '');

		$('[data-active-tab]').val(id);
	});

	$.Joomla('submitbutton', function(task) {
		
		console.log(task);

		if (task == 'reset') {
			
			EasySocial.dialog({
				"content": EasySocial.ajax( "admin/views/settings/confirmReset", { "section" : "<?php echo $page;?>"} ),
				"bindings": {
					"{resetButton} click" : function() {
						this.resetForm().submit();
					}
				}
			});

			return false;
		}

		if (task == 'export') {
			$.download( '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=settings&format=raw&layout=export&tmpl=component' );
			return false;
		}

		if (task == 'import') {
			EasySocial.dialog(
			{
				"content": EasySocial.ajax( "admin/views/settings/import" , { "page" : "<?php echo $page;?>"}),
				"bindings":  {
					"{submitButton} click" : function() {
						this.importForm().submit();
					}
				}
			});
		}

		if (task == 'apply') {
			$.Joomla('submitform', [task]);
			return;
		}

		if (task == 'purgeTextAvatars') {
			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/settings/confirmPurgeTextAvatars'),
				"bindings": {
					"{submitButton} click": function() {
						this.form().submit();
					}
				}
			})
		}

		return false;
	});

});
