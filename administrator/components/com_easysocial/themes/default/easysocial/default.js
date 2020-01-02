EasySocial.require()
.done(function($) {

	// Bind the news controller on the news widget.
	$('[data-dashboard]').implement(EasySocial.Controller.News);

	$.Joomla('submitbutton', function(task) {

		if (task == 'clearCache') {
			EasySocial.dialog({
				content: EasySocial.ajax( 'admin/views/easysocial/confirmPurgeCache'),
				bindings: {
					"{purgeButton} click" : function() {
						this.form().submit();
						return false;
					} 
				}
			});
		}
	});

	// Fix chart plot not showing. #1712
	setTimeout(function() {
		var activeTab = $('.active[data-form-tabs]').data('item');
		var contents = $('[data-dashbooard-content-tab]').children();

		activeTab = activeTab + '-tabs';

		$.each(contents, function() {
			var tab = this;

			if (tab.id !== activeTab) {
				tab.removeClass('active');
			}
		})
	}, 210);
});