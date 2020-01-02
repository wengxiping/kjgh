EasySocial.module( 'site/conversations/item' , function($){

var module  = this;

EasySocial.require()
.script('site/conversations/message')
.library('scrollTo')
.done(function($){

EasySocial.Controller('Conversations.Item', {
	defaultOptions: {
		"keepAliveInterval": 3,
		"typingInterval": 2,
		'activeItem' : '',
		"{item}": "[data-es-conversation]"
	}
}, function(self, opts){ return {

	init: function() {
		var activeItem = $('[data-es-item].is-active');

		if (activeItem.length > 0) {
			opts.activeItem = activeItem;
			self.keepAlive();

			if (self.parent.options.typingState) {
				self.checkTypingState();
			}
		}
	},

	checkTypingState: function() {
		var interval = opts.typingInterval * 1000;

		setTimeout(function(){
			$.ajax({
				"url": window.es.rootUrl + '/components/com_easysocial/polling.php',
				"method": "post",
				"data": {
					"method": "typingState",
					"userId": self.parent.options.userId,
					"key": self.parent.options.userKey,
					"conversationId": self.parent.options.conversationId
				}
			}).done(function(data) {
				var typing = self.parent.typing();

				if (data.users == null) {
					typing.addClass('t-hidden');
					self.checkTypingState();
					return;
				}

				var message = self.parent.options.typingMessage;
				message = message.replace('%s', data.users.name);

				$('[data-typing]')
					.html(message)
					.removeClass('t-hidden')

				self.checkTypingState();
			});

		}, interval);
	},

	"{item} click" : function(el, event) {

		var item = el.closest('[data-es-item]');

		// Do not trigger anything
		if (item.hasClass('is-editing-title')) {
			return;
		}

		opts.activeItem = item;

		// Find the anchor link so we can route it
		var anchor = item.find('[data-link]');
		anchor.route();

		// Save any current message in the form as draft
		self.parent.options.composerController.saveDraftMessage();

		// Get the object property
		var id = item.data('id');

		// We need to update the reply form id.
		self.parent.replyForm().data('id', id);
		self.parent.options.composerController.setDraftMessage(id);

		// Add loader and clear up contents
		self.parent.contentsWrapper().addClass('is-loading');
		self.parent.messageContent().empty();

		// triggering conversation toggle for responsive view
		self.trigger('togglees.conversation');

		EasySocial.ajax( 'site/views/conversations/getConversation',{
			"id"   : id,
			"isloadmore" : 0
		}).done(function(title, messages, lastupdate, actions, lastCreatorUserEmail, lastCreatorExist, canEditTitle) {

			var arguments = [title, messages, lastupdate, actions, lastCreatorUserEmail, lastCreatorExist, canEditTitle];
			self.trigger('es.getConversation', arguments);

			// Update the parent's conversation id
			self.parent.options.conversationId = id;

			// update the timestamp on lastupdate
			item.data('lastupdate', lastupdate);

			// Update the title
			self.parent.conversationTitle().html(title);

			self.parent.editTitle().toggleClass('t-hidden', !canEditTitle);

			// Update the contents
			self.parent.messageContent().html(messages);

			self.parent.messageContent().implement(EasySocial.Controller.Conversations.Message , {
				"{parent}": self
			});

			// append the actions into dropdown.
			if (actions != undefined && actions.length > 0) {
				self.parent.toolDropDown().html(actions);
			}

			// Set active conversation
			setTimeout(function() {
				self.parent.setActiveConversation(item);
			}, 1);

			// check for new messages.
			self.keepAlive();
		})
		.fail(function( message ){
			self.parent.messageContent().html(message);
		})
		.always(function() {
			self.parent.contentsWrapper().removeClass('is-loading');
		});

	},

	"keepAlive": function() {
		// When checking, ensure that all previous queues are stopped
		self.stop();

		// now start new checking
		self.start();
	},

	getInterval: function() {
		var interval = opts.keepAliveInterval * 1000;

		return interval;
	},

	start: function() {
		self.options.state = setTimeout(self.check, self.getInterval());
	},

	stop: function() {
		clearTimeout(self.options.state);
	},

	check: function() {
		// When checking, ensure that all previous queues are stopped
		self.stop();

		setTimeout(function(){

			var lastUpdate = opts.activeItem.data('lastupdate');

			$.ajax({
				"url": window.es.rootUrl + '/components/com_easysocial/polling.php',
				"method": "post",
				"data": {
					"method": "checkNewMessages",
					"userId": self.parent.options.userId,
					"key": self.parent.options.userKey,
					"conversationId": self.parent.options.conversationId,
					"lastUpdate": lastUpdate
				}
			}).done(function(data) {

				// Update the last updated timestamp
				opts.activeItem.data('lastupdate', data.timestamp);

				if (data.hasNew) {

					// Make an ajax call to retrieve the HTML
					EasySocial.ajax('site/views/conversations/getNewMessages', {
						"id": self.parent.options.conversationId,
						"lastUpdate": lastUpdate
					}).done(function(messages) {
						self.parent.messageContent().append(messages);

						self.parent.messageItems().implement(EasySocial.Controller.Conversations.Message, {
							"{parent}": self
						});

						self.parent.goToLatest();
					});
				}

				// Restart the loop
				self.start();
			});

		}, self.getInterval());
	}

}});

module.resolve();

});

});

