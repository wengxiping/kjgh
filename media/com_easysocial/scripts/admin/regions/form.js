EasySocial.module('admin/regions/form', function($) {

var module = this;

EasySocial.Controller('Regions.Form', {
	defaultOptions: {
		'{type}': '[data-type]',
		'{parentBase}': '[data-parent-base]',
		'{parentContent}': '[data-parent-content]',
		'{parentUid}': '[data-parent-uid]',
		'{parentType}': '[data-parent-type]'
	}
}, function(self, opts, base) { return {
		init: function() {
			self.element.find('input[type="text"]').prop('disabled', !self.type().val());

			self.element.find('[data-bs-toggle="radio-buttons"]').toggleClass('disabled', !self.type().val());
		},

		'{type} change': function(el) {
			var parentType = el.find(':selected').data('parent');

			self.parentType().val(parentType);

			self.element.find('input[type="text"]').prop('disabled', !el.val());

			self.element.find('[data-bs-toggle="radio-buttons"]').toggleClass('disabled', !el.val());

			if (parentType) {
				self.parentBase().show();

				!self.parentContent().data('loaded') &&
				self.getParents(parentType)
					.done(function(parents) {
						self.parentContent().html(parents);
					});
			} else {
				self.parentBase().hide();
			}
		},

		getParents: $.memoize(function(key) {
			return EasySocial.ajax('admin/controllers/regions/getParents', {
				type: key
			});
		}),

		validate: function() {
			return self.type().val() && self.element.find('input[name="name"]').val() && self.element.find('input[name="code"]').val();
		}
	}
});

module.resolve();
});