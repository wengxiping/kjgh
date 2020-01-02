EasySocial.module('site/events/createRecurring', function($) {
	var module = this;

	EasySocial.Controller('Events.CreateRecurring', {
		defaultOptions: {
			schedule: [],
			totalRecurringEvents: 0,
			eventId: null,

			'{progress}': '[data-progress-bar]',
			'{form}': '[data-form]'
		}
	}, function(self) {
		return {
			init: function() {
				self.start();
			},

			counter: 0,

			start: function() {
				if (self.options.schedule[self.counter] === undefined) {
					return self.completed();
				}

				self.create(self.options.schedule[self.counter])
					.done(function() {
						self.counter++;

						var percentage = Math.ceil((self.counter / self.options.schedule.length) * 100);

						self.progress().css({
							width: percentage + '%'
						});

						self.start();
					})
					.fail(function(msg) {
						console.log(msg);
					});
			},

			create: function(datetime) {

				var isLastRecurringEvent = 0;

				// determine which recurring event process now
				var currentRecurringCounter = self.counter + 1;

				// determine if this recurring proceed the last event 
				if (currentRecurringCounter == self.options.totalRecurringEvents) {
					var isLastRecurringEvent = 1;
				}

				return EasySocial.ajax('site/controllers/events/createRecurring', {
					eventId: self.options.eventId,
					datetime: datetime,
					isLastRecurringEvent: isLastRecurringEvent
				});
			},

			completed: function() {
				self.progress().parent().removeClass('progress-info').addClass('progress-success');
				self.form().submit();
			}
		}
	})

	module.resolve();
});
