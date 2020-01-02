EasySocial.module('uploader/uploader', function($){

var module = this;

EasySocial.require()
.library('plupload')
.script('uploader/queue')
.done(function() {

EasySocial.Controller('Uploader', {
	defaults: {

		url: $.indexUrl + '?option=com_easysocial&controller=uploader&task=uploadTemporary&format=json&tmpl=component&' + EasySocial.token() + '=1',
		uploaded: [],

		// Allows caller to define their custom query.
		query: "",

		plupload: '',
		dropArea: 'uploaderDragDrop',
		extensionsAllowed: 'jpg,jpeg,png,gif',

		// Determines if we should upload the file first or not
		temporaryUpload: false,

		// Contains a list of files in the queue so others can manipulate this.
		files: [],

		'{uploaderForm}': '[data-uploader-form]',
		'{uploadButton}': '[data-uploader-browse]',
		'{uploadArea}': '.uploadArea',

		// This contains the file list queue.
		'{queue}': '[data-uploaderQueue]',

		// The queue item.
		'{queueItem}': '[data-uploaderQueue-item]',

		// When the queue doesn't have any item, this is the container.
		'{emptyFiles}': '[data-uploader-empty]',

		// This is the file removal link.
		'{removeFile}': '[data-uploaderQueue-remove]',
		'{uploadCounter}': '.uploadCounter',
	}
}, function(self, opts, base) { return {

	init: function(){

		// Implement the uploader queue.
		self.queue().implement(EasySocial.Controller.Uploader.Queue);

		if (opts.temporaryUpload) {
			opts.url = $.indexUrl + '?option=com_easysocial&controller=uploader&task=uploadTemporary&format=json&tmpl=component&' + EasySocial.token() + '=1';
		}

		if (opts.query != '') {
			opts.url = opts.url + '&' + opts.query;
		}

		// Implement the plupload controller on the upload form
		self.uploaderForm().implement('plupload', {
			settings: {
				url: opts.url,
				drop_element: opts.dropArea,
				filters: [{
					title: 'Allowed File Type',
					extensions: opts.extensionsAllowed
				}]
			},
			'{uploader}': '[data-uploader-form]',
			'{uploadButton}': '[data-uploader-browse]'
		}, function() {
			// Get the plupload options
			opts.plupload = this.plupload;
		});
	},


	createFileItem: function(files) {

		$.each(files, function(index, file) {

			if (self.getItem(file)) {
				return;
			}

			// Get the file size.
			file.size = self.formatSize(file.size);

			var queueTemplate = $('[data-uploaderQueue-item-template]');
			var content = queueTemplate.clone();

			content.removeClass('t-hidden');

			content.removeAttr('data-uploaderQueue-item-template');
			content.attr('id', file.id);
			content.find('[data-filename]').text(file.name);
			content.find('[data-filesize]').text(file.size);

			// Implement the queue item controller.
			$(content).implement(EasySocial.Controller.Uploader.Queue.Item, {
				"{uploader}": self
			});

			// Keep a copy of the item in our registry
			self.items[file.id] = content;

			// Add this item into our own queue.
			opts.files.push(file);

			// Hide the "No files" value
			self.emptyFiles().hide();

			// Append the queue item into the queue
			self.queue().append(content);
		});
	},

	/**
	 * Formats the size in bytes into kilobytes.
	 */
	formatSize: function(bytes) {

		// @TODO: Currently this only converts bytes to kilobytes.
		var val = parseInt( bytes / 1024 );

		return val;
	},

	// Remove the item from the list.
	reset: function() {

		// self.queueItem().remove();
		$("[data-uploaderQueue-item].is-done").remove();
	},

	removeItem: function(id) {
		var element = $('#' + id);

		// When an item is removed, we need to send an ajax call to the server to delete this record
		var uploaderId = $(element).find('input[name=upload-id\\[\\]]').val();

		element.remove();

		if (!uploaderId) {
			self.options.plupload.removeFile(self.options.plupload.getFile(id));

			return;
		}

		EasySocial.ajax('site/controllers/uploader/delete' , { "id" : uploaderId })
		.done(function() {
			// Now remove the item from the plupload queue.
			self.options.plupload.removeFile( self.options.plupload.getFile( id ) );
		});

		delete self.items[id];
	},

	startUpload: function() {
		self.upload();
	},

	upload: function() {

		if (self.options.plupload.files.length > 0) {
			self.options.uploading 	= true;
			self.options.plupload.start();
		}
	},

	 hasFiles: function(){
		return self.options.files.length > 0;
	 },

	"{uploaderForm} FilesAdded": function(el, event, uploader, files ) {
		// Add a file to the queue when files are selected.
		self.createFileItem(files);

		// Begin the upload immediately if needed
		if (opts.temporaryUpload) {
			self.startUpload();
		}
	},

	items: {},

	getItem: function(file) {
		var id;

		// By id
		if ($.isString(file)) {
			id = file;
		}

		// By file object
		if (file && file.id) {
			id = file.id;
		}

		return self.items[id];
	},

	"{uploaderForm} UploadProgress" : function(el, event, uploader, file) {
		if (file) {
			self.queueItem('#' + file.id)
				.trigger('UploadProgress', file);
		}

	},

	'{uploaderForm} FileUploaded' : function( el , event, uploader, file , response ){
		self.queueItem('#' + file.id)
			.trigger('FileUploaded', [file , response]);
	},

	"{uploaderForm} UploadComplete" : function(el, event , uploader , files) {
		self.options.uploading 	= false;
	},

	'{uploaderForm} Error': function(el, event, uploader, error) {
		// Clear previous message
		self.clearMessage();

		var obj = { 'message' : error.message , 'type' : 'error' };

		self.setMessage( obj );
	},

	'{uploaderForm} FileError': function(el, event, uploader, file, response) {
		var obj = { 'message' : response.message , 'type' : 'error' };

		self.setMessage(obj);

		self.queueItem( '#' + file.id ).trigger('FileError', [file, response]);
	}
}});

module.resolve();

});


});
