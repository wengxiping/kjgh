EasySocial.module('apps/fields/user/joomla_fullname/content', function($) {

var module = this;

EasySocial.Controller('Field.Joomla_fullname', {
    defaultOptions: {
        "nameFormat": 1,
        "max": 0,
        "required": true,
        '{field}': '[data-field-joomla_fullname]',
        '{firstName}': '[data-field-jname-first]',
        '{middleName}': '[data-field-jname-middle]',
        '{lastName}': '[data-field-jname-last]',
        '{name}': '[data-field-jname-name]'
    }
}, function(self, opts, base) { return {

    init : function() {
        opts.nameFormat = self.field().data('name-format');
        opts.max = self.field().data('max');

        var data = self.field().htmlData();
        opts.error = data.error || {};
    },

    "{self} onRender": function() {

        var data = self.field().htmlData();
        opts.error = data.error || {};
    },

    validateInput : function() {

        self.clearError();

        if (!opts.required) {
            return true;
        }

        // Name format
        // 1 - first, middle, last
        // 2 - last, middle, first
        // 3 - single name
        // 4 - first, last
        // 5 - last, first

        if (opts.nameFormat == 3) {

            if($.isEmpty(self.name().val())) {
                self.raiseError(opts.error.empty);
                return false;
            }

            return true;
        }

        if ($.isEmpty(self.firstName().val())) {
            self.raiseError(opts.error.empty);
            return false;
        }

        return true;
    },

    raiseError: function(msg) {
        self.trigger('error', [msg]);
    },

    clearError: function() {
        self.trigger('clear');
    },

    "{firstName} blur" : function(el, event) {
        self.validateInput();
    },

    "{name} blur": function(el, event) {
        self.validateInput();
    },

    "{self} onError": function(el, event, type, field) {
        self.raiseError();
    },

    "{self} onSubmit" : function(el, event, register) {
        register.push(self.validateInput());

        return;
    }
}});

module.resolve();

});
