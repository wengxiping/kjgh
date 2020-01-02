EasySocial.module('apps/fields/user/checkbox/sample_content', function($) {
    var module  = this;

    EasySocial.Controller('Field.Checkbox.Sample', {
        defaultOptions: {
            '{checkboxes}'	: '[data-checkboxes]',
            '{checkbox}'	: '[data-checkbox]',
            '{sample}'		: '[data-sample-checkboxes] > [data-sample-checkbox]',
            '{checkboxInput}' : '[data-checkbox-input]',
            '{checkboxTitle}' : '[data-checkbox-title]'
        }
    }, function(self) {
        return {
            init: function() {
            },

            '{self} onChoiceTitleChanged': function(el, event, index, data) {

                self.checkboxTitle().eq(index).text(data);
            },

            '{self} onChoiceValueChanged': function(el, event, index, data) {
                self.checkboxInput().eq(index).val(data);
            },

            '{self} onChoiceAdded': function(el, event, index, data) {

				var sample = self.sample().clone();

				sample.removeData('data-sample-checkbox');
				sample.removeAttr('data-sample-checkbox');

				sample.attr('data-checkbox','');
				sample.data('data-checkbox','');

                if(self.checkbox().eq(index).length > 0) {
                    self.checkbox().eq(index).before(sample);
                } else {
                    self.checkboxes().append(sample);
                }
            },

            '{self} onChoiceRemoved': function(el, event, index) {
                self.checkbox().eq(index).remove();
            },

            '{self} onChoiceToggleDefault': function(el, event, index, value) {
                var element = self.checkboxInput().eq(index);

                if(value) {
                    element.prop('checked', true);
                } else {
                    element.prop('checked', false);
                }
            }
        }
    });

    module.resolve();
});
