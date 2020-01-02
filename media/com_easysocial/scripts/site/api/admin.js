EasySocial.module('site/api/admin', function($){

var module = this;

// Admin tools - Unban user
$(document)
	.on('click.es.user.unban', '[data-es-user-unban]', function() {
		var element = $(this);
		var uid = element.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/profile/confirmUnban', {id: uid}),
			bindings: {
				"{unbanButton} click": function() {
					var button =  $('[data-unban-button]');
					var loadButton = $('[data-unban-button-loader]');

					loadButton.addClass('is-active');

					// Prevent users to click more than one time
					button.attr("disabled", true);

					EasySocial.ajax('site/controllers/profile/unbanUser', {
						"id": uid
					}).done(function(html) {
						EasySocial.dialog({
							content: html
						});
					});

				},

				"{closeButton} click": function() {
					EasySocial.dialog().close();
				}
			}
		});

	});


// Admin tools - Delete user
$(document)
	.on('click.es.user.delete', '[data-es-user-delete]', function() {

		var element = $(this);
		var uid = element.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/profile/confirmDeleteUser', {id: uid}),
			bindings: {
				"{deleteButton} click": function() {
					EasySocial.ajax('site/controllers/profile/deleteUser', {
						"id": uid
					}).done(function(html) {
						EasySocial.dialog({
							content: html
						});
					});
				},

				"{closeButton} click": function() {
					EasySocial.dialog().close();
				}
			}
		});
	});

// Admin tools - Ban user
$(document)
	.on('click.es.user.ban', '[data-es-user-ban]', function() {
		var element = $(this);
		var uid = element.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/profile/confirmBanUser', {id: uid}),
			bindings: {

				"{banButton} click": function() {
					var textarea = $('[data-ban-reason]');
					var reason = textarea.val();
					var period = $('[data-ban-period]').val();
					var notice = $('[data-composer-notice]');
					var errorMsg = textarea.data('required-error');
					var button =  $('[data-ban-button]');
					var loadButton = $('[data-ban-button-loader]');

					loadButton.addClass('is-active');

					// Prevent users to click more than one time
					button.attr("disabled", true);

					if (reason.length < 1) {
						notice.html(errorMsg)
							.toggleClass('t-hidden', reason.length > 1);

						loadButton.removeClass('is-active');
						button.removeAttr("disabled");
						return;
					}

					EasySocial.ajax('site/controllers/profile/banUser', {
						"id": uid,
						"period": period,
						"reason": reason
					}).done(function(html) {
						EasySocial.dialog({
							content: html
						});

					});
				},

				"{closeButton} click": function() {
					EasySocial.dialog().close();
				}
			}
		})


	});


module.resolve();

});
