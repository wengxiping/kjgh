EasySocial.module('admin/events/users', function($) {
var module = this;

EasySocial.Controller('Events.Users', {
	defaultOptions: {
		eventid: null,

		'{inviteGuest}': '[data-event-invite-guest]',
		'{removeGuest}': '[data-event-remove-guest]',
		'{approveGuest}': '[data-event-approve-guest]',
		'{promoteGuest}': '[data-event-promote-guest]',
		'{demoteGuest}': '[data-event-demote-guest]'
	}
}, function(self, opts) { return {

	'{inviteGuest} click': function(el, ev) {
		var guests = {};


		window.inviteGuests = function(guest) {
			if (guest.state) {
				guests[guest.id] = guest
			} else {
				delete guests[guest.id];
			}
		};

		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/events/inviteGuests', {"id":opts.eventid}),
			bindings: {
				'{submitButton} click': function() {
					var form = $('[data-form-add-members]');
					var input = form.find('[data-ids]');
					var ids = [];

					$.each(guests, function(i, member) {
						ids.push(member.id);
					});

					input.val(JSON.stringify(ids));

					$('[data-form-add-members]').submit();
				}
			}
		});
	},

	'{removeGuest} click': function(el, ev) {
		if(document.adminForm.boxchecked.value == 0) {
			alert(opts.error.empty);
		} else {
			$.Joomla('submitform', ['removeGuests']);
		}
	},

	'{approveGuest} click': function(el, ev) {
		if(document.adminForm.boxchecked.value == 0) {
			alert(opts.error.empty);
		} else {
			$.Joomla('submitform', ['approveGuests']);
		}
	},

	'{promoteGuest} click': function(el, ev) {
		if(document.adminForm.boxchecked.value == 0) {
			alert(opts.error.empty);
		} else {
			$.Joomla('submitform', ['promoteGuests']);
		}
	},

	'{demoteGuest} click': function(el, ev) {
		if(document.adminForm.boxchecked.value == 0) {
			alert(opts.error.empty);
		} else {
			$.Joomla('submitform', ['demoteGuests']);
		}
	}
}});

module.resolve();
});
