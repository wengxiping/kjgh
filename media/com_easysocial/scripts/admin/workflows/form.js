EasySocial.module('admin/workflows/form', function($) {

var module = this;

EasySocial.require()
.library('ui/sortable', 'scrollTo')
.script('admin/workflows/choices')
.done(function($) {

EasySocial.Controller('Workflows', {
	defaultOptions: {
		id: null,
		tmpSteps: null,
		tpls: {},
		fieldsValue: {},
		fieldsData: {},
		fieldsChoices: {},
		fieldsConditions: {},
		stepsValue: {},
		stepsHtml: {},
		fieldsConfigValue: {},
		deletedFields: {},
		deletedSteps: {},
		hiddenClass: 't-hidden',

		"{templates}": '[data-workflow-templates]',
		"{content}": "[data-content]",
		"{titleInput}": "[data-input-workflow-title]",
		"{descInput}": "[data-input-workflow-description]",
		"{workflowConfigSave}": "[data-workflow-config-save-button]",

		// Steps
		"{steps}": "[data-steps]",
		"{step}": "[data-step-item]",
		"{newStep}": "[data-step-new]",
		"{stepTitle}": "[data-step-title]",
		"{editStep}": "[data-step-edit]",
		"{deleteStepButton}": "[data-step-delete]",

		// Fields
		"{fields}": "[data-fields]",
		"{field}": "[data-field-item]",
		"{newField}": "[data-field-new]",
		"{moveField}": "[data-field-item-move]",
		"{deleteField}": "[data-field-item-delete]",
		"{editField}": "[data-field-item-edit]",
		"{conditionalField}": "[data-field-item-conditional]",
		"{conditionalParam}" : "[data-fields-conditional-param]",
		"{fieldRequiredSymbol}" : "[data-field-item-required]",

		// Input type to hold fields configuration values
		"{saveFieldsInput}": "[data-fields-saved-value]",

		// Fields Settings
		"{fieldSettings}": "[data-field-settings]",
		"{fieldParam}": "[data-fields-config-param]",

		// Fields Browser
		"{fieldBrowser}": "[data-field-browser]",
		"{fieldBrowserClose}": "[data-field-browser-close]",
		"{fieldsTypeTab}" : "[data-field-type-tab]",
		"{fieldTypeContent}" : "[data-field-type-content]",
		"{fieldBrowserItems}" : "[data-field-browser-items]",
		"{fieldBrowserItem}" : "[data-field-browser-item]",

		// Fields Dialog
		"{dialogFieldSave}": "[data-field-save-button]",
		"{dialogFieldCancel}": "[data-field-cancel-button]",
		"{dialogFieldTab}": "[data-field-tab]",
		"{dialogFieldContent}": "[data-field-content]",
		"{dialogStepSave}": "[data-field-step-save-button]",

		// Error dialog
		"{dialogError}": "[data-dialog-error]",
		"{dialogErrorClose}" : "[data-dialog-error-close]"
	}
}, function(self, opts, base) { return {
	
	init: function() {
		opts.id = self.element.data('id');

		// Initialize all available templates on the page
		this.initTemplates();

		// Need to initialize the drag / drop here
		this.initSortable();

		// Initialize fields default data if this is a new workflow
		this.initDefaultData();

		this.initBindEvent();

		// Duckpunch setMessage
		self._setMessage = self.setMessage;

		self.setMessage = function(message, type, element) {

			if (element == undefined) {
				// Do not set any messages when story is collapsed or is resizing.
				if (base.hasClass("is-resizing")) {
					return;
				}

				// Remove any previous message group first to avoid stacking error messages.
				this.element
					.find('[data-message-group]')
					.remove();

				self._setMessage.apply(this, [message, type]);
			} else {

				var tpl = self.getTemplate('alert');
				var classType = 'o-alert--' + type;
				var container = element.find('[data-message-group]');

				tpl.addClass(classType);

				// Get any message directly from the container
				if (!message) {
					message = container.data('message');
				}

				tpl.find('[data-message]').html(message);

				element.find('[data-message-group]').html(tpl);
			}
		};
	},

	initDefaultData: function() {
		if (!opts.id || opts.id == '') {
			var defaults = JSON.parse(opts.tmpSteps);
			var steps = [];
			var fields = [];

			// Construct steps informations
			$.each(defaults, function(key, step) {

				var stepData = [
					{
						name : "title",
						value: step.title
					},
					{
						name : "description",
						value : step.description
					}
				]

				// Store step
				self.storeSteps(step.id, stepData);

				if (step.fields) {
					$.each(step.fields, function(key, field) {
						var fieldsData = [];

						$.each(field, function(name, value) {
							var input = {
								name: name,
								value : value
							}

							fieldsData.push(input);
						})

						// Store individual field
						self.storeFieldsData(field.id, fieldsData);
					});
				}
			});
		}
	},

	// Fetch all available templates on the page
	initTemplates: function() {
		var templates = self.templates().children();

		$.each(templates, function() {
			var template = $(this);
			var key = template.data('template');
			
			opts.tpls[key] = template.html();
		});
	},

	initSortable: function() {
		this.initStepsSortable();
		this.initFieldsSortable();
	},

	initStepsSortable: function() {

		self.steps().sortable({
			items: self.step.selector,
			cursor: 'move',
			forceHelperSize: true,
			axis : 'x',
			update : function(event, ui) {
				self.updateOrdering(self.step());
			}
		});
	},

	initFieldsSortable: function() {
		self.fields().sortable({
			items: self.field.selector,
			cursor: 'move',
			forceHelperSize: true,
			axis : 'y',
			start: function(event, ui) {
				ui.helper.addClass('is-new');

				// Temporarily removed has-condition class
				if (ui.helper.hasClass('has-condition')) {
					ui.helper.removeClass('has-condition').addClass('has-condition-tmp');
				}
			},
			stop: function(event, ui) {
				setTimeout(function() {
					ui.item.removeClass('is-new');

					if (ui.item.hasClass('has-condition-tmp')) {
						ui.item.removeClass('has-condition-tmp').addClass('has-condition');
					}
				}, 150);
			},
			update : function(event, ui) {
				self.updateOrdering(self.field());
			}
		});
	},

	initBindEvent: function() {
		$(document).on('keyup', function(e) {
			// esc key
			if (e.keyCode == 27) {
				self.closeDialog();
			}
		});
	},

	getTemplate: function(key) {
		var tpl = $(opts.tpls[key]).clone();
		return tpl;
	},

	updateOrdering: function(element) {
		$.each(element, function() {
			var item = $(this);
			var index = item.index();

			// Update the sequence
			item.attr('data-ordering', index);
		});
	},

	changed: false,

	isChanged: function() {
		self.changed = true;
	},

	mandatoryCheck: function() {
		return self.fieldsTypeTab().filter('[id="core"]').hasClass(opts.hiddenClass);
	},

	hasEmptySteps: function() {
		var stepData = self.step();
		var hasEmpty = false;

		$.each(stepData, function(i, step) {
			var step = $(step);
			var stepId = step.data('id');

			if (stepId) {
				if (!self.isStepEmpty(stepId)) {
					return;
				} else {
					hasEmpty = true;
					return false;
				}
			}
		});

		return hasEmpty;
	},

	isStepEmpty: function(stepId) {
		var stepContent = $(self.content.selector + '[data-id="' +  stepId + '"]');
		var fields = stepContent.find(self.field.selector);

		return fields.length > 0 ? false : true;
	},

	validate: function() {

		// Check for mandatory field
		if (!self.mandatoryCheck()) {
			self.showDialogError('mandatory');
			return false;
		}

		// Warn user about empty steps
		if (self.hasEmptySteps()) {
			self.showDialogError('empty-step');
			return false;
		}

		return true;
	},

	save: function() {
		var dfd = $.Deferred();

		// Perform save validation
		if (!self.validate()) {
			dfd.reject();
			return dfd;
		}

		// Perform collection of the input
		var workflowData = self.fetchWorkflowData();

		// Inject the data into the input form
		self.injectSaveData(workflowData);

		// When everything is done, we can mark the deferred object as resolved
		dfd.resolve();

		return dfd;
	},

	getAvailableFields: function() {
		return self.fetchWorkflowData(true, true);
	},

	fetchWorkflowData: function(currentStep, simpleData) {
		var steps = [];
		var stepOrdering = 0;

		// Get all available step
		var stepData = self.step();

		// Determine if we want to retrieve the data for current step only
		if (currentStep) {
			stepData = self.getCurrentStep();
		}

		$.each(stepData, function(i, step) {
			var step = $(step);
			var stepContent = $(self.content.selector + '[data-id="' +  step.data('id') + '"]');
			var stepsData = [];
			var tmpFieldData = [];

			// Get all fields within the step
			var fields = stepContent.find(self.field.selector);

			if (fields.length > 0) {

				var fieldOrdering = 0;

				// Construct the fields data
				$.each(fields, function(i, field) {
					var field = $(field);

					if (simpleData) {
						var fieldData = {
							fieldTitle : field.find('[data-field-item-title]').html(),
							fieldId: field.data('id'),
							fieldElement: field.data('element'),
							appId: field.data('appid'),
						}
					} else {
						var fieldData = {
							fieldTitle : field.find('[data-field-item-title]').html(),
							fieldId: field.data('id'),
							fieldElement: field.data('element'),
							appId: field.data('appid'),
							ordering: fieldOrdering,
							isNew: field.data('isnew'),
							params: opts.fieldsValue[field.data('id')]
						}
					}

					tmpFieldData.push(fieldData);

					fieldOrdering++;
				});

				if (simpleData) {
					var stepsData = {
						stepId: step.data('id'),
						fields: tmpFieldData
					}
				} else {
					var stepsData = {
						stepId: step.data('id'),
						ordering: stepOrdering,
						isNew: step.data('isnew'),
						params: opts.stepsValue[step.data('id')],
						fields: tmpFieldData
					}
				}

				steps.push(stepsData);

				stepOrdering++;
			} else {

				// Remove the step since there are no field within the steps
				if (step.data('id')) {
					if (self.deletedSteps === undefined) {
						self.deletedSteps = [];
					}

					self.deletedSteps.push(step.data('id'));
				}
			}
		});

		var deleted = {
			steps : self.deletedSteps,
			field : self.deletedFields
		}

		var workflowData = {
			"steps" : steps,
			"deleted" : deleted
		}

		return workflowData;
	},

	openWorkflowConfig: function() {
		var config = $(self.getTemplate('workflow-config'));

		// Set title and description
		config.find('[data-workflow-config-title]').val(self.titleInput().val());
		config.find('[data-workflow-config-description]').val(self.descInput().val());

		self.fieldSettings().html(config.wrap('<p/>').parent().html());

		config.unwrap();
	},

	"{workflowConfigSave} click": function(el, event) {
		var parent = el.parents('[data-config-dialog]');

		var title = parent.find('[data-workflow-config-title]').val();
		var description = parent.find('[data-workflow-config-description]').val();

		// Update the value on the input and the page header
		self.titleInput().val(title);
		self.descInput().val(description);

		$('[data-structure-heading]').html(title);
		$('[data-structure-description]').html(description);

		self.closeDialog();
	},

	"{newStep} click": function() {
		self.addStep();
	},

	"{step} click": function(el, event) {
		var id = el.data('id');
		self.switchTab(id);
	},

	showFieldBrowser: function() {
		self.fieldBrowser().removeClass(opts.hiddenClass);

		self.fieldBrowser()
			.find('[data-message-group]')
			.html('');
	},

	// Centralize all the dialogs
	closeDialog: function() {

		// Field browser popup
		self.fieldBrowser().addClass(opts.hiddenClass);

		// Error dialog
		self.dialogError().addClass(opts.hiddenClass);

		// Various dialog form
		self.dialogFieldCancel().trigger('click');
	},

	showDialogError: function(type) {
		var errorDialog = $(self.getTemplate('dialog-error'));
		var dialogMessage = errorDialog.find('[data-error-type="' + type + '"]');

		if (dialogMessage.length > 0) {
			dialogMessage.removeClass(opts.hiddenClass);
		} else {
			errorDialog.find('[data-error-type="default"]').removeClass(opts.hiddenClass)
		}

		self.fieldSettings().html(errorDialog.wrap('<p/>').parent().html());
		errorDialog.unwrap();
	},

	getNewFieldButton: function() {
		var tpl = $('[data-field-new]').first().clone();
		return tpl;
	},

	getField: function(fieldId) {
		var field = $(self.field.selector + '[data-id="' + fieldId + '"]');

		return field;
	},

	updateFieldBrowser: function(appId, action) {
		var hide = true;

		// Get the field
		var field = self.fieldBrowserItem().filter('[data-id="' + appId + '"]');

		// Parent of the field
		var parent = field.parents(self.fieldTypeContent.selector);

		// Exception for standard field
		if (parent.attr('id') == 'standard') {
			return;
		}

		// Hide or show field
		field[action + 'Class'](opts.hiddenClass);

		// Determine if we should hide or show tabs of the fields
		var fields = parent.find(self.fieldBrowserItems.selector).children();
		var fieldTab = self.fieldsTypeTab().filter('[id="' + parent.attr('id') + '"]');

		// No more fields left
		if (fields.not('.' + opts.hiddenClass).length < 1) {

			// Hide tab
			fieldTab.addClass(opts.hiddenClass);

			// Switch to nearest tab
			self.fieldsTypeTab().not('.' + opts.hiddenClass).eq(0).trigger('click');
		} else {

			// Show tab
			fieldTab.removeClass(opts.hiddenClass);
		}
	},

	switchTab: function(id) {
		// Get the step tab
		var tab = self.getStepTab(id);

		// Get the content of the steps
		var steps = self.getStepContent(id);

		self.step().removeClass('is-active');
		tab.addClass('is-active');

		self.content().addClass(opts.hiddenClass);
		steps.removeClass(opts.hiddenClass);
	},

	addStep: function() {
		// Generate unique id
		var uniqueId = $.uid();

		// Insert a new step template
		var template = self.getTemplate('step');

		// set unique id for this steps
		template.attr('data-id', uniqueId);
		
		// Append the template before the new button
		template.insertBefore(self.newStep());

		// Append the editor template on the main content
		var editorTemplate = self.getTemplate('editor');

		// Set unique id
		editorTemplate.attr('data-id', uniqueId);

		// Append the content
		editorTemplate.insertBefore(self.templates());

		// Updata the ordering
		self.updateOrdering(self.step());

		// Show delete steps button
		self.deleteStepButton().removeClass(opts.hiddenClass);

		// Change the page to newly created step
		template.trigger('click');
	},

	addField: function(element, field, wrapper) {

		var newField = false;

		if (field == undefined) {
			// Get field template
			var field = self.getTemplate('field');
			var fieldElement = element.data('fieldElement');

			// Add header class for header field
			if (fieldElement == 'header') {
				field.addClass('es-wf-field--header');
			}

			field.attr('data-id', $.uid());
			field.attr('data-appid', element.data('id'));
			field.attr('data-element', fieldElement)
			field.find('[data-field-item-title]').html(element.data('fieldTitle'));
			field.find('[data-field-item-element]').html(fieldElement.toUpperCase());

			newField = true;
		}

		// Get current steps wrapper
		if (wrapper == undefined) {
			var wrapper = self.getCurrentStep();
		}

		// Get the fields wrapper
		var fieldsWrapper = wrapper.find('[data-fields-wrapper]');

		// Get lists of fields
		var fieldsContent = fieldsWrapper.find('[data-fields]');

		if (fieldsContent.children().length > 0) {
			var clickedNewField = fieldsContent.find(self.currentNewField);
			var action = clickedNewField.data('action');

			clickedNewField[action](field);
		} else {
			self.currentNewField = self.getNewFieldButton().attr('data-action', 'after');

			fieldsContent.append(self.currentNewField);
			fieldsContent.append(field);
			fieldsContent.append(self.getNewFieldButton().attr('data-action', 'before'));
		}

		// Remove empty fields wrapper empty state
		fieldsWrapper.removeClass('is-empty');

		// Update field ordering
		self.updateOrdering(fieldsContent.children());

		// Re-initialize sortable
		self.initFieldsSortable();

		// Update field browser
		if (newField) {

			// self.closeDialog();
			// Tell the user that the field is added to the form
			self.setMessage(false, 'info', self.fieldBrowser());

			self.updateFieldBrowser(element.data('id'), 'add');
		}

		// Scroll to the field
		$.scrollTo(field.offset().top - 250, 200);

		// Add highlight effects of the newly added fields for a few seconds
		field.addClass('is-new');
		setTimeout(function() {
			field.removeClass('is-new');
		}, 1500);
	},

	"{fieldBrowserItem} click": function(element, event) {
		self.addField(element);
	},

	"{fieldBrowser} click": function(element, event) {
		if (event.target == element[0]) {
			event.preventDefault();
			event.stopPropagation();

			self.closeDialog();
		}
	},

	"{dialogError} click": function(element, event) {
		if (event.target == element[0]) {
			event.preventDefault();
			event.stopPropagation();

			self.closeDialog();
		}
	},

	"{dialogErrorClose} click": function(element, event) {
		event.preventDefault();
		event.stopPropagation();

		self.closeDialog();
	},

	"{newField} click": function(element, event) {
		self.currentNewField = element;
		self.showFieldBrowser();
	},

	"{fieldBrowserClose} click": function(el, ev) {
		ev.preventDefault();
		ev.stopPropagation();

		self.closeDialog();
	},

	"{fieldsTypeTab} click": function(el, event) {
		self.fieldsTypeTab().removeClass('active');
		el.addClass('active');

		var id = el.attr('id');
		var content = self.fieldTypeContent();

		content.addClass(opts.hiddenClass);

		content.each(function() {
			if ($(this).attr('id') == id) {
				$(this).removeClass(opts.hiddenClass);
			}
		})
	},

	"{editStep} click": function(el, event) {
		self.fieldSettings().html(self.getTemplate('dialog-loader'));

		var stepId = el.parents(self.content.selector).data('id');

		self.fieldsData = [];

		EasySocial.ajax('admin/views/fields/loadStepConfiguration', {
			"id": stepId
		}).done(function(content, values, params) {

			// Check for previous data
			if (self.stepsHtml) {
				$.each(self.stepsHtml, function(key, data) {
					if (data.stepId == stepId) {
						content = data.html;
						return false;
					}
				});
			}

			self.fieldSettings().html(content);

			// Apply multi choices
			self.fieldSettings().find('[data-fields-config-param-choices]').addController('EasySocial.Controller.Config.Choices');

			// Listen to change event on field editor
			$(self.fieldParam.selector).on('change', function(el) {
				self.paramChanged($(this), params);
			});

			// Listen to keyup event on field editor
			$(self.fieldParam.selector).on('keyup', function(ev, el) {
				self.paramChanged($(this), params);
			});
		});
	},

	"{dialogFieldCancel} click": function(el, event) {
		event.preventDefault();
		event.stopPropagation();

		self.fieldSettings().html('');
	},

	storeFields: function(fieldId, stepId, field) {
		self.storeFieldsData(fieldId, field);
	},

	storeSteps: function(stepId, data) {
		self.storeStepsData(stepId, data);
	},

	deleteFields: function(fieldId, stepId) {

		if (opts.fieldsValue[fieldId]) {
			self.storeFieldsData(fieldId);
		}
	},

	deleteStep: function(stepId) {

		// Get the step
		var step = $(self.step.selector + '[data-id=' + stepId + ']');

		// Get nearest step before we delete it
		if (step.prev().length > 0) {
			var nearestStep = step.prev();
		} else {
			var nearestStep = step.next();
		}

		var content = self.getStepContent(stepId);

		// Update field browser for each deleted fields within the step
		$.each(content.find(self.field.selector), function(i, el) {
			self.updateFieldBrowser($(el).data('appid'), 'remove');
		});

		// Determine if the steps was previously stored in the database
		if (self.deletedSteps === undefined) {
			self.deletedSteps = [];
		}

		// Determine if this field was stored in database previously
		if (step.data('isnew') === false) {
			self.deletedSteps.push(stepId);
		}

		// Remove the content and steps
		content.remove();
		step.remove();

		// Hide delete steps icon if there only one step left in the workflow
		if (self.steps().children(self.step.selector).length < 2) {
			self.deleteStepButton().addClass(opts.hiddenClass);
		}

		// Delete all fields within the step
		delete opts.stepsValue[stepId];

		// Switch to nearest step
		nearestStep.trigger('click');

		self.isChanged();
	},

	storeFieldsData: function(fieldId, field) {
		opts.fieldsValue[fieldId] = field;
	},

	storeStepsData: function(stepId, data) {
		opts.stepsValue[stepId] = data;
	},

	getCurrentStep: function() {
		return self.content().not('.' + opts.hiddenClass);
	},

	getStepTab: function(stepId) {
		return $(self.step.selector + '[data-id="' + stepId + '"]');
	},

	getStepContent: function(stepId) {
		return $('[data-content][data-id=' + stepId + ']');
	},

	injectSaveData: function(data) {

		// Empty the saved data first
		self.saveFieldsInput().val('');

		if (data) {
			data = JSON.stringify(data);
			self.saveFieldsInput().val(data);
		}
	},

	updateFieldTitle: function(fieldId, title) {
		if (!title) {
			return;
		}

		var field = self.getField(fieldId);
		field.find('[data-field-item-title]').html(title);
	},

	updateFieldRequired: function(fieldId, required) {
		var field = self.getField(fieldId);
		var fieldRequired = field.find(self.fieldRequiredSymbol.selector);

		field.attr('data-required', required);

		if (required == "1") {
			fieldRequired.removeClass(opts.hiddenClass);
		} else {
			fieldRequired.addClass(opts.hiddenClass);
		}
	},

	updateConditionsUI: function(fieldId, hasCondition) {
		var field = $(self.field.selector + '[data-id="' + fieldId + '"]');

		// Reset the condition ui
		if (!hasCondition || hasCondition == undefined) {
			field.find('[data-field-item-conditional]').addClass(opts.hiddenClass);
			field.removeClass('has-condition');
			return;
		}

		field.find('[data-field-item-conditional]').removeClass(opts.hiddenClass);
		field.addClass('has-condition');
	},

	updateStepHeader: function(stepId, title, description) {
		if (!title && !description) {
			return;
		}

		// Step bar
		var stepBar = $(self.step.selector + '[data-id="' + stepId + '"]');

		// Step header in content
		var stepHeader = $(self.content.selector + '[data-id="' + stepId + '"]');

		if (title) {
			stepBar.find('[data-step-title]').html(title);
			stepHeader.find('[data-step-title]').html(title);
		}

		if (description) {
			stepHeader.find('[data-step-description]').html(description);
		}
	},

	"{dialogStepSave} click": function(el, event) {
		var parent = el.parents('[data-field-dialog]');
		var stepId = parent.data('id');

		var data = [];
		var newTitle = false;
		var newDesc = false;

		if (self.fieldsData) {
			$.each(self.fieldsData, function(key, stepData) {
				var obj = {
					"name": stepData.name,
					"value": stepData.value
				}

				if (stepData.name == 'title') {
					newTitle = stepData.value;
				}

				if (stepData.name == 'description') {
					newDesc = stepData.value;
				}

				data.push(obj);
			});
		}

		var step = {
			stepId : stepId,
			html : parent.wrap('<p/>').parent().html()
		}

		var tmp = self.stepsHtml;
		self.stepsHtml = [];

		// Temporarily store the html of the field configs
		if (tmp) {
			$.each(tmp, function(key, data) {
				if (data.stepId != stepId) {
					self.stepsHtml.push({
						stepId: data.stepId,
						html: data.html
					});
				}
			});
		}

		// Temporarily store the new data
		self.storeSteps(stepId, data);
		self.stepsHtml.push(step);

		self.updateStepHeader(stepId, newTitle, newDesc);

		self.closeDialog()

		self.isChanged();
	},

	"{dialogFieldSave} click": function(el, event) {
		var parent = el.parents('[data-field-dialog]');
		var fieldId = parent.data('id');
		var appId = parent.data('appid');
		var stepId = self.getCurrentStep().data('id');

		var isConditional = parent.find('[data-field-is-conditional]').is(':checked');

		var tmpData = [];
		var data = [];
		var newTitle = false;

		var newDataName = [];

		// Get required data
		var required = self.getField(fieldId).data('required');

		// Check for changed config
		if (self.fieldsData) {
			$.each(self.fieldsData, function(key, fieldData) {

				var obj = {
					"name": fieldData.name,
					"value": fieldData.value
				}

				if (fieldData.name == 'title') {

					// Only set the value if the title is not empty
					if (fieldData.value) {
						newTitle = fieldData.value;
					} else {
						obj = false;
					}
				}

				// Retrieve new required data
				if (fieldData.name == 'required') {
					required = fieldData.value;
				}

				if (obj) {
					data.push(obj);
					newDataName[fieldData.name] = true;
				}
			});
		}

		var field = {
			fieldId : fieldId,
			html : parent.wrap('<p/>').parent().html()
		}

		// Choices configuration
		if (self.fieldsChoices) {
			var choices = [];

			$.each(self.fieldsChoices, function(key, value) {
				var obj = {
					value: value.value
				}

				choices.push({items: obj});
			});

			data.push({choices : choices});
			newDataName['choices'] = true;
		}

		// Emptied the conditional data directly
		if (!isConditional) {
			self.fieldsConditions = [];

			// Push empty data
			data.push({conditions: false});
		}

		// conditions params
		if (self.fieldsConditions) {
			var conditions = [];

			$.each(self.fieldsConditions, function(key, value) {
				var obj = {
					value: value.value
				}

				data.push({conditions: obj});
				newDataName['conditions'] = true;
			});
		}

		if (opts.fieldsValue[fieldId]) {
			$.each(opts.fieldsValue[fieldId], function(key, value) {
				if (value.name !== undefined && !newDataName[value.name]) {
					data.push({
						name: value.name,
						value: value.value
					})
				}

				if (value.choices && !newDataName['choices']) {
					data.push({
						choices: value.choices
					})
				}

				if (value.conditions && !newDataName['conditions']) {
					data.push({
						conditions: value.conditions
					})
				}
			})
		}

		// Temporarily store the new data
		self.storeFields(fieldId, stepId, data);

		self.updateFieldTitle(fieldId, newTitle);

		self.updateFieldRequired(fieldId, required);

		self.updateConditionsUI(fieldId, isConditional);

		self.closeDialog();

		self.isChanged();
	},

	"{dialogFieldTab} click": function(el, event) {
		var tabId = el.data('id');

		self.dialogFieldTab().removeClass('active');
		el.addClass('active');

		self.dialogFieldContent().addClass(opts.hiddenClass);
		self.dialogFieldContent().filter('[data-id="' + tabId + '"]').removeClass(opts.hiddenClass);
	},

	"{deleteStepButton} click": function(el, event) {

		var content = el.parents(self.content.selector);
		var stepId = content.data('id');

		// Directly delete the step if its empty
		if (self.isStepEmpty(stepId)) {
			self.deleteStep(stepId);
			return true;
		}

		// Display warning dialog
		var warningDialog = self.getTemplate('dialog-delete-step');

		self.fieldSettings().html(warningDialog.wrap('<p/>').parent().html());
		warningDialog.unwrap();
		var confirmDelete = self.fieldSettings().find('[data-dialog-confirm]');

		confirmDelete.on('click', function(event) {
			self.deleteStep(stepId);
			self.closeDialog();
		});
	},

	"{editField} click": function(el, event) {
		var field = el.parents(self.field.selector);
		var appId = field.data('appid');
		var fieldId = field.data('id');

		var previousData = opts.fieldsValue[fieldId];

		// Get available fields for conditional fields
		var availableFields = self.getAvailableFields();

		var appParams = [];

		// Re-initialize the tmp storage
		self.fieldsData = [];
		self.fieldsConditions = [];
		self.fieldsChoices = [];

		self.fieldSettings().html(self.getTemplate('dialog-loader'));

		EasySocial.ajax('admin/views/fields/loadFieldConfiguration', {
			"appId" : appId,
			"fieldId" : fieldId,
			"previousData" : previousData,
			"availableFields" : availableFields
		}).done(function(content, values, params) {

			$.each(params, function(i, property) {
				$.each(property.fields, function(name, field) {

					if (field.subfields) {
						$.each(field.subfields, function(subname, subfield) {
							appParams[name + '_' + subname] = subfield;
						});
					} else {
						appParams[name] = field;
					}
				});
			});

			self.fieldSettings().html(content);

			// Apply multi choices
			self.fieldSettings().find('[data-fields-config-param-choices]').addController('EasySocial.Controller.Config.Choices');

			// Listen to change event on field editor
			$(self.fieldParam.selector).on('change', function(el) {
				self.paramChanged($(this), appParams);
			});

			// Listen to keyup event on field editor
			$(self.fieldParam.selector).on('keyup', function(ev, el) {
				self.paramChanged($(this), appParams);
			});

			// Conditional checkbox
			self.fieldSettings().find('[data-field-is-conditional]').on('change', function(ev, el) {
				self.toggleConditional($(this));
			});

			// Listen to change event in conditional params
			$(self.conditionalParam.selector).on('keyup', function(ev, el) {
				self.conditionalChanged($(this), appParams);
			});

			// Listen to change event in conditional params
			$(self.conditionalParam.selector).on('change', function(ev, el) {
				self.conditionalChanged($(this), appParams);
			});

			// Trigger onChange once to sync the data
			$(self.fieldParam.selector).trigger('change');
		});
	},

	toggleConditional: function(element) {
		var checked = element.is(':checked');
		var conditionalTab = self.fieldSettings().find('[data-field-tab][data-id="conditional-rule"]');
		var conditionalPage = self.fieldSettings().find('[data-field-content][data-id="conditional-rule"]');

		if (checked) {
			conditionalTab.removeClass(opts.hiddenClass);
		} else {
			conditionalTab.addClass(opts.hiddenClass);
			conditionalPage.addClass(opts.hiddenClass);

			// Switch to first tab
			self.fieldSettings().find('[data-field-tab]').first().trigger('click');
		}
	},

	conditionalChanged: function(element) {
		var name = element.data('name');
		var parent = element.parents('[data-field-dialog]');
		var value = self.getConditionsValue(name, parent);

		var obj = {
			"name": name,
			"value": value
		}

		var tmpData = self.fieldsConditions;

		self.fieldsConditions = [];

		// Check for existing data and re-assign it back
		if (tmpData) {
			$.each(tmpData, function(key, value) {

				// Old data exists, skip this since we will overwrite it later
				if (value.name == name) {
					return true;
				} else {
					self.fieldsConditions.push({
						"name": value.name,
						"value": value.value
					});
				}
			});
		}

		// Push new data
		self.fieldsConditions.push(obj);
	},

	paramChanged: function(element, appParams) {
		var name = element.data('name');
		var parent = element.parents('[data-field-dialog]');
		var value = self.getConfigValue(name, appParams, parent);
		var field = appParams[name];

		// Manually convert boolean field into boolean value for toggle to work properly
		if (field.type === 'boolean') {
			value = element.is(':checked') ? "1" : "0";
		}

		if (field.type == 'choices') {
			return self.onChoicesChanged(element, appParams);
		}

		var obj = {
			"name": name,
			"value": value
		}

		var tmpData = self.fieldsData;

		self.fieldsData = [];

		// Check for existing data and re-assign it back
		if (tmpData) {
			$.each(tmpData, function(key, value) {

				// Old data exists, skip this since we will overwrite it later
				if (value.name == name) {
					return true;
				} else {
					self.fieldsData.push({
						"name": value.name,
						"value": value.value
					});
				}
			});
		}

		// Push new data
		self.fieldsData.push(obj);
	},

	getConditionsValue: function(name, parent) {
		var element = parent.find(self.conditionalParam.selector).filterBy('name', name);

		if (element.length === 0) {
			return undefined;
		}

		values = [];

		$.each(element.find('li'), function(i, choice) {
			choice = $(choice);
			var field = choice.find('[data-fields-condition-param-choice-field]'),
				operator = choice.find('[data-fields-condition-param-choice-operator]'),
				value = choice.find('[data-fields-condition-param-choice-value]');

			values.push({
				'id': choice.data('id'),
				'field': field.val(),
				'operator': operator.val(),
				'value': value.val()
			});

			field.attr('value', field.val());
			operator.attr('value', operator.val());
			value.attr('value', value.val());
		});

		return values;
	},

	getConfigValue: function(name, appParams, parent) {
		var field = appParams[name];
		var element = parent.find(self.fieldParam.selector).filterBy('name', name);

		if (element.length === 0) {
			return undefined;
		}

		var values = '';

		// console.log(appParams);

		switch (field.type) {
			
			case 'choices':
				values = [];

				$.each(element.find('li'), function(i, choice) {
					choice = $(choice);

					var titleField = choice.find('[data-fields-config-param-choice-title]'),
						valueField = choice.find('[data-fields-config-param-choice-value]'),
						defaultField = choice.find('[data-fields-config-param-choice-default]');

					values.push({
						'id': choice.data('id'),
						'title': titleField.val(),
						'value': valueField.val(),
						'default': defaultField.val()
					});

					titleField.attr('value', titleField.val());
					valueField.attr('value', valueField.val());
					defaultField.attr('value', defaultField.val());
				});
			break;

			case 'boolean':
				values = element.is(':checked') ? 1 : 0;

				element.attr('value', values);
			break;

			case 'checkbox':
				values = [];
				$.each(field.option, function(k, option) {
					var checkbox = element.filter('[data-fields-config-param-option-' + option.value + ']');

					if(checkbox.length > 0 && checkbox.is(':checked')) {
						values.push(option.value);

						checkbox.attr('checked', 'checked');
					} else {
						checkbox.removeAttr('checked');
					}
				});
			break;

			case 'list':
			case 'select':
			case 'dropdown':
				values = element.length > 0 ? element.val() : field["default"] || '';

				element.find('option').prop('selected', false);

				element.find('option[value="' + values + '"]').prop('selected', true);
			break;

			case 'input':
		case 'text':
			default:
				values = element.length > 0 ? element.val() : field["default"] || '';

				element.attr('value', values);
			break;
		}

		return values;
	},

	onChoicesChanged: function(element, appParams) {
		var name = element.data('name');
		var parent = element.parents('[data-field-dialog]');
		var value = self.getConfigValue(name, appParams, parent);
		var field = appParams[name];

		var obj = {
			"name": name,
			"value": value
		}

		var tmpData = self.fieldsChoices;

		self.fieldsChoices = [];

		// Check for existing data and re-assign it back
		if (tmpData) {
			$.each(tmpData, function(key, value) {

				// Old data exists, skip this since we will overwrite it later
				if (value.name == name) {
					return true;
				} else {
					self.fieldsChoices.push({
						"name": value.name,
						"value": value.value
					});
				}
			});
		}

		// Push new data
		self.fieldsChoices.push(obj);
	},

	"{moveField} click": function(el, event) {
		var field = el.parents(self.field.selector);
		var moveDialog = self.getTemplate('dialog-move-field');

		// Get available steps in this workflow
		var steps = self.step();
		var selectionContainer = moveDialog.find('[data-move-selection]');

		if (steps.length > 1) {
			$.each(steps, function(key, step){
				var id = $(this).data('id');

				if (id) {
					var selectHtml = '<option value="' + id + '">' + $(this).find('[data-step-title]').html() + '</option>';
					selectionContainer.append(selectHtml);
				}
			});

			moveDialog.find('[data-field-available]').removeClass(opts.hiddenClass);
		} else {
			moveDialog.find('[data-field-unavailable]').removeClass(opts.hiddenClass);
		}

		self.fieldSettings().html(moveDialog.wrap('<p/>').parent().html());
		moveDialog.unwrap();
		var confirmMove = self.fieldSettings().find('[data-dialog-move]');

		confirmMove.on('click', function() {
			var container = $(this).parents('[data-dialog-move-field]');
			var stepId = container.find('[data-move-selection]').val()

			// Get steps
			var newStep = self.getStepContent(stepId);

			// Clone the field
			var fieldClone = field.clone();

			// Remove the existing field from current step
			self.removeField(field.data('id'), true);

			self.currentNewField = '[data-field-new][data-action="before"]';

			// Re-add the field to the new step
			self.addField(false, fieldClone, newStep);

			// Switch to step page
			self.getStepTab(stepId).trigger('click');

			self.closeDialog();
		});
	},

	"{deleteField} click": function(el, event) {
		var field = el.parents(self.field.selector);
		var fieldId = field.data('id');

		self.removeField(fieldId);
	},

	removeField: function(fieldId, preserveData) {
		var field = self.getField(fieldId);
		var parent = field.parent();
		var wrapper = parent.parent();

		// Remove the field
		field.remove();

		// Determine if the steps already empty
		// Length set to 2 because there are 2 "add" button left in the wrapper
		if (parent.children().length < 3) {

			// Steps is empty
			wrapper.addClass('is-empty');
			parent.html('');
		} else {

			// There are still some fields left. Let's re-calculate the ordering
			self.updateOrdering(parent.children());
		}

		if (!preserveData || preserveData == undefined) {
			if (self.deletedFields === undefined) {
				self.deletedFields = [];
			}

			// Determine if this field was stored in database previously
			if (field.data('isnew') === false) {
				self.deletedFields.push(fieldId);
			}

			// Delete field from cache if there any
			self.deleteFields(fieldId, self.getCurrentStep().data('id'));

			// Update field browser
			self.updateFieldBrowser(field.data('appid'), 'remove');
		}
	}
}});

module.resolve();
});
});