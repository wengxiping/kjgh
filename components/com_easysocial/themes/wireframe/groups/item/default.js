
EasySocial
.require()
.script('site/stream/filter')
.done(function($){

	// Process the title so that there is no double qoute issue.
	var title = "<?php echo addslashes($title);?>";

	$('[data-es-group]').implement('EasySocial.Controller.Stream.Filter', {
		"title": title,
		"type": "<?php echo SOCIAL_TYPE_GROUP;?>",
		"uid": "<?php echo $group->id;?>",
		"ajaxNamespace": "site/controllers/groups/getStream",
		"ajaxController": "groups",
		"ajaxTask": "getStream",
		"ajaxOptions": {
			"groupId": "<?php echo $group->id;?>"
		}
	});

	// Implement puller on the dashboard
	<?php if ($this->isMobile()) { ?>
	EasySocial.require()
	.script('site/vendors/puller')
	.done(function($) {

		window.initPuller = function() {
			return window.es.puller.init({
									mainElement: '.es-profile-header',
									triggerElement: '.es-profile-header',
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
