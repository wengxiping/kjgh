EasySocial.module('site/comments/frame', function($) {

var module = this;

EasySocial
.require()
.library('mentions')
.script('site/comments/item', 'uploader/uploader', 'site/comments/form')
.done(function() {

EasySocial.Controller('Comments', {
	defaultOptions: {

		'group': 'user',
		'element': 'stream',
		'verb': 'null',
		'uid': 0,
		'enterkey': 'newline',
		'url': '',
		'streamid': '',
		'clusterid': '',

		// Comment link
		'{actionLink}': '[data-stream-actions] [data-action] [data-type=comments]',
		'{actionContent}': '[data-action-contents-comments]',

		'{stat}': '[data-comments-stat]',
		'{load}': '[data-comments-load]',
		'{list}': '[data-comments-list]',
		'{item}': '[data-comments-item]',
		'{form}': '[data-comments-form]'
	}
}, function(self, opts, base) { return {

	init: function() {

		opts.uid = base.data('uid') || opts.uid;
		opts.element = base.data('element') || opts.element;
		opts.group = base.data('group') || opts.group;
		opts.verb = base.data('verb') || opts.verb;
		opts.url = base.data('url') || opts.url;
		opts.streamid = base.data('streamid') || opts.streamid;
		opts.timestamp = base.data('timestamp') || opts.timestamp;
		opts.clusterid = base.data('clusterid') || opts.clusterid;

		self.$Stat = self.addPlugin('stat');
		self.$Load = self.addPlugin('load');
		self.$List = self.addPlugin('list');
		self.$Form = self.addPlugin('form');

		// Comment Control needs to be required once when there is a frame on the page
		EasySocial.require().script('site/comments/control').done(function() {

			// This block needs to be registered
			EasySocial.Comments.register(self);
		});

		// Trigger commentInit on self
		self.trigger('commentInit', [self]);
	},

	// Create a registry of items
	$Comments: {},

	registerComment: function(instance) {
		var id = instance.options.id;

		self.$Comments[id] = instance;
	},

	'{actionLink} click' : function() {
		self.actionContent().toggle();
	},

	_export: function() {
		var data = {
			// "total": self.$Stat.total(),
			// "count": self.$Stat.count(),
			"timestamp": opts.timestamp,
			"ids": $._.keys(self.$Comments)
		};

		return data;
	},

	updateComment: function(comments) {

		var newComments = [];

		$.each(comments['ids'], function(commentid, state) {

			if (state !== true) {

				if (state === false) {
					// Trigger commentDeleted event on self (as parent)
					self.trigger('commentDeleted', [commentid]);
				} else {

					// Always ensure that the comments is enabled and append the comment to the list
					self.showComments();
					self.$List.addToList(state, 'append', false);

					// Add this comment into the list of new comments
					newComments.push(state);
				}
			}
		});

		// Update the new total count
		self.$Stat.total(comments['total']);

		// Trigger oldCommentsLoaded event
		self.trigger('oldCommentsLoaded', [newComments]);
	},

	showComments: function() {
		self.element.removeClass('t-hidden');
	},

	// Triggered on the wrapper level
	'{self} show': function() {
		self.showComments();

		// Focus on the input
		self.$Form.input().focus();
	}
}});

EasySocial.Controller('Comments.List', {
	defaultOptions: {
		'{list}': '[data-comments-list]',
		'{item}': '[data-comment-item]'
	}
}, function(self) { return {
	init: function() {

		// Multiple instances of items
		self.initItemController(self.item(), false);
	},

	initItemController: function(item, isNew) {
		item.addController('EasySocial.Controller.Comments.Item', {
			controller: {
				parent: self.parent
			},
			isNew: isNew
		});

		return item;
	},

	'{parent} newCommentSaved': function(el, event, comment) {
		// Add the comment to the list
		self.addToList(comment);
	},

	addToList: function(comment, type, isNew) {
		// Set type to append by default
		type = type === undefined ? 'append' : type;

		// Set isNew to true by default
		isNew = isNew === undefined ? true : isNew;

		// Wrap comment in jQuery
		comment = $(comment);

		// Implement item controller on comment
		self.initItemController(comment, isNew);

		// Check if type is append/prepend
		if(type == 'append' || type == 'prepend') {

			// Prepare function values based on type (append/prepend)
			var filter = type == 'append' ? ':last' : ':first',
				action = type == 'append' ? 'after' : 'before';

			// Add the comment item into list
			if(self.item().length === 0) {
				// If no comments yet then add the html into the list
				self.list().html(comment);
			} else {
				// If there are existing comments, then append/prepend comment into the list
				self.item(filter)[action](comment);
			}
		} else {

			// If type is neither append or prepend, then type could be the comment id
			var item = self.parent.$Comments[type];

			// Check if type is a valid comment, if it is then by this means prepend on top
			if(item !== undefined) {
				item.element.before(comment);
			}
		}

		// Show the whole comment block because the block could be hidden
		self.parent.actionContent().show();
	},

	'{parent} commentDeleted': function(el, event, id) {
		// Remove this comment from comment registry
		if(self.parent.$Comments[id] !== undefined) {

			// Remove the element
			self.parent.$Comments[id].element.remove();

			// Remove the controller reference in the registry
			delete self.parent.$Comments[id];
		}
	}
}});

EasySocial.Controller('Comments.Stat', {
	defaultOptions: {
		'{stats}': '[data-comments-stats]',
		"count": 0,
		"total": 0,
		"limit": 10
	}
}, function(self, opts, base) { return {

	init: function() {
		opts.count = self.element.data('count');
		opts.total = self.element.data('total');
	},

	// Get / set total comments
	total: function(count) {

		if (count !== undefined) {
			opts.total = parseInt(count);

			var visible = self.stats().find('[data-visible]');
			visible.text(self.count());

			var total = self.stats().find('[data-total]');
			total.text(self.total());
		}

		return opts.total;
	},

	// Get / set current comments
	count: function(count) {

		if (count !== undefined) {
			opts.count = parseInt(count);

			var visible = self.stats().find('[data-visible]');
			visible.text(self.count());

			var total = self.stats().find('[data-total]');
			total.text(self.total());
		}

		return opts.count;
	},

	getNextCycle: function() {
		var start = Math.max(self.total() - self.count() - opts.limit, 0);
		var limit = self.total() - self.count() - start;

		return {
			"start": start,
			"limit": limit
		};
	},

	'{parent} oldCommentsLoaded': function(el, event, comments) {
		var count = comments.length;

		self.count(self.count() + count);
	},

	'{parent} newCommentSaved': function(element, event, html, stats) {

		self.total(self.total() + 1);
		self.count(self.count() + 1);
	},

	'{parent} commentDeleted': function() {
		self.total(self.total() - 1);

		self.count(self.count() - 1);
	}
} });

EasySocial.Controller('Comments.Load', {
	defaultOptions: {
		'{load}'		: '[data-comments-load]',
		'{loadMore}'	: '[data-comments-load-loadMore]'
	}
}, function(self) { return {

	'{loadMore} click': function(el, event) {
		if(el.enabled()) {

			// Disable the button
			el.disabled(true);

			// Get boundary details
			var cycle = self.parent.$Stat.getNextCycle();

			// If limit is 0, means no comment to load
			if (cycle.limit == 0) {
				return false;
			}

			// Send load comments command to the server
			self.loadComments(cycle.start, cycle.limit)
				.done(function(comments) {
					// Comments come in with chronological order array
					// Hence need to reverse comment and prepend from bottom

					// Create a copy of reverse comments to not affect the original array
					// Slice is to create a non reference copy of the array
					var reversedComments = comments.slice().reverse();

					$.each(reversedComments, function(index, comment) {
						self.parent.$List.addToList(comment, 'prepend', false);
					});

					// Trigger oldCommentsLoaded event
					self.parent.trigger('oldCommentsLoaded', [comments]);

					// Enable the button
					el.enabled(true);

					// If start is 0, means this is the last round of comments to load
					cycle.start == 0 && self.load().hide();

					// re-init reaction when in mobile
					if (window.es.mobile || window.es.tablet) {
						window.es.initReactions();
					}

				})
				.fail(function(msg) {

					// Trigger oldCommentsLoadError event
					self.parent.trigger('oldCommentsLoadError', [msg]);
				});
		}
	},

	loadComments: function(start, limit) {

		limit = limit || 10;

		return EasySocial.ajax('site/controllers/comments/load', {
			uid: self.parent.options.uid,
			element: self.parent.options.element,
			group: self.parent.options.group,
			verb: self.parent.options.verb,
			start: start,
			length: limit
		});
	}
}});




module.resolve();
});


});
