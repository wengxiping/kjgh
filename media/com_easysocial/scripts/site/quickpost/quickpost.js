EasySocial.module('site/quickpost/quickpost', function($){

var module = this;

EasySocial.Controller('Quickpost', {
	defaultOptions: {
		'{storyButton}': '[data-es-quickpost-button]',
		'{storyPopup}': '[data-es-quickpost-popup]',
		'{storyClose}': '[data-es-quickpost-close]',
		'{storyForm}': '[data-es-quickpost-form]',
		'{storyWrapper}': '[data-es-quickpost-wrapper]',
		'{storyTitle}': '[data-es-quickpost-title]'
	}
}, function(self,opts,base) { return {

	init: function() {
		self.initKeyBinding();
	},

	initKeyBinding: function() {
		$(document).keyup(function(e) {
			if (e.keyCode == 27) {
				self.storyClose().trigger('click');
			}
		})
	},

	open: function() {
		$('body').addClass('is-es-quickpost-popup');

		self.storyPopup().removeClass('t-hidden');
		self.storyWrapper().addClass('t-hidden');
	},

	close: function() {
		// Clear up any content inside the form
		self.storyForm().html('');

		self.storyWrapper().addClass('t-hidden');
		self.storyPopup().addClass('t-hidden');

		$('body').removeClass('is-es-quickpost-popup');
	},

	loading: function() {
		self.storyPopup().toggleClass('is-loading');
	},

	renderStoryForm: function(type, title) {

		// Open form
		self.open();

		// Show loading while the ajax load
		self.loading();

		EasySocial.ajax('site/views/story/renderForm', {
			"type": type
		})
		.done(function(content) {
			self.storyForm().html(content);
			self.storyTitle().html(title);

			self.loading();
			self.storyWrapper().removeClass('t-hidden');
		})
	},

	"{storyClose} click": function(el, ev) {
		self.close();
	},

	"{storyButton} click": function(el, ev) {
		var type = el.data('type');
		var title = el.data('title');

		self.renderStoryForm(type, title);
	}
}});

module.resolve();

});
