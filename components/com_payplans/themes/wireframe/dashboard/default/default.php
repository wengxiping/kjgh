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
<div class="pp-orders">
	<?php if ($subscriptions) { ?>
	<div class="t-lg-mt--lg">
		<?php foreach ($subscriptions as $subscription) { ?>
		<div class="o-card t-lg-mb--xl">
			<div class="o-card__body">
				<div class="o-alert o-alert--danger t-lg-mb--lg <?php echo !$subscription->isExpired() ? 't-hidden' : '';?>">
					<?php echo JText::_('COM_PP_SUBSCRIPTION_EXPIRED_PLEASE_RENEW'); ?>
				</div>
				<div class="o-grid">
					<div class="o-grid__cell">
						<div class="o-card__title">
							<a href="<?php echo $subscription->getPermalink();?>">
								<?php echo JText::_($subscription->getTitle());?>
							</a>
						</div>
						<div class="o-card__desc">
							<span>
								#<?php echo $subscription->getKey();?>
							</span>

							<span class="t-lg-ml--md">
								<a href="<?php echo $subscription->getPermalink();?>">
									<i class="far fa-file"></i>&nbsp; <?php echo JText::_('COM_PP_INVOICES');?>
								</a>
							</span>

							<?php if ($subscription->getSubscriptionDate()) { ?>
							<span class="t-lg-ml--md">
								<a href="javascript:void(0);" data-pp-provide="tooltip" data-title="<?php echo JText::sprintf('COM_PP_SUBSCRIPTION_CREATED_TOOLTIP', $subscription->getSubscriptionDate()->format(JText::_('DATE_FORMAT_LC4')));?>">
									<i class="far fa-calendar"></i>&nbsp; <?php echo $subscription->getSubscriptionDate()->format(JText::_('DATE_FORMAT_LC3'));?>
								</a>
							</span>
							<?php } ?>
						</div>

						<?php if ($subscription->isRecurring() && $subscription->order->isCancelled()) { ?>
						<div class="o-card__desc">
							<?php echo JText::_('COM_PP_SUBSCRIPTION_CANCELLED_AND_WILL_NOT_BE_REBILLED');?>
						</div>
						<?php } ?>
					</div>
					<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
						<?php if ($subscription->isExpired() && $subscription->getExpirationDate()) { ?>
							<?php echo $this->html('subscription.status', $subscription, array('postfix' => '(' . $subscription->getExpirationDate()->format(JText::_('DATE_FORMAT_LC4')) . ')')); ?>
						<?php } else { ?>
							<?php if (!$subscription->isNotActive()) { ?>
								<?php echo $this->html('subscription.status', $subscription); ?>
							<?php } ?>
						<?php } ?>
					</div>

				</div>
			</div>

			<div class="o-card-list-group o-card--shade">
				<div class="o-card-list-group__item">
					<div class="o-card--meta">
						<div class="o-grid">
							<div class="o-grid__cell">
								<div><?php echo JText::_('COM_PP_SUBSCRIPTION_PRICE');?></div>
							</div>
							<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
								<div>
									<?php if ($subscription->isRecurring()) { ?>
										<?php if (in_array($subscription->expirationType, array(PP_RECURRING_TRIAL_1, PP_RECURRING_TRIAL_2))) { ?>

											<div>
												<?php echo $this->html('html.amount', $subscription->getPrice(PP_PRICE_RECURRING_TRIAL_1), $subscription->currency); ?>

												<?php echo JText::sprintf('COM_PAYPLANS_DASHBOARD_SUBSCRIPTION_CONFIRM_FIRST_CHARGABLE_AMOUNT', $this->html('html.plantime', $subscription->getExpiration(PP_RECURRING_TRIAL_1))); ?>
											</div>

											<?php if ($subscription->expirationType == PP_RECURRING_TRIAL_2) { ?>
											<div>
												<?php echo $this->html('html.amount', $subscription->getPrice(PP_PRICE_RECURRING_TRIAL_2),  $subscription->currency);?>

												<?php echo JText::sprintf('COM_PAYPLANS_DASHBOARD_SUBSCRIPTION_CONFIRM_SECOND_CHARGABLE_AMOUNT', $this->html('html.plantime', $subscription->getExpiration(PP_RECURRING_TRIAL_2)));?>
											</div>
											<?php } ?>
										<?php } ?>

										<?php $amountHtml = $this->html('html.amount', $subscription->getPrice(), $subscription->currency); ?>
										<?php if ($subscription->getRecurrenceCount() <= 0) { ?>
										<div>
											<?php echo JText::sprintf('COM_PAYPLANS_DASHBOARD_SUBSCRIPTION_CONFIRM_FIRST_RECURRENCE_COUNT_ZERO_RECURRENCE_COUNT', $amountHtml, $this->html('html.plantime', $subscription->getExpiration()));?>
										</div>
										<?php } else { ?>
										<div>
											<?php echo JText::sprintf('COM_PAYPLANS_DASHBOARD_SUBSCRIPTION_CONFIRM_FIRST_RECURRENCE_COUNT', $amountHtml, $this->html('html.plantime', $subscription->getExpiration()), $subscription->getRecurrenceCount());?>
										</div>
										<?php } ?>

									<?php } else { ?>
										<b><?php echo $this->html('html.amount', $subscription->getTotal(), $subscription->currency); ?></b>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="o-card-list-group__item">
					<div class="o-card--meta">
						<div class="o-grid">
							<div class="o-grid__cell">
								<div><?php echo JText::_('COM_PP_SUBSCRIPTION_PERIOD');?></div>
							</div>

							<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
								<div>
									<?php if ($subscription->isOnHold()) { ?>
										&mdash;
									<?php } ?>

									<?php if ($subscription->isActive()) { ?>
										<?php if ($subscription->expirationDate) { ?>
											<i class="far fa-calendar"></i>&nbsp;
											<b>
												<?php echo $subscription->getSubscriptionDate()->format(JText::_('DATE_FORMAT_LC4'));?>
											</b>

											<span class="separator t-lg-ml--md t-lg-mr--md">&mdash;</span>

											<b>
												<?php echo $subscription->getExpirationDate()->format(JText::_('DATE_FORMAT_LC4'));?>
											</b>
										<?php } else { ?>
											<b><?php echo JText::_('COM_PAYPLANS_ORDER_SUBSCRIPTION_TIME_LIFETIME'); ?></b>
										<?php } ?>
									<?php } ?>

									<?php if ($subscription->isExpired()) { ?>
										<b class="t-text--danger"><?php echo JText::_('COM_PP_EXPIRED'); ?></b>
									<?php } ?>

									<?php if ($subscription->isNotActive()) { ?>
										<b class="t-text--danger"><?php echo JText::_('COM_PAYPLANS_ORDER_SUBSCRIPTION_NOT_ACTIVATED'); ?></b>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php if (!$subscription->isOnHold() || $subscription->canCancel() || $subscription->actions) { ?>
			<div class="o-card__footer t-bg--default">
				<div class="o-grid">
					<div class="o-grid__cell">
						<?php if ($subscription->canCancel()) { ?>
						<a href="javascript:void(0);" class="btn btn--link t-text--danger t-lg-pl--no"  data-cancel-subscription data-key="<?php echo $subscription->order->getKey();?>">
							<?php echo JText::_('COM_PP_CANCEL_SUBSCRIPTION');?>
						</a>
						<?php } ?>
					</div>

					<?php if (!$subscription->isOnHold() || $subscription->actions) { ?>
					<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
						<div class="o-btn-toolbar">

							<?php if ($subscription->actions) { ?>
								<?php foreach ($subscription->actions as $action) { ?>
									<?php echo $action;?>
								<?php } ?>
							<?php } ?>

							<?php if ($subscription->isNotActive() && $subscription->pendingInvoice && !$subscription->pendingInvoice->hasTransaction()) { ?>

								<?php if ($this->config->get('user_delete_orders')) { ?>
									<div class="o-btn-group">
										<a href="javascript:void(0);" class="btn btn--link t-text--danger t-lg-pl--no"  data-delete-subscription data-key="<?php echo $subscription->order->getKey();?>">
										<?php echo JText::_('COM_PP_DELETE_ORDER');?>
										</a>
									</div>
								<?php } ?>

								<div class="o-btn-group">
									<a href="<?php echo PPR::_('index.php?option=com_payplans&view=checkout&invoice_key=' . $subscription->pendingInvoice->getKey() . '&tmpl=component'); ?>" class="btn btn-pp-primary">
									<?php echo JText::_('COM_PP_COMPLETE_ORDER_NOW');?>
									</a>
								</div>
							<?php } ?>

							<?php if ($subscription->isRenewable()) { ?>
							<div class="o-btn-group">
								<a href="<?php echo PPR::_('index.php?option=com_payplans&view=order&layout=processRenew&subscription_key=' . $subscription->getKey() . '&tmpl=component'); ?>" class="btn btn-pp-primary">
									<?php echo JText::_('COM_PP_APP_RENEW_BUTTON'); ?>
								</a>
							</div>
							<?php } ?>

							<?php if ($subscription->isUpgradable()) { ?>
							<div class="o-btn-group">
								<button type="button" class="btn btn-pp-default-o" data-upgrade-button data-key="<?php echo $subscription->order->getKey(); ?>">
									<?php echo JText::_('COM_PP__APP_UPGRADE_BUTTON'); ?>
								</button>
							</div>
							<?php } ?>
						</div>

					</div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if (!$subscriptions) { ?>
	<div class="pp-access-alert pp-access-alert--warning">
		<div class="pp-access-alert__icon"><i class="fas fa-exclamation-circle"></i></div>
		<div class="pp-access-alert__content">
			<div class="pp-access-alert__title t-lg-mb--xl">
				<?php echo JText::_('COM_PP_NO_SUBSCRIPTIONS_CURRENTLY'); ?>
			</div>
			<div class="pp-access-alert__desc">
				<?php echo JText::_('COM_PP_NO_SUBSCRIPTIONS_CURRENTLY_INFO'); ?>
			</div>
		</div>
		<div class="pp-access-alert__action">
			<a href="<?php echo PPR::_('index.php?option=com_payplans&view=plan');?>" class="btn btn-pp-primary t-lg-mt--xl">
				<i class="fa fa-shopping-basket"></i>&nbsp; <?php echo JText::_('COM_PP_VIEW_AVAILABLE_PLANS');?>
			</a>
		</div>
	</div>
	<?php } ?>
</div>
