EasySocial.module('apps/fields/user/joomla_password/registermini_content', function($) {
    var module = this;

EasySocial.Controller('Field.Joomla_password.Mini', {
    defaultOptions: {
        required: false,
        reconfirmPassword: false,
        min: 4,
        max: 0,

        '{field}': '[data-field-joomla_password]',
        '{input}': '[data-password]',
        '{reconfirm}'   : '[data-field-password-confirm]',
        '{reconfirmNotice}' : '[data-reconfirmPassword-failed]'
    }
}, function(self, opts, base) { return {

    init: function() {

        var data = self.field().htmlData();
        opts.error = data.error || {};
    },

    "{self} onRender": function() {
        var data = self.field().htmlData();

        opts.error = data.error;
    },

    '{input} keyup': function() {
        self.checkPassword();
    },

    '{reconfirm} keyup': function() {
        self.validatePassword();
    },

    '{reconfirm} blur': function() {
        self.validatePassword();
    },

    checkPassword: function() {
        self.clearError();

        var value = self.input().val();

        if(self.options.required && value.length == 0) {
            self.raiseError(opts.error.empty);
            return false;
        }

        if(self.options.min > 0 && value.length < self.options.min) {
            self.raiseError(opts.error.min);
            return false;
        }

        if(self.options.max > 0 && value.length > self.options.max) {
            self.raiseError(opts.error.long);
            return false;
        }

        if(self.options.minInteger > 0) {
            var test = value.match(/[0-9]/g);
            if (!test || test.length < self.options.minInteger) {
                self.raiseError(opts.error.min);
                return false;
            }
        }

        if(self.options.minSymbol > 0) {
            var test = value.match(/[\W]/g);
            if (!test || test.length < self.options.minSymbol) {
                self.raiseError(opts.error.minsymbols);
                return false;
            }
        }

        if(self.options.minUpperCase > 0) {
            var test = value.match(/[A-Z]/g);
            if (!test || test.length < self.options.minUpperCase) {
                self.raiseError(opts.error.minupper);
                return false;
            }
        }

        return true;
    },

    validatePassword: function() {
        self.clearError();

        var input = self.input().val();
        var reconfirm = self.reconfirm().val();

        if(self.options.reconfirmPassword && !self.validatePasswordConfirm()) {
            return false;
        }

        return true;
    },

    validatePasswordConfirm: function() {
        var input = self.input().val(),
            reconfirm = self.reconfirm().val();

        // Check if either input or reconfirm is not empty
        if(!$.isEmpty(input) || !$.isEmpty(reconfirm)) {
            if($.isEmpty(input)) {
                self.raiseError(opts.error.empty);
                return false;
            }

            if($.isEmpty(reconfirm)) {
                self.raiseError(opts.error.emptyconfirm);
                return false;
            }

            if(input !== reconfirm) {
                self.raiseError(opts.error.mismatch);
                return false;
            }
        }

        return true;
    },

    '{self} onSubmit': function(el, event, register, mode) {
        if (mode !== 'onRegisterMini') {
            return;
        }

        register.push(self.checkPassword());
    },

    clearError: function() {
        self.trigger('clear');
    },

    raiseError: function(msg) {
        self.trigger('error', [msg]);
    }
}});

module.resolve();
})
