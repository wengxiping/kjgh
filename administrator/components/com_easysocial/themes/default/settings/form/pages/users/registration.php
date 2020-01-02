<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_USERS_SETTINGS_REGISTRATION'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_ALLOW_REGISTRATIONS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registrations.enabled', $this->config->get('registrations.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_DISPLAY_MINI_REGISTRATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registrations.mini.enabled', $this->config->get('registrations.mini.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_MODE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'registrations.mini.mode', $this->config->get('registrations.mini.mode'), array(
								array('value' => 'quick', 'text' => 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_QUICK'),
								array('value' => 'full', 'text' => 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_FULL'),
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_PROFILE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'registrations.mini.profile', '', $this->config->get('registrations.mini.profile'), array('default' => true)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_SHOW_CLEAR_PASSWORD'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registrations.email.password', $this->config->get('registrations.email.password')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_REGISTRATION_SETTINGS_PROFILE_TYPE_SELECTION_LAYOUT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'registrations.profiles.selection.layout', $this->config->get('registrations.profiles.selection.layout'), array(
								array('value' => 'list', 'text' => 'COM_ES_LIST_LAYOUT'),
								array('value' => 'dropdown', 'text' => 'COM_ES_DROPDOWN_LAYOUT'),
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_SHOW_PROFILE_AVATAR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registrations.layout.avatar', $this->config->get('registrations.layout.avatar')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_SHOW_USERS_REGISTERED_IN_PROFILE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registrations.layout.users', $this->config->get('registrations.layout.users')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_ALLOW_REGISTRATIONS_SHOW_EMAIL_RECONFIRMATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registrations.email.reconfirmation', $this->config->get('registrations.email.reconfirmation')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">

	</div>
</div>
