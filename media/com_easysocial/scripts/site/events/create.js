EasySocial.module('site/events/create', function($) {
	var module = this;

	EasySocial.require()
	.script('shared/fields/validate', 'shared/fields/base', 'shared/fields/conditional')
	.done(function() {
		EasySocial.Controller('Events.Create', {
			defaultOptions: {
				'previousLink': null,
				'{fields}': '[data-field-item]',
				'{previous}': '[data-create-previous]',
				'{postForm}': '[data-post-form]',
				'{next}': '[data-create-submit]',


				// Category select page
				"{toggleSub}": "[data-toggle-subcategories]",
				"{categoryItem}": "[data-category-item]",
				"{itemsContainer}": "[data-es-items-container]",
				"{backButton}": "[data-select-category-back]"
			}
		}, function(self, opts, base) {
			return {
				init: function() {
					self.fields().addController('EasySocial.Controller.Field.Base');

					self.fields().addController('EasySocial.Controller.Field.Conditional')
				},

				'{previous} click': function() {
					window.location = self.options.previousLink;
				},

				'{next} click': function(el) {
					if (el.enabled()) {
						el.disabled(true);

						el.addClass('btn-loading');

						self.element.validate({fieldSelector : self.fields.selector})
							.done(function() {
								el.removeClass('btn-loading');
								el.enabled(true);

								self.postForm().submit();
								// self.element.submit();
							})
							.fail(function() {
								el.removeClass('btn-loading');
								el.enabled(true);

								EasySocial.dialog({
									content: EasySocial.ajax('site/views/profile/showFormError')
								});
							});
					}
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

					EasySocial.ajax('site/controllers/events/getSubcategories', {
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

					var groupId = base.data('group-id') ? base.data('group-id') : 0;
					var pageId = base.data('page-id') ? base.data('page-id') : 0;

					EasySocial.ajax('site/controllers/events/getSubcategories', {
						"parentId": parentId,
						"groupId": groupId,
						"pageId": pageId,
						"backId": backId
					})
					.done(function(html) {
						self.itemsContainer().html(html);
						self.backButton().removeClass('t-hidden');
					});
				}
			}
		});

		module.resolve();
	});
});
