EasySocial.module('apps/fields/event/startend/content', function($) {
	
var module = this;
var lang = EasySocial.options.momentLang;

EasySocial
.require()
.library('datetimepicker', 'moment/' + lang, 'chosen')
.done(function($) {

EasySocial.Controller('Field.Event.Startend', {
	defaultOptions: {
		dateFormat: '',
		allowTime: true,
		allowTimezone: true,
		disallowPast: false,
		minuteStepping: 15,
		yearfrom: '',
		yearto: '',
		requiredEnd: false,
		allday: false,
		calendarLanguage: 'english',
		dow: 0,

		'{field}': '[data-field-startend]',
		'{startForm}': '[data-event-start]',
		'{endForm}': '[data-event-end]',
		'{timezone}': '[data-event-timezone]'
	}
}, function(self, opts, base) { return {
	init: function() {

		var data = self.field().htmlData();

		opts.error = data.error || {};

		// There is an issue with yearto where if I set yearto = 2014, I won't be able to select 2014 dates. 
		// This is a bug in datetimepicker. Currently, temporarily, we manually add 1 to the value if there are value set.
		if (!$.isEmpty(self.options.yearto)) {
			self.options.yearto = parseInt(self.options.yearto) + 1;
		} else {
			self.options.yearto = new Date().getFullYear() + 100
		}

		self.options.yearfrom = self.options.yearfrom || 1930;

		// Add controller on the start date
		self.startDatetime = self.startForm().addController('EasySocial.Controller.Field.Event.Startend.Form', {
			'{parent}': self,
			"type": 'start'
		});

		// Add controller on the end date
		self.endDatetime = self.endForm().addController('EasySocial.Controller.Field.Event.Startend.Form', {
			'{parent}': self,
			"type": 'end'
		});

		// Apply chosen on timezone field
		if (self.timezone().length > 0) {
			self.timezone().chosen({
				allow_single_deselect: true,
				search_contains: true
			});
		}
	},

	'{self} onSubmit': function(el, ev, register) {
		register.push(self.validateInput());
	},

	validateInput: function() {
		self.clearError();

		if ($.isEmpty(self.startDatetime.datetime().val())) {
			self.raiseError(opts.error.start.required);

			return false;
		}

		if (self.options.requireEnd && $.isEmpty(self.endDatetime.datetime().val())) {
			self.raiseError(opts.error.end.required);

			return false;
		}

		return true;
	},

	raiseError: function(msg) {
		self.trigger('error', [msg]);
	},

	clearError: function() {
		self.trigger('clear');
	}
}});

EasySocial.Controller('Field.Event.Startend.Form', {
	defaultOptions: {
		type: null,

		'{picker}': '[data-picker]',
		'{toggle}': '[data-picker-toggle]',
		'{datetime}': '[data-datetime]'
	}
}, function(self, options) {

	return {
		init: function() {
			self.load();
		},

		"{window} easysocial.fields.startend.start.change": function() {

			// When the start date is changed, set the minimum date on the end date
			if (options.type == 'start' && self.parent.endDatetime) {
				self.parent.endDatetime.datetimepicker('destroy');

				self.parent.endDatetime.load();
			}
		},


		'{window} easysocial.fields.allday.change': function(el, ev, value) {
			self.datetimepicker('destroy');

			self.parent.options.allday = value == 1 ? true : false;

			self.load();
		},

		// We move this here because there is a possibility that we want to "reinit"
		load: function() {

			// Generate a minimum date from momentjs
			var minDate = new $.moment();

			// If configured to disallow past dates, we need to minus 1 on the date as we need to allow today.
			if (self.parent.options.disallowPast) {
				minDate.date(minDate.date() - 1);
			} else {
				minDate.year(self.parent.options.yearfrom);
			}

			// If this type is end date, we need to set the minimum date based on the start date
			if (options.type == 'end') {
				var startDatetimeValue = self.parent.startDatetime.datetime().val();

				if (startDatetimeValue) {
					var minDate = $.moment(startDatetimeValue);
					
					// minus 1 on the date as we need to allow today.
					var minDate = minDate.date(minDate.date() - 1);
				}
			}

			var allowTime = self.parent.options.allowTime && !self.parent.options.allday;
			var dateFormat = self.parent.options.dateFormat;

			// If time is not allowed, then we remove the time part
			// Since the format is always (10 chars) (remaining chars)
			// We just substr by 10 chars
			if (!allowTime) {
				dateFormat = dateFormat.substr(0, 10);
			}

			self.picker()._datetimepicker({
				component: "es",
				useCurrent: false,
				format: dateFormat,
				minDate: minDate,
				maxDate: new $.moment({y: self.parent.options.yearto}),
				icons: {
					time: 'far fa-clock',
					date: 'fa fa-calendar',
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down'
				},
				sideBySide: false,
				pickTime: allowTime,
				minuteStepping: parseInt(self.parent.options.minuteStepping),
				language: self.parent.options.calendarLanguage == 'english' ? 'en-gb' : lang,
				dow: self.parent.options.dow
			});

			var date = self.datetime().val();

			// Datetimepicker is using moment.js, hence here we manually create a moment object to pass in instead of passing in date time string
			// This is because datetimepicker.setDate function passes along the format from self.options.calendarDateFormat to generate the date object, which will render moment.js to generate an invalid dateobject
			// self.options.calendarDateFormat is only for display purposes
			// Raw date object is always in SQL format
			if (!$.isEmpty(date)) {
				var dateObj = $.moment(date);

				self.datetimepicker('setDate', dateObj);
			}

			if (options.type == 'end') {
				var startDatetimeValue = self.parent.startDatetime.datetime().val();

				if (startDatetimeValue) {
					var startDateObj = $.moment(startDatetimeValue);

					if (!$.isEmpty(date)) {
						var endDateObj = $.moment(date);

						// If start date is greated than current end date, adjust it accordingly
						if (startDateObj.unix() > endDateObj.unix()) {
							self.datetimepicker('setDate', startDateObj);
						}

					} else {
						// Simulate jump to date based on start date
						self.datetimepicker('setDate', startDateObj);

						// Empty the value again since initially there are no value set
						self.datetimepicker('setDate', '');
						self.datetime().val('');
					}
				}
			}
		},

		datetimepicker: function(name, value) {
			return self.picker().data('DateTimePicker')[name](value);
		},

		'{toggle} click': function() {
			self.picker().focus();
		},

		'{picker} dp.change': function(el, ev) {

			self.setDateValue(ev.date.toDate());

			// easysocial.fields.startend.start.change
			// easysocial.fields.startend.end.change
			$(window).trigger('easysocial.fields.startend.' + options.type + '.change', [ev.date]);
		},

		'{picker} change': function(el, ev) {
			if ($.isEmpty(el.val())) {
				self.datetime().val('');
			}
		},

		setDateValue: function(date) {
			// Convert the date object into sql format and set it into the input
			self.datetime().val(date.getFullYear() + '-' +
								('00' + (date.getMonth()+1)).slice(-2) + '-' +
								('00' + date.getDate()).slice(-2) + ' ' +
								('00' + date.getHours()).slice(-2) + ':' +
								('00' + date.getMinutes()).slice(-2) + ':' +
								('00' + date.getSeconds()).slice(-2));
		}
}});

module.resolve();
});
});
