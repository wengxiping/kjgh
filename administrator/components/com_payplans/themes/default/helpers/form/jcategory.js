PayPlans.require()
.script('shared/select2')
.done(function($) {
	$('[data-category-suggest]').select2({
		'ajax': {
			url: window.pp.ajaxUrl + '&task=articles.suggestCategory',
			dataType: 'json',
			delay: 250,
			cache: true,
			data: function(params) {
				return {
					q: params.term,
					page: params.page
				};
			},
			processResults: function(data, params) {
				params.page = params.page || 1;

				return {
					results: data.items,
					pagination: {
						more: (params.page * 30) < data.total_count
					}
				};
			}
		},
		'minimumInputLength': 1,
		'multiple': true
	});

	// Re-initialized default value
	$('[data-category-suggest] > option').prop("selected","selected");
	$('[data-category-suggest]').trigger("change");
});
