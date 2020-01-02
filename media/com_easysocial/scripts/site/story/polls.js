EasySocial.module('site/story/polls', function($) {

var module = this;

EasySocial.Controller('Story.Polls', {
	defaultOptions: {
		tmpForm: null,
		'{storyForm}': '[data-story-polls-form]',
		'{polls}': '[data-polls-form]'
	}
}, function(self, opts) { return {

	init: function() {

		// Clone initial form
		opts.tmpForm = self.storyForm().clone().html();
	},

	'{story} save': function(element, event, save) {

		if (save.currentPanel != 'polls') {
			return;
		}

		var pollController = self.polls().controller('EasySocial.Controller.Polls.Form');

		self.options.name = 'polls';

		var task = save.addTask('validatePollsForm');
		self.save(task, pollController);
	},

	save: function(task, pollController) {

		var valid = pollController.validateForm();

		if (!valid) {
			return task.reject(opts.error);
		}

		var data = pollController.toData();
		task.save.addData(self, data);

		task.resolve();
	},

	'{story} clear': function(element, event, clear) {
		self.storyForm().html(opts.tmpForm);

		var pollsForm = self.storyForm().find('[data-polls-form]');
		var controller = pollsForm.addController('EasySocial.Controller.Polls.Form');

		// Re-initialize the controller
		controller.init();
	}
}});

module.resolve();

});