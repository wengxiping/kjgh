<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

?>
<div class="row">
	<div class="col-lg-5">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_APP_GENERAL'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_TITLE', '', 3, true, true); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'title', $app->getTitle()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_PUBLISH_STATE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'published', $app->getPublished()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_DESCRIPTION'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.textarea', 'description', $app->getDescription(), '', array('rows' => 5)); ?>
					</div>
				</div>
			</div>

		</div>
	</div>

	<div class="col-lg-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_ASSIGNMENT_CRITERIA'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_ASSIGNMENT_PROFILE_SOURCE'); ?>
					<div class="o-control-input">
						<select class="o-form-control" name="app_params[source]" data-profile-source>
							<option value="joomla_usertype" <?php echo $profileSource == 'joomla_usertype' ? 'selected="selected"' : ''; ?>> 
								<?php echo JText::_('COM_PP_PROFILE_USED_JOOMLA_USERTYPE');?>
							</option>

							<?php if ($esEnabled) { ?>
								<option value="easysocial_profiletype" <?php echo $profileSource == 'easysocial_profiletype' ? 'selected="selected"' : ''; ?>> 
									<?php echo JText::_('COM_PP_PROFILE_USED_EASYSOCIAL_PROFILETYPE');?>
								</option>
							<?php } ?>

							<?php if ($communityEnabled) { ?>
								<option value="jomsocial_profiletype" <?php echo $profileSource == 'jomsocial_profiletype' ? 'selected="selected"' : ''; ?>> 
									<?php echo JText::_('COM_PP_PROFILE_USED_JOOMSOCIAL_PROFILETYPE');?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_ASSIGNMENT_PROFILE_TYPE'); ?>
					<div class="o-control-input" data-profile-select>
						<?php echo $this->output('admin/assigns/form/profiletypes'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_ASSIGNMENT_ASSIGN_PLAN'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.plans', 'app_params[signup_plans]', $signupPlans, true, true); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>