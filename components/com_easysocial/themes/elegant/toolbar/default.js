
EasySocial
.require()
.script('site/toolbar/notifications','site/search/toolbar')
.done(function($){

	$('[data-es-toolbar]').implement(EasySocial.Controller.Notifications);
	$('[data-toolbar-search]').implement(EasySocial.Controller.Search.Toolbar);

	<?php if ($this->isMobile()) { ?>
	$('[data-es-toolbar-toggle]').on('click', function() {
		// Get the menu contents
		var contents = $('[data-es-toolbar-menu]').html();

		EasySocial.dialog({
			"title": "<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_MENU_TITLE', true);?>",
			"content": contents
		});
	});


	$('[data-elegant-toggle-search]')
		.off('click')
		.on('click', function() {
			var button = $(this);
			var toolbar = button.parents('[data-es-toolbar]');

			toolbar.toggleClass('show-search');
		});

	<?php } ?>
});
