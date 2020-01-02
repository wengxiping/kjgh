EasySocial.module('apps/fields/user/permalink/content', function($) {

var module = this;

EasySocial.Controller('Field.Permalink', {
    defaultOptions: {
        required: false,
        max     : 0,
        id      : null,
        userid  : null,
        '{field}': '[data-field-permalink]',
        '{checkButton}': '[data-permalink-check]',
        '{input}': '[data-permalink-input]',
        '{available}': '[data-permalink-available]'
    }
}, function(self, opts, base) { return {
    
    state: false,

    init: function() {
        opts.message = {
            length: base.find('[data-error-length]').data('error-length'),
            required: base.find('[data-error-required]').data('error-required')
        };

        opts.max = self.field().data('max');
    },

    "{checkButton} click" : function() {
        self.delayedCheck();
    },

    "{input} keyup" : function() {
        self.delayedCheck();
    },

    delayedCheck: $.debounce(function() {
        self.checkPermalink();
    }, 250),

    checkPermalink: function() {
        self.clearError();

        var permalink   = self.input().val();

        self.available().hide();

        if(self.options.max > 0 && permalink.length > self.options.max) {
            self.raiseError(opts.message.length);
            return false;
        }

        if(!$.isEmpty(permalink))
        {
            self.checkButton().addClass('is-loading');

            var state = $.Deferred();

            EasySocial.ajax('fields/user/permalink/isValid',
            {
                "id"        : self.options.id,
                "userid"    : self.options.userid,
                "permalink" : permalink
            })
            .done(function(msg)
            {
                self.checkButton().removeClass( 'is-loading' );

                self.available().show();

                state.resolve();
            })
            .fail(function(msg)
            {
                self.raiseError(msg);

                self.checkButton().removeClass('is-loading');

                self.available().hide();

                state.reject();
            });

            return state;
        }

        if(self.options.required && $.isEmpty(permalink))
        {
            self.available().hide();

            self.raiseError(opts.message.required);
            return false;
        }

        return true;
    },

    raiseError: function(msg)
    {
        self.trigger('error', [msg]);
    },

    clearError: function()
    {
        self.trigger('clear');
    },

    '{self} onSubmit': function(el, ev, register)
    {
        register.push(self.checkPermalink());
    }
}});

module.resolve();
});
