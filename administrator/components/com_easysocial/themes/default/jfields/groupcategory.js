
EasySocial.require()
.library('dialog')
.done(function($) {
	window.selectGroupCategory 	= function(obj) {
		$('[data-jfield-groupcategory-title]').val(obj.title);

		$('[data-jfield-groupcategory-value]').val(obj.id + ':' + obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-groupcategory-remove]').on('click', function() {

		// Reset the category value
		$('[data-jfield-groupcategory-value]').val('');
		$('[data-jfield-groupcategory-title]').val('');

	});

	$('[data-jfield-groupcategory]').on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/groups/browseCategory', {
				'jscallback': 'selectGroupCategory' 
			})
		});
	});

});
