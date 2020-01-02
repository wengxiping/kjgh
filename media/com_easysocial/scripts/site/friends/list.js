EasySocial.module( 'site/friends/list' , function($){

	var module 	= this;

	EasySocial.require()
	.library('history')
	.script('site/friends/suggest')
	.done(function($){



		module.resolve();
	});
});
