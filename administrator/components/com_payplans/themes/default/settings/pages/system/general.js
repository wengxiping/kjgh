PayPlans.ready(function($) {

	$('[data-pp-automated-cron]').on('change', function() {
		var checked = $(this).is(':checked');

		$('[data-pp-cron-frequency]').toggleClass('t-hidden', !checked);
	});

	var self = this;
	
	this.editKey = function() {
		$('[data-key-input]').removeAttr('disabled');
		$('[data-key-update]').removeClass('t-hidden');
		$('[data-key-edit]').addClass('t-hidden');
	};

	this.doneEditKey = function() {
		$('[data-key-update]').addClass('t-hidden');
		$('[data-key-edit]').removeClass('t-hidden');
		$('[data-key-input]').attr('disabled', 'disabled');
	};


	$('[data-key-update]').on('click', function() {
		self.doneEditKey();
	});

	$('[data-key-edit]').on('click', function(){
		var value = $('[data-key-input]').val();

		if (value != '') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/config/editKeyDialog'),
				"bindings": {
					"{submitButton} click": function() {

						self.editKey();

						PayPlans.dialog.close();
					}
				}
			});

			return;
		}
	});

});