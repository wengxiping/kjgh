
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

	// We need to unbind the click for conflicts with pagespeed
	$(document)
		.off('click.search.toggle')
		.on('click.search.toggle', '[data-es-toolbar-search-toggle]', function() {
			var searchBar = $('[data-toolbar-search]');

			searchBar.toggleClass('t-hidden');
		});

	<?php } ?>
});
