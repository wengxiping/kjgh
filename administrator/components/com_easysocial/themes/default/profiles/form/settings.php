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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PROFILES_FORM_GENERAL'); ?>

			<div class="panel-body">
				<div class="form-group" data-profile-avatar data-hasavatar="<?php echo $profile->hasAvatar(); ?>" data-defaultavatar="<?php echo $profile->getDefaultAvatar(); ?>">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_AVATAR'); ?>

					<div class="col-md-7">

						<?php if ($profile->id) { ?>
						<div class="mb-20">
							<img src="<?php echo $profile->getAvatar();?>" class="es-avatar es-avatar-md es-avatar-border-sm" data-profile-avatar-image />
						</div>
						<?php } ?>

						<div>
							<input type="file" name="avatar" data-uniform data-profile-avatar-upload />
							<span class="t-lg-ml--md" data-profile-avatar-remove-wrap <?php if( !$profile->hasAvatar() ) { ?>style="display: none;"<?php } ?>> <?php echo JText::_( 'COM_EASYSOCIAL_OR' ); ?>
								<a href="javascript:void(0);" class="btn btn-sm btn-es-danger t-lg-ml--sm" data-profile-avatar-remove-button>
									<?php echo $profile->hasAvatar() ? JText::_('COM_EASYSOCIAL_PROFILES_FORM_REMOVE_AVATAR') : JText::_('COM_EASYSOCIAL_PROFILES_FORM_CLEAR_AVATAR'); ?>
								</a>
							</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SELECT_WORKFLOW', true, '', 5, true); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.workflows', 'workflow_id', SOCIAL_TYPE_USER, $profile->getWorkflow()->id); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_TITLE', true, '', 5, true); ?>
					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'title', $profile->title); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_ALIAS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'alias', $profile->alias); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_DESCRIPTION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.textarea', 'description', $profile->description, 'description', array('data-profile-description')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_PUBLISHING_STATUS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'state', $profile->state, 'state'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_DEFAULT_PROFILE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'default', $profile->default, 'default'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_PROFILE_DELETION'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler' , 'params[delete_account]' , $param->get('delete_account'), 'params[delete_account]'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_PROFILE_REGISTRATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registration', $profile->registration, 'registration'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PROFILES_USERS_VERIFIED'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'params[verified]', $param->get('verified', false), 'params[verified]'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_COMMUNITY_ACCESS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'community_access', $profile->community_access, 'community_access'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_PROFILE_SWITCHABLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'switchable', $profile->switchable, 'switchable'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PROFILES_FORM_EXCLUDE_USERLISTING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'exclude_userlisting', $profile->exclude_userlisting, 'exclude_userlisting'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_LOGIN_REDIRECTION'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.menus', 'params[login_success]', $param->get('login_success'),
												array(
														JText::_('COM_EASYSOCIAL_USERS_SETTINGS_MENU_GROUP_CORE') => array(
															JHtml::_('select.option', 'null', JText::_('COM_EASYSOCIAL_DEFAULT_BEHAVIOR'))
														)
												)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_DAILY_POINTS_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'params[points_limit]', $param->get('points_limit', 0), 'ordering', array('class' => 'input-short text-center')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_ORDERING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'ordering', $profile->ordering, 'ordering', array('class' => 'input-short text-center')); ?>
					</div>
				</div>

				<?php if (ES::get('multisites')->exists()) { ?>
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_SITE_ID'); ?>
					<div class="col-md-7"><?php echo ES::get('multisites')->getForm('site_id', $profile->site_id); ?></div>
				</div>
				<?php } ?>

			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_PROFILES_SPECIAL_PERMISSIONS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PROFILES_ALLOW_MODERATOR_ACCESS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'moderator_access', $profile->moderator_access, 'moderator_access'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_PROFILES_USER_LABELS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_USER_LABEL_COLOUR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'params[label_colour]', $param->get('label_colour', false), '', 'data-label-font'); ?>
					</div>
				</div>

				<div class="form-group <?php echo $param->get('label_colour') ? '' : 't-hidden';?>" data-label-font-wrapper>
					<?php echo $this->html('panel.label', 'COM_ES_USER_LABEL_FONT_COLOUR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.colorpicker', 'params[label_font_colour]', $param->get('label_font_colour', '#ffffff')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_USER_LABEL_BACKGROUND'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'params[label_background]', $param->get('label_background', false), '', 'data-label-background'); ?>
					</div>
				</div>

				<div class="form-group <?php echo $param->get('label_background') ? '' : 't-hidden';?>" data-label-background-wrapper>
					<?php echo $this->html('panel.label', 'COM_ES_USER_LABEL_BACKGROUND_COLOUR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.colorpicker', 'params[label_background_colour]', $param->get('label_background_colour', '#ffffff')); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_TYPE'); ?>

					<div class="col-md-7">
						<select name="params[registration]" class="registrationType o-form-control">
							<option value="approvals"<?php echo $param->get('registration') == 'approvals' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_REQUIRE_APPROVALS' ); ?></option>
							<option value="verify"<?php echo $param->get('registration') == 'verify' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_REQUIRE_SELF_ACTIVATION'); ?></option>
							<option value="confirmation_approval"<?php echo $param->get('registration') == 'confirmation_approval' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_PROFILES_FORM_REGISTRATION_REQUIRE_USER_CONFIRMATION_AND_ADMIN_APPROVAL'); ?></option>
							<option value="auto"<?php echo $param->get('registration') == 'auto' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_REQUIRE_AUTO_LOGIN'); ?></option>
							<option value="login"<?php echo $param->get('registration') == 'login' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_NORMAL'); ?></option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_SUCCESS_REDIRECTION'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.menus', 'params[registration_success]', $param->get('registration_success'),
												array(
														JText::_('COM_EASYSOCIAL_USERS_SETTINGS_MENU_GROUP_CORE') => array(
															JHtml::_('select.option', 'null', JText::_('COM_EASYSOCIAL_DEFAULT_BEHAVIOR')),
															JHtml::_('select.option', 'previous', JText::_('COM_ES_PREVIOUS_PAGE_BEFORE_REGISTRATION')),
														)
												)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_OAUTH_REGISTRATION_TYPE'); ?>

					<div class="col-md-7">
						<select name="params[oauth.registration]" class="registrationType o-form-control">
							<option value="approvals"<?php echo $param->get('oauth.registration') == 'approvals' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_REQUIRE_APPROVALS'); ?></option>
							<option value="verify"<?php echo $param->get('oauth.registration') == 'verify' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_REQUIRE_SELF_ACTIVATION' ); ?></option>
							<option value="confirmation_approval"<?php echo $param->get('oauth.registration') == 'confirmation_approval' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_PROFILES_FORM_REGISTRATION_REQUIRE_USER_CONFIRMATION_AND_ADMIN_APPROVAL'); ?></option>
							<option value="auto"<?php echo $param->get('oauth.registration') == 'auto' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_REQUIRE_AUTO_LOGIN'); ?></option>
							<option value="login"<?php echo $param->get('oauth.registration') == 'login' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_NORMAL'); ?></option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_SEND_EMAILS_USER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'params[email.users]', $param->get('email.users', true)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_REGISTRATION_SEND_EMAILS_ADMIN'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'params[email.moderators]', $param->get('email.moderators', true)); ?>
					</div>
				</div>

			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PROFILES_FORM_GROUPS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PROFILES_FORM_GROUPS_DEFAULT_USER_GROUP'); ?>

					<div class="col-md-7">
						<?php echo $this->html( 'tree.groups' , 'gid' , $profile->gid , $guestGroup ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
