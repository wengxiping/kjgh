EasySocial.module('site/conversations/conversations', function($){

var module  = this;

EasySocial.require()
.library("scrollTo")
.script('site/conversations/composer','site/conversations/item')
.done(function($){

EasySocial.Controller('Conversations', {

	defaultOptions: {

		// Determines if these features should be enabled.
		attachments : true,
		location : true,
		maxSize : "3mb",
		extensionsAllowed : "",
		isMobile : false,
		enterToSubmit: false,

		attachmentController : null,
		locationController : null,
		composerController   : null,

		"{tabs}" : "[data-es-tab]",
		"{contents}": "[data-contents]",
		"{conversationHeader}" : "[data-es-conversation-header]",

		"{listWrapper}" : "[data-es-list]",
		"{contentsWrapper}" : "[data-es-contents-wrapper]",

		"{lists}" : "[data-es-list-items]",
		"{listItem}" : "[data-es-item]",

		"{emptyListSidebar}" : "[data-es-list-empty]",
		"{emptyContents}" : "[data-es-content-empty]",
		"{emptyActionBtn}" : "[data-es-action-btn-empty]",

		"{scroller}" : "[data-es-scroller]",
		"{latestWrapper}" : "[data-es-latest]",
		"{messageContent}" : "[data-es-messages]",
		"{messageItems}" : "[data-es-message]",
		"{toolDropDown}" : "[data-item-tools]",

		"{unreadButton}" : "[data-es-unread]",
		"{archiveButton}" : "[data-es-archive]",
		"{unarchiveButton}" : "[data-es-unarchive]",
		"{leaveButton}" : "[data-es-leave]",
		"{deleteButton}" : "[data-es-delete]",
		"{addParticipant}" : "[data-es-addparticipant]",
		"{viewParticipants}" : "[data-es-viewparticipants]",
		"{editTitle}" : "[data-es-rename]",

		"{composer}" : "[data-es-composer]",

		"{attachments}" : "[data-uploaderQueue-id]",
		"{replyForm}" : "[data-es-reply-form]",

		"{replyButton}" : "[data-es-reply-button]",
		"{attachmentForm}" : "[data-es-attachment-form]",
		"{attachmentToggle}" : "[data-es-attachment-toggle]",
		"{emoticonsToggle}" : "[data-comment-smileys]",
		"{attachmentCloseBtn}" : "[data-es-attachment-close]",

		"{locationForm}" : "[data-es-location-form]",
		"{locationToggle}" : "[data-es-location-toggle]",

		"{conversationTitle}" : "[data-es-title]",
		"{conversationActions}" : "[data-es-actions]",
		"{titleContainer}" : "[data-es-title-container]",
		"{titleInputBox}" : "[data-es-title-textbox]",
		"{titleSaveButton}" : "[data-title-save]",
		"{titleCancelButton}" : "[data-title-cancel]",

		// Item
		"{item}": "[data-item]",
		"{itemMenuDropdown}": "[data-menu-dropdown]",
		"{itemMenu}": "[data-item-menu]",
		"{listTitleInput}": "[data-es-title-textbox-list]",
		"{listTitleSaveButton}": "[data-title-save-list]",
		"{listRenameTitleButton}": "[data-es-rename-list]",

		"{typing}": "[data-typing]",

		// search
		"{search}": "[data-es-search]",

		// conversation back button. used in responsive mode.
		"{backBtn}": "[data-back-button]",

		"{pagination}": "[data-es-conversation-pagination]",
		"{paginationWrapper}" : "[data-es-conversation-pagination-wrapper]",
		"{smileyItem}": "[data-comment-smiley-item]"
	}
}, function(self, opts, base) { return {
	init: function() {

		// Implement composer controller.
		self.composer().implement(EasySocial.Controller.Conversations.Composer, {
			"{parent}"  : self,
			"{uploader}" : "[data-es-attachment-form]",
			"{location}" : "[data-es-location]",
			maxSize : self.options.maxSize,
			extensionsAllowed : self.options.extensionsAllowed,
			emoticons: self.options.emoticons
		});

		self.options.composerController = self.composer().controller();

		// Get the uploader controller.
		if (opts.attachments) {
			self.options.attachmentController = self.options.composerController.uploader().controller();
		}

		if (self.options.location){
			self.options.locationController = self.options.composerController.location().controller();
		}

		self.messageContent().implement(EasySocial.Controller.Conversations.Message, {
			"{parent}"  : self
		});

		self.lists().implement(EasySocial.Controller.Conversations.Item, {
			"{parent}"  : self
		});

		self.goToLatest();
	},

	setActiveTab: function(tab) {
		self.tabs().removeClass('active');
		tab.addClass('active');

		self.wrapperEmpty(false);
		self.loading(true);
		self.conversationTitle().empty();
	},

	getConversationItem: function(element) {
		var item = element.closest(self.item.selector);

		return item;
	},

	filter: $.debounce(function(keyword) {

		var totalList = self.listItem().length;
		if (totalList <= 0) {
			// nothing to find. just return false.
			return;
		}
		self.listWrapper().removeClass("is-empty");

		if (!keyword || !(keyword=$.trim(keyword)) || keyword.length <= 2) {
			// self.listItem().removeClass('hidden');
			self.listItem().show();
			return;
		}

		// show all 1st
		self.listItem().show();

		var list = self.listItem().filter(function() {
			// if not match, then return this item so that we can 'hide' it.
			if ($(this).data('title').indexOf(keyword.toLowerCase()) <= -1) {
				return this;
			}
		});

		if (list.length == totalList) {
			// this mean no item found. show empty list message.
			self.listWrapper().addClass("is-empty");
		}

		list.hide();


	}, 250),

	"{composer} save": function() {
		self.replyButton().click();
	},

	"{search} keyup": function() {
		var keyword = self.search().val();
		self.filter(keyword);
	},

	"{itemMenuDropdown} click": function(dropdown, event) {
		event.preventDefault();
		event.stopPropagation();

		// is-dropdown is injected to all conversation item. It should be removed.
		var wrapper = dropdown.parents('[data-es-list-items]');
		wrapper.find('[data-item]').removeClass('is-dropdown');

		// inject is-dropdown class when conversation is click.
		dropdown.closest('[data-item]').addClass('is-dropdown');

		var button = dropdown.find('[data-bs-toggle]');
		button.trigger('click.bs.dropdown');
	},

	"{itemMenu} click" : function(menu, event) {

		var item = self.getConversationItem(menu);
		var id = item.data('id');
		var task = menu.data('item-menu');

		if (task == 'unread') {
			self.setUnread(id);
			return;
		}

		self.showDialog(task, id);
	},

	"{listItem} click": function() {

		// need to clear off the title edit form.
		if (self.conversationHeader().hasClass('is-editing')) {
			self.toggleTitleEditForm();
		}
	},

	loadMore: function() {

		//conversation id
		var type = self.pagination().data('type');

		// Get the pagination attributes
		var limitstart = self.pagination().data('limitstart');

		if (limitstart < 0) {
			return;
		}

		// Set the current loading state
		self.moreLoading = true;

		// Add loading indicator
		self.pagination().addClass('is-loading');

		EasySocial.ajax('site/views/conversations/getConversations',{
			"type" : type,
			"limitstart" : limitstart,
			'loadmore' : 1
		}).done(function(conversations, emptyList, emptyContents, nextlimit) {

			// we need to remove the pagination becuase the new html already included the pagination div.
			self.pagination().data('limitstart', nextlimit);

			// append the conversations into the list
			self.lists().append(conversations);

			if (nextlimit < 0) {
				self.paginationWrapper().addClass('t-hidden');
			}

			// add support to kunena [tex] replacement.
			try { MathJax && MathJax.Hub.Queue(["Typeset",MathJax.Hub]); } catch(err) {};

		}).always(function(){
			self.pagination().removeClass('is-loading');
			self.moreLoading = false;
		});

	},

	"{pagination} click" : function(el, event) {
		self.loadMore();
	},

	"{tabs} click" : function(tab, event) {
		event.stopPropagation();
		event.preventDefault();

		var anchor = tab.find('> a');
		anchor.route();

		self.setActiveTab(tab);

		var type = tab.data('es-tab');

		EasySocial.ajax('site/views/conversations/getConversations',{
			"type": type
		}).done(function(conversations, emptyList, emptyContents, nextlimit){

			self.contents().removeClass('has-active');

			if (conversations.length < 1) {
				self.pagination().data('limitstart', '0');
				self.wrapperEmpty(true, type, emptyList, emptyContents);
				self.replyForm().addClass('t-hidden');
				self.paginationWrapper().addClass('t-hidden');
			} else {

				self.pagination().data('limitstart', nextlimit);
				self.contents().addClass('has-active');
				self.replyForm().removeClass('t-hidden');

				if (nextlimit < 0) {
					self.paginationWrapper().addClass('t-hidden');
				} else {
					self.paginationWrapper().removeClass('t-hidden');
				}

			}

			// re-assign the contents to th conversation listing.
			self.lists().html(conversations);

			if (!self.options.isMobile) {
				// Find the first list item
				var item = self.lists().children(':first').find('[data-es-conversation]');
				item.trigger('click');
			}

			self.loading(false);
		});

	},

	"{backBtn} click": function() {
		// triggering conversation toggle for responsive view
		self.trigger('togglees.conversation');
	},

	// mark unread
	"{deleteButton} click": function() {
		var conversationId = self.replyForm().data('id');
		self.showDialog('Delete', conversationId);
	},

	"{archiveButton} click": function() {
		var conversationId = self.replyForm().data('id');
		self.showDialog('Archive', conversationId);
	},

	"{unarchiveButton} click": function() {
		var conversationId = self.replyForm().data('id');
		self.showDialog('Unarchive', conversationId);
	},

	"{leaveButton} click": function() {
		var conversationId = self.replyForm().data('id');
		self.showDialog('Leave', conversationId);
	},


	"{unreadButton} click": function() {
		var conversationId = self.replyForm().data('id');
		self.setUnread(conversationId);
	},

	"{addParticipant} click": function() {
		var conversationId = self.replyForm().data('id');
		self.showInviteForm(conversationId);
	},

	"{viewParticipants} click": function() {
		var conversationId = self.replyForm().data('id');
		self.showParticipants(conversationId);
	},

	"{attachmentToggle} click" : function(el, event) {
		// Stop bubbling up.
		event.preventDefault();
		self.attachmentForm().toggleClass('t-hidden');
	},

	"{emoticonsToggle} click" : function(el, event) {
		// Stop bubbling up.
		event.preventDefault();

		if (el.hasClass('active')) {
			el.removeClass('active');
			return;
		}

		el.addClass('active');
	},

	"{attachmentCloseBtn} click" : function(el, event) {
		self.attachmentForm().toggleClass('t-hidden');
	},

	"{locationToggle} click" : function(el, event) {
		// Stop bubbling up.
		event.preventDefault();
		self.locationForm().toggleClass('t-hidden');
	},

	"{editTitle} click": function(el, event) {
		self.toggleTitleEditForm();

		// now assign the title into the textbox
		var title = self.conversationTitle().text();

		title = title.trim();

		self.titleInputBox().val(title);
		self.titleInputBox().focus();
	},

	"{titleInputBox} keydown": function(el, event) {
		var wrapper = el.parents('[data-es-title-container]');
		var saveButton = wrapper.find('[data-title-save]');

		if (event.keyCode == 13) {
			saveButton.trigger('click');
			event.preventDefault();
		}

		if (event.keyCode == 27) {
			self.toggleTitleEditForm();
		}
	},

	"{listTitleInput} keydown": function(el, event) {

		var wrapper = el.parents('[data-es-item]');
		var saveButton = wrapper.find('[data-title-save-list]');

		// Capture enter key
		if (event.keyCode == 13) {
			saveButton.trigger('click');
			event.preventDefault();
		}

		// Capture esc button to cancel the edit
		if (event.keyCode == 27) {
			self.toggleItemTitle(wrapper);
		}
	},

	"{listRenameTitleButton} click": function(el, event) {
		var wrapper = el.parents('[data-es-item]');

		// Toggle item title button
		self.toggleItemTitle(wrapper);

		// now assign the title into the textbox
		var title = wrapper.find('[data-item-title]').text().trim();
		var inputBox = wrapper.find('[data-es-title-textbox-list]');

		inputBox.val(title);
		inputBox.focus();
	},

	toggleItemTitle: function(wrapper) {
		var itemTitleBox = wrapper.find('[data-item-title-textbox]');
		var itemTitle = wrapper.find('[data-item-title]');

		// Reset the classes first
		$(itemTitle).removeClass('t-hidden');
		wrapper.removeClass('is-editing-title');

		var isNotActive = $(itemTitleBox).toggleClass('t-hidden').hasClass('t-hidden');

		if (!isNotActive) {
			$(itemTitle).addClass('t-hidden');
			wrapper.addClass('is-editing-title');
		}
	},

	toggleTitleEditForm: function() {
		self.conversationHeader().toggleClass('is-editing');

		// clear the input box
		self.titleInputBox().val('');
	},

	"{titleCancelButton} click": function(el, event) {
		self.toggleTitleEditForm();
	},

	"{titleSaveButton} click": function(el, event) {

		//disable button
		self.titleSaveButton().attr('disabled', true);

		var id = self.replyForm().data('id');
		var title = self.titleInputBox().val();

		if (title.trim() == '') {
			self.titleSaveButton().attr('disabled', false);
			return;
		}

		var options = {
			"id" : id,
			"title" : title
		};

		// Do an ajax call to submit the reply.
		EasySocial.ajax('site/controllers/conversations/updateTitle', options)
		.done(function(title) {
			self.conversationTitle().text(title);
			self.updateListTitle(id, title);
			self.toggleTitleEditForm();

		})
		.fail(function(error) {

			EasySocial.dialog({
				content: error.message
			});
		})
		.always(function(){
			self.titleSaveButton().attr('disabled', false);
		});
	},

	"{listTitleSaveButton} click": function(el, event) {
		$(el).attr('disabled', true);

		var wrapper = el.parents('[data-es-item]');
		var id = wrapper.data('id');
		var title = wrapper.find('[data-es-title-textbox-list]').val();

		var options = {
			"id" : id,
			"title" : title
		};

		// Do an ajax call to submit the reply.
		EasySocial.ajax('site/controllers/conversations/updateTitle', options)
		.done(function(title) {

			parentId = self.replyForm().data('id');

			// If current conversation is being display, let's update the title on the fly.
			if (parentId == id) {
				self.conversationTitle().text(title);
			}

			self.updateListTitle(id, title);
			self.toggleItemTitle(wrapper);

		})
		.fail(function(error) {

			EasySocial.dialog({
				content: error.message
			});
		})
		.always(function(){
			$(el).attr('disabled', false);
		});
	},

	updateListTitle: function(id, title) {
		// need to update the conversation title at left panel
		var curConversation = $('[data-es-item][data-id="' + id + '"]');

		if (curConversation.length > 0) {
			curConversation.data('title', title);
			curConversation.find("[data-link]").attr('title', title);
			curConversation.find("[data-item-title]").text(title);
		}
	},

	isReplying: false,

	toggleReplyButton: function(action) {
		self.replyForm().toggleClass('is-uploading', action);
		self.replyButton().toggleClass('disabled', action);
	},

	"{replyButton} click" : function( el , event ) {

		if (self.replyForm().hasClass('is-uploading')) {
			return false;
		}

		// Stop bubbling up.
		event.preventDefault();

		var conversationId = self.replyForm().data('id');

		var content = opts.composerController.editor().val();
		var files = new Array;

		if (opts.attachments) {
			// Get through each attachments.
			self.attachments().each(function(i, attachment){
				var fid = $( attachment ).val();
				if (fid) {
					files.push( $( attachment ).val() );
				}
			});
		}

		if (content.length <= 0 && files.length <= 0) {
			return false;
		}

		if (self.isReplying) {
			return false;
		}

		self.isReplying = true;

		var options = {
			"id" : conversationId,
			"message" : content
		};

		if (self.options.attachments) {
			options['upload-id'] = files;
		}

		if (self.options.location) {
			var locationController = self.composer().controller().location().controller();
			options.address     = locationController.textField().val();
			options.latitude    = locationController.latitude().val();
			options.longitude   = locationController.longitude().val();
		}

		options['tags'] = self.composer().controller().editorArea().mentions('controller').toArray();

		options['tags'] = $.map(options['tags'], function(mention){

			if (mention.type==="emoticon" && $.isPlainObject(mention.value)) {
				mention.value = mention.value.title.slice(1);
			}
			return JSON.stringify(mention);
		});

		// Disable submit button.
		self.replyButton().attr('disabled', true);

		// Do an ajax call to submit the reply.
		EasySocial.ajax('site/controllers/conversations/reply', options)
		.done(function(message, html, lastupdate) {

			// update the lastupdate timestamp
			$('[data-es-item][data-id="' + conversationId + '"]').data('lastupdate', lastupdate);

			// Apply controller on the appended item.
			var item = $(html);

			// Append the data back to the list.
			self.messageContent().append(item);

			// Reset the composer form.
			self.resetForm();

			// Go to the latest reply
			self.goToLatest();

		})
		.fail(function(error) {
			EasySocial.dialog({
				content: error.message
			});
		})
		.always(function(){
			self.replyButton().attr('disabled', false);
			self.isReplying = false;
		});

		return false;
	},



	getLanguageString: function(string) {
		return $('[data-id="' + string + '"]').text();
	},

	resetForm: function() {
		// Reset the editor form.
		self.options.composerController.resetForm();

		var mentions = self.composer().controller().editorArea().mentions('controller');
		mentions.reset();

		if (self.options.location) {
			// Reset the location.
			// self.options.locationController.unset();
			var locationController = self.composer().controller().location().controller();
			locationController.unset();

			// hide the location form.
			self.locationForm().addClass('t-hidden');

		}

		if (self.options.attachments) {
			// Reset the uploader.
			self.options.attachmentController.reset();

			// hide the attatchment form.
			self.attachmentForm().addClass('t-hidden');
		}
	},


	loading: function(loading) {

		if (loading) {
			self.listWrapper().addClass('is-loading');
			self.contentsWrapper().addClass('is-loading');
		} else {
			self.listWrapper().removeClass('is-loading');
			self.contentsWrapper().removeClass('is-loading');
		}
	},

	wrapperEmpty: function(isEmpty, type, emptyList, emptyContent) {
		if (isEmpty) {

			// update the empty content message.
			self.emptyListSidebar().find('div.o-empty__text').html(emptyList);
			self.emptyContents().find('div.o-empty__text').html(emptyContent);

			if (type == 'archives') {
				self.emptyActionBtn().addClass('t-hidden');
				self.toolDropDown().addClass('t-hidden');
			} else {
				self.emptyActionBtn().removeClass('t-hidden');
				self.toolDropDown().removeClass('t-hidden');
			}

			self.editTitle().addClass('t-hidden');
			self.listWrapper().addClass('is-empty');
			self.contentsWrapper().addClass('is-empty');
		} else {
			self.listWrapper().removeClass('is-empty');
			self.contentsWrapper().removeClass('is-empty');
			self.toolDropDown().removeClass('t-hidden');
			self.editTitle().removeClass('t-hidden');
		}
	},

	// Set an active conversation item
	setActiveConversation: function(item) {
		// Update the conversation area
		self.listItem().removeClass('is-active');

		// Addd active class on the element
		item.addClass('is-active');

		item.removeClass('is-unread');

		self.toolDropDown().removeClass('hidden');

		// Go to the latest content
		self.goToLatest();
	},

	// Scroll to the latest
	goToLatest: function() {
		self.scroller().scrollTo(self.latestWrapper(), 250, {offset: {top: -100}});
	},

	showDialog: function(type, id) {
		var methodName = type.charAt(0).toUpperCase() + type.slice(1);

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/conversations/confirm' + methodName, {
				"id" : id
			})
		});
	},

	showInviteForm: function(id) {
		EasySocial.dialog(
		{
			content : EasySocial.ajax( 'site/views/conversations/addParticipantsForm', {
				"id" : id
			})
		});
	},

	showParticipants: function(id) {
		EasySocial.dialog(
		{
			content : EasySocial.ajax( 'site/views/conversations/viewParticipants', {
				"id" : id
			})
		});
	},

	setUnread: function(id) {

		EasySocial.ajax( 'site/controllers/conversations/markUnread',{
			"id"   : id
		}).done(function(){
			var item = $('[data-es-item][data-id="' + id + '"]');
			if (item.length > 0) {
				item.removeClass('is-active');
				item.addClass('is-unread');
			}
		})
		.fail(function( message ){
			EasySocial.dialog({
				content: message
			});
		})
	},

	"{smileyItem} click": function(smileyItem, event) {

		var editor = self.composer().controller().editorArea();
		var value = smileyItem.data('comment-smiley-value');

		// Add additional space to allow multiple smiley to be click at once. #3122
		value = value + ' ';

		var controller = editor.mentions("controller");

		var textarea = controller.textarea();

		previousCursor = controller.previousCursorPosition;

		var currentValue = textarea.val();
		var beforeValue = currentValue.substring(0, previousCursor) + value;
		var newValue = beforeValue + currentValue.substring(previousCursor);

		// We need to trigger the mention
		controller.isPasting = true;
		controller.smileyLength = value.length;

		textarea.val(newValue);
		textarea.trigger('input');

		controller.moveCursor(beforeValue.length);
	}


}});

module.resolve();

});

});

