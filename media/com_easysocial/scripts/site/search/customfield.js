EasySocial.module( 'site/search/customfield' , function($){
	var module = this;

	EasySocial.require()
	.script('shared/fields/conditional')
	.done(function(){

		EasySocial.Controller('Search.Customfield', {
			defaultOptions: {
				"{fieldItem}": "[data-field-item]",
				"{dataCondition}" : "[data-condition]",
				"{dataCheckbox}" : "[data-checkbox-option]",
				"{dataDropdown}" : "[data-dropdown-field]",
				"{dataBoolean}" : "[data-boolean-field]"
			}
		},
		function(self) {
			return {

				init : function() {
					self.fieldItem().each(function() {
						var field = $(this);
						var fieldSearchItem = field.parents('[data-customfield-search-item]');
						field.addController(EasySocial.Controller.Field.Conditional);

						if (!field.hasClass('t-hidden')) {
							fieldSearchItem.removeClass('t-hidden');
						}

						field.on('onFieldShow', function() {
							fieldSearchItem.removeClass('t-hidden');
						});

						field.on('onFieldHide', function() {
							fieldSearchItem.addClass('t-hidden');

							// reset the field value so that it wont affect query
							self.resetField();
						});
					});
				},

				"{dataCheckbox} click" : function(el) {

					var fieldItem = el.parents(self.fieldItem().selector);

					// get all checked item
					var checkedItems = fieldItem.find(self.dataCheckbox().selector + ':checked');
					var values = [];

					checkedItems.each(function(){
						values.push($(this).val());
					});

					var conditions = values.join('|');

					self.dataCondition().val(conditions);
				},

				"{dataDropdown} change" : function(el) {
					self.dataCondition().val(el.val());
				},

				"{dataBoolean} change" : function(el) {
					self.dataCondition().val(el.val());
				},

				resetField: function() {
					self.dataCondition().val('');
					self.dataDropdown().prop('selectedIndex',0);
					self.dataCheckbox().prop('checked', false);
				}
			}
		});

		module.resolve();

	});

});
