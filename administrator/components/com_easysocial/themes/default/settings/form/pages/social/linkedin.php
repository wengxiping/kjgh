<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_SOCIAL_SETTINGS_LINKEDIN_GENERAL'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_ALLOW_REGISTRATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.linkedin.registration.enabled', $this->config->get('oauth.linkedin.registration.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_FACEBOOK_SETTINGS_FACEBOOK_OAUTH_REDIRECT_URI'); ?>

					<div class="col-md-7">
						<p>Effective <b>March 2019</b>, LinkedIn will be <a href="https://engineering.linkedin.com/blog/2018/12/developer-program-updates" target="_blank">migrating their API and OAuth to version 2.0</a>. With this new API, you will need to copy the links below and add it under OAuth 2.0 Redirect URLs settings in LinkedIn app.</p>
						<?php
						$i = 1;
						foreach ($oauthLinkedinURIs as $oauthLinkedinURI) { ?>
							<div class="o-input-group mb-10">
								<input type="text" data-oauthuri-input id="linkedin-oauth-uri-<?php echo $i?>" name="linkedin-oauth-uri" class="o-form-control" value="<?php echo $oauthLinkedinURI;?>" size="60" style="pointer-events:none;" />
								<span class="o-input-group__btn"
									data-oauthuri-button
									data-original-title="<?php echo JText::_('COM_ES_COPY_TOOLTIP')?>"
									data-placement="left"
									data-es-provide="tooltip"
								>
									<a href="javascript:void(0);" class="btn btn-es-default-o">
										<i class="fa fa-copy"></i>
									</a>
								</span>
							</div>
						<?php $i++; } ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_CLIENT_ID'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.linkedin.app', $this->config->get('oauth.linkedin.app')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_CLIENT_SECRET'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.linkedin.secret', $this->config->get('oauth.linkedin.secret')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_REGISTRATION_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'oauth.linkedin.registration.type', $this->config->get('oauth.linkedin.registration.type'), array(
							array('value' => 'simplified', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_SIMPLIFIED'),
							array('value' => 'normal', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_NORMAL')
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_PROFILE_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'oauth.linkedin.profile', 'oauth.linkedin.profile', $this->config->get('oauth.linkedin.profile')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_IMPORT_AVATAR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.linkedin.registration.avatar', $this->config->get('oauth.linkedin.registration.avatar')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>
</div>
