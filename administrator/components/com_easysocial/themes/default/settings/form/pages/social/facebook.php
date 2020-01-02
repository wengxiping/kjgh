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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_SOCIAL_SETTINGS_FACEBOOK_GENERAL'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_ALLOW_REGISTRATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.facebook.registration.enabled', $this->config->get('oauth.facebook.registration.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_FACEBOOK_SETTINGS_FACEBOOK_OAUTH_REDIRECT_URI'); ?>

					<div class="col-md-7">
						<p>Effective <b>March 2018</b>, Facebook will be <a href="https://developers.facebook.com/blog/post/2017/12/18/strict-uri-matching/" target="_blank">imposing strict URI matching</a>. You will need to copy the links below and add it under the Valid OAuth redirect URIs section of the Facebook app.</p>
						<?php 
						$i = 1;
						foreach ($oauthFacebookURIs as $oauthFacebookURI) { ?>
							<div class="o-input-group mb-10">
								<input type="text" data-oauthuri-input id="facebook-oauth-uri-<?php echo $i?>" name="facebook-oauth-uri" class="o-form-control" value="<?php echo $oauthFacebookURI;?>" size="60" style="pointer-events:none;" />
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
					<?php echo $this->html('panel.label', 'COM_ES_FACEBOOK_SETTINGS_FACEBOOK_SCOPE_PERMISSION', true, '', 5); ?>

					<div class="col-md-7">
						<div class="help-block small">
							<p>Effective <b>April 24, 2018</b>, Facebook <a href="https://developers.facebook.com/blog/post/2018/04/24/new-facebook-platform-product-changes-policy-updates/" target="_blank">publish_actions permission will be deprecated</a>. Apps created from May 1, 2018 onwards will not have access to this <b>publish_actions</b> permission. Apps created before May 1, 2018 that have been previously approved to request <b>publish_actions</b> can continue to do so until August 1, 2018. And the rest of the permissions have to get approval from the Facebook reviewer before you select.</p>
						</div>

						<?php echo $this->html('form.scopes', 'oauth.facebook.scopes[]', 'oauth.facebook.scopes', $selectedScopesPermission); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_AUTOMATIC_LOGIN'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.facebook.autologin', $this->config->get('oauth.facebook.autologin')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_IMMEDIATE_LINKING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.facebook.autolink', $this->config->get('oauth.facebook.autolink')); ?>
					</div>
				</div>


				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_APP_ID'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.facebook.app', $this->config->get('oauth.facebook.app'), '', array('data-oauth-facebook-id')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_APP_SECRET'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.facebook.secret', $this->config->get('oauth.facebook.secret'), '', array('data-oauth-facebook-secret')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_ADD_OPENGRAPH_TAGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.facebook.opengraph.enabled', $this->config->get('oauth.facebook.opengraph.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_REGISTRATION_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'oauth.facebook.registration.type', $this->config->get('oauth.facebook.registration.type'), array(
							array('value' => 'simplified', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_SIMPLIFIED'),
							array('value' => 'normal', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_NORMAL')
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_PROFILE_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'oauth.facebook.profile', 'oauth.facebook.profile', $this->config->get('oauth.facebook.profile')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_USERNAME'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'oauth.facebook.username', $this->config->get('oauth.facebook.username'), array(
							array('value' => 'email', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_USERNAME_USE_EMAIL'),
							array('value' => 'name', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_USERNAME_USE_FULL_NAME')
						)); ?>
						<div class="help-block">
							<?php echo JText::_('COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_USERNAME_INFO'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_IMPORT_AVATAR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.facebook.registration.avatar', $this->config->get('oauth.facebook.registration.avatar')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_FACEBOOK_IMPORT_COVER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.facebook.registration.cover', $this->config->get('oauth.facebook.registration.cover')); ?>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_JFBCONNECT_INTEGRATIONS'); ?>

			<div class="t-lg-mt--xl">
				<img width="128" style="margin-left: 20px;margin-right:25px; float: left;" align="left" src="<?php echo JURI::base();?>components/com_easysocial/themes/default/images/sourcecoast.png" />

				<div style="overflow:hidden;">
					<?php echo JText::_('COM_EASYSOCIAL_FACEBOOK_SETTINGS_JFBCONNECT_INFO');?> <br /><br />

					<a href="http://www.shareasale.com/r.cfm?B=495360&U=614082&M=46720&urllink=" class="btn btn-es-primary-o btn-sm" target="_blank">
						<?php echo JText::_('COM_EASYSOCIAL_GET_JFBCONNECT_NOW');?>
					</a>
				</div>
			</div>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_USE_JFBCONNECT_BUTTONS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.facebook.jfbconnect.enabled', $this->config->get('oauth.facebook.jfbconnect.enabled')); ?>
						<div class="help-block">
							<?php echo JText::_('COM_EASYSOCIAL_FACEBOOK_SETTINGS_USE_JFBCONNECT_BUTTONS_INFO'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>