EasySocial.module('site/mobile/filters', function($) {

var module = this;

EasySocial.require()
.library('swiper')
.done(function($) {

EasySocial.Controller('Mobile.Filters', {
	defaultOptions: {
		"{sliders}": "[data-es-swiper-slider]",
		"{sliderGroup}": "[data-es-swiper-slider-group]",
		"{group}": "[data-es-swiper-filter-group]",
		"{groupSection}": "[data-es-swiper-group]"
	}
}, function(self, opts, base) { return {

	init: function() {
		var activeGroup = self.getActiveGroup();
		var slider = self.getSliderFromGroup(activeGroup);

		// Init slider that is already visible
		self.initSlider(slider);

		if (self.sliderGroup().length > 0) {
			self.initSlider(self.sliderGroup());
		}

		// hide inactive slider
		self.hideInactiveSlider();
	},

	hideInactiveSlider: function() {
		setTimeout(function() {
			self.groupSection().addClass('t-hidden');
			self.getActiveGroup().removeClass('t-hidden');
		}, 150);
	},

	getActiveGroup: function() {
		return self.groupSection('.is-active');
	},

	getSliderFromGroup: function(group) {
		return group.find(self.sliders.selector);
	},

	initSlider: function(slider) {
		var sliderController = slider.controller();

		// Ensure that it hasn't been implemented before
		if (sliderController === undefined) {
			slider.implement(EasySocial.Controller.Mobile.Filters.Slider);
		}
	},

	"{group} click": function(element) {
		var group = element.data('type');
		var section = self.groupSection('[data-type=' + group + ']');
		var isDialog = element.data('dialog') !== undefined;

		if (!isDialog) {

			if (group) {
				self.groupSection().addClass('t-hidden');
				section.removeClass('t-hidden');
			}

			var slider = self.getSliderFromGroup(section);
			self.initSlider(slider);
			slider.controller().refresh();
		}

		// For dialogs, we need to render a dialog
		if (isDialog) {
			var child = section.children().clone();
			child.removeClass('t-hidden');

			EasySocial.dialog({
				"content": child[0]
			});
		}
	}
}});

EasySocial.Controller('Mobile.Filters.Slider', {
	defaultOptions: {
		sliderOptions: {
			init: false,
			slidesPerView: 'auto',
			spaceBetween: 8,
			resistance: false,
			freeMode: true,
			freeModeMomentum: false,
			freeModeMomentumBounce: false,
			centerInsufficientSlides: false,
			freeModeSticky: false,
			centeredSlides: false
		},

		// Slider item
		"{item}": "[data-es-swiper-item]",
		"{container}": "[data-es-swiper-container]"
	}
}, function(self, opts) { return {
	init: function() {
		self.initSlider();
	},

	getSelected: function() {
		var activeIndex = 0;

		self.item().each(function(index, item) {
			var item = $(item);

			if (item.hasClass('is-active')) {
				activeIndex = index;
			}
		});

		return activeIndex;
	},

	getSliderContainer: function() {
		return self.element.find(self.container.selector);
	},

	// Binded to sly/swiper
	onMove: function() {
		self.element.removeClass('is-end-left is-end-right');

		if (self.element.swiper.isBeginning) {
			self.element.addClass('is-end-left');
		}

		if (self.element.swiper.isEnd) {
			self.element.addClass('is-end-right');
		}
	},

	initSlider: function() {
		// Initialize selected
		var selectedIndex = self.getSelected();

		var options = $.extend({}, opts.sliderOptions, {
			initialSlide: selectedIndex
		});

		// Activate sly/swiper animation
		self.element.swiper = new Swiper(self.getSliderContainer(), options);
		self.element.swiper.init();

		// Capture move event
		self.element.swiper.on('touchMove', self.onMove);
	},

	refresh: function() {
		setTimeout(function() {
			self.element.swiper.update();
		}, 16);
	},

	"{item} click": function(element) {
		var isDialog = element.data('dialog') !== undefined;

		// Check if the filter is rendering dialog
		if (!isDialog) {
			self.item().removeClass('is-active');
			element.addClass('is-active');
		}
	}
}});

module.resolve();

});

});

