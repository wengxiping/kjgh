
$(document).ready(function(){

	$('.hasTooltip').tooltip();

	loading = $('[data-installation-loading]'),
	submit = $('[data-installation-submit]'),
	retry = $('[data-installation-retry]'),
	form = $('[data-installation-form]'),
	completed = $('[data-installation-completed]'),
	source = $('[data-source]'),
	steps = $('[data-installation-steps]');
});


var es = {
	ajaxUrl: "<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&ajax=1",
	installation: {
		path: null,
		ajaxCall: function(task, properties, callback) {

			var prop = $.extend({
									"apikey": "<?php echo $input->get('apikey', '');?>",
									"path": es.installation.path
								}, properties);

			$.ajax({
				type: "POST",
				url: es.ajaxUrl + "&controller=installation&task=" + task ,
				data: prop
			}).done(function(result) {
				callback.apply(this, [result]);
			});
		},

		showRetry: function(step) {

			steps.addClass('error');

			retry
				.data('retry-step', step)
				.removeClass('hide');

			// Hide the submit
			submit.addClass('hide');

			// Hide the loading
			loading.addClass('hide');
		},

		extract: function() {

			es.installation.setActive('data-progress-extract');

			es.installation.ajaxCall('extract', {}, function(result) {

				es.installation.update('data-progress-extract', result, '10%');

				if (!result.state) {
					return false;
				}

				es.installation.path = result.path;

				es.installation.runSQL();
			});
		},

		download: function() {

			es.installation.setActive('data-progress-download');

			es.installation.ajaxCall('download', {}, function(result){

				// Set the progress
				es.installation.update('data-progress-download' , result , '10%');

				if (!result.state) {
					es.installation.showRetry('download');
					return false;
				}

				// Set the installation path
				es.installation.path = result.path;
				es.installation.runSQL();
			});
		},

		runSQL: function() {
			// Install the SQL stuffs
			es.installation.setActive('data-progress-sql');

			es.installation.ajaxCall('installSQL', {}, function(result) {
				es.installation.update('data-progress-sql', result, '15%');

				if (!result.state) {
					es.installation.showRetry('runSQL');
					return false;
				}

				es.installation.installAdmin();
			});
		},

		installFiles: function () {
			es.installation.setActive('data-progress-files');
			es.installation.installAdmin();
		},

		installAdmin: function() {

			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('installCopy', {"type" : "admin"} , function(result) {
				// Set the progress
				es.installation.update('data-progress-admin', result, '20%');

				if (!result.state) {
					es.installation.showRetry('installAdmin');
					return false;
				}

				es.installation.installSite();
			});
		},

		installSite : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('installCopy', {"type" : "site"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-files', result, '25%');

				if (!result.state) {
					es.installation.showRetry('installSite');
					return false;
				}

				es.installation.installLanguages();
			});
		},

		installLanguages : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('installCopy', {"type" : "languages"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-files' , result , '25%');

				if (!result.state) {
					es.installation.showRetry('installLanguages');
					return false;
				}

				es.installation.installMedia();
			});
		},

		installMedia : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('installCopy', {"type" : "media"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-files', result, '30%');

				if (!result.state) {
					es.installation.showRetry('installMedia');
					return false;
				}

				es.installation.syncDB();
			});
		},

		syncDB: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-syncdb');

			es.installation.ajaxCall('syncDB', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-syncdb', result, '35%');

				if (!result.state) {
					es.installation.showRetry('syncDB');
					return false;
				}

				es.installation.installApps();
			});
		},


		installApps: function() {

			es.installation.setActive('data-progress-apps');
			es.installation.installUserApps();

		},

		installUserApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('installApps', {"group" : "user"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-apps', result, '40%');

				if (!result.state) {
					es.installation.showRetry('installUserApps');
					return false;
				}

				es.installation.installGroupApps();
			});
		},

		installGroupApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('installApps', {"group" : "group"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-apps', result, '45%');

				if (!result.state) {
					es.installation.showRetry('installGroupApps');
					return false;
				}

				es.installation.installPageApps();
			});
		},

		installPageApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('installApps', {"group" : "page" }, function(result) {

				// Set the progress
				es.installation.update('data-progress-apps', result, '46%');

				if (!result.state) {
					es.installation.showRetry('installPageApps');
					return false;
				}

				es.installation.installEventApps();
			});
		},

		installEventApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('installApps', {"group" : "event"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-apps', result, '48%');

				if (!result.state) {
					es.installation.showRetry('installEventApps');
					return false;
				}

				es.installation.installFields();
			});
		},

		installFields: function() {

			es.installation.setActive('data-progress-fields');
			es.installation.installUserFields();
		},

		installUserFields: function() {

			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('installFields', {"group" : "user"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-fields', result, '50%');

				if (!result.state) {
					es.installation.showRetry('installUserFields');
					return false;
				}

				es.installation.installGroupFields();
			});
		},

		installGroupFields: function() {
			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('installFields', {"group" : "group"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-fields', result, '55%');

				if (!result.state) {
					es.installation.showRetry('installGroupFields');
					return false;
				}

				es.installation.installPageFields();
			});
		},

		installPageFields: function() {
			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('installFields', {"group" : "page"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-fields' , result , '56%');

				if (!result.state) {
					es.installation.showRetry('installPageFields');
					return false;
				}

				es.installation.installEventFields();
			});
		},

		installEventFields: function() {
			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('installFields', { "group" : "event" } , function(result) {
				// Set the progress
				es.installation.update('data-progress-fields' , result , '58%');

				if (!result.state) {
					es.installation.showRetry('installEventFields');
					return false;
				}

				es.installation.installJoomlaExtensions();
			});
		},

		installJoomlaExtensions: function() {
			es.installation.setActive('data-progress-plugins');
			es.installation.installPlugins();
		},

		installPlugins: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-plugins');

			es.installation.ajaxCall('installPlugins', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-plugins' , result , '60%');

				if (!result.state) {
					es.installation.showRetry('installPlugins');
					return false;
				}

				es.installation.installModules();
			});
		},

		installModules: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-plugins');

			es.installation.ajaxCall('installModules', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-plugins', result , '65%');

				if (!result.state) {
					es.installation.showRetry('installModules');
					return false;
				}

				es.installation.uninstallModules();
			});
		},

		uninstallModules: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-plugins');

			es.installation.ajaxCall('uninstallModules', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-plugins', result , '67%');

				if (!result.state) {
					es.installation.showRetry('uninstallModules');
					return false;
				}

				es.installation.installAdminModules();
			});
		},

		installAdminModules: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-plugins');

			es.installation.ajaxCall('installAdminModules', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-plugins', result , '68%');

				if (!result.state) {
					es.installation.showRetry('installAdminModules');
					return false;
				}

				es.installation.installCores();
			});
		},

		installCores: function () {
			es.installation.setActive('data-progress-cores');
			es.installation.installBadges();
		},

		installBadges : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('installBadges' , {} , function( result )
			{
				// Set the progress
				es.installation.update('data-progress-cores' , result , '70%');

				if( !result.state )
				{
					es.installation.showRetry('installBadges');
					return false;
				}

				es.installation.installPoints();
			});
		},

		installPoints : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('installPoints', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-cores' , result , '75%');

				if (!result.state) {
					es.installation.showRetry('installPoints');
					return false;
				}

				es.installation.installAccess();
			});
		},

		installAccess : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('installAccess', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-cores' , result , '78%');

				if (!result.state) {
					es.installation.showRetry('installAccess');
					return false;
				}

				es.installation.installPrivacy();
			});
		},

		installPrivacy : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('installPrivacy' , {} , function(result) {
				// Set the progress
				es.installation.update('data-progress-cores' , result , '80%');

				if (!result.state) {
					es.installation.showRetry('installPrivacy');
					return false;
				}

				es.installation.installWorkflows();
			});
		},

		installWorkflows : function() {
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('installWorkflows', {}, function(result) {

				es.installation.update('data-progress-cores', result, '83%');

				if (!result.state) {
					es.installation.showRetry('installWorkflows');
					return false;
				}

				es.installation.installProfiles();
			})
		},

		installProfiles : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('installProfiles' , {} , function(result) {
				// Set the progress
				es.installation.update('data-progress-cores' , result , '85%');

				if (!result.state) {
					es.installation.showRetry('installProfiles');
					return false;
				}

				es.installation.installAlerts();
			});
		},

		installAlerts : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('installAlerts' , {} , function(result) {
				// Set the progress
				es.installation.update('data-progress-cores' , result , '97%');

				if (!result.state) {
					es.installation.showRetry('installAlerts');
					return false;
				}

				es.installation.installCategories();
			});
		},

		installCategories: function() {
			es.installation.setActive('data-progress-categories');
			es.installation.installGroupCategories();
		},

		installGroupCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('installDefaultGroupCategories', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-categories' , result , '90%');

				if (!result.state) {
					es.installation.showRetry('installGroupCategories');
					return false;
				}

				es.installation.installPageCategories();
			});
		},

		installPageCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('installDefaultPageCategories', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-categories' , result , '90%');

				if (!result.state) {
					es.installation.showRetry('installPageCategories');
					return false;
				}

				es.installation.installEventCategories();
			});
		},

		installEventCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('installDefaultEventCategories' , {} , function(result) {
				// Set the progress
				es.installation.update('data-progress-categories' , result , '92%');

				if (!result.state) {
					es.installation.showRetry('installEventCategories');
					return false;
				}

				es.installation.installVideoCategories();
			});
		},

		installVideoCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('installDefaultVideoCategories' , {} , function(result) {
				// Set the progress
				es.installation.update('data-progress-categories' , result , '94%');

				if (!result.state) {
					es.installation.showRetry('installVideoCategories');
					return false;
				}

				es.installation.installAudioGenres();
			});
		},

		installAudioGenres : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('installDefaultAudioGenres' , {} , function(result) {
				// Set the progress
				es.installation.update('data-progress-categories' , result , '96%');

				if (!result.state) {
					es.installation.showRetry('installAudioGenres');
					return false;
				}

				es.installation.installSocialElements();
			});
		},

		installSocialElements: function () {

			es.installation.setActive('data-progress-reactions');
			es.installation.installReactions();
		},

		installReactions : function() {
			es.installation.setActive('data-progress-reactions');

			es.installation.ajaxCall('installReactions', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-reactions', result, '98%');

				if (!result.state) {
					es.installation.showRetry('installReactions');
					return false;
				}

				es.installation.installEmoticons();
			});
		},

		installEmoticons : function() {
			es.installation.setActive('data-progress-reactions');

			es.installation.ajaxCall('installEmoticons', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-reactions', result, '99%');

				if (!result.state) {
					es.installation.showRetry('installEmoticons');
					return false;
				}

				es.installation.postInstall();
			});
		},

		postInstall : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-postinstall');

			es.installation.ajaxCall('installPost' , {} , function(result) {

				// Set the progress
				es.installation.update('data-progress-postinstall' , result , '100%');

				if (!result.state) {
					es.installation.showRetry('postInstall');
					return false;
				}

				$('[data-installation-completed]').show();

				$('[data-installation-loading]').hide();

				$('[data-installation-submit]').removeClass('hide');
				$('[data-installation-submit]').show();

				$('[data-installation-submit]').bind('click' , function(){
					$('[data-installation-form]').submit();
				});

			});
		},

		update : function( element, obj, progress) {
			var className = obj.state ? ' text-success' : ' text-error',
				stateMessage = obj.state ? 'Success' : 'Failed',
				stateIcon = obj.state ? 'icon-checkmark text-success' : 'icon-warning text-error';

			// Update the icon
			$('[' + element + ']')
				.find('.progress-icon > i')
				.removeClass('icon-warning text-error loader')
				.addClass(stateIcon);

			// Update the state
			$('[' + element + ']')
				.find('.progress-state')
				.html( stateMessage )
				.removeClass('text-info')
				.addClass(className);

			// Update the message
			// show only if there is an error
			if (! obj.state) {
				$('[' + element + ']')
					.find('.notes')
					.html( obj.message )
					.removeClass('text-info')
					.addClass(className);
			}


			// Update the progress
			// es.installation.updateProgress(progress);
		},

		// updateProgress : function(percentage) {
		// 	$('[data-progress-bar]').css('width' , percentage );
		// 	$('[data-progress-bar-result]').html( pexercentage );
		// },

		setActive : function(item) {
			$('[data-progress-active-message]').html( $('[' + item + ']').find('.split__title').html() + ' ...');
			$('[' + item + ']').removeClass('pending').addClass('active');
			$('[' + item + ']').find('.progress-icon > i') .removeClass('icon-radio-unchecked') .addClass('loader');

			// remove error message if any.
			$('[' + item + ']').find('.progress-state').removeClass('text-error').addClass('text-info').text('Initializing');
			$('[' + item + ']').find('.notes').removeClass('text-error').text('');
		}
	},
	maintenance :
	{
		totalSyncUsers: 0,
		totalProfileUsers: 0,

		numUsers: 0,
		numProfiles: 0,

		init: function()
		{
			// Initializes the installation process.
			es.maintenance.getTotalUsers('users');
		},

		updateProgress: function(element, value) {

			var progressBar = $('[' + element + ']').find('[data-progress-bar]');
			var progressBarResult = $('[' + element + ']').find('[data-progress-bar-result]');

			var currentWidth = value;

			if (currentWidth == undefined) {
				// update the progress bar here
				currentWidth = parseInt(progressBar[0].style.width);
				currentWidth++;
			}

			var percentage = Math.round(currentWidth);

			progressBar.css('width', percentage + '%');
			progressBarResult.html(percentage + '%');

		},

		getTotalUsers: function(type) {

			var ns = type == 'users' ? 'getTotalUnsyncUsers' : 'getTotalUnsyncProfileUsers'

			$.ajax({
				type: 'POST',
				url: es.ajaxUrl + '&controller=maintenance&task=' + ns,
			})
			.done(function(result){

				if (type == 'users') {
					es.maintenance.totalSyncUsers = result;
					es.maintenance.syncUsers();

				} else {
					es.maintenance.totalProfileUsers = result;
					es.maintenance.syncProfiles();
				}

			});
		},


		syncUsers : function() {

			var progress = $('[data-users-progress]');
			var progressBar = $('[data-users-progress]').find('[data-progress-bar]');
			var progressActiveMsg = $('[data-users-progress]').find('[data-progress-active-message]');
			var progressCompleteMsg = $('[data-users-progress]').find('[data-progress-active-message]');

			progress.removeClass('hide');

			if (es.maintenance.totalSyncUsers == 0) {

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-users-progress', 100);

				progressActiveMsg
					.addClass('hide');

				progressCompleteMsg
					.removeClass('hide');

				return es.maintenance.getTotalUsers('profiles');
			}

			$.ajax({
				type : "POST",
				url : es.ajaxUrl + "&controller=maintenance&task=syncUsers"
			})
			.done(function(result) {

				es.maintenance.numUsers++;
				var progressUnit = es.maintenance.totalSyncUsers - (es.maintenance.totalSyncUsers - es.maintenance.numUsers);

				// convert into percentage
				progressUnit = (progressUnit * 100) / es.maintenance.totalSyncUsers;
				var percentage = Math.round(progressUnit);

				if (percentage <= 0) {
					// lets set it atlease 1%
					percentage = 1;
				}

				es.maintenance.updateProgress('data-users-progress', percentage);

				// If there are more items to process, call itself again.
				if (result.state == 2) {
					return es.maintenance.syncUsers();
				}

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-users-progress', 100);

				progressActiveMsg
					.addClass('hide');

				progressCompleteMsg
					.removeClass('hide');

				es.maintenance.getTotalUsers('profiles');
			});
		},

		syncProfiles : function() {
			// $('[data-progress-syncprofiles]').addClass('active').removeClass('pending');

			var progress = $('[data-profiles-progress]');
			var progressActiveMsg = $('[data-profiles-progress]').find('[data-progress-active-message]');
			var progressCompleteMsg = $('[data-profiles-progress]').find('[data-progress-active-message]');

			progress.removeClass('hide');

			if (es.maintenance.totalProfileUsers == 0) {

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-profiles-progress', 100);

				progressActiveMsg
					.addClass('hide');

				progressCompleteMsg
					.removeClass('hide');

				return es.maintenance.execMaintenance();
			}

			$.ajax({
				type : "POST",
				url : es.ajaxUrl + "&controller=maintenance&task=syncProfiles"
			})
			.done( function(result) {

				es.maintenance.numProfiles++;

				var progressUnit = es.maintenance.totalProfileUsers - (es.maintenance.totalProfileUsers - es.maintenance.numProfiles);

				// convert into percentage
				progressUnit = (progressUnit * 100) / es.maintenance.totalProfileUsers;
				var percentage = Math.round(progressUnit);

				if (percentage < 1) {
					// lets set it atlease 1%
					percentage = 1;
				}

				es.maintenance.updateProgress('data-profiles-progress', percentage);

				// If there are more items to process, call itself again.
				if (result.state == 2) {
					return es.maintenance.syncProfiles();
				}

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-profiles-progress', 100);

				progressActiveMsg
					.addClass('hide');

				progressCompleteMsg
					.removeClass('hide');

				es.maintenance.execMaintenance();
			});
		},

		execMaintenance: function() {
			var frame = $('[data-progress-execscript]');

			frame.addClass('active').removeClass('pending');

			var progress = $('[data-sync-progress]');
			progress.removeClass('hide');

			$.ajax({
				type: 'POST',
				url: es.ajaxUrl + '&controller=maintenance&task=getScripts'
			})
			.done(function(result){

				var item = $('<li>');
				item.addClass('text-success').html(result.message);

				$('[data-progress-execscript-items]').append(item);

				es.maintenance.runScript(result.scripts, 0);
			});
		},

		runScript: function(scripts, index) {

			if (scripts[index] === undefined) {
				// If the logics come here, means we are done with running scripts

				// run script completed. lets update the scriptversion
				$.ajax({
					type: 'POST',
					url: es.ajaxUrl + '&controller=maintenance&task=updateScriptVersion'
				}).done(function(result) {
					var item = $('<li>');
					item.addClass('text-success').html(result.message);
					$('[data-progress-execscript-items]').append(item);

					$('[data-progress-execscript]')
						.find('.progress-state')
						.html( result.stateMessage )
						.addClass('text-success')
						.removeClass('text-info');

					var progress = $('[data-sync-progress]');
					var progressActiveMsg = $('[data-sync-progress]').find('[data-progress-active-message]');
					var progressCompleteMsg = $('[data-sync-progress]').find('[data-progress-active-message]');

					progressActiveMsg
						.addClass('hide');

					progressCompleteMsg
						.removeClass('hide');

					es.maintenance.updateProgress('data-sync-progress', 100);

					es.maintenance.complete();
				});

				return true;
			}

			$.ajax({
				type: 'POST',
				url: es.ajaxUrl + '&controller=maintenance&task=runScript',
				data: {
					script: scripts[index]
				}
			})
			.always(function(result) {

				var item = $('<li>'),
					className	= result.state ? 'text-success' : 'text-error';

				item.addClass(className).html(result.message);

				$('[data-progress-execscript-items]').append(item);

				es.maintenance.updateProgress('data-sync-progress', index);


				es.maintenance.runScript(scripts, ++index);
			});
		},

		complete: function() {
			$('[data-installation-loading]').hide();
			$('[data-installation-submit]').show();

			$('[data-installation-submit]').on('click', function() {
				$('[data-installation-form]').submit();
			});
		}
	}
}
