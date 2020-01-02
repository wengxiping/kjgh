EasySocial.require()
.done(function($){

<?php if ($this->config->get('users.dashboard.guest', true)) { ?>
	EasySocial.ajax('site/controllers/dashboard/getPublicStream', {
		"hashtag": "<?php echo $hashtag;?>"
	})
	.done(function(content, count) {

		if (count == 0) {
			$('[data-wrapper]').addClass('is-empty');
		}

		// Update the contents of the dashboard area
		$('[data-wrapper]').removeClass("is-loading");

		$('body').trigger('beforeUpdatingContents');

		// Hide the content first.
		$.buildHTML(content)
			.appendTo($('[data-es-dashboard] [data-contents]'));

		$('body').trigger('afterUpdatingContents');

		// 3PD FIX: Kunena [text] replacement
		try {
			MathJax && MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
		} catch(err) {};

	}).fail(function(message) {
		return message;
	});
<?php } ?>


	// Implement puller on the dashboard
	<?php if ($this->isMobile()) { ?>
	EasySocial.require()
	.script('site/vendors/puller')
	.done(function($) {

		var targetElement = '[data-story-form]';

		// If story form not available, we use the first stream item
		if ($(targetElement).length == 0) {
			targetElement = '[data-stream-item]:first-child';
		}

		targetElement = '[data-es-container]';

		window.initPuller = function() {
			return window.es.puller.init({
									mainElement: targetElement,
									triggerElement: targetElement,
									onRefresh: function (done) {
										setTimeout(function () {
											var controller = $('body').controller(EasySocial.Controller.System.Notifier);

											controller.check(true, true);
											done();

										}, 150);
									}
								});
		};

		var puller = this.initPuller();
	});
	<?php } ?>
});
