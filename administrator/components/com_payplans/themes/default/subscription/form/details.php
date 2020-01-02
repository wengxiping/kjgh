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

<?php if ($order->getId() && $subscription->canCancel() && $subscription->isRecurring() && $order->isCancelled()) { ?>
<div class="row">
	<div class="col-lg-12 t-lg-mb--lg">
		<div class="o-alert o-alert--warning">
			<i class="fa fa-info"></i>&nbsp; <?php echo JText::_('COM_PP_SUBSCRIPTION_CANCELLED_INFO'); ?>
		</div>
	</div>
</div>
<?php } ?>

<div class="row">
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_SUBSCRIPTION_DETAILS'); ?>

			<div class="panel-body">
				<?php if ($subscription->getId()) { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ID'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $subscription->getId();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_SUBSCRIPTION_KEY'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $subscription->getKey();?>
					</div>
				</div>
				<?php } ?>

				<div class="o-form-group"<?php echo !$subscription->getId() ? ' data-pp-validate data-type="empty" data-target="pp-form-plan"' : '';?>>
					<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_PLAN'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.plans', 'plan_id', $subscription->getPlan()->getId(), !$subscription->getId() ? true : false, false, array('data-pp-form-plan' => '')); ?>
						<?php echo $this->html('form.validate', 'COM_PP_SUBSCRIPTION_VALIDATION_PLAN_REQUIRED'); ?>
					</div>
				</div>

				<div class="o-form-group"<?php echo !$subscription->getId() ? ' data-pp-validate data-type="empty" data-target="pp-form-user-input"' : ''; ?>>
					<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_USER'); ?>

					<div class="o-control-input col-md-7">
						<?php if (!$subscription->isNew()) { ?>
							<a href="index.php?option=com_payplans&view=user&layout=form&id=<?php echo $subscription->getBuyer()->getId();?>"><?php echo $subscription->getBuyer()->getUsername();?></a>
						<?php } else { ?>
							<?php echo $this->html('form.user', 'user_id', ''); ?>

							<?php echo $this->html('form.validate', 'COM_PP_SUBSCRIPTION_VALIDATION_USER_REQUIRED'); ?>
						<?php } ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_STATUS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.status', 'status', $subscription->getStatus(), 'subscription'); ?>
					</div>
				</div>

				<?php if ($subscription->getId()) { ?>
					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_ORDER_TOTAL'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('html.amount', $order->getTotal(), $order->getCurrency()); ?>
						</div>
					</div>

					<?php if ($params->get('units', false)) { ?>
						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_TOTAL_UNITS_PURCHASED'); ?>

							<div class="o-control-input col-md-7">
								<?php echo $params->get('units'); ?>
							</div>
						</div>
					<?php } ?>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_SUBSCRIPTION_DATE'); ?>

						<div class="o-control-input col-md-7">
							<?php if ($subscription->getSubscriptionDate()) { ?>
								<?php echo JHtml::_('calendar', $subscription->getSubscriptionDate()->toSql(), 'subscription_date', 'subscription_date', '%Y-%m-%d %H:%M:%S', array('class' => 'hello')); ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_EXPIRATION_DATE'); ?>

						<div class="o-control-input col-md-7">
							<?php if ($subscription->getExpirationDate()) { ?>
								<?php echo JHtml::_('calendar', $subscription->getExpirationDate()->toSql(), 'expiration_date', 'expiration_date', '%Y-%m-%d %H:%M:%S'); ?>
							<?php } else { ?>
								<?php echo JText::_('Plan never expires'); ?>
							<?php } ?>
						</div>
					</div>
				<?php } ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_PARAM_NOTES_LABEL'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.textarea', 'params[notes]', $params->get('notes'), 'params[notes]', array('rows' => 5)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<?php if ($upgradedFrom || $upgradedTo) { ?>
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_PARAMETERS'); ?>

			<div class="panel-body">

				<?php if ($upgradedFrom) { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_PARAM_UPGRADED_FROM'); ?>

					<div class="o-control-input">
						<?php if (!$upgradedFromSubscription) { ?>
							<?php echo PP::encryptor()->encrypt($upgradedFrom); ?>
						<?php } else { ?>
							<a href="index.php?option=com_payplans&view=subscription&layout=form&id=<?php echo $upgradedFrom;?>"><?php echo PP::encryptor()->encrypt($upgradedFrom); ?></a>
						<?php } ?>
					</div>
				</div>
				<?php } ?>

				<?php if ($upgradedTo) { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_SUBSCRIPTION_EDIT_PARAM_UPGRADED_TO'); ?>

					<div class="o-control-input">
						<a href="index.php?option=com_payplans&view=subscription&layout=form&id=<?php echo $upgradedTo;?>"><?php echo PP::encryptor()->encrypt($upgradedTo); ?></a>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>



