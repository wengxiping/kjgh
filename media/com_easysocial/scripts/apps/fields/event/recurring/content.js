EasySocial.module('apps/fields/event/recurring/content', function($) {

var module = this;
var lang = EasySocial.options.momentLang;

EasySocial.require().library('datetimepicker', 'moment/' + lang).done(function() {

EasySocial.Controller('Field.Event.Recurring', {
	defaultOptions: {
		dateFormat: '',
		id: null,
		value: {},
		allday: 0,
		showWarningMessages: 0,
		eventId: null,
		dow: 0,

		'{type}': '[data-recurring-type]',
		'{endBlock}': '[data-recurring-end-block]',
		'{picker}': '[data-recurring-end-picker]',
		'{toggle}': '[data-recurring-end-toggle]',
		'{result}': '[data-recurring-end-result]',
		'{dailyBlock}': '[data-recurring-daily-block]',
		'{dailyInput}': '[data-recurring-daily-block] input',
		'{summaryBlock}': '[data-recurring-summary-block]',
		"{showSchedules}": "[data-show-schedules]",
		'{scheduleLoadingBlock}': '[data-recurring-schedule-loading-block]',
		'{deleteRecurringButton}': '[data-recurring-delete]'
	}
}, function(self, opts) { return {
	init: function() {
		var dateFormat = self.options.dateFormat;
		var startofweek = self.options.dow;

		self.picker()._datetimepicker({
			pickTime: false,
			component: "es",
			useCurrent: false,
			format: dateFormat,
			language: lang,
			dow: startofweek

		});

		var value = self.result().val();

		if (!$.isEmpty(value)) {
			var dateObj = $.moment(value);

			self.datetimepicker('setDate', dateObj);
		}

		// Calculate total recurring events
		self.calculateRecurringEvents();
	},

	changed: 0,

	'{window} easysocial.fields.allday.change': function(el, ev, value) {
		self.options.allday = value;

		self.calculateRecurringEvents();
	},

	'{window} easysocial.fields.startend.start.change': function(el, ev, date) {
		self.calculateRecurringEvents();
	},

	'{toggle} click': function() {
		self.picker().focus();
	},

	'{picker} dp.change': function(el, ev) {
		self.setDateValue(ev.date.toDate());

		self.detectChanges();

		self.calculateRecurringEvents();
	},

	'{type} change': function(el, ev) {
		var value = el.val();

		self.endBlock()[value === 'none' ? 'hide' : 'show']();

		self.dailyBlock()[value === 'daily' ? 'show': 'hide']();

		self.detectChanges();

		self.calculateRecurringEvents();
	},

	'{dailyInput} change': function(el, ev) {
		self.detectChanges();

		self.calculateRecurringEvents();
	},

	calculateRecurringEvents: function() {
		
		self.summaryBlock().hide();

		self.scheduleLoadingBlock().hide();

		self.clearError();

		var start = $('[data-event-start]').find('[data-datetime]').val();
		var timezone = $('[data-event-timezone]').val();
		var end = self.result().val();
		var type = self.type().val();
		var daily = [];

		if (type == 'none' && !self.options.showWarningMessages) {
			return;
		}

		if ($.isEmpty(start) || $.isEmpty(end) || $.isEmpty(type)) {
			return;
		}

		$.each(self.dailyBlock().find('input'), function(i, input) {
			el = $(input);
			if (el.is(':checked')) {
				daily.push(el.val());
			}
		});

		self.scheduleLoadingBlock().show();

		self.getTotalRecurring({
			"start": start,
			"timezone": timezone,
			"end": end,
			"type": type,
			"daily": daily
		});
	},

	getTotalRecurring: $.debounce(function(options) {
		self.clearError();

		EasySocial.ajax('fields/event/recurring/calculateRecurringEvents', {
			"id": self.options.id,
			"start": options.start,
			"timezone": options.timezone,
			"allday": self.options.allday,
			"end": options.end,
			"type": options.type,
			"daily": options.daily,
			"eventId": self.options.eventId,
			"changed": self.changed,
			"showWarningMessages": self.options.showWarningMessages
		}).done(function(html) {
			self.summaryBlock().html(html).show();
		}).fail(function(msg) {
			self.raiseError(msg);
		}).always(function() {
			self.scheduleLoadingBlock().hide();
		});
	}, 500),

	detectChanges: function() {
		var end = self.result().val(),
			type = self.type().val(),
			daily = [],
			changed = false;

		$.each(self.dailyBlock().find('input'), function(i, input) {
			el = $(input);
			if (el.is(':checked')) {
				daily.push(el.val());
			}
		});

		if (type != self.options.value.type || end != self.options.value.end || daily.length != self.options.value.daily.length) {
			changed = true;
		}

		$.each(daily, function(i, d) {
			if ($.inArray(d, self.options.value.daily) == -1) {
				changed = true;
				return false;
			}
		});

		$.each(self.options.value.daily, function(i, d) {
			if ($.inArray(d, daily) == -1) {
				changed = true;
				return false;
			}
		});

		self.changed = changed ? 1 : 0;

		$(window).trigger('easysocial.fields.recurring.changed', [changed]);
	},

	"{showSchedules} click": function(button, event) {
		
		var start = $('[data-event-start]').find('[data-datetime]').val();
		var timezone = $('[data-event-timezone]').val();
		var end = self.result().val();
		var type = self.type().val();
		var daily = [];

		$.each(self.dailyBlock().find('input'), function(i, input) {
			el = $(input);

			if (el.is(':checked')) {
				daily.push(el.val());
			}
		});

		EasySocial.dialog({
			"content": EasySocial.ajax('fields/event/recurring/getScheduledEvents', {
							"id": self.options.id,
							"start": start,
							"timezone": timezone,
							"allday": self.options.allday,
							"end": end,
							"type": type,
							"daily": daily,
							"eventId": self.options.eventId
					})
		});
	},

	'{deleteRecurringButton} click': function(el, ev) {
		
		EasySocial.dialog({
			content: EasySocial.ajax('site/views/events/deleteRecurringDialog', {
				id: self.options.eventId
			}),
			bindings: {
				"{submitButton} click": function() {
					self.deleteRecurring()
						.done(function() {
							EasySocial.dialog().close();
							self.calculateRecurringEvents();
						});
				}
			}
		})
	},

	deleteRecurring: function() {
		return EasySocial.ajax('site/controllers/events/deleteRecurring', {
			eventId: self.options.eventId
		})
	},

	datetimepicker: function(name, value) {
		return self.picker().data('DateTimePicker')[name](value);
	},

	setDateValue: function(date) {
		// Convert the date object into sql format and set it into the input
		self.result().val(date.getFullYear() + '-' +
							('00' + (date.getMonth()+1)).slice(-2) + '-' +
							('00' + date.getDate()).slice(-2) + ' ' +
							('00' + date.getHours()).slice(-2) + ':' +
							('00' + date.getMinutes()).slice(-2) + ':' +
							('00' + date.getSeconds()).slice(-2));
	},

	'{self} onSubmit': function(el, ev, register) {
		register.push(true);
	},

	raiseError: function(msg) {
		self.trigger('error', [msg]);
	},

	clearError: function() {
		self.trigger('clear');
	}
}});

module.resolve();
});
});
