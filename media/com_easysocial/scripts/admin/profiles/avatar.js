EasySocial.module( 'admin/profiles/avatar' , function($){

var module = this;

EasySocial.require()
.script('uploader/uploader')
.done(function() {

EasySocial.Controller('Profiles.Avatar', {
	defaultOptions: {
		token : null,

		// Elements
		"{fileUploader}" : "[data-profile-avatars-uploader]",
		"{startUploadButton}": "[data-profile-avatars-startupload]",
		"{avatarList}": "[data-profile-avatars-list]",
		"{avatarEmpty}": "[data-profile-avatars-empty]",
		"{avatarItem}": "[data-profile-avatars-item]",
		"{messagePlaceholder}": "[data-profile-avatars-message]",
		"{removeFile}": ".removeFile",
		"{clearUploadedItems}": "[data-uploader-clear]"
	}
}, function(self, opts) { return {

	init: function() {
		opts.id = self.element.data('id');

		self.initUploader();
		self.initAvatar();
	},

	initUploader: function() {
		self.fileUploader().implement(EasySocial.Controller.Uploader, {
			url : window.es.rootUrl + '/administrator/index.php?option=com_easysocial&namespace=admin/controllers/profiles/uploadDefaultAvatars&' + window.es.token + '=1&tmpl=component&format=ajax&uid=' + opts.id
		});
	},

	initAvatar: function() {
		self.avatarItem().implement( 'EasySocial.Controller.Profiles.Avatar.Item', {
			"{parent}": self,
			"items": self.avatarItem
		});
	},

	addMessage: function(message) {
		self.clearMessage();
		self.setMessage(message);
	},

	"{removeFile} click" : function(el, event) {
		var id = el.parents('li').attr('id');

		self.fileUploader().controller().removeItem(id);
	},

	"{startUploadButton} click" : function() {
		var controller = self.fileUploader().controller();

		controller.startUpload();
	},

	"{fileUploader} UploadProgress" : function( el , event , file ) {
		// Get the upload progress.
		var progress	= file.percent,
			elementId	= '#' + file.id,
			progressBar	= $( elementId ).find( '.progressBar' );

		// Show the progress bar.
		progressBar.show();

		// Update the width of the progress bar.
		progressBar.find( '.bar' ).css( 'width' , progress + '%' );
	},

	"{fileUploader} FileUploaded" : function( el, event, file, response ) {
		if( response[ 0 ] != undefined )
		{
			var contents 	= response[0].data[ 0 ];

			// Hide empty if any
			self.avatarEmpty().hide();

			// Prepend the item
			self.avatarList().prepend( contents );

			self.clearUploadedItems().show();

			// Apply the controller
			self.initAvatar();
		}
	},

	"{clearUploadedItems} click" : function() {
		var controller 	= self.fileUploader().controller();

		// Reset the queue
		controller.reset();

		// Hide itself since there's no history now.
		self.clearUploadedItems().hide();
	}
}});

EasySocial.Controller('Profiles.Avatar.Item', {
	defaultOptions: {
		id: null,
		"{deleteLink}" : "[data-avatar-delete]",
		"{setDefaultAvatar}" : "[data-avatar-default]"
	}
}, function(self, opts) { return {

	init : function() {
		opts.id = self.element.data('id');
	},

	"{setDefaultAvatar} click" : function(el , event ) {
		EasySocial.ajax('admin/controllers/avatars/setDefault', {
			"id" : opts.id
		})
		.done(function(message) {
			// Remove all default class
			self.parent.avatarItem().removeClass( 'default' );

			// Add a default class to itself
			self.element.addClass('default');

			self.parent.addMessage(message);
		});
	},

	"{deleteLink} click": function() {
		EasySocial.dialog({
			"content": EasySocial.ajax('admin/views/profiles/confirmDeleteAvatar'),
			"bindings": {
				"{deleteButton} click" : function(el, event) {
					$(el).addClass('btn-loading');

					EasySocial.ajax('admin/controllers/avatars/delete', {
						"id" : opts.id
					})
					.done(function(message) {
						self.element.remove();

						if (self.parent.avatarList().children().length == 0) {
							self.parent.avatarEmpty().show();
						}

						self.parent.addMessage(message);

						EasySocial.dialog().close();
					});
				}
			}
		});
	}
}});

module.resolve();

});
});


