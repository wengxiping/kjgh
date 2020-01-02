EasySocial.module('apps/fields/user/joomla_email/registermini_content', function($) {

var module = this;

EasySocial.Controller('Field.Joomla_email.Mini', {
    defaultOptions: {
        require: true,
        id: null,

        '{field}': '[data-field-joomla_email]',
        '{input}': '#email',
        '{confirm}' : '[data-field-email-reconfirm-input]'
    }
}, function(self, opts) { return {
    init: function() {
        self.origEmail = self.input().val();

        var data = self.field().htmlData();
        opts.error = data.error || {};
    },

    '{self} onRender': function() {
        var data = self.field().htmlData();

        opts.error = data.error || {};
    },

    '{input} keyup': function(el) {
        if(el.val().length > 0) {
            self.delayedCheck();
        }
    },

    '{confirm} blur': function(el, ev) {
        self.checkEmail();
    },

    state: false,

    delayedCheck: $.debounce(function() {
        self.checkEmail();
    }, 250),

    checkEmail: function() {

        self.clearError();

        var email = self.input().val();

        if(self.options.required && email.length == 0) {
            self.raiseError(opts.error.required);
            return false;
        }

        if(!$.isEmpty(email) && self.options.regex) {
            var regex = new RegExp(self.options.regexFormat, self.options.regexModifier);

            if(!regex.test(email)) {
                self.raiseError(opts.error.format);
                return false;
            }
        }


        if (opts.reconfirm) {
            var reconfirm = self.confirm().val();

            if (email !== self.origEmail && $.isEmpty(reconfirm)) {
                self.raiseError(opts.error.reconfirmrequired);
                return false;
            }

            if (!$.isEmpty(reconfirm) && email !== reconfirm) {
                self.raiseError(opts.error.mismatch);
                return false;
            }
        }


        if(email.length > 0) {
            var state = $.Deferred();

            self.input().addClass('is-loading');

            var email = self.input().val();

            EasySocial.ajax('fields/user/joomla_email/isValid', {
                id: self.options.id,
                userid: 0,
                email: email
            }).done(function(msg) {

                state.resolve();

            }).fail(function(msg) {
                self.raiseError(msg);

                state.reject();
            }).always(function() {
                self.input().removeClass('is-loading');
            })

            return state;
        }

        return true;
    },

    raiseError: function(msg) {
        self.trigger('error', [msg]);
    },

    clearError: function() {
        self.trigger('clear');
    },

    '{self} onSubmit': function(el, ev, register, mode) {
        if (mode !== 'onRegisterMini') {
            return;
        }

        if(self.options.required || self.input().val().length > 0) {
            register.push(self.checkEmail());
        }
    },

    setLoaded: function() {
        self.trigger('loaded');
    }
}});

module.resolve();
});
