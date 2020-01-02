EasySocial.require()
.done(function($) {

	// https://github.com/joomla/joomla-cms/issues/475
	// Override if Mootools loaded
	if (typeof MooTools != 'undefined' ) {
		var mHide = Element.prototype.hide;
		var mShow = Element.prototype.show;
		var mSlide = Element.prototype.slide;

		Element.implement({
			hide: function () {
				if (this.hasClass("mootools-noconflict")) {
					return this;
				}
				mHide.apply(this, arguments);
			},

			show: function (v) {
				if (this.hasClass("mootools-noconflict")) {
					return this;
				}
				mShow.apply(this, v);
			},

			slide: function (v) {
				if (this.hasClass("mootools-noconflict")) {
					return this;
				}
				mSlide.apply(this, v);
			}
		});
	};


	$('[data-es-ads-showcase]').on('slid.bs.carousel', function() {
		// console.log(this);
	});

	// Prev and Next button
	$('a[data-bp-slide="prev"]').click(function() {
		console.log($('[data-es-ads-showcase]').carousel('prev'));
		$('[data-es-ads-showcase]').carousel('prev');
	});
	$('a[data-bp-slide="next"]').click(function() {
		$('[data-es-ads-showcase]').carousel('next');
	});

	$('[data-es-ads-showcase]').carousel({
		pause: 'hover'
	});

	$('[data-module-ads-link]').click(function() {
		var item = $(this).parents('[data-module-ads-item]');
		var href = item.data('link');

		EasySocial.ajax('site/controllers/ads/click', {
			"id" : item.data('id')
		});

		window.open(href);
	})
});
