(function($){
    $(window).on('load',function () {
//     	var currentPaging = 0;
//     	var clickNav = 0;
		var owl = $('.owl-carousel').owlCarousel({
// 			items: 9,
			stagePadding: 50,
			margin: 10,
			slideBy: 'page',
			autoWidth: true,
			nav: true
		});

// 		owl.on('changed.owl.carousel', function(event) {
// 			if (event.property.name != 'position') return;
// 			if (clickNav < event.item.index) {
// 				clickNav = event.item.index;
// 				currentPaging++;
// 			} else {
// 				currentPaging--;
// 				clickNav = event.item.index;
// 			}
// 			
// 			var max = event.item.count;
// 			var currentItem = event.item.index;
// 			var paging = event.page.count;

// 			if (currentPaging==0) { // prev to first page.
// 				event.relatedTarget.to(0, 1000);
// 			}
// 			if ((currentPaging+1)*paging > max) { // last page.
// 				let lastItem = (max+1) - currentPaging*paging;
// 				event.relatedTarget.to(lastItem, 1000);
// 			}

// 			// normal navigation
// 			if ((currentPaging+1)*paging < max) {
// 				event.relatedTarget.to((currentPaging+1)*paging, 1000);
// 			}
// 		});
    });
})(jQuery);
