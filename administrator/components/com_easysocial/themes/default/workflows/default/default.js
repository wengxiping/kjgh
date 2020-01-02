EasySocial
.require()
.script('admin/api/toolbar')
.done(function($){
	$.Joomla('submitbutton', function(task) {
		$.Joomla('submitform' , [task]);
	})
})