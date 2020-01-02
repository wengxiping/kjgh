
EasySocial
.require()
.script('site/toolbar/notifications','site/search/toolbar')
.done(function($){

	// Prevent closing toolbar dropdown
	$(document).on('click.toolbar', '[data-es-toolbar-dropdown]', function(event) {
		event.stopPropagation();
	});

	$('[data-es-toolbar]').implement(EasySocial.Controller.Notifications);
	$('[data-toolbar-search]').implement(EasySocial.Controller.Search.Toolbar, {
		"enforceMinimumLength": <?php echo $this->config->get('search.minimum') ? 'true' : 'false';?>,
		"minimumLength": "<?php echo $this->config->get('search.characters');?>"
	});

	<?php //if ($this->isMobile()) { ?>
	$('[data-es-toolbar-toggle]').on('click', function() {
		// Get the menu contents
		var contents = $('[data-es-toolbar-menu]').html();

		EasySocial.dialog({
			"title": "<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_MENU_TITLE', true);?>",
			"content": contents,
			"width": '80%',
			"height": '80%'
		});
	});

	// We need to unbind the click for conflicts with pagespeed
	$(document)
		.off('click.search.toggle')
		.on('click.search.toggle', '[data-es-toolbar-search-toggle]', function() {
			var searchBar = $('[data-toolbar-search]');
			var esToolBar = $('[data-es-toolbar]');

			esToolBar.toggleClass('es-toolbar--search-on');
		});

	<?php //} ?>
});
