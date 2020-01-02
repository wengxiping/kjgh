EasySocial.module('admin/users/privacy' , function($){

	var module 	= this;

	EasySocial.require()
	.library('textboxlist')
	.done(function($){

		EasySocial.Controller(
			'Profile.Privacy',
			{
				defaultOptions:
				{
					userId	: '',

					"{privacyItem}" : "[data-privacy-item]",

					//input form
					"{privacyForm}" : "[data-profile-privacy-form]",

					// global custom fields
					"{defaultFieldInput}" : "[data-default-privacy-field]",
					"{defaultFieldEdit}" : "[data-default-field-edit]"
				}
			},
			function(self) {
				return {

					init : function() {
						self.privacyItem().implement(EasySocial.Controller.Profile.Privacy.Item , {
							"{parent}"	: self
						});
					},

					"{defaultFieldEdit} click" : function() {

						var selected = self.defaultFieldInput().val();
						var controller = self.privacyItem().controller();
						controller.editField(selected, true, self.defaultFieldInput());
					},

				}
			}
		);


		EasySocial.Controller(
			'Profile.Privacy.Item', {
				defaultOptions :
				{
					"{selection}"		: "[data-privacy-select]",
					"{hiddenCustom}" 	: "[data-hidden-custom]",
					"{hiddenField}" 	: "[data-hidden-field]",
					"{customForm}" 		: "[data-privacy-custom-form]",

					"{customTextInput}" : "[data-textfield]",
					"{customItems}"		: "input[]",
					"{customHideBtn}"	: "[data-privacy-custom-hide-button]",
					"{customInputItem}"	: "[data-textboxlist-item]",
					"{customEditBtn}"   : "[data-privacy-custom-edit-button]",

					// used in profile type privacy.
					"{fieldEdit}" : "[data-privacy-field]"
				}
			},
			function(self)
			{
				return {
					init : function()
					{
						if (self.customTextInput().length > 0) {

							self.customTextInput().textboxlist(
								{
									component: 'es',
									unique: true,

									plugin: {
										autocomplete: {
											exclusive: true,
											minLength: 2,
											cache: false,
											query: function(keyword) {

												var users = self.getTaggedUsers();

												var ajax = EasySocial.ajax("site/views/privacy/getfriends",
													{
														q: keyword,
														userid: self.parent.options.userId,
														exclude: users
													});
												return ajax;
											}
										}
									}
								}
							);

							self.textboxlistLib = self.customTextInput().textboxlist("controller");
						}
					},

					getTaggedUsers: function() {
						var users = [];
						var items = self.customInputItem();

						if (items.length > 0) {
							$.each(items, function(idx, element) {
								users.push($(element).data('id'));
							});
						}

						return users;
					},

					// event listener for adding new name
					"{customTextInput} addItem": function(el, event, data) {

						// lets get the exiting ids string
						var ids    = self.hiddenCustom().val();
						var values = '';

						if (ids == '') {
							values = data.id;
						} else {
							var idsArr = ids.split(',');
							idsArr.push(data.id);

							values = idsArr.join(',');
						}

						//now update the customhidden value.
						self.hiddenCustom().val(values);
					},

					// event listener for removing name
					"{customTextInput} removeItem": function(el, event, data) {
						// lets get the exiting ids string
						var ids    = self.hiddenCustom().val();
						var values = '';
						var newIds = [];

						var idsArr = ids.split(',');

						for (var i = 0; i < idsArr.length; i++) {
							if (idsArr[i] != data.id) {
								newIds.push(idsArr[i]);
							}
						}

						if (newIds.length <= 0) {
							values = '';
						} else {
							values = newIds.join(',');
						}

						//now update the customhidden value.
						self.hiddenCustom().val(values);
					},

					"{customEditBtn} click" : function(el) {
						self.customForm().toggle();
					},

					"{selection} change" : function(el) {
						var selected = el.val();

						if (selected == 'custom') {
							self.customForm().show();
							self.customEditBtn().show();
						} else {
							self.customForm().hide();
							self.customEditBtn().hide();
						}

						return;
					},

					"{customHideBtn} click" : function() {
						self.customForm().hide();
						self.customEditBtn().show();

						self.textboxlistLib.autocomplete.hide();

						return;
					},

					"{fieldEdit} click" : function() {

						var selected = self.hiddenField().val();
						self.editField(selected, true, self.hiddenField());

					},

					editField: function(selected, isDefault, input) {

						var selection = [];
						var updated = false;
						var curSelected = [];

						if (isDefault == undefined) {
							isDefault = false;
						}

						if (selected) {
							var curSelected = selected.split(',');
						}

						EasySocial.dialog({
							content : EasySocial.ajax("admin/views/privacy/fields", {
								"selected" : selected,
								"isDefault" : isDefault ? 1 : 0
							}),
							selectors : {
								"{addFieldButton}" : "[data-field-add]",
								"{removeFieldButton}" : "[data-field-remove]",
								"{fieldTemplate}" : "[data-field-template]",
								"{fieldWrapper}" : "[data-field-wrapper]",
								"{fieldSelect}" : "[data-field-item] [data-field-select]",
								"{tmplFieldSelect}" : "[data-field-template] [data-field-select]",
								"{fieldItem}" : "[data-field-item]"
							},
							bindings : {
								"{addFieldButton} click" : function() {
									var template = this.fieldTemplate().clone();

									template.removeAttr('data-field-template')
										.removeClass('t-hidden')
										.attr('data-field-item', '')
										.appendTo(this.fieldWrapper());
								},

								"{removeFieldButton} click" : function(el) {
									// check if we allow to remove or not.
									if (this.fieldItem().length > 1) {

										// var seletecVal = $(el).val();
										var selectVal = $(el).closest('[data-field-item]').find('[data-field-select]').val();

										if ($.inArray(selectVal, curSelected) >= 0) {
											var tmpArr = [];
											$.each(curSelected, function(idx, val) {
												if (val != selectVal) {
													tmpArr.push(val);
												}
											});
											curSelected = tmpArr;
										}

										$(el).closest('[data-field-item]').remove();
									}
								},

								"{fieldSelect} change": function(el) {
									var selectVal = $(el).val();

									$(el).closest('[data-field-item]').find('[data-field-notice]').addClass('t-hidden');

									if ($.inArray(selectVal, curSelected) >= 0) {
										$(el).closest('[data-field-item]').find('[data-field-notice]').removeClass('t-hidden');
										$(el).val('');
									} else {
										curSelected.push(selectVal);
									}
								},

								"{saveButton} click" : function() {
									updated = true;

									var selectInputs = this.fieldSelect();

									$.each(selectInputs, function(idx, ele) {
										var data = $(ele).val();

										if (data != '' && $.inArray(data, selection) == -1) {
											selection.push(data);
										}
									});

									// close the dialog.
									EasySocial.dialog().close();

									var str = '';
									if (selection.length > 0) {
										str = selection.join(',');
										input.val(str);
									}

								}
							}
						});
					}
				}
			});


		module.resolve();
	});

});
