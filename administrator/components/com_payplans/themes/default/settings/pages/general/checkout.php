<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row">
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_GENERAL_REGISTRATION'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_GENERAL_DEFAULT_FORM_ORDER'); ?>

					<div class="col-md-7 o-control-input">

						<?php echo $this->html('form.lists', 'default_form_order', $this->config->get('default_form_order'), '', '', array(
									array('title' => 'COM_PP_FORM_ORDER_LOGIN', 'value' => 'login'),
									array('title' => 'COM_PP_FORM_ORDER_REGISTER', 'value' => 'register'),
								)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_REGISTRATION_TYPE_LABEL'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.registrationType', 'registrationType', $this->config->get('registrationType', 'auto')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'easysocial' ? '' : 't-hidden';?>" data-es-social>
					<?php echo $this->html('form.label', 'COM_PP_ES_ALLOW_SOCIAL_LOGIN'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'registration_es_social', $this->config->get('registration_es_social')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'jomsocial' ? '' : 't-hidden';?>" data-jom-social>
					<?php echo $this->html('form.label', 'COM_PP_JOMSOCIAL_SKIP_PROFILETYPE_SELECTION'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'registration_skip_ptype', $this->config->get('registration_skip_ptype')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'jomsocial' ? '' : 't-hidden';?>" data-jom-social>
					<?php echo $this->html('form.label', 'COM_PP_JOMSOCIAL_DEFAULT_PROFILETYPE'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.jomsocialMultiprofile', 'js_default_profiletype', $this->config->get('js_default_profiletype')); ?>
					</div>
				</div>


				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_GENERAL_REGISTRATION_VERIFICATION'); ?>

					<div class="col-md-7 o-control-input">

						<?php echo $this->html('form.lists', 'account_verification', $this->config->get('account_verification'), '', '', array(
									array('title' => 'COM_PP_REQUIRE_VERIFICATION', 'value' => 'user'),
									array('title' => 'COM_PP_REQUIRE_MODERATION', 'value' => 'admin'),
									array('title' => 'COM_PP_AUTO_ACTIVATION', 'value' => 'auto')
								)); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('account_verification') == 'auto' && $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-autologin>
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_AUTO_LOGIN'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'autologin', $this->config->get('autologin')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'PLG_PAYPLANSREGISTRATION_AUTO_SEND_PASSWORD'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'send_password', $this->config->get('send_password')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'COM_PP_SHOW_FULL_NAME'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'show_fullname', $this->config->get('show_fullname')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'COM_PP_SHOW_USERNAME'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'show_username', $this->config->get('show_username')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'COM_PP_SHOW_CONFIRM_PASSWORD'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'show_confirmpassword', $this->config->get('show_confirmpassword')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_CHECKOUT_ASK_ADDRESS'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'show_address', $this->config->get('show_address')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_CHECKOUT_ASK_COUNTRY'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'show_country', $this->config->get('show_country')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'PLG_PAYPLANSREGISTRATION_AUTO_NOTIFY_ADMIN'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.toggler', 'notify_admin', $this->config->get('notify_admin')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('registrationType') == 'auto' ? '' : 't-hidden';?>" data-pp-auto>
					<?php echo $this->html('form.label', 'PLG_PAYPLANSREGISTRATION_AUTO_REDIRECT_URL'); ?>

					<div class="col-md-7 o-control-input is-column">
						<?php echo $this->html('form.text', 'activation_redirect_url', $this->config->get('activation_redirect_url')); ?>
						<div class="t-lg-mt-md t-text-muted">
							<?php echo JText::_('PLG_PAYPLANSREGISTRATION_AUTO_REDIRECT_URL_NOTICE'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_CAPTCHA_REGISTRATION'); ?>
			
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'show_captcha', 'COM_PP_DISPLAY_RECAPTCHA'); ?>
				<?php echo $this->html('settings.toggle', 'recaptcha_invisible', 'COM_PP_USE_INVISIBLE_RECAPTCHA'); ?>
				<?php echo $this->html('settings.textbox', 'recaptcha_sitekey', 'COM_PP_RECAPTCHA_SITE_KEY'); ?>
				<?php echo $this->html('settings.textbox', 'recaptcha_secretkey', 'COM_PP_RECAPTCHA_SECRET_KEY'); ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_RECAPTCHA_THEME'); ?>

					<div class="col-md-7 o-control-input">
						<?php echo $this->html('form.lists', 'recaptcha_theme', 'COM_PP_RECAPTCHA_THEME', '', '', array(
							array('title' => 'Light', 'value' => 'light'),
							array('title' => 'Dark', 'value' => 'dark')
						)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_RECAPTCHA_LANGUAGE'); ?>

					<div class="col-md-7 o-control-input">

						<?php echo $this->html('form.lists', 'default_recaptcha_language', $this->config->get('default_recaptcha_language'), '', '', array(
									array('title' => 'COM_PP_RECAPTCHA_LANGUAGE_AUTO', 'value' => 'auto'),
									array('title' => 'COM_PP_RECAPTCHA_LANGUAGE_SELECT', 'value' => 'none'),
								)); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('default_recaptcha_language') == 'none' ? '' : 't-hidden';?>" data-recaptcha_language>
					<?php echo $this->html('form.label', 'COM_PP_RECAPTCHA_SELECT_LANGUAGE'); ?>

					<div class="col-md-7 o-control-input">
						<select name="recaptcha_language" id="recaptcha_language" class="o-form-control">
							<?php $languages = PP::captcha()->getRecaptchaLanguages(); ?>
							<?php foreach ($languages as $language) { ?>
								<option value="<?php echo $language->value; ?>"<?php echo $this->config->get('recaptcha_language') == $language->value ? ' selected="selected"' : ''; ?>><?php echo $language->language;?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
