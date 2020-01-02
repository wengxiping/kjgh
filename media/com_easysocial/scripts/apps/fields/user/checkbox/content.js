EasySocial.module('apps/fields/user/checkbox/content', function($) {

var module = this;

EasySocial.Controller('Field.Checkbox', {
    defaultOptions: {
        required: false,
        "{item}": "[data-field-checkbox-item]",
        "{field}": "[data-field-checkbox]"
    }
}, function(self, opts, base) { return {
    init : function() {
    },

    "{self} onRender": function() {
        var data = self.field().htmlData();

        opts.error = data.error || {};
    },

    validateInput : function() {
        self.clearError();

        if(self.options.required && self.item(':checked').length == 0) {
            self.raiseError();
            return false;
        }

        return true;
    },

    raiseError: function() {
        self.trigger('error', [opts.error.empty]);
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
