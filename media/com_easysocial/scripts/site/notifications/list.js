EasySocial.module( "site/notifications/list", function($){

var module = this;

EasySocial.Controller('NotificationsList', {
	defaultOptions: {
		"{item}": "[data-notifications-list-item]",
		"{unread}": "[data-unread]",
		"{read}": "[data-read]",
		"{delete}": "[data-delete]",
		"{list}": "[data-notifications-list]",
		"{markAllRead}": "[data-notification-all-read]",
		"{deleteAll}": "[data-notification-all-clear]",
		"{loadMore}": "[data-notification-loadmore-btn]"
	}
}, function(self, opts, base) { return {

	setItemLayout: function(item, className) {
		item.removeClass('is-read is-hidden is-unread')
			.addClass(className);
	},

	"{unread} click" : function(button) {
		var item = button.closest(self.item.selector);
		var id = item.data('id');

		EasySocial.ajax('site/controllers/notifications/setState', {
			"id" : id,
			"state"	: "unread"
		}).done(function() {
			self.setItemLayout(item, 'is-unread');
		});
	},

	"{read} click" : function(button) {
		var item = button.closest(self.item.selector);
		var id = item.data('id');

		EasySocial.ajax('site/controllers/notifications/setState', {
			"id" : id,
			"state"	: "read"
		}).done(function() {
			self.setItemLayout(item, 'is-read');
		});
	},

	"{delete} click" : function(button) {
		var item = button.closest(self.item.selector);
		var id = item.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax("site/views/notifications/clearConfirm"),
			bindings: {
				"{clearButton} click": function() {

					EasySocial.ajax( 'site/controllers/notifications/setState', {
						"id": id,
						"state": "clear"
					}).done(function() {
						self.setItemLayout(item, 'is-remove is-read');

						EasySocial.dialog().close();
					});
				}
			}
		});
	},

	"{markAllRead} click" : function() {
		self.markAllRead().addClass('is-loading');

		EasySocial.ajax('site/controllers/notifications/setAllRead')
		.done(function() {
			self.setItemLayout(self.item(), 'is-read');
			$(window).trigger('easysocial.clearSystemNotification');
		}).always(function(){
			self.markAllRead().removeClass('is-loading');
		});
	},

	"{deleteAll} click" : function() {

		EasySocial.dialog({
			content: EasySocial.ajax("site/views/notifications/clearAllConfirm"),
			bindings: {
				"{clearButton} click": function() {

					EasySocial.ajax( 'site/controllers/notifications/setState', {
						"state"	: "clear"
					}).done(function() {
						self.setItemLayout(self.item(), 'is-remove is-read');

						EasySocial.dialog().close();
					})
				}
			}
		})
	},

	"{loadMore} click" : function(button, event) {
		var startlimit = button.data('startlimit');

		if (startlimit < 0) {
			return;
		}

		EasySocial.ajax('site/controllers/notifications/loadmore', {
			"startlimit" : startlimit
		}).done(function(contents, nextlimit) {
			
			// update next limit
			button.data('startlimit', nextlimit);

			if (contents.length > 0) {
				
				$.buildHTML(contents)
				 	.insertBefore(button);
			}

			// If there's nothing to read, just hide the button
			if (nextlimit < 0) {
				button.hide();
			}

		});
	}
}});

module.resolve();
});
