
EasySocial.require()
.library('dialog')
.done(function($) {
	
	window.selectPageCategory = function(obj) {
		$('[data-jfield-pagecategory-title]').val(obj.title);

		$('[data-jfield-pagecategory-value]').val(obj.id + ':' + obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-pagecategory-remove]').on('click', function() {

		// Reset the category value
		$('[data-jfield-pagecategory-value]').val('');
		$('[data-jfield-pagecategory-title]').val('');

	});

	$('[data-jfield-pagecategory]').on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/pages/browseCategory', { 
				'jscallback': 'selectPageCategory' 
			})
		});
	});

});
