EasySocial.module('apps/fields/user/gender/content', function($) {
var module = this;

EasySocial.Controller('Field.Gender', {
    defaultOptions: {
        required: false,
        '{field}': '[data-field-gender]',
        '{selection}': '[data-field-gender-select]'
    }
}, function(self, opts, base) { return {

    "{self} onRender": function() {
        var data = self.field().htmlData();

        opts.error = data.error || {};
    },

    validateInput: function() {
        if(!self.options.required) {
            return true;
        }

        self.clearError();

        var value = self.selection(':checked').val();

        if($.isEmpty(value))
        {
            self.raiseError();
            return false;
        }

        return true;
    },

    raiseError: function() {
        self.trigger('error', [opts.error.required]);
    },

    clearError: function() {
        self.trigger('clear');
    },

    '{self} onSubmit': function(el, event, register) {
        register.push(self.validateInput());
    },

    '{selection} click': function() {
        self.validateInput();
    }
}});

module.resolve();
});
