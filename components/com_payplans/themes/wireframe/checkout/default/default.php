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
<div class="pp-checkout-container">
	<?php echo $this->output('site/checkout/default/header', array('step' => 'info', 'title' => 'COM_PP_ORDER_CONFIRMATION')); ?>

	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="checkout" novalidate autocomplete="off" class="pp-checkout-container__form" data-pp-checkout-form>
		<div class="pp-checkout-wrapper">
			<div class="pp-checkout-wrapper__sub-content">
				<?php echo PP::info()->html(array('t-lg-mb--xl')); ?>

				<div class="pp-checkout-menu">
					<div class="t-text--center">
						<div class="o-loader o-loader--inline" data-pp-checkout-loader></div>
					</div>

					<div class="t-lg-mb--lg">
						<div>
							<b><?php echo JText::_($invoice->getTitle()); ?></b>
							<span class="t-lg-pull-right"><b>#<?php echo $invoice->getKey();?></b></span>
						</div>
						<div>
							<?php echo JText::_($plan->getDescription(true));?>
						</div>

						<?php echo $this->output('site/checkout/default/plandetails', array('invoice' => $invoice, 'recurring' => $invoice->isRecurring())); ?>
					</div>

					<div class="<?php echo $skipInvoice ? 't-hidden' : ''; ?>">
						<div class="t-bg--shade t-lg-pl--lg t-lg-pr--lg t-lg-mb--lg">
							<table class="pp-checkout-table">
								<tbody>
									<tr>
										<td>
											<?php echo JText::_('COM_PP_PRICE');?>
										</td>
										<td class="t-text--right">
											<?php if ($plan->isFree()) { ?>
												<?php echo JText::_('COM_PAYPLANS_PLAN_PRICE_FREE');?>
											<?php } else { ?>
												<?php echo $this->html('html.amount', $invoice->getSubtotal(), $invoice->getCurrency()); ?>
											<?php } ?>
										</td>
									</tr>

								</tbody>
							</table>

							<table class="pp-checkout-table">
								<tbody data-pp-modifiers>
									<div data-pp-registration-wrapper class="<?php echo $registrationOnly ? 't-hidden' : ''; ?>">
										<?php echo $this->output('site/checkout/default/modifier'); ?>
									</div>
								</tbody>
							</table>

							<hr data-pp-registration-wrapper class="hr--light<?php echo $registrationOnly ? ' t-hidden' : ''; ?>" />

							<?php if ($this->config->get('enableDiscount')) { ?>
							<div data-pp-registration-wrapper class="<?php echo $registrationOnly ? 't-hidden' : ''; ?>">
								<?php echo $this->output('site/checkout/default/discounts'); ?>
							</div>
							<?php } ?>

							<?php echo $this->renderPlugins($pluginResult, 'payplans_order_confirm_payment'); ?>

							<table class="pp-checkout-table">
								<tbody>
									<tr data-pp-payable data-pp-registration-wrapper class="<?php echo $registrationOnly ? 't-hidden' : ''; ?>">
										<th>
											<?php echo JText::_('COM_PAYPLANS_ORDER_CONFIRM_AMOUNT_PAYABLE');?>
										</th>
										<th class="t-text--right" data-pp-payable-label>
											<?php echo $this->html('html.amount', $invoice->getTotal(), $invoice->getCurrency()); ?>
										</th>
									</tr>
								</tbody>
							</table>
						</div>

						<div data-pp-registration-wrapper class="<?php echo $registrationOnly ? 't-hidden' : ''; ?>">
							<!-- Social Discounts -->
							<?php if ($socialDiscount->isEnabled()) { ?>
								<?php echo $socialDiscount->html($invoice);?>
							<?php } ?>

							<?php echo $this->output('site/checkout/default/referrals'); ?>

							<?php echo $this->renderPlugins($pluginResult, 'pp-checkout-options'); ?>

							<?php echo $this->output('site/checkout/default/addons'); ?>

							<?php if ($invoice->isPaymentNeeded()) { ?>
							<div class="o-card o-card--borderless" data-pp-payment-form>
								<div class="o-card__header o-card__header--nobg t-lg-pl--no">
									<?php echo JText::_('COM_PAYPLANS_ORDER_MAKE_PAYMENT_FROM');?>
								</div>
								<div class="o-card__body">
									<?php if ($provider) { ?>
										<?php echo JText::sprintf('COM_PP_PAYMENT_METHOD_DEFAULT', $provider->getTitle()); ?>
										<?php echo $this->html('form.hidden', 'app_id', $provider->getId()); ?>
									<?php } else { ?>
										<?php if ($plan->isFree()) { ?>
											<?php echo JText::_('COM_PAYPLANS_ORDER_NO_PAYMENT_METHOD_NEEDED')?>
										<?php } else { ?>
										<div class="o-form-group o-form-group--float" data-pp-form-group>
											<div class="o-select-group">
												<select id="payment_provider" name="app_id" class="o-form-control">
													<?php foreach ($providers as $provider) { ?>
													<option value="<?php echo $provider->getId();?>"><?php echo JText::_($provider->getTitle());?></option>
													<?php } ?>
												</select>
												<span class="o-select-group__drop"></span>
											</div>
											<label class="o-control-label" for="payment_provider"><?php echo JText::_('COM_PP_CHECKOUT_SELECT_PAYMENT_PROVIDER');?></label>
										</div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>

					<?php if (!$this->my->id) { ?>
						<?php echo $registration->html($invoice);?>
					<?php } ?>

					<div data-pp-registration-wrapper class="<?php echo $registrationOnly ? 't-hidden' : ''; ?>">
						<?php echo $this->output('site/checkout/default/company'); ?>

						<?php $position = 'pp-subscription-details';?>
						<?php //echo $this->loadTemplate('partial_position',compact('plugin_result','position'));?>

						<?php $position = 'pp-user-mobile-number';?>
						<?php //echo $this->loadTemplate('partial_position',compact('plugin_result','position'));?>

						<?php echo $this->renderPlugins($pluginResult, 'pp-user-details'); ?>

						<?php if ($userCustomDetails) { ?>
							<?php foreach ($userCustomDetails as $customDetail) { ?>
								<?php echo $customDetail->renderForm($user->getParams(), true, 'userparams'); ?>
							<?php } ?>
						<?php } ?>

						<?php if ($subsCustomDetails) { ?>
							<?php foreach ($subsCustomDetails as $customDetail) { ?>
								<?php echo $customDetail->renderForm($subscriptionParams, true, 'subscriptionparams'); ?>
							<?php } ?>
						<?php } ?>
					</div>

					<?php echo $this->renderPlugins($pluginResult, 'pp-before-actions'); ?>

					<div class="o-card o-card--borderless t-lg-mb--lg">
						<div class="o-card__body">
							<div class="o-grid-sm">
								<div class="o-grid-sm__cell o-grid-sm__cell--center">
									<a href="<?php echo PPR::_('index.php?option=com_payplans&view=plan&from=checkout');?>">
										&larr; <?php echo JText::_('COM_PP_CANCEL_AND_RETURN');?>
									</a>
								</div>

								<div class="o-grid-sm__cell o-grid-sm__cell--right">
									<button type="submit" class="btn btn-pp-primary btn--lg" data-pp-submit>
										<?php if ($this->my->id || (!$registration->isBuiltIn() && $registration->getNewUserId())) { ?>
										<span><?php echo JText::_('COM_PAYPLANS_ORDER_CONFIRM_BTN');?></span>
										<?php } else { ?>
											<span data-pp-submit-login data-pp-registration-wrapper class="<?php echo $registrationOnly || ($registration->isBuiltIn() && $this->config->get('default_form_order') == 'register') ? ' t-hidden' : ''; ?>"><?php echo JText::_('COM_PP_LOGIN_ORDER_CONFIRM_BTN'); ?></span>
											<?php if ($registration->isBuiltIn()) { ?>
											<span data-pp-submit-register class="<?php echo $this->config->get('default_form_order') != 'register' ? 't-hidden' : ''; ?>"><?php echo JText::_('COM_PP_REGISTER_ORDER_CONFIRM_BTN'); ?></span>
											<?php } ?>
										<?php } ?>
									</button>
								</div>
							</div>
						</div>
					</div>

					<?php $position = 'order-confirm-footer';?>
					<?php //echo $this->loadTemplate('partial_position',compact('plugin_result','position'));?>
				</div>
			</div>
		</div>

		<?php echo $this->html('form.hidden', 'account_type', $accountType, array('data-pp-account-type' => '')); ?>
		<?php echo $this->html('form.action', '', 'checkout.confirm'); ?>
		<?php echo $this->html('form.hidden', 'invoice_key', $invoice->getKey(), array('data-pp-invoice-key' => '')); ?>
	</form>
</div>
