EasySocial.module("site/cover/post.as", function($){

var module = this;

EasySocial
.require()
.done(function($) {

EasySocial.Controller("Postas", {
	defaultOptions: {
		"{postAsToggle}": "[data-postas-toggle]",
		"{postAsItem}": "[data-postas-menu] > [data-item]",
		"{postAsIcon}": "[data-postas-toggle] > [data-postas-icon]",
		"{postAsAvatar}": "[data-postas-toggle] > [data-postas-avatar]",
		"{postAsKey}": "[data-postas-hidden]"
	}
}, function(self, opts, base) { return {

	init: function() {
		var storyAvatar = $('[data-es-container] [data-story-avatar]');
		storyAvatar.attr("src",self.postAsAvatar().find('img').attr('src'));
	},

	"{postAsItem} click" : function(item) {

		var data = $._.pick(item.data(), "value");

		data.icon = item.data('postas-icon');
		data.avatar = item.find('[data-postas-avatar]').html();
		data.name = item.find('[data-postas-avatar]').data('name');

		// Deactivate other Post As item
		self.postAsItem().removeClass("is-active");
		self.postAsItem('[data-value=' + data.value + ']').addClass('is-active');

		// Set Post As value
		self.postAsKey().val(data.value);

		// Update the display
		self.postAsIcon().attr("class", data.icon);
		self.postAsAvatar().html(data.avatar);
		self.postAsToggle().attr('data-original-title', 'View page as ' + data.name);

		// Update the story form avatar
		var storyAvatar = $('[data-es-container] [data-story-avatar]');
		storyAvatar.attr("src",self.postAsAvatar().find('img').attr('src'));
	},

}});

// Resolve module
module.resolve();

});

});
