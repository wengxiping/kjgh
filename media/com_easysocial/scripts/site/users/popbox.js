EasySocial.module("site/users/popbox", function($) {

var module = this;

EasySocial.require()
.library("popbox")
.done(function(){

	EasySocial.module("users/popbox", function($) {

		this.resolve(function(popbox) {

			var ids = popbox.button.data("ids");
			var position = popbox.button.attr("data-popbox-position") || "top-left";

			return {
				content: EasySocial.ajax("site/views/users/popbox", {ids: ids}),
				id: "es",
				component: "",
				type: "users",
				position: position
			};
		});
	});

});

module.resolve();

});
