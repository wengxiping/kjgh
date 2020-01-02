EasySocial.ready(function($) {

	$('[data-sidebar-parent]').on('click', function(event) {
		var item = $(this);
		var hasChild = item.data('childs') > 0 ? true : false;

		if (!hasChild) {
			return;
		}

		event.preventDefault();
		
		var parent = item.parent('[data-sidebar-item]');

		// Remove active 
		$('[data-sidebar-item]').removeClass('active');

		// Toggle dropdown
		parent.toggleClass('active');
	});
});