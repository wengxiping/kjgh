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
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_EDIT_PLAN_DETAILS'); ?>

			<div class="panel-body">
				<div class="o-form-group" data-pp-validate data-type="empty" data-target="plan-title">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_TITLE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'title', $plan->getTitle(), 'title', array('data-plan-title' => '')); ?>
					</div>
				</div>

				<?php if ($this->config->get('useGroupsForPlan')) { ?>
					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_GROUPS_TITLE'); ?>
						<div class="o-control-input">
							<?php echo $this->html('form.groups', 'groups', $planGroups); ?>
						</div>
					</div>
				<?php } ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_REDIRECTURL'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'redirecturl', $plan->getRedirecturl(), 'redirecturl', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_TEASER_TEXT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'teasertext', $plan->getTeasertext(), 'teasertext', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PUBLISHED'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'published', $plan->getPublished(), 'published'); ?>
					</div>
				</div>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_VISIBLE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'visible', $plan->getVisible(), 'visible'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_DESCRIPTION'); ?>

					<div class="o-control-input col-md-7">
						<?php if ($renderEditor) { ?>
							<?php echo $this->html('form.editor', 'description', $plan->getDescription(true), 'description', array(), array(), array(), false); ?>
						<?php } else { ?>
							<?php echo $this->html('form.textarea', 'description', $plan->getDescription(true), 'description', array(), false); ?>
						<?php } ?>
					</div>
				</div>				
			</div>
		</div>
	</div>

	<div class="col-md-6">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_EDIT_TIME_PARAMETERS'); ?>
			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_EXPIRATION_TYPE'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.dependency', 'expirationtype', $plan->getExpirationtype(), 'expirationtype', 'fixed-expiration-type', $expirationTypes); ?>
					</div>
				</div>

				<div class="o-form-group" data-expiry-price>
					<?php echo $this->html('form.label', 'COM_PP_PLAN_PAYMENT_PRICE'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.text', 'price', $plan->getPrice(), 'price'); ?>
					</div>
				</div>

				<div class="o-form-group" data-expiry-timer>
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_EXPIRATION'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.timer', 'expiration', $plan->getExpiration(PP_PRICE_FIXED, true), 'expiration', 'data-expire-fixed data-expire-recurring data-expire-recurring-trial-1 data-expire-recurring-trial-2'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_PRICE_1'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.text', 'trial_price_1', $plan->getPrice(PP_PRICE_RECURRING_TRIAL_1), 'trial_price_1', 'data-expire-recurring-trial-1 data-expire-recurring-trial-2'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_TIME_1'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.timer', 'trial_time_1', $plan->getExpiration(PP_PRICE_RECURRING_TRIAL_1, true), 'trial_time_1', 'data-expire-recurring-trial-1 data-expire-recurring-trial-2'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_PRICE_2'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.text', 'trial_price_2', $plan->getPrice(PP_PRICE_RECURRING_TRIAL_2), 'trial_price_2', 'data-expire-recurring-trial-2'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_TIME_2'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.timer', 'trial_time_2', $plan->getExpiration(PP_PRICE_RECURRING_TRIAL_2, true), 'trial_time_2', 'data-expire-recurring-trial-2'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_RECURRENCE_COUNT'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.text', 'recurrence_count', $plan->getRecurrenceCount(), 'recurrence_count', 'data-expire-recurring data-expire-recurring-trial-1 data-expire-recurring-trial-2', array(
							'class' => 't-text--center',
							'size' => 10,
							'postfix' => JText::_('Times')
						)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_RECURRENCE_VALIDATION'); ?>
					<div class="o-control-input">
						<button type="button" class="btn btn-pp-default-o" data-recurr-validate data-expire-recurring data-expire-recurring-trial-1 data-expire-recurring-trial-2>
							<?php echo JText::_('COM_PAYPLANS_ELEMENT_POPUP_CLICK_HERE'); ?>
						</button>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>



