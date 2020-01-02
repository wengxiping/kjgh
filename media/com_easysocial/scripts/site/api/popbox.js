EasySocial.module('site/api/popbox', function($) {

var module = this;

EasySocial.require()
.library("popbox")
.done(function($) {

	// System Notifications
	EasySocial.module("notifications/popbox", function($){

		this.resolve(function(popbox) {

			var autoread = popbox.button.data('autoread');

			return {

				content: EasySocial.ajax("site/controllers/notifications/getNotifications", {
					layout: "popbox.notifications"
				}).done(function(){

					if (autoread) {
						$('[data-notificationSystem-counter]').parents('li').removeClass('has-notice');
						$('[data-notificationSystem-counter]').html(0);
					}
				}),
				id: "es",

				// type: "notifications",
				cache: false
			};
		});
	});

	EasySocial.module("conversations/popbox", function($) {
		this.resolve(function(popbox) {
			var view = popbox.button.data('popbox-view');

			if (view == undefined) {
				view = '';
			}

			return {
				content: EasySocial.ajax("site/controllers/conversations/getNotificationItems", {
					"paginate": "1",
					"view": view,
					"layout": "popbox.conversations"
				}),
				id: "es",

				cache: false
			};
		});
	});

	// Friends notifications dropdown
	EasySocial.module("friends/popbox", function($){

		this.resolve(function(popbox) {

			return {
				content: EasySocial.ajax("site/controllers/friends/getRequests", {
								layout: "popbox.friends"
				}),
				id: "es",

				cache: false
			};
		});
	});

	// Reaction stats popbox
	EasySocial.module("likes/popbox", function($){

		this.resolve(function(popbox) {
			var wrapper = popbox.button.parents('[data-likes-content]');

			var options = {
				"uid": wrapper.data('id'),
				"type": wrapper.data('likes-type'),
				"group": wrapper.data('group'),
				"verb": wrapper.data('verb'),
				"streamid": false,
				"filter": popbox.button.data('reaction-item')
			};

			return {
				content: EasySocial.ajax("site/views/likes/popbox", options),
				hideDelay: 300,
				cache: false
			};
		});
	});

	// We should check if popbox should be initialized or not.
	var initPopbox = (EasySocial.options.lockdown && !EasySocial.options.guest) || !EasySocial.options.lockdown;

	if (initPopbox) {
		EasySocial.module("profile/popbox", function($) {

			this.resolve(function(popbox){

				var id = popbox.button.data("userId");
				var position = popbox.button.attr('data-popbox-position') || 'top-left';

				return {
					content: EasySocial.ajax("site/views/profile/popbox", {id: id}),
					id: "es",
					component: "",
					type: "profile",
					position: position,
					exclusive: true,
					hideDelay: 300
				}
			})
		});
	}
});

module.resolve();
});
