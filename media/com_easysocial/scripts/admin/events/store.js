EasySocial.module('admin/events/store', function($) {
    var module = this;

    EasySocial.Controller('Events.Update', {
        defaultOptions: {
            postdata: {},
            updateids: [],
            schedule: [],
            eventId: null,
            totalRecurringEvents: 0,
            isNew: 0,

            '{progress}': '[data-progress-bar]',
            '{form}': '[data-form]'
        }
    }, function(self) {
        return {
            init: function() {
                self.startUpdate();
            },

            updateCounter: 0,
            createCounter: 0,
            isNewEvent: 0,

            updateProgressBar: function() {
                var percentage = Math.ceil(((self.updateCounter + self.createCounter) / (self.options.updateids.length + self.options.schedule.length)) * 100);

                self.progress().css({
                    width: percentage + '%'
                });
            },

            startUpdate: function() {
                if (self.options.updateids[self.updateCounter] === undefined) {
                    return self.startCreate();
                }

                self.update(self.options.updateids[self.updateCounter])
                    .done(function() {
                        self.updateCounter++;

                        self.updateProgressBar();

                        self.startUpdate();
                    })
                    .fail(function(msg, errors) {
                        console.log(msg, errors);
                    });
            },

            update: function(id) {

                var isLastRecurringEvent = 0;

                // determine which recurring event process now
                var currentRecurringCounter = self.updateCounter + 1;

                // determine if this recurring proceed the last event
                if (currentRecurringCounter == self.options.updateids.length) {
                    var isLastRecurringEvent = 1;
                }

                self.isNewEvent = self.options.isNew;

                var post = $.extend({}, self.options.postdata, {
                    id: id,
                    applyRecurring: 1,
                    isLastRecurringEvent: isLastRecurringEvent,
                    isNew: self.isNewEvent
                });

                return EasySocial.ajax('admin/controllers/events/store', post);
            },

            startCreate: function() {
                if (self.options.schedule[self.createCounter] === undefined) {
                    return self.completed();
                }

                self.create(self.options.schedule[self.createCounter])
                    .done(function() {
                        self.createCounter++;

                        self.updateProgressBar();

                        self.startCreate();
                    })
                    .fail(function(msg, errors) {
                        console.log(msg, errors);
                    });
            },

            create: function(datetime) {

                var isLastRecurringEvent = 0;

                // determine which recurring event process now
                var currentRecurringCounter = self.createCounter + 1;

                // determine if this recurring proceed the last event
                if (currentRecurringCounter == self.options.totalRecurringEvents) {
                    var isLastRecurringEvent = 1;
                }

                self.isNewEvent = self.options.isNew;

                return EasySocial.ajax('admin/controllers/events/createRecurring', {
                    eventId: self.options.eventId,
                    datetime: datetime,
                    postdata: self.options.postdata,
                    isLastRecurringEvent: isLastRecurringEvent,
                    isNew: self.isNewEvent
                });
            },

            completed: function() {
                self.progress().parent().removeClass('progress-info').addClass('progress-success');
                self.form().submit();
            }
        }
    });

    module.resolve();
});
