EasySocial.module('shared/sidebarmenu', function($) {

var module = this;
var isController = $.isController('EasySocial.Controller.Sidebarmenu');

if (isController) {
	return;
}

EasySocial.Controller('Sidebarmenu', {
	defaultOptions: {
		mainmenu: null,
		viewOpenClass: 'dl-subviewopen',
		subViewClass: 'dl-subview',
		animate: true,
		"{subMenuLink}" : "[data-submenu-link]",
		"{subMenuBack}" : "[data-submenu-back]"
	}
}, function(self, opts, base) { return {
	
	init: function() {
	},

	open: function(element) {
		var parent = element.parent('li');
		var submenu = parent.children('ul');

		self.animate('open', submenu).done(function() {

			// Change all of the dl-subviewopen to dl-subview first
			base.find('.dl-subviewopen').addClass(opts.subViewClass).removeClass(opts.viewOpenClass);

			parent.removeClass(opts.subViewClass).addClass(opts.viewOpenClass);
		});
	},

	close: function(submenu) {
		var parent = submenu.parent('li');
		parent.removeClass(opts.viewOpenClass);

		var main = parent.parent('ul');
		main.parent('li').removeClass(opts.subViewClass).addClass(opts.viewOpenClass);

		self.animate('close', submenu, main).done(function() {

			if (main.attr('data-sidebar-menu') !== undefined) {
				main.removeClass(opts.subViewClass);
			}
		});
	},

	animate: function(operation, submenu, main) {

		if (main !== undefined && main.attr('data-sidebar-menu') !== undefined) {
			base.removeClass(opts.subViewClass);
		}

		// Simulate the opposite effect
		if (opts.animate) {
			var dfd1 = $.Deferred(),
				dfd2 = $.Deferred();

			operation1 = operation == 'open' ? 'out' : 'in';
			operation2 = operation == 'open' ? 'in' : 'out';

			var subMenuClone = submenu.clone();

			subMenuClone.find('ul').remove();
			base.after(subMenuClone);
			base.addClass('dl-animate-' + operation1 + '-1');

			// Wait 0.3 second
			setTimeout(function() {
				base.addClass(opts.subViewClass).removeClass('dl-animate-' + operation1 + '-1');

				dfd1.resolve();
			}, 300);

			// Sub menu transition
			subMenuClone.addClass('dl-animate-' + operation2 + '-1');

			if (operation2 == 'in') {
				subMenuClone.css('opacity', '0');
			}

			// Wait 0.3 second
			setTimeout(function() {
				subMenuClone.removeClass('dl-animate-' + operation2 + '-1');
				subMenuClone.remove();

				dfd2.resolve();
			}, 300);

			// Return when the animation is finished
			return $.when(dfd1, dfd2).done(function() {
			}).promise();
		} else {
			var dfd = $.Deferred();

			base.addClass(opts.subViewClass);

			dfd.resolve();

			return dfd;
		}
	},

	"{subMenuLink} click": function(element, event) {
		event.preventDefault();
		event.stopPropagation();

		// Open submenu
		self.open(element);
	},

	"{subMenuBack} click": function(element, event) {
		event.preventDefault();
		event.stopPropagation();

		var parent = element.parent('ul');

		// Close submenu
		self.close(parent);
	},
}});

module.resolve();
});