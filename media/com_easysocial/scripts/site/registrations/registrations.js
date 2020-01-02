EasySocial.module( 'site/registrations/registrations' , function($){

var module = this;

EasySocial.require()
.script('shared/fields/validate', 'shared/fields/base', 'shared/fields/conditional')
.done(function($){


EasySocial.Controller('Registrations.Form', {
	defaultOptions: {
		previousLink: null,

		"{item}": "[data-field-item]",
		"{submit}": "[data-registration-submit]",
		"{previous}": "[data-registration-previous]",
		"{itemInput}": "[data-input-trigger]"
	}
}, function(self, opts, base) { return {

	init: function() {

		// Implement controller on each field item
		self.item().addController(EasySocial.Controller.Field.Base, {
			"mode": "register"
		});

		// Implement conditional field controller
		self.item().addController(EasySocial.Controller.Field.Conditional);
	},

	"{previous} click" : function(button, event) {
		event.preventDefault();
		event.stopPropagation();

		window.location.href = opts.previousLink;

		return false;
	},

	"{submit} click" : function(button, event) {
		event.preventDefault();
		event.stopPropagation();

		// Apply loading class on button
		button.addClass('is-loading');

		// Apply validation
		self.element.validate({fieldSelector : self.item.selector})
			.done(function() {
				self.element.submit();
			})
			.fail(function() {

				// Remove loading class
				button.removeClass('is-loading');

				EasySocial.dialog({
					"content": EasySocial.ajax('site/views/registration/getErrorDialog')
				});
			});

		return false;
	}
}});

module.resolve();
});

});
