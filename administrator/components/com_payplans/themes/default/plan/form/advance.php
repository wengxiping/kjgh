<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_EDIT_PLAN_ADVANCE'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_LIMIT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'limit_count', $plan->getMaxSubscriptionLimit(), 'limit_count', array('data-plan-max-limit' => '')); ?>
						<input type="hidden" name="total_count" value="<?php echo $plan->getTotalSubscribers(); ?>" />
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_SCHEDULED'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'scheduled', $plan->isScheduled(), 'scheduled', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_START_DATE'); ?>

					<div class="o-control-input">
						<?php //echo $this->html('form.calendar', 'start_date', $plan->getPublishedDate(), 'start_date', array()); ?>
						<?php echo JHtml::_('calendar', $plan->getPublishedDate()->toSql(), 'start_date', 'start_date', '%Y-%m-%d', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_END_DATE'); ?>

					<div class="o-control-input">
						<?php //echo $this->html('form.calendar', 'end_date', $plan->getUnpublishedDate(), 'end_date', array()); ?>
						<?php echo JHtml::_('calendar', $plan->getUnpublishedDate()->toSql(), 'end_date', 'end_date', '%Y-%m-%d', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_AUTO_APPROVE_SUBSCRIPTION'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'moderate_subscription', $plan->requireModeration(), 'moderate_subscription', array()); ?>
					</div>
				</div>

			</div>
		</div>
		<div class="panel <?php echo $plan->getExpirationtype() == 'fixed' ? '' : 't-hidden';?>" data-fixed-expiration-wrapper >
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_FIXED_DATE_EXPIRATION'); ?>
			<div class="panel-body">
				<div class="o-form-group">
				<?php echo $this->html('form.label', 'COM_PP_PLAN_EXTEND_SUBSCRIPTION'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'enable_fixed_expiration_date', $plan->isFixedExpirationDate(), 'enable_fixed_expiration_date', 'data-expire-fixed'); ?>
					</div>
				</div>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EXPIRE_ON'); ?>

					<div class="o-control-input">
						<?php echo JHtml::_('calendar', $plan->getExpirationOnDate()->toSql(), 'expiration_date', 'expiration_date', '%Y-%m-%d', array()); ?>
					</div>
				</div>
				<div class="o-form-group">
					<label class="o-control-label"></label>
					<div class="o-control-input">
						<?php echo JText::_('COM_PP_PLAN_FIXED_DATERANGE_DESC'); ?> 
					</div>
				</div>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_FROM'); ?>

					<div class="o-control-input">
						<?php $startDate = ""; 
							if ($plan->getParams()->get('subscription_from')) { 
								$startDate = $plan->getSubscriptionFromExpirationDate()->toSql();
							} ?>
						<?php echo JHtml::_('calendar', $startDate, 'subscription_from', 'subscription_from', '%Y-%m-%d', array()); ?>
					</div>
				</div>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_TO'); ?>

					<div class="o-control-input">
						<?php $endDate = ""; 
							if ($plan->getParams()->get('subscription_to')) { 
								$endDate = $plan->getSubscriptionEndExpirationDate()->toSql();
							} ?>
						<?php echo JHtml::_('calendar', $endDate, 'subscription_to', 'subscription_to', '%Y-%m-%d', array()); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_EDIT_PLAN_RELATIONSHIP'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_RELATIONSHIP_DEPENDS_ON'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.plans', 'parentplans', $plan->getDependablePlans(), true, true, array(), array($plan->getId())); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_RELATIONSHIP_DISPLAY_CONDITION'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.lists', 'displaychildplanon', $plan->getParams()->get('displaychildplanon', PP_CONST_ANY), 'displaychildplanon', '', $childPlansDisplay); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>