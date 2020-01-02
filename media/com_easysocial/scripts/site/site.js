EasySocial.require()
.script(
	'site/api/data',
	'site/api/admin',
	'site/api/popbox',
	'site/api/likes',
	'site/api/repost',
	'site/api/photos',
	'site/api/oauth',
	'site/api/share',
	'site/api/stream',
	'site/locations/popbox',
	'site/api/floatlabels',
	'site/api/mobile',

	// Layouts
	'shared/responsive',
	'shared/elements',
	'shared/popdown',
	'shared/privacy'
)
.library('history', 'dialog').done(function(){

	if (window.es.mobile) {
		EasySocial.require()
		.library('swiper')
		.done(function($) {

			var swiper = new Swiper('.swiper-container', {
				"freeMode": true,
				"slidesPerView": 'auto',
				"visibilityFullFit": true,
				"freeModeFluid": true,
				"slidesOffsetAfter": 88

			});
		});

		// Fixed issue with link opening new tab in mobile safari #1681
		(function(document,navigator,standalone) {

			if ((standalone in navigator) && navigator[standalone]) {
				var curnode,
					location = document.location,
					stop = /^(a|html)$/i;

				document.addEventListener('click', function(e) {
					curnode = e.target;

					while (!(stop).test(curnode.nodeName)) {
						curnode = curnode.parentNode;
					}

					if ('href' in curnode && (curnode.href.indexOf('http') || ~curnode.href.indexOf(location.host))) {
						e.preventDefault();
						location.href = curnode.href;
					}
				},false);
			}
		})(document, window.navigator, 'standalone');
	}
});
