EasySocial.module('admin/grid/grid' , function($) {

var module = this;

EasySocial.require()
.script('admin/grid/sort')
.done(function($) {

EasySocial.Controller('Grid', {
	defaultOptions : {
		"{sortColumns}": "[data-table-grid-sort]",
		"{ordering}": "[data-table-grid-ordering]",
		"{saveorder}": "[data-table-grid-saveorder]",
		"{direction}": "[data-table-grid-direction]",

		"{task}": "[data-table-grid-task]",

		"{searchInput}": "[data-table-grid-search-input]",
		"{search}": "[data-table-grid-search]",
		"{resetSearch}" : "[data-table-grid-search-reset]",

		"{checkAll}": "[data-table-grid-checkall]",
		"{checkboxes}": "input[type=checkbox][data-table-grid-id]",

		"{publishItems}": "[data-table-grid-publishing]",
		"{itemRow}": "tr",
		"{boxChecked}": "[data-table-grid-box-checked]",
		"{filters}": "[data-table-grid-filter]"
	}
}, function(self, opts) { return {

	init : function() {
		// Implement sortable items.
		self.implementSortable();
	},

	"{filters} change" : function() {
		// Always reset the task before submitting.
		self.setTask('');

		self.submitForm();
	},

	"{search} click" : function() {
		self.submitForm();
	},

	"{saveorder} click" : function() {
		self.setTask('saveorder');

		// check all checkbox.
		self.checkAll().click();
		self.submitForm();
	},

	"{resetSearch} click" : function(el, event) {
		self.searchInput().val('');
		self.submitForm();
	},

	submitForm: function() {

		// Allow page to hook into this event
		self.trigger('beforeSubmitForm', [self.task().val()]);

		self.element.submit();
	},

	setTask: function(task) {
		self.task().val( task );
	},

	setOrdering: function(ordering) {
		self.ordering().val( ordering );
	},

	setDirection: function(direction) {
		self.direction().val( direction );
	},

	updateBoxChecked: function() {
		var total = self.checkboxes(':checked').length;
		
		self.boxChecked().val(total);
	},

	toggleSelectRow: function(row) {
		var checkbox = $(row.find('input[name=cid\\[\\]]'));
		var checked = checkbox.prop('checked') == true;

		if (checked) {
			checkbox.prop('checked', false);
			return;
		}

		checkbox.prop('checked', true);
		return;
	},

	selectRow: function(row) {
		var checkbox 	= row.find( 'input[name=cid\\[\\]]' );

		$( checkbox ).prop( 'checked' , true );
	},

	implementSortable: function() {
		self.sortColumns().implement(EasySocial.Controller.Grid.Sort, {
			"{parent}" 	: self
		});
	},

	"{checkboxes} click": function(checkbox, event) {
		event.stopPropagation();
	},

	"{itemRow} click": function(row, event) {
		var checkbox = row.find(self.checkboxes.selector);

		checkbox.prop("checked", !checkbox.is(':checked'))
			.trigger('change');
	},

	"{checkboxes} change": function(checkbox, event) {
		var checked = checkbox.is(':checked');
		var row = checkbox.closest(self.itemRow.selector);

		// Get a list of checked items
		var total = self.checkboxes().is(':checked').length;

		self.updateBoxChecked();

		row.toggleClass('is-checked', checked);
	},

	"{checkAll} change": function(element, event) {

		// Find all checkboxes in the grid.
		self.checkboxes()
			.prop('checked', element.is(':checked'))
			.trigger('change');

		// Update the total number of checkboxes checked.
		var total = element.is(':checked') ? self.checkboxes().length : 0;

		self.updateBoxChecked();
	},

	//
	// Publish buttons
	//
	"{publishItems} click": function(element, event) {
		var row = element.parents(self.itemRow.selector);
		var task = element.data('task');

		self.selectRow(row);
		self.setTask(task);
		self.submitForm();
	}
}});

module.resolve();
});


});
