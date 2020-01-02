<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_FEATURES'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'friends.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_FRIENDS_SYSTEM'); ?>
				<?php echo $this->html('settings.toggle', 'friends.invites.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ALLOW_FRIEND_INVITES'); ?>
				<?php echo $this->html('settings.toggle', 'followers.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_FOLLOWERS_SYSTEM'); ?>
				<?php echo $this->html('settings.toggle', 'privacy.enabled', 'COM_ES_ENABLE_PRIVACY'); ?>
				<?php echo $this->html('settings.toggle', 'points.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_POINTS'); ?>
				<?php echo $this->html('settings.toggle', 'activity.logs.enabled', 'COM_ES_ENABLE_ACTIVITY_LOGS'); ?>
				<?php echo $this->html('settings.toggle', 'polls.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_POLLS'); ?>
				<?php echo $this->html('settings.toggle', 'badges.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_ACHIEVEMENT_SYSTEM'); ?>
				<?php echo $this->html('settings.toggle', 'ads.enabled', 'COM_ES_GENERAL_SETTINGS_ENABLE_ADS_SYSTEM'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_GENERAL_SETTINGS_ADVERT_DISPLAY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'ads.frequency', $this->config->get('ads.frequency'), array(
								array('value' => '5', 'text' => 'COM_ES_GENERAL_SETTINGS_ADVERT_DISPLAY_EVERY_FIVE'),
								array('value' => '10', 'text' => 'COM_ES_GENERAL_SETTINGS_ADVERT_DISPLAY_EVERY_TEN'),
								array('value' => '20', 'text' => 'COM_ES_GENERAL_SETTINGS_ADVERT_DISPLAY_EVERY_TWENTY')
							)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'apps.browser', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_APPLICATIONS'); ?>
				<?php echo $this->html('settings.toggle', 'apps.tnc.enabled', 'COM_ES_ENABLE_TNC_FOR_APPS'); ?>
				<?php echo $this->html('settings.textarea', 'apps.tnc.message', 'COM_EASYSOCIAL_GENERAL_SETTINGS_TERMS_AND_CONDITIONS_MESSAGE'); ?>
				<?php echo $this->html('settings.toggle', 'welcome.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_WELCOME_MESSAGE'); ?>
				<?php echo $this->html('settings.textarea', 'welcome.text', 'COM_EASYSOCIAL_GENERAL_SETTINGS_MESSAGE'); ?>
				<?php echo $this->html('settings.toggle', 'search.minimum', 'COM_ES_ENABLE_MINIMUM_CHARACTERS_FOR_SEARCH'); ?>
				<?php echo $this->html('settings.textbox', 'search.characters', 'COM_ES_MINIMUM_CHARACTERS_BEFORE_ALLOW_SEARCH', '', array('postfix' => 'Characters'), '', 'input-short text-center'); ?>
				<?php echo $this->html('settings.toggle', 'search.suggestion', 'COM_ES_ENABLE_SEARCH_SUGGESTION'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_SETTINGS_LOCKDOWN'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'general.site.lockdown.enabled', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ENABLE_LOCKDOWN_MODE'); ?>
				<?php echo $this->html('settings.toggle', 'general.site.lockdown.registration', 'COM_EASYSOCIAL_GENERAL_SETTINGS_ALLOW_REGISTRATIONS_IN_LOCKDOWN_MODE'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_SOCIAL_SETTINGS_SOCIALBOOKMARKS_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'sharing.enabled', 'COM_EASYSOCIAL_SHARING_SETTINGS_ENABLE_SHARING'); ?>
				<?php echo $this->html('settings.toggle', 'sharing.vendors.email', 'COM_EASYSOCIAL_SHARING_SETTINGS_ENABLE_EMAIL'); ?>
				<?php echo $this->html('settings.textbox', 'sharing.email.limit', 'COM_EASYSOCIAL_SHARING_SETTINGS_LIMIT_PER_HOUR', '', array('postfix' => 'E-mails'), '', 'input-short text-center'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_REPORTS_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'reports.enabled', 'COM_EASYSOCIAL_REPORTS_SETTINGS_ENABLE_REPORTING'); ?>
				<?php echo $this->html('settings.toggle', 'reports.guests', 'COM_EASYSOCIAL_REPORTS_SETTINGS_ALLOW_GUESTS'); ?>
				<?php echo $this->html('settings.textbox', 'reports.notifications.emails', 'COM_EASYSOCIAL_REPORTS_SETTINGS_CUSTOM_EMAIL_ADDRESSES'); ?>
			</div>
		</div>
	</div>
</div>
