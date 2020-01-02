EasySocial.module('site/api/likes', function($){

var module = this;
var deepPress = false;
var reactionEvent = false;

if (window.es.mobile || window.es.tablet) {
	window.es.initReactions = function() {
		EasySocial.require()
		.library('pressure', 'mobile-events')
		.done(function($) {

			// Since pressure library only applicable for ios, we just apply to ios devices
			if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {

				Pressure.set('[data-button-main]', {
					start: function() {
					},
					end: function() {
					},
					change: function(force) {
						if (force > 0.15) {

							if (!reactionEvent) {
								$(this).addClass('is-hover');

								reactionEvent = true;
								deepPress = true;

								// Activate event to hide reaction buttons popup
								setTimeout(function() {
									initReactionHide();
								}, 500);
							}
						}
					}
				});

			} else {

				$(document)
					.on('taphold', '[data-es-likes]', function(e) {

						if (!reactionEvent) {
							$(this).addClass('is-hover');

							deepPress = true;

							// Activate event to hide reaction buttons popup
							setTimeout(function() {
								initReactionHide();
							}, 500);
						}
					});

				$(document)
					.on('contextmenu', '[data-es-likes]', function(e) {
						e.preventDefault();
						e.stopPropagation();

						return false;
					});
			}
		});
	};

	window.es.initReactions();
}

function initReactionHide() {

	// Activate click event
	bindReactionHideEvent();

	$(document).on('click.es.likes.reaction.hide', function(event) {
		var container = $('[data-reaction-list]');

		// if the target of the click isn't the container nor a descendant of the container
		if (reactionEvent && !container.is(event.target) && container.has(event.target).length === 0) {

			$('[data-button-main]').removeClass('is-hover');

			deepPress = false;

			// unbind event
			unbindReactionHideEvent();
		}
	});
}

// Bind hide event
function bindReactionHideEvent() {
	$(document).bind('click.es.likes.reaction.hide');
	reactionEvent = true;
}

// Unbind hide event
function unbindReactionHideEvent() {
	$(document).unbind('click.es.likes.reaction.hide');
	reactionEvent = false;
}

// Reaction statistics
$(document)
	.on('click.es.likes.stats', '[data-es-reaction-stats] [data-bs-toggle]', function(event) {
		var tab = $(this);
		var siblings = tab.siblings();

		siblings.removeClass('is-active');
		tab.addClass('is-active');
	});

$(document)
	.on("click.es.likes.action", "[data-es-likes]", function(event) {

		// Button
		var button = $(this);
		var reaction = button.data('es-likes');
		var isMainButton = button.data('button-main') !== undefined;

		if (deepPress && isMainButton && reactionEvent) {

			// unbind event
			unbindReactionHideEvent();
			return;
		}

		// Containers
		var container = button.parents('[data-es-likes-container]');
		var reactionList = container.find('[data-reactions-list]');
		var currentReaction = container.data('current');

		// Main button
		var mainButton = container.find('[data-button-main]');
		var mainButtonIcon = mainButton.find('[data-button-main-icon]');
		var mainButtonText = mainButton.find('[data-button-text]');
		var defaultText = container.data('default-text');
		var defaultReaction = container.data('default');

		// Ensure that remove the backslash before populate the word during like process
		// Because there got some possibility translator will translate the like word which got contain the single quote
		defaultText = defaultText.replace(/\\/g, "");

		// Once a reaction is tapped, hide the hover
		mainButton.removeClass('is-hover');
		deepPress = false;

		var postAsHidden = $('[data-postas-base] [data-postas-hidden]');
		var postActor = postAsHidden.length > 0 ? postAsHidden.val() : 'user';

		// Data to be sent to the server
		var data = {
				"id": container.data("id"),
				"type": container.data("likes-type"),
				"group": container.data("group"),
				"verb": container.data("verb"),
				"streamid": container.data("streamid"),
				"clusterid": container.data("clusterid"),
				"reaction": reaction,
				"uri": container.data('uri'),
				"reactas": postActor
		};

		// Find the actions row
		var key = data.type + "-" + data.group + "-" + data.id;
		var actions = button.closest('[data-stream-actions]');
		var content = actions.find('[data-likes-content=' + key + ']');

		// Find all the counters
		var counter = content.find('[data-reaction-counter=' + reaction + ']');

		// Determines if we are adding a new reaction
		var addingReaction = false;

		// If the user previously reacted, and the new reaction isn't the same, we need to update
		// the reaction with the one they chose
		if (currentReaction != reaction) {

			var count = parseInt(counter.text());
			count = count + 1;

			container.data('current', reaction);

			// Update the main button with the appropriate reaction
			mainButton
				.data('es-likes', reaction)
				.addClass('is-active');

			if (mainButtonIcon.length) {
				mainButtonIcon
					.removeClass('es-icon-reaction--' + defaultReaction)
					.removeClass('es-icon-reaction--' + currentReaction)
					.addClass('es-icon-reaction--' + reaction);
			}

			// Update the counter
			counter.text(count.toString());

			// Update the active button text
			var active = reactionList.find('[data-es-likes=' + reaction + ']');
			var activeText = active.data('text');
			var activeReactionItem = content.find('[data-reaction-item=' + reaction + ']');

			activeReactionItem.removeClass('t-hidden');
			mainButtonText.text(activeText);

			// Get the previous reaction and reduce the count
			if (currentReaction) {
				var previousReactionItem = content.find('[data-reaction-item=' + currentReaction + ']');
				var previousReactionCounter = content.find('[data-reaction-counter=' + currentReaction + ']');
				var previousReactionCount = parseInt(previousReactionCounter.text()) - 1;
				previousReactionCounter.text(previousReactionCount.toString());

				if (previousReactionCount <= 0) {
					previousReactionItem.addClass('t-hidden');
				}
			}

			addingReaction = true;
		}

		// User already provided a reaction earlier and they are probably withdrawing
		// their previous reaction by clicking on the same button again
		if (currentReaction == reaction) {

			// Reset the current reaction
			container.data('current', '');

			// Get the previous reaction and reduce the count
			var previousReactionItem = content.find('[data-reaction-item=' + currentReaction + ']');
			var previousReactionCounter = content.find('[data-reaction-counter=' + currentReaction + ']');
			var previousReactionCount = parseInt(previousReactionCounter.text()) - 1;

			previousReactionCounter.text(previousReactionCount.toString());

			if (previousReactionCount <= 0) {
				previousReactionItem.addClass('t-hidden');
			}

			// Update the main button text
			mainButton
				.data('es-likes', defaultReaction)
				.removeClass('is-active');

			// Reset the main button icon
			mainButtonIcon.removeClass('es-icon-reaction--' + currentReaction)
				.addClass('es-icon-reaction--' + defaultReaction);

			mainButtonText.text(defaultText);
		}

		// The result isn't shown yet. We should show it
		if (!currentReaction && content.hasClass('t-hidden')) {
			content.removeClass('t-hidden');
		}

		var label = content.find('[data-reaction-label]');

		EasySocial.ajax('site/controllers/likes/react', data)
			.done(function(labelText, hideResult, action, count) {
				// Update the label on the result

				label.html(labelText);

				// Get the stream id from the button
				var id = button.data("streamid");

				// Furnish data with like count
				data.uid = data.id;
				data.count = count;

				// Hide the result row if there is nothing left
				if (data.count <= 0) {
					content.addClass('t-hidden');
				}

				// verb = like/unlike
				var trigger = addingReaction ? "onLiked" : "onUnliked";
				button.trigger(trigger, [data]);

				if (addingReaction && id != "") {
					var exclusion = $('[data-es-streams]').data('excludeids');
					var newIds = '';

					if (exclusion != '' && exclusion != undefined) {
						newIds = exclusion + ',' + id;
					} else {
						newIds = id;
					}

					$('[data-es-streams]').data('excludeids', newIds);
				}
			});
	});

	module.resolve();
});
