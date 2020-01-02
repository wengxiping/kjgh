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
			<?php echo $this->html('panel.heading', 'COM_PP_INVOICE_EDIT_PARAMETERS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_TITLE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[title]', $params->get('title'), 'params[title]'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_EXPIRATION_TYPE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.expiration', 'params[expirationtype]', $params->get('expirationtype'), 'params[expirationtype]', array('data-form-select-dropdown' => '')); ?>
					</div>
				</div>

				<!-- appear on recurring_trial_1 and recurring_trial_2-->
				<div class="o-form-group t-hidden expirationtype trial_price_1">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_PRICE_1'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[trial_price_1]', $params->get('trial_price_1'), 'params[trial_price_1]'); ?>
					</div>
				</div>

				<!-- appear on recurring_trial_1 and recurring_trial_2-->
				<div class="o-form-group t-hidden expirationtype trial_price_1">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_TIME_1'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.timer', 'params[trial_time_1]', $params->get('trial_time_1'), 'params[trial_time_1]'); ?>
					</div>
				</div>

				<!-- appear on recurring_trial_2-->
				<div class="o-form-group t-hidden expirationtype trial_price_2">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_PRICE_2'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[trial_price_2]', $params->get('trial_price_2'), 'params[trial_price_2]'); ?>
					</div>
				</div>

				<!-- appear on recurring_trial_2-->
				<div class="o-form-group t-hidden expirationtype trial_price_2">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_TRIAL_TIME_2'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.timer', 'params[trial_time_2]', $params->get('trial_time_2'), 'params[trial_time_2]'); ?>
					</div>
				</div>

				<!-- appear on all-->
				<div class="o-form-group t-hidden expirationtype price">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_PAYMENT_PRICE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[price]', $params->get('price'), 'params[price]'); ?>
					</div>
				</div>

				<!-- appear on all-->
				<div class="o-form-group t-hidden expirationtype expiration">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_EXPIRATION_TIME'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.timer', 'params[expiration]', $params->get('expiration'), 'params[expiration]'); ?>
					</div>
				</div>

				<!-- appear on recurring, recurring_trial_1 and recurring_trial_2-->
				<div class="o-form-group t-hidden expirationtype recurrence_count">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_TIME_RECURRENCE_COUNT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[recurrence_count]', $params->get('recurrence_count'), 'params[recurrence_count]'); ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->renderPlugins($pluginResult, 'pp-invoice-details'); ?>

		<?php if ($this->config->get('enableDiscount') && !$invoice->isPaid() && !$invoice->isRefunded()) { ?>
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_DISCOUNTS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_CODE_AMOUNT'); ?>

					<div class="o-control-input">
						<div class="o-input-group">
							<?php echo $this->html('form.text', 'app_discount_code', '', 'app_discount_code_id', array('placeholder' => JText::_('COM_PAYPLANS_PRODISCOUNT_ENTER_DISCOUNT_CODE_OR_AMOUNT'))); ?>
							<span class="o-input-group__append">
								<a class="btn btn-pp-default-o" id="app_discount_code_submit" data-pp-discount-appy data-pp-invoice-id="<?php echo $invoice->getId(); ?>">
									<?php echo JText::_("COM_PAYPLANS_PRODISCOUNT_APPLY");?>
								</a>
							</span>
						</div>
					</div>
				</div>

				<div class="t-text--danger" data-pp-discount-message></div>

			</div>
		</div>
		<?php } ?>

	</div>
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_INVOICE_EDIT_DETAILS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ID'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $invoice->getId(); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_OBJECT'); ?>

					<div class="o-control-input col-md-7">
						<?php $refObject = $invoice->getReferenceObject(); ?>
						<?php $subscription = $refObject->getSubscription(); ?>
						<a href="index.php?option=com_payplans&view=subscription&layout=form&id=<?php echo $subscription->getId();?>"><?php echo $subscription->getId()." (".$subscription->getKey().")"; ?></a>
					</div>

				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_STATUS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $invoice->getStatusName();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_CREATED_DATE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $invoice->getCreatedDate(); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_PAID_DATE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $invoice->getPaidDate(); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_INVOICE_SERIAL'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $invoice->getSerial(); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_BUYER'); ?>

					<div class="o-control-input col-md-7">
						<a href="index.php?option=com_payplans&view=user&layout=form&id=<?php echo $invoice->getBuyer()->user_id; ?>"><?php echo $invoice->getBuyer()->getUsername();?></a>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_SUBTOTAL'); ?>

					<div class="o-control-input col-md-7">
						<div class="">
							<?php echo $this->html('form.amount', $invoice->getSubtotal(), $invoice->getCurrency()); ?>
						</div>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_DISCOUNTABLE'); ?>

					<div class="o-control-input col-md-7">
						<div class="">
							<?php echo $this->html('form.amount', $invoice->getDiscountable(), $invoice->getCurrency()); ?>
						</div>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_DISCOUNT'); ?>

					<div class="o-control-input col-md-7">
						<div class="">
							<?php echo $this->html('form.amount', $invoice->getDiscount(), $invoice->getCurrency()); ?>
						</div>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_TAXABLE'); ?>

					<div class="o-control-input col-md-7">
						<div class="">
							<?php echo $this->html('form.amount', $invoice->getTaxableAmount(), $invoice->getCurrency()); ?>
						</div>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_TAX'); ?>

					<div class="o-control-input col-md-7">
						<div class="">
							<?php echo $this->html('form.amount', $invoice->getTaxAmount(), $invoice->getCurrency()); ?>
						</div>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_NON_TAXABLE'); ?>

					<div class="o-control-input col-md-7">
						<div class="">
							<?php echo $this->html('form.amount', $invoice->getNontaxableAmount(), $invoice->getCurrency()); ?>
						</div>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_INVOICE_EDIT_TOTAL'); ?>

					<div class="o-control-input col-md-7">
						<div class="">
							<?php echo $this->html('form.amount', $invoice->getTotal(), $invoice->getCurrency()); ?>
						</div>
					</div>
				</div>


			</div>
		</div>
	</div>
</div>
