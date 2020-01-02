EasySocial.require()
.done(function($){
	
	var storyAvatar = $('[data-es-container] [data-story-avatar]');
	var postAsAvatar = $('[data-postas-toggle] > [data-postas-avatar]').find('img').attr('src')
	storyAvatar.attr("src", postAsAvatar);

	$('[data-postas-menu] > [data-item]').click(function(el){
		var clusterid = $('[data-postas-base]').data('clusterid');
		var returnUrl = $('[data-postas-base]').data('return-url');
		EasySocial.ajax('site/views/pages/togglePostAs', {
			"type": $(this).data('value'), 
			"id": clusterid,
			"return": returnUrl
		});
	});

	// $('[data-postas-base]').addController(EasySocial.Controller.Postas);
});
