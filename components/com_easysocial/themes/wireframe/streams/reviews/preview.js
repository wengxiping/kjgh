EasySocial
.require()
.script('site/vendors/jquery.raty')
.done(function($){
	var ratings = $('[data-es-ratings-stars-<?php echo $reviews->id; ?>] ');
	ratings.raty({
		score: ratings.data('score'),
		readOnly: true
	});
});


