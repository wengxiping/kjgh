jQuery(document).ready(function($) {
	$('input[name="jform\[params\]\[jastyle\]"]:checked').next().children('img.ja-thumbnail').addClass('active');
	$('img.ja-thumbnail').popover({
		html : true,
		trigger: 'hover',
		placement: 'top',
		content: function() {
			return '<img src="'+$(this).attr('src')+'" />'
		}
	});
	
	$('img.ja-thumbnail').on('click', function() {
		$('img.ja-thumbnail').removeClass('active');
		$(this).addClass('active');
	});
});