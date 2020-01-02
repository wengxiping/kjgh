PayPlans.ready(function($) {

	// Get the current version of Payplans
	$.ajax({
		url: "<?php echo PP_SERVICE_VERSION;?>",
		jsonp: "callback",
		dataType: "jsonp",
		data: {
			"apikey": "<?php echo $this->config->get('general.key');?>",
			"current": "<?php echo $version;?>"
		},
		success: function(data) {

			var newsSection = $('[data-dashboard-news]');
			var newsTemplate = $('[data-news-templates]');
			var news = data.news;

			if (data.news) {
				newsSection.removeClass('is-loading');

				$.each(data.news, function(key, news) {
					var template = newsTemplate.clone();

					template.find('[data-news-permalink]').attr('href', news.permalink);
					template.find('[data-news-title]').html(news.title);
					template.find('[data-news-content]').html(news.content);
					template.find('[data-news-meta]').html(news.date);
					template.find('[data-news-image]').attr('src', news.image);

					newsSection.append(template.html());
				})
			}

			if (data.error) {
				$('#pp.pp-backend').prepend('<div style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;" class="app-alert o-alert o-alert--danger"><div class="row-table"><div class="col-cell cell-tight"><i class="app-alert__icon fa fa-bolt"></i></div><div class="col-cell alert-message">' + data.error + '</div></div></div>');
			}

			// Hide version checker
			var versionChecker = $('[data-version-check]');
			versionChecker.addClass('t-hidden');

			var versionWrapper = $('[data-version-info]');

			var version = {
				"latest" : data.version,
				"installed": "<?php echo $version; ?>"
			}

			var outdated = PayPlans.compareVersion(version.installed, version.latest) === -1;

			if (outdated) {
				versionWrapper.find('[data-version-outdated]').removeClass('t-hidden');
				versionWrapper.find('[data-version-icon]').addClass('db-panel-item-icon--warning');
				versionWrapper.find('[data-version-update-button]').removeClass('t-hidden');
				versionWrapper.addClass('db-panel-item--warning t-hidden');
			} else {
				versionWrapper.find('[data-version-updated]').removeClass('t-hidden');
				versionWrapper.find('[data-version-icon]').addClass('db-panel-item-icon--success');
			}

			versionWrapper.find('[data-latest-version]').html(version.latest);
			versionWrapper.removeClass('t-hidden');

			// Update with banner
			var banner = $('[data-outdated-banner]');

			if (banner.length > 0 && outdated) {
				banner.removeClass('t-hidden');
			}
		}
	});

	$('[data-sidebar-parent]').on('click', function(event) {

		var item = $(this);
		var hasChild = item.data('childs') > 0 ? true : false;

		if (!hasChild) {
			return;
		}

		event.preventDefault();
		
		var parent = item.parent('[data-sidebar-item]');

		// Remove active 
		$('[data-sidebar-item]').removeClass('active');

		// Toggle dropdown
		parent.toggleClass('active');
	});

	// Fix the header for mobile view
	$('.container-nav').appendTo($('.header'));

	$(window).scroll(function () {
		if ($(this).scrollTop() > 50) {
			$('.header').addClass('header-stick');
		} else if ($(this).scrollTop() < 50) {
			$('.header').removeClass('header-stick');
		}
	});

	$('.nav-sidebar-toggle').click(function(){
		$('html').toggleClass('show-payplan-sidebar');
		$('.subhead-collapse').removeClass('in').css('height', 0);
	});

	$('.nav-subhead-toggle').click(function(){
		$('html').removeClass('show-payplan-sidebar');
		$('.subhead-collapse').toggleClass('in').css('height', 'auto');
	});
	
});