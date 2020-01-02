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
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_GENERAL_FEATURES'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'displayExistingSubscribedPlans', 'COM_PAYPLANS_CONFIG_DISPLAY_EXISTING_SUBSCRIBED_PLANS'); ?>
				<?php echo $this->html('settings.toggle', 'useGroupsForPlan', 'COM_PAYPLANS_CONFIG_USE_GROUPS_FOR_PLAN'); ?>
				<?php echo $this->html('settings.toggle', 'layout_plan_description_use_editor', 'COM_PP_CONFIG_LAYOUT_PLAN_DESCRIPTION_USE_HTML'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_GENERAL_ADDONS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'addons_enabled', 'COM_PP_CONFIG_ADDONS_ENABLED'); ?>
				<?php echo $this->html('settings.toggle', 'addons_select_multiple', 'COM_PP_CONFIG_ADDONS_SELECT_MULTIPLE'); ?>
				<?php echo $this->html('settings.toggle', 'addons_forceful_default', 'COM_PP_CONFIG_ADDONS_FORCE_DEFAULT'); ?>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_GENERAL_ASSIGNS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'profileplan_enabled', 'COM_PP_CONFIG_ASSIGNS_ENABLED'); ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_PROFILE_DEFAULT_PLAN'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.plans', 'profileplan_default', $this->config->get('profileplan_default'), true, false); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_PROFILE_TYPE_SOURCE'); ?>

					<div class="o-control-input col-md-7">

						<?php echo $this->html('form.lists', 'profile_used', $this->config->get('profile_used'), '', '', array(
									array('title' => 'COM_PP_PROFILE_USED_JOOMLA_USERTYPE', 'value' => 'joomla_usertype'),
									array('title' => 'COM_PP_PROFILE_USED_EASYSOCIAL_PROFILETYPE', 'value' => 'easysocial_profiletype'),
									array('title' => 'COM_PP_PROFILE_USED_JOOMSOCIAL_PROFILETYPE', 'value' => 'jomsocial_profiletype')
									
								)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
