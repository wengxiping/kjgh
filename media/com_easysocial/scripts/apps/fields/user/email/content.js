EasySocial.module('apps/fields/user/email/content', function($) {

var module = this;

EasySocial.Controller('Field.Email', {
    defaultOptions: {
        required: false,
        regex: 0,
        regexFormat: '',

        regexModifier: '',

        "{field}": "[data-field-email]",
        "{input}": "[data-field-email-input]"
    }
}, function(self, opts, base) { return {

    "{self} onRender": function() {
        var data = self.field().htmlData();

        opts.error = data.error || {};
    },

    validateInput: function() {
        var value   = self.input().val();

        if(self.options.required && $.isEmpty(value)) {
            self.raiseError(opts.error.required);
            return false;
        }

        if(!$.isEmpty(value) && self.options.regex) {
            var regex = new RegExp(self.options.regexFormat, self.options.regexModifier);

            if(!regex.test(value)) {
                self.raiseError(opts.error.invalid);
                return false;
            }
        }

        return true;
    },

    raiseError: function(msg) {
        self.trigger('error', [msg]);
    },

    clearError: function() {
        self.trigger('clear');
    },

    "{self} onSubmit": function(el, event, register) {

        register.push(self.validateInput());

        return;
    }
}});

module.resolve();
});
