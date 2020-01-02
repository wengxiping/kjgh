EasySocial.ready(function($){

	EasySocial.compareVersion = function(version1, version2) {
		var nRes = 0;
		var parts1 = version1.split('.');
		var parts2 = version2.split('.');
		var nLen = Math.max(parts1.length, parts2.length);

		for (var i = 0; i < nLen; i++) {
			var nP1 = (i < parts1.length) ? parseInt(parts1[i], 10) : 0;
			var nP2 = (i < parts2.length) ? parseInt(parts2[i], 10) : 0;

			if (isNaN(nP1)) {
				nP1 = 0;
			}

			if (isNaN(nP2)) {
				nP2 = 0;
			}

			if (nP1 != nP2) {
				nRes = (nP1 > nP2) ? 1 : -1;
				break;
			}
		}

		return nRes;
	}

	// Get the current version of EasySocial
	$.ajax({
		url: "<?php echo SOCIAL_SERVICE_VERSION;?>",
		jsonp: "callback",
		dataType: "jsonp",
		data: {
			"apikey": "<?php echo $this->config->get('general.key');?>",
			"current": "<?php echo $version;?>"
		},
		success: function(data) {

			if (data.error) {
				$('#es.es-backend').prepend('<div style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;" class="app-alert o-alert o-alert--danger"><div class="row-table"><div class="col-cell cell-tight"><i class="app-alert__icon fa fa-bolt"></i></div><div class="col-cell alert-message">' + data.error + '</div></div></div>');
			}

			// Update the latest version
			$('[data-latest-version]').html(data.version);

			var versionSection = $('[data-version-status]');
			var currentVersion = $('[data-current-version]');
			var installedSection = $('[data-version-installed]');

			var version = {
				"latest": data.version,
				"installed": '<?php echo $version;?>'
			};

			var outdated = EasySocial.compareVersion(version.installed, version.latest) === -1;

			if (versionSection.length > 0) {
				currentVersion.html(version.installed);
				installedSection.removeClass('hide');
				versionSection.removeClass('is-loading');

				// Update version checking
				if (outdated) {
					versionSection.addClass('is-outdated');
				} else {
					versionSection.addClass('is-updated');
				}
			}

			// Update with banner
			var banner = $('[data-outdated-banner]');

			if (banner.length > 0 && outdated) {
				banner.removeClass('t-hidden');
			}
		}
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
		$('html').toggleClass('show-easysocial-sidebar');
		$('.subhead-collapse').removeClass('in').css('height', 0);
	});

	$('.nav-subhead-toggle').click(function(){
		$('html').removeClass('show-easysocial-sidebar');
		$('.subhead-collapse').toggleClass('in').css('height', 'auto');
	});
});
