EasySocial.module('site/pages/edit', function($) {
	
var module	= this;

EasySocial.require()
.script('shared/fields/validate', 'shared/fields/base', 'shared/fields/conditional')
.done(function($) {
EasySocial.Controller(
	'Pages.Edit', {
		defaultOptions: {
			id: null,

			"{stepContent}": "[data-page-edit-fields-content]",
			"{stepItem}": "[data-page-edit-fields-step]",

			// Forms.
			"{pageForm}": "[data-page-fields-form]",
			"{taskHiddenInput}": "[data-task-hidden-input]",

			// Content for page editing
			"{pageContent}": "[data-page-edit-fields]",

			"{fieldItem}": "[data-field-item]",

			// Submit buttons
			"{submit}": "[data-page-fields-submit]"
		}
	}, function(self) {
		return {

			init: function()
			{
				self.fieldItem().addController('EasySocial.Controller.Field.Base', {
					mode: 'edit'
				});

				self.fieldItem().addController('EasySocial.Controller.Field.Conditional');
			},

			errorFields: [],

			// Support field throwing error internally
			'{fieldItem} error': function(el, ev)
			{
				self.triggerStepError(el);
			},

			// Support for field resolving error internally
			'{fieldItem} clear': function(el, ev)
			{
				self.clearStepError(el);
			},

			// Support validate.js throwing error externally
			'{fieldItem} onError': function(el, ev)
			{
				self.triggerStepError(el);
			},

			triggerStepError: function(el)
			{
				var fieldid = el.data('id'),
					stepid = el.parents(self.stepContent.selector).data('id');

				if($.inArray(fieldid, self.errorFields) < 0)
				{
					self.errorFields.push(fieldid);
				}

				self.stepItem().filterBy('for', stepid).trigger('error');
			},

			clearStepError: function(el)
			{
				var fieldid = el.data('id'),
					stepid = el.parents(self.stepContent.selector).data('id');

				self.errorFields = $.without(self.errorFields, fieldid);

				self.stepItem().filterBy('for', stepid).trigger('clear');
			},

			"{stepItem} click" : function(el, event)
			{
				var id 	= $(el).data('for');

				// page form should be hidden
				self.pageContent().show();

				// Hide all page steps.
				self.stepContent().hide();

				// Remove active class on step item
				self.stepItem().removeClass('active');

				// Add active class on the selected item.
				el.addClass('active');

				// Get the step content element
				var stepContent = self.stepContent('.step-' + id);

				// Show active page step.
				stepContent.show();

				// Trigger onShow on the field item in the content
				stepContent.find(self.fieldItem.selector).trigger('show');
			},

			"{stepItem} error": function(el)
			{
				el.addClass('error');
			},

			"{stepItem} clear": function(el)
			{
				if(self.errorFields.length < 1)
				{
					el.removeClass('error');
				}
			},

			"{submit} click" : function(el, event)
			{
				// Get the task for this action
				var task = el.data('task');

				if (task != 'update') {
					task = task + 'Page';
				}

				// Run some error checks here.
				event.preventDefault();

				el.addClass('btn-loading');

				self.pageForm()
					.validate({fieldSelector : self.fieldItem.selector})
					.fail(function()
					{
						el.removeClass('btn-loading');
						EasySocial.dialog(
						{
							content : EasySocial.ajax('site/views/profile/showFormError')
						});
					})
					.done(function()
					{
						// Change the task hidden input
						self.taskHiddenInput().val(task);
						
						self.pageForm().submit();
					});

				return false;
			}
		}
	}
);


module.resolve();
});
});

