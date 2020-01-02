
EasySocial
.require()
.script('site/events/create')
.done(function($){
	$('[data-es-select-category]').implement(EasySocial.Controller.Events.Create);
});
