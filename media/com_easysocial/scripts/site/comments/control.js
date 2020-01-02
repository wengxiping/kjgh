EasySocial.module('site/comments/control', function($) {

var module = this;

/**
 *	Comments update controller
 *	Should only exist once on the page
 *	Act as a data handler between server and client for comments update (add/delete/edit etc)
 *	Global functions should be here as well
 */

EasySocial.Controller('CommentsControl', {
	defaultOptions: {
		interval: 30
	}
}, function(self, opts) { return {

	init: function() {
		// self.startUpdate();


		// listening to notifier.updates trigger
        $(window).on('notifier.updates', function(event, data) {

            if (data.comment == undefined) {
                return;
            }

            var result = data.comment.data;

			// Push updates to each comment block
			$.each(result, function(element, block) {
				$.each(block, function(uid, comments) {

					var comment = self.$Blocks[element][uid];

					if (comment._destroyed) {
						return;
					}

					comment.updateComment(comments);
				});
			});
        });

		// listening to notifier.collection trigger
        $(window).on('notifier.collection', function(event, data) {
            comments = self.populate();
            data.comment = comments;
        });

	},

	// Comments block registry
	$Blocks: {},

	startUpdate: function() {
		opts.monitoring = true;
		self.updateBlocks();
	},

	stopUpdate: function() {
		opts.monitoring = false;
	},

	updateBlocks: function(){

		(self.updateBlocks = $._.debounce(function() {

			var data = self.populate();

			if (!opts.monitoring) {
				return false;
			}

			EasySocial.ajax('site/controllers/comments/getUpdates', {
				data: data
			}).done(function(result) {

				// Push updates to each comment block
				$.each(result, function(element, block) {
					$.each(block, function(uid, comments) {

						var comment = self.$Blocks[element][uid];

						if (comment._destroyed) {
							return;
						}

						comment.updateComment(comments);
					});
				});

			}).always(function() {

				self.updateBlocks();
			});
		}, opts.interval * 1000))();
	},

	register: function(instance) {
		var group = instance.options.group;
		var element = instance.options.element;
		var streamid = instance.options.streamid;
		var uid = instance.options.uid;
		var verb = instance.options.verb;
		var timestamp = instance.options.timestamp;

		if (streamid == '') {
			streamid = '0';
		}

		var key = element + '.' + group + '.' + verb;

		if(self.$Blocks[key] === undefined) {
			self.$Blocks[key] = {};
		}

		// we need to use the stream id + uid so that for those aggregated items,
		// we can still get the comments correctly for each individual items. E.g. upload mulitple photos will create same stream id for each photo items.
		var blockkey = streamid + '.' + uid;

		self.$Blocks[key][blockkey] = instance;

		instance.trigger('commentBlockRegistered');
	},

	populate: function() {
		var data = {};

		$.each(self.$Blocks, function(key, block) {
			data[key] = {};

			$.each(block, function(blockkey, comments) {
				data[key][blockkey] = comments._export();
			});
		});

		return data;
	}
}});


EasySocial.ready(function(){

	// Implement this controller on to es-wrap
	EasySocial.Comments = $('body').addController('EasySocial.Controller.CommentsControl');

	module.resolve();
});


});
