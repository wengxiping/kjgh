(function($) {
	var JUB = {};

	JUB.init = function () {
		$(window).on('load', function () {
			var $aff = $('.affix-top');
			if (!$aff.length) return ;
			$aff.each(function(){
				var $container = $(this);
				$container.parent().css('min-height',$container.outerHeight());
				if ($container.data('bs.affix')) {
					// update offset
					$container.data('bs.affix').options.offset.top = $container.offset().top;
				} else {
					try {
						$container.affix({
							offset: {
							 	top: $container.offset().top
							}
						})
					} catch (e) {
						if (console) console.log ('Error: ', e);
					}
				}
			})
		})
	}

	JUB.ready = function () {
		$('.animated[data-animated-type]').css('opacity',0);
		$('.animated[data-animated-type]').on('inview', function (event, isInView) {
			var $this = $(this),
				animatedClass = $this.data('animated-type');
			if (!animatedClass) return;
			if (isInView) {
				$this.css('opacity',1);
				$this.addClass(animatedClass);
			}
		})
	}

	JUB.afterRenderBlock = function (callback, blockid) {
		$block = $('[data-jub-block-id="' + blockid + '"]');
		if (!$block.length) return;
		callback($, $block);
	}

	window.JUB = JUB;

	JUB.init();

	$(document).ready(function() {
		JUB.ready();
	})
})(jQuery)