EasySocial.module('site/clusters/create', function($) {

var module	= this;

EasySocial.require()
.script('shared/fields/validate', 'shared/fields/base', 'shared/fields/conditional')
.done(function($) {

EasySocial.Controller('Clusters.Create', {
	defaultOptions: {
		'previousLink': null,
		'clusterType': null,
		'{field}': '[data-field-item]',
		"{form}": "[data-form]",
		"{previous}": "[data-previous]",
		"{next}": "[data-next]",

		// Category select page
		"{toggleSub}": "[data-toggle-subcategories]",
		"{categoryItem}": "[data-category-item]",
		"{itemsContainer}": "[data-es-items-container]",
		"{backButton}": "[data-select-category-back]"
	}
}, function(self, opts) { return {

	init: function() {
		self.field().addController('EasySocial.Controller.Field.Base');

		// Implement conditional field controller
		self.field().addController('EasySocial.Controller.Field.Conditional');

	},

	"{previous} click": function() {
		window.location = opts.previousLink;
	},

	"{next} click": function(button, event) {

		if (!button.enabled()) {
			return false;
		}

		// Set it to disabled
		button.disabled(true);
		button.addClass('is-loading');

		self.element
			.validate({fieldSelector : self.field.selector})
			.done(function() {
				button.removeClass('is-loading');
				button.enabled(true);

				self.form().submit();
			}).fail(function() {
				button.removeClass('is-loading');
				button.enabled(true);

				EasySocial.dialog({
					"content": EasySocial.ajax('site/views/profile/showFormError')
				});
			});
	},

	hideChild: function(id) {
		var items = document.querySelectorAll(self.categoryItem.selector + '[data-parent-id="' + id + '"]');

		items = Array.prototype.slice.call(items,0);

		items.forEach(function(item){ 
			var childId = $(item).data('id');
			self.hideChild(childId);
		});

		$(items).remove();
	},

	"{backButton} click": function(el) {
		// We need to get the first item's parent id
		var parentId = self.itemsContainer().find(self.categoryItem.selector).data('back-id');

		EasySocial.ajax('site/controllers/' + opts.clusterType + '/getSubcategories', {
			"parentId": parentId
		})
		.done(function(html) {
			self.itemsContainer().html(html);
			self.backButton().toggleClass('t-hidden', parentId == 0);
		});
	},

	"{toggleSub} click": function(el) {
		
		var itemWrapper = el.parent(self.categoryItem.selector);
		var parentId = itemWrapper.data('id');
		var backId = itemWrapper.data('parent-id');

		EasySocial.ajax('site/controllers/' + opts.clusterType + '/getSubcategories', {
			"parentId": parentId,
			"backId": backId
		})
		.done(function(html) {
			self.itemsContainer().html(html);
			self.backButton().removeClass('t-hidden');
		});
	}

}});

module.resolve();
});
});

