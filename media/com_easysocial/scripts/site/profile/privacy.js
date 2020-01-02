EasySocial.module('site/profile/privacy', function($) {

var module 	= this;

EasySocial.require()
.library('textboxlist')
.done(function($) {

EasySocial.Controller('Profile.Privacy', {
	defaultOptions: {

		"{filterItem}": "[data-filter-item]",
		"{activeFilter}": "[data-privacy-active]",
		"{contents}": "[data-contents]",
		"{privacyItem}" : "[data-privacy-item]",
		"{privacyForm}" : "[data-profile-privacy-form]",
		"{formActions}": "[data-form-actions]"
	}
}, function(self, opts, base) { return {

	init : function() {
		self.privacyItem().implement(EasySocial.Controller.Profile.Privacy.Item, {
			"{parent}"	: self
		});
	},

	updateContents: function(group) {
		self.contents().hide();
		self.contents().filter('[data-type=' + group + ']').show();

		if (group == 'blocked') {
			self.formActions().hide();
		} else {
			self.formActions().show();
		}
	},

	setActiveFilter: function(filter) {
		var group = filter.data('filter-item');

		self.activeFilter().val(group);

		self.filterItem().removeClass('active');
		filter.addClass('active');
	},

	"{filterItem} click" : function(filterItem, event) {
		var group = filterItem.data('filter-item');

		self.setActiveFilter(filterItem);
		self.updateContents(group);
	}
}});

EasySocial.Controller('Profile.Privacy.Item', {
	defaultOptions : {
		"{selection}": "[data-privacy-select]",
		"{hiddenCustom}" 	: "[data-hidden-custom]",
		"{customForm}" 		: "[data-privacy-custom-form]",

		"{customTextInput}" : "[data-textfield]",
		"{customItems}"		: "input[]",
		"{customHideBtn}"	: "[data-privacy-custom-hide-button]",
		"{customInputItem}"	: "[data-textboxlist-item]",
		"{customEditBtn}"   : "[data-privacy-custom-edit-button]"
	}
}, function(self, opts, base) { return {

	init : function() {
		self.customTextInput().textboxlist({
				component: 'es',
				unique: true,
				plugin: {
					autocomplete: {
						exclusive: true,
						minLength: 2,
						cache: false,
						query: function( keyword ) {

							var users = self.getTaggedUsers();

							var ajax = EasySocial.ajax("site/views/privacy/getfriends", {
								q: keyword,
								exclude: users
							});
							return ajax;
						}
					}
				}
			}
		);

		self.textboxlistLib = self.customTextInput().textboxlist("controller");
	},

	getTaggedUsers: function() {
		var users = [];
		var items = self.customInputItem();

		if( items.length > 0 )
		{
			$.each( items, function( idx, element ) {
				users.push( $( element ).data('id') );
			});
		}

		return users;
	},

	// event listener for adding new name
	"{customTextInput} addItem": function(el, event, data) {

		// lets get the exiting ids string
		var ids    = self.hiddenCustom().val();
		var values = '';

		if( ids == '')
		{
			values = data.id;
		}
		else
		{
			var idsArr = ids.split(',');
			idsArr.push( data.id );

			values = idsArr.join(',');
		}

		//now update the customhidden value.
		self.hiddenCustom().val( values );
	},

	// event listener for removing name
	"{customTextInput} removeItem": function(el, event, data ) {
		// lets get the exiting ids string
		var ids    = self.hiddenCustom().val();
		var values = '';
		var newIds = [];

		var idsArr = ids.split(',');

		for( var i = 0; i < idsArr.length; i++ )
		{
			if( idsArr[i] != data.id )
			{
				newIds.push( idsArr[i] );
			}
		}

		if( newIds.length <= 0 )
		{
			values = '';
		}
		else
		{
			values = newIds.join(',');
		}

		//now update the customhidden value.
		self.hiddenCustom().val( values );
	},

	"{customEditBtn} click" : function( el ) {
		self.customForm().toggle();
	},

	"{selection} change" : function( el ) {
		var selected = el.val();

		if( selected == 'custom' )
		{
			self.customForm().show();
			self.customEditBtn().show();
		}
		else
		{
			self.customForm().hide();
			self.customEditBtn().hide();
		}

		return;
	},

	"{customHideBtn} click" : function()
	{
		self.customForm().hide();

		self.customEditBtn().show();

		self.textboxlistLib.autocomplete.hide();

		return;
	}
}});


module.resolve();
});

});
