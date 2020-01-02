PayPlans.require()
.script('shared/select2')
.done(function($) {
	$('[data-article-suggest]').select2({
		'ajax': {
			url: window.pp.ajaxUrl + '&task=articles.suggest',
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
		'multiple': "<?php echo $multiple; ?>",
	});

	// Re-initialized default value
	$('[data-article-suggest] > option').prop("selected","selected");
	$('[data-article-suggest]').trigger("change");
});
