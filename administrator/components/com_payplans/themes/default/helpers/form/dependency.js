PayPlans.ready(function($) {

dependency = self = {
	
	data: null,

	init: function(data) {
		self.data = data;

		// Run once
		self.update(data);

		self.element().on('change', function() {
			self.update(data);
		});
	},

	update: function(data) {
		self.data = data;

		// Hide all dependents for the current element element
		self.hide();

		// If there are dependents, hide the dependents
		var selectedOption = self.element().find('option:selected');
		var dependents = selectedOption.data('dependency-for');

		if (dependents.length <= 0) {
			return;
		}

		self.show(dependents);
	},

	element: function() {
		return $('[data-dependency-' + this.data + ']');	
	},

	options: function() {
		var options = self.element().children();

		return options;
	},
	
	hasDependents: function(option) {
		var dependents = option.data('dependency-for');

		if (dependents.length <= 0) {
			return false;
		}

		return true;
	},

	hide: function() {
		var options = self.options();

		$.each(options, function() {
			var option = $(this);

			if (!self.hasDependents(option)) {
				return;
			}

			var dependents = option.data('dependency-for');
			dependents = dependents.split(',');

			$.each(dependents, function() {

				if (this.length <= 0) {
					return;
				}
				
				var selector = '[' + this + ']';
				var el = $(selector);

				if (el.length <= 0) {
					return;
				}

				// console.log(selector);

				var inputParent = el.parents('.o-form-group');

				if (inputParent.length <= 0) {
					return;
				}

				inputParent.addClass('t-hidden');
			});
		});
	},

	show: function(dependents) {
		dependents = dependents.split(',');

		$.each(dependents, function() {
			if (this.length <= 0) {
				return;
			}
			
			var el = $('[' + this + ']');

			if (el.length <= 0) {
				return;
			}

			var inputParent = el.parents('.o-form-group');

			if (inputParent.length <= 0) {
				return;
			}

			inputParent.removeClass('t-hidden');
		});
	}
};


dependency.init('<?php echo $uid;?>');
});