EasySocial.module( 'site/search/advanced' , function(){

var module	= this;

var lang = EasySocial.options.momentLang;

EasySocial.require()
.library('datetimepicker', 'moment/' + lang)
.script('site/search/map', 'site/search/osm')
.done(function($) {

EasySocial.Controller('Search.Advanced', {
	defaultOptions: {

		// Results
		"{result}": "[data-result]",

		// Criteria item
		"{criterias}": "[data-criterias]",
		"{item}": "[data-item]",
		"{criteriaTemplate}": "[data-item-template]",

		// Actions
		"{addCriteria}": "[data-insert-criteria]",
		"{editCriteria}": "[data-edit-criteria]",
		"{saveFilter}": "[data-save-filter]",
		"{deleteFilter}" : "[data-delete-filter]",

		// Wrapper
		"{contents}": "[data-contents]",
		"{searchForm}": "[data-search-form]",

		// Sidebar
		"{sidebar}": "[data-sidebar]",
		"{filterItem}": "[data-filter-item]",

		// Pagination
		"{pagination}": "[data-pagination]"
	}
}, function(self, opts, base) { return {

	init: function() {
		// Duplicate the template
		opts.tmpl = self.criteriaTemplate().clone();

		self.item()
			.addController(EasySocial.Controller.Search.Advanced.Criteria, {"{parent}" : self});

	},

	getSerializedForm: function() {
		var form = self.searchForm().find('> form');
		var data = form.serializeJSON();

		return data;
	},

	setActiveFilter: function(filter) {
		self.filterItem().removeClass('active');
		filter.addClass('active');
	},

	updatingContents: function() {
		self.contents().empty();
		self.element.addClass("is-loading");
	},

	updateContents: function(contents) {
		self.element.removeClass("is-loading");
		self.contents().html(contents);
	},

	loadMore: function() {

		var next_limit = self.pagination().data('last-limit');
		var data = self.getSerializedForm();

		if (next_limit == '-1') {
			self.pagination().hide();
			return;
		}

		self.pagination().addClass('is-loading');

		EasySocial.ajax('site/controllers/search/loadmore', {
			"data" : data,
			"nextlimit" : next_limit
		}).done(function(contents, next_limit) {

			// update next last-update
			self.pagination().data('last-limit', next_limit);

			// Append result to the list
			self.result().append(contents);

			if (next_limit == '-1') {
				self.pagination().hide();
			}

			self.pagination().removeClass('is-loading');
		});
	},

	"{pagination} click": function(){
		self.loadMore();
	},

	"{filterItem} click" : function(item, event) {
		event.stopPropagation();
		event.preventDefault();

		self.setActiveFilter(item);

		var id = item.data('id');

		// Update the url
		var anchor = item.find('> a');
		anchor.route();

		self.updatingContents();

		EasySocial.ajax('site/controllers/search/getFilterResults', {
			"id": id,
		}).done(function(contents) {

			var contents = $.buildHTML(contents);

			contents.find(self.item.selector)
				.addController(EasySocial.Controller.Search.Advanced.Criteria, {
					"{parent}" : self.root
				});

			self.updateContents(contents);
		});
	},

	"{deleteFilter} click": function(button, event) {
		var id = button.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax( 'site/views/search/confirmDeleteFilter', {"id": id})
		});

	},

	"{saveFilter} click" : function() {
		var data = self.searchForm().find('form').serializeJSON();

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/search/confirmSaveFilter', {
				"type": opts.type,
				"data": data
			})
		});
	},

	"{editCriteria} click": function(button, event) {
		button.addClass('t-hidden');

		self.searchForm().removeClass('t-hidden');
	},

	"{addCriteria} click" : function(button, event) {
		var template = opts.tmpl.clone();

		// Remove any unecessary attributes for the template
		template
			.removeClass('t-hidden')
			.removeAttr('data-item-template')
			.addController(
				EasySocial.Controller.Search.Advanced.Criteria, {
					"{parent}" : self
				});

		// Append the template to the list now.
		self.criterias().append(template);
	}
}});



EasySocial.Controller('Search.Advanced.Criteria', {
	defaultOptions: {

		// Actions
		"{remove}": "[data-remove-criteria]",

		// Wrappers
		"{operatorWrapper}": "[data-wrapper-operator]",
		"{conditionWrapper}": "[data-wrapper-condition]",
		"{keysWrapper}": "[data-wrapper-keys]",

		// Conditions
		"{field}": "[data-field]",
		"{operator}": "[data-operator]",
		"{condition}": "[data-condition]",
		"{key}": "[data-key]",

		"{itemConditionDiv}": "[data-itemConditionDiv]",

		"{dateStart}": "[data-start]",
		"{dateEnd}": "[data-end]",
		"{dataCondition}": "[data-condition]",

		'{frmDistance}': '[data-distance]',
		'{frmAddress}': '[data-address]',
		'{frmLatitude}': '[data-latitude]',
		'{frmLongitude}': '[data-longitude]',
		'{osmMap}': '[data-osm-map]',

		"{locationLabel}": "[data-location-label]",
		"{textField}": "[data-location-textfield]",

		// to support 3rd party custom fields that has range condition
		"{itemStart}": "[data-item-start]",
		"{itemEnd}": "[data-item-end]"
	}
}, function(self, opts, base) { return {

	init : function() {


		if (self.osmMap().length > 0) {
			self.element.addController(EasySocial.Controller.Search.Osm);
		} else {
			self.element.addController(EasySocial.Controller.Search.Map);
		}

		if (self.frmAddress().val() != undefined && self.frmAddress().val() != '') {
			self.textField().val(self.frmAddress().val());
			self.locationLabel().removeClass('t-hidden');
		}

		opts.searchType = $('input[name="type"]').val();
	},

	getFieldData: function(value) {

		var data = value.split('|');

		return {
			"key": data[0],
			"type": data[1]
		};
	},

	getConditions : function(type, operation, datakey) {

		// lets destroy all the datetimpicker here.
		self.conditionWrapper().find('.datepicker').each(function(idx, el) {

			var datepicker = $(el).data('DateTimePicker');
			if (datepicker != undefined) {
				datepicker.destroy();
			}
		});

		EasySocial.ajax("site/controllers/search/getConditions", {
			"type": opts.searchType,
			"element": type,
			"operator": operation,
			"datakey": datakey
		}).done(function(condition) {
			self.condition().replaceWith(condition);

			// start date and end date
			var start = self.conditionWrapper().find('[data-start]');
			var end = self.conditionWrapper().find('[data-end]');

			if (type != 'numeric' && datakey != 'age' && start.length > 0) {
				self.attachDateTimePicker(start);
				self.attachDateTimePicker(end);
			}

			// console.log(self.dataCondition());

			//single date
			var singleDate = self.dataCondition().find('[data-isdate]');
			if (type != 'numeric' && datakey != 'age' && singleDate.length > 0) {
				self.attachDateTimePicker(singleDate);
			}
		});
	},

	attachDateTimePicker : function(element) {

		// do not limit the min date in date field.
		var minDate = new $.moment({ y: 1900 });

		element._datetimepicker({
			component: "es",
			useCurrent: false,
			format: "DD-MM-YYYY",
			minDate: minDate,
			sideBySide: false,
			pickTime: false,
			minuteStepping: 1,
			language: lang,
			icons: {
				time: 'far fa-clock',
				date: 'fa fa-calendar',
				up: 'fa fa-chevron-up',
				down: 'fa fa-chevron-down'
			}
		});

	},

	"{frmDistance} change" : function() {

		var distance = self.frmDistance().val();
		var address = self.frmAddress().val();
		var lat = self.frmLatitude().val();
		var lng = self.frmLongitude().val();

		var computedVal = distance + '|' + lat + '|' + lng + '|' + address;
		self.dataCondition().val(computedVal);
	},

	"{itemStart} change" : function( el ) {
		start 	= self.itemStart().val();
		end 	= self.itemEnd().val();

		var data = start;

		if (end.length > 0) {
			data = data + '|' + end;
		}

		if (data == '|') {
			data = '';
		}

		// update value
		self.dataCondition().val( data );
	},

	"{itemEnd} change" : function() {
		start 	= self.itemStart().val();
		end 	= self.itemEnd().val();

		var data = start;
		data = data + '|' + end;

		if (data == '|') {
			data = '';
		}

		// update value
		self.dataCondition().val( data );
	},

	"{dateStart} change" : function( el ) {
		start 	= self.dateStart().val();
		end 	= self.dateEnd().val();


		var data = start;

		if( end.length > 0 )
		{
			data = data + '|' + end;
		}

		// update value
		self.dataCondition().val( data );
	},

	"{dateEnd} change" : function() {
		start 	= self.dateStart().val();
		end 	= self.dateEnd().val();

		var data = start;
		data = data + '|' + end;

		// update value
		self.dataCondition().val( data );
	},

	"{field} change" : function(field, event) {
		var value = field.val();

		if (!value || value == '') {
			return;
		}

		var data = self.getFieldData(value);

		EasySocial.ajax("site/controllers/search/getCriteria", {
			"type": opts.searchType,
			"key": data.key,
			"element": data.type
		}).done(function(hasKey, datakeys, operators, conditions) {

			if (data.type != 'address') {
				self.locationLabel().addClass('t-hidden');
			}

			// Insert the new operators
			self.operatorWrapper().html(operators);

			// Adding new operators keys
			self.keysWrapper()
				.html(datakeys)
				.addClass('t-hidden');

			if (hasKey) {
				self.keysWrapper()
					.removeClass('t-hidden');
			}

			// Insert conditions
			self.conditionWrapper().html(conditions);
		});
	},

	"{operator} change" : function(dropdown, event) {

		var field = self.field();
		var datakey = self.key();
		var operator = self.operator();
		var data = field.val().split('|');
		var key = data[0];
		var type = data[1];

		var operation = operator.val();
		var datakey = datakey.val();

		if (operation == 'blank' || operation == 'notblank') {
			self.condition().hide();

			return;
		}

		var dateTypes = ['datetime', 'birthday', 'startend', 'joomla_lastlogin', 'joomla_joindate'];

		if ($.inArray(type, dateTypes)) {

			if (operation == 'between' || self.dateStart().length > 0) {
				self.getConditions(type, operation, datakey);
			}
		}

		// if this is true, this mean the operator is not meant for range condition. lets get the correct condition again.
		if (self.condition().is('[data-range]')) {
			self.getConditions(type, operation, datakey);
		}

		if (self.condition().is(":hidden") || self.condition().hasClass('t-hidden')) {

			self.condition().show();
			self.condition().removeClass('t-hidden')
		}
	},

	"{key} change" : function(dropdown, event) {
		var field = self.field();
		var data = self.getFieldData(field.val());
		var datakey = dropdown.val();

		EasySocial.ajax("site/controllers/search/getOperators", {
			"type": opts.searchType,
			"key": data.key,
			"element": data.type,
			"datakey" : datakey
		}).done(function(operators, conditions) {

			self.operator().replaceWith(operators);
			self.condition().replaceWith(conditions);

			if (datakey == 'distance') {
				self.locationLabel().removeClass('t-hidden');
			} else {
				self.locationLabel().addClass('t-hidden');
			}

			if (datakey == 'age' || datakey == 'date' || datakey == 'numeric') {
				// start date and end date
				var start = self.conditionWrapper().find('[data-start]');
				var end = self.conditionWrapper().find('[data-end]');

				if (datakey != 'numeric' && datakey != 'age' && start.length > 0) {
					self.attachDateTimePicker(start);
					self.attachDateTimePicker(end);
				}

				//single date
				var singleDate = self.dataCondition().find('[data-condition]');

				if (datakey != 'numeric' && datakey != 'age' && singleDate.length > 0) {
					self.attachDateTimePicker(singleDate);
				}
			}

		});
	},

	"{remove} click" : function() {
		var criterias = self.parent.criterias();

		// If this is the last item on the list, don't allow
		if (criterias.children().length == 1) {
			return;
		}

		self.element.remove();
	}
}});

module.resolve();
});
});
