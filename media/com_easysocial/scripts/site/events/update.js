EasySocial.module('site/events/update', function($) {
    var module = this;

    EasySocial.Controller('Events.Update', {
        defaultOptions: {
            postdata: {},
            updateids: [],
            schedule: [],
            isNew: 0,
            eventId: null,

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

                return EasySocial.ajax('site/controllers/events/update', post);
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
                return EasySocial.ajax('site/controllers/events/createRecurring', {
                    eventId: self.options.eventId,
                    datetime: datetime
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
