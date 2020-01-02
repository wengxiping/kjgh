EasySocial.require()
.script('site/articles/suggest')
.done(function($) {
	$('[data-article-suggest]').addController(EasySocial.Controller.Articles.Suggest, {
		"max": 1,
		"name": 'config_<?php echo $name;?>'
	});
});