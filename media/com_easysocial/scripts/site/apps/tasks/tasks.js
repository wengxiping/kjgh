EasySocial.module('site/apps/tasks/tasks', function($) {

var module = this;

EasySocial.require()
.script('site/members/suggest')
.done(function($){

	$(document)
	.on('change.tasks.app', '[data-task-checkbox]', function() {
		var checkbox = $(this);
		var id = checkbox.data('id');
		var checked = checkbox.is(':checked');

		if (checked) {
			EasySocial.ajax('site/controllers/tasks/resolveTask', {
				"id": id
			}).done(function() {

			});
		} else {

			EasySocial.ajax('site/controllers/tasks/unresolveTask', {
				"id": id
			}).done(function() {

			});
		}
	});


	EasySocial.Controller('Apps.Tasks.Milestones.Form', {
		defaultOptions: {
			// Wrapper for suggest to work.
			"{memberSuggest}": "[data-members-suggest]",
			"exclusion": []
		}
	}, function(self, opts) { return {

		init: function() {

			opts.id = self.element.data('id');

			// console.log(opts.id);
			opts.uid = self.element.data('uid');

			// bind member suggest controller
			self.memberSuggest().addController(EasySocial.Controller.Members.Suggest, {"uid": opts.uid, "max": 1, "exclusion": opts.exclusion});
		}
	}});



	EasySocial.Controller('Apps.Tasks.Milestones.Browse', {
		defaultOptions: {
			eventId: null,
			"{milestone}": "[data-tasks-milestone-item]"
		}
	}, function(self, opts) { return {

		init: function() {
			opts.id = self.element.data('id');
			opts.type = self.element.data('type');

			self.milestone().addController(EasySocial.Controller.Apps.Tasks.Milestones.Item, {
				"{parent}": self,
				"return": opts.return
			});
		}
	}});

	EasySocial.Controller('Apps.Tasks.Milestones.Item', {
		defaultOptions: {
			"{task}": "[data-milestone-task]",
			"{delete}": "[data-milestone-delete]",
			"{milestone}": "[data-event-tasks-milestone-item]"
		}
	}, function(self, opts) { return {

		init: function() {
			opts.id = self.element.data('id');
		},

		"{task} click" : function(link, event) {
			var task = link.data('milestone-task');

			EasySocial.ajax('site/controllers/tasks/' + task, {
				"id": opts.id
			}).done(function() {

				if (task == 'resolve') {
					self.element
						.removeClass('is-due')
						.addClass('is-completed');
				}

				if (task == 'unresolve') {
					self.element
						.removeClass('is-completed')
						.addClass('is-due');
				}
			});
		},

		"{delete} click" : function() {


			EasySocial.dialog( {
				content : EasySocial.ajax('site/views/tasks/confirmDeleteMilestone', {
					"id": opts.id,
					"return": opts.return
				})
			});
		}
	}});

EasySocial.Controller('Apps.Tasks', {
	defaultOptions: {

		// Creating task items form
		"{create}": "[data-create]",
		"{input}": "[data-form-input]",
		"{assignee}": "[data-suggest-id]",
		"{edit}": "[data-task-edit]",
		"{cancelEdit}": "[data-cancel-edit]",
		"{saveEdit}": "[data-save-edit]",

		"{due}": "[data-form-due]",
		"{error}": "[data-form-error]",
		"{form}": "[data-form]",
		'{formWrapper}': '[data-tasks-form-wrapper]',
		'{taskList}': '[data-tasks-list]',

		// Tasks item
		"{item}": "[data-item]",
		'{checkbox}': '[data-item-checkbox]',
		'{deleteItem}': '[data-remove]',

		// Wrapper for suggest to work.
		"{memberSuggest}": "[data-members-suggest]",

		"{viewOpenTasks}": "[data-view-open-tasks]",

		// Completed list
		'{completedList}': '[data-tasks-completed]',

		// Counters
		'{openCounter}': '[data-tasks-open-counter]',
		'{closedCounter}': '[data-tasks-closed-counter]',

		"{completeMilestone}": "[data-milestone-mark-complete]",
		"{uncompleteMilestone}": "[data-milestone-mark-incomplete]",
		"{deleteMilestone}": "[data-milestone-delete]",
		"{wrapper}": "[data-tasks-wrapper]"
	}
}, function(self, opts) { return {

	init: function() {
		opts.id = self.element.data('id');

		// console.log(opts.id);
		opts.uid = self.element.data('uid');

		// bind member suggest controller
		self.memberSuggest().addController(EasySocial.Controller.Members.Suggest, {"uid": opts.uid, "max": 1});

	},

	updateOpenCounter: function(total) {
		self.openCounter().html(total);
	},

	updateClosedCounter: function(total) {
		self.closedCounter().html(total);
	},

	insertCompleted: function(task) {
		task.appendTo(self.completedList());
	},

	insertTask: function(task) {
		self.taskList().prepend(task);
	},

	resetForm: function() {
		var form = self.form();

		form[0].reset();
	},

	updateCounter: function(resolved) {
		var totalOpen = parseInt(self.openCounter().html());
		var totalClosed = parseInt(self.closedCounter().html());

		if (resolved) {
			self.updateOpenCounter(totalOpen - 1);
			self.updateClosedCounter(totalClosed + 1);
		} else {
			self.updateOpenCounter(totalOpen + 1);
			self.updateClosedCounter(totalClosed - 1);
		}
	},

	"{input} keyup" : function(el, event) {

		if(event.keyCode == 13) {
			self.create().click();
		}
	},

	"{saveEdit} click" : function(button) {

		button.addClass('is-loading');

		var taskId = button.data('save-edit-task-id');

		// Get the updated title
		var title = $('[data-edit-title-value]').val();

		if (title == '') {
			self.error().removeClass('t-hidden');
			return false;
		}

		// Hide the error message if there is title
		self.error().addClass('t-hidden');

		// Get the updated due date and assignee
		var due = $('[data-edit-due-value]').val();
		var user = self.assignee();
		var assignee = '';

		if (user !== undefined) {
			assignee = user.val();
		}

		EasySocial.ajax('site/controllers/tasks/saveEditedTask', {
			"title": title,
			"assignee": assignee,
			"due": due,
			"taskId": taskId
		}).done(function(task) {

			button.removeClass('is-loading');

			self.viewOpenTasksList();
		});
	},

	"{create} click" : function(button, event) {
		var title = self.input().val();

		if (title == '') {
			self.error().removeClass('t-hidden');
			return false;
		}

		// Hide the error message
		self.error().addClass('t-hidden');

		// Get the other properties
		var user = self.assignee();
		var assignee = '';

		if (user !== undefined) {
			assignee = user.val();
		}

		var due = self.due().val();

		EasySocial.ajax('site/controllers/tasks/saveTask', {
			"title": title,
			"assignee": assignee,
			"due": due,
			"milestoneId": opts.id
		}).done(function(task) {

			// Reset the form
			self.resetForm();

			// Increment the counter
			var total = parseInt(self.openCounter().html());
			self.updateOpenCounter(total + 1);

			// self.insertTask(task);

			// after create a new task, it will always show in the correct order
			self.viewOpenTasksList();
		});
	},

	"{uncompleteMilestone} click" : function() {
		EasySocial.ajax('site/controllers/tasks/unresolve', {
			id: opts.id
		}).done(function() {
			self.wrapper().removeClass('is-due').removeClass('is-completed');
		});
	},

	"{completeMilestone} click" : function() {
		EasySocial.ajax('site/controllers/tasks/resolve', {
			id: opts.id
		})
		.done(function() {
			self.wrapper().removeClass('is-due').addClass('is-completed');
		});
	},

	"{deleteMilestone} click" : function() {
		EasySocial.dialog({
			content : EasySocial.ajax('site/views/tasks/confirmDeleteMilestone', {
				"id": opts.id,
				"return": opts.return
			})
		});
	},


	'{deleteItem} click' : function(link, event) {
		var item = link.closest(self.item.selector);
		var id = item.data('id');
		var checked = item.find(self.checkbox.selector).is(':checked');

		EasySocial.ajax('site/controllers/tasks/deleteTask', {
			"id": id
		}).done(function() {

			if (checked) {
				var total = parseInt(self.closedCounter().html());
				self.updateClosedCounter(total - 1);
			}

			if (!checked) {
				var total = parseInt(self.openCounter().html());
				self.updateOpenCounter(total - 1);
			}

			item.remove();
		});
	},

	'{viewOpenTasks} click': function() {
		self.viewOpenTasksList();
	},

	'{edit} click': function(el) {

		var task_id = el.data('task-id');

		EasySocial.ajax('site/views/tasks/editTask', {
			"task_id": task_id
		}).done(function(output, taskId){
			var itemToEdit = "[data-id=" + taskId + "]";
			var taskList = $('[data-tasks-list]');
			var item = taskList.find(itemToEdit);

			// Hide the selected task item and show its edit form
			item.addClass('t-hidden');

			$('[data-tasks-list]').find("[data-edit-task-id=" + taskId + "]").removeClass('t-hidden');
			$('[data-tasks-list]').find("[data-edit-task-id=" + taskId + "]").html(output);
		});
	},

	"{cancelEdit} click": function(el) {
		var task_id = el.data('cancel-task-id');
     	var itemToEdit = "[data-id=" + task_id + "]";
		var taskList = $('[data-tasks-list]');
		var item = taskList.find(itemToEdit);

		item.removeClass('t-hidden');
		$('[data-tasks-list]').find("[data-edit-task-id=" + task_id + "]").addClass('t-hidden');
    },

	viewOpenTasksList: function() {

		var cluster_id = self.element.data('uid');
		var milestoneId = self.element.data('id');
		var type = self.element.data('task-cluster-type');

		EasySocial.ajax('site/views/tasks/viewOpenTasks', {
			"milestone_id": milestoneId,
			"cluster_id": cluster_id,
			"cluster_type": type
		}).done(function(output){
			$('[data-task-select-open]').html(output);
		});
	},

	'{checkbox} change': function(checkbox, event) {

		var checked = checkbox.is(':checked');
		var task = checked ? 'resolveTask' : 'unresolveTask';
		var item = checkbox.closest(self.item.selector);
		var id = item.data('id');


		EasySocial.ajax('site/controllers/tasks/' + task, {
			"id": id
		}).done(function() {

			self.updateCounter(checked);

			if (task == 'resolveTask') {
				self.insertCompleted(item);
			} else {
				self.insertTask(item);
			}
		});

	}
}});


module.resolve();
});

});

