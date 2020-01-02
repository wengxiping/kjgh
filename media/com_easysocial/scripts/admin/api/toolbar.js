EasySocial.module('admin/api/toolbar', function($) {

var module = this;


// Toolbar actions
var joomlaToolbar = $('#toolbar');
var actions = $('[data-toolbar-actions]');

if (actions.length > 0) {
	actions.each(function() {
		var buttonGroup = $(this);
		var position = buttonGroup.data('position') || 'append';

		buttonGroup.removeClass('t-hidden');

		if (position == 'append') {
			buttonGroup.appendTo(joomlaToolbar);
		} else {
			buttonGroup.prependTo(joomlaToolbar);
		}

		// Bind all the actions in this button group
		buttonGroup.find('[data-action]').each(function() {
			var element = $(this);
			var action = element.data('action');

			element.on('click', function() {
				Joomla.submitbutton(action);
			});
		});
	});
}

module.resolve();

});
