EasySocial.module('apps/fields/event/startend/display', function($) {

var module = this;

EasySocial
.require()
.library('chosen', 'popbox')
.done(function($) {

EasySocial.Controller('Field.Event.Startend.Display.Box', {
    defaultOptions: {
        date: null,
        timezone: null,
        local: null,
        '{toggle}': '[data-popbox]',
        '{content}': '[data-popbox-content]',
        '{date}': '[data-date]',
        '{timezone}': '[data-timezone]',
        '{loading}': '[data-loading]'
    }
}, function(self, opts) { return {

    init: function() {
        opts.timezone = self.timezone().data('timezone');
        opts.date = self.date().data('date-utc');

        // Get the local timezone first through client browser
        opts.local = -new Date().getTimezoneOffset / 60;

        var content = self.content().html();
        var position = self.toggle().data('popbox-position');

        self.toggle().popbox({
            content: content,
            id: 'es',
            component: 'ui',
            type: 'timezone',
            toggle: 'click',
            position: position
        }).attr('data-popbox', '');
    },

    '{toggle} popboxActivate': function(el, event, popbox) {
        popbox.tooltip.addController('EasySocial.Controller.Field.Event.Startend.Display.Timezone', {
            '{parent}': self
        });
    },

    datetime: $.memoize(function(tz) {
        return EasySocial.ajax('fields/event/startend/getDatetime', {
            "id": opts.id,
            "userid": opts.userid,
            "tz": tz,
            "local": self.options.local,
            "datetime": opts.date
        });
    })
}});

EasySocial.Controller('Field.Event.Startend.Display.Timezone', {
    defaultOptions: {
        '{timezones}': '[data-timezone-select]',
        '{reset}': '[data-timezone-reset]',
        '{local}': '[data-timezone-local]'
    }
}, function(self, opts) { return {
    init: function() {
        self.timezones().chosen({
            search_contains: true
        });
    },

    '{timezones} change': function(dropdown, event) {
        var option = dropdown.find(':selected');
        var value = option.val();
        var text = option.text();

        // Apply loading indicator
        self.parent.element.addClass('is-loading');

        // Update the text
        self.parent.timezone().html(text);

        self.parent.datetime(value).done(function(value) {
            self.parent.date().html(value);
            self.parent.element.removeClass('is-loading');
        });
    },

    '{reset} click': function() {
        self.setTimezone(self.parent.options.timezone);
    },

    '{local} click': function() {
        self.setTimezone('local')
    },

    setTimezone: function(tz) {
        self.timezones()
            .val(tz)
            .trigger('liszt:updated')
            .trigger('change');
    }

}});

module.resolve();

});

});
