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
<style type="text/css">
@media print {
	.pp-invoice-actions {
		display: none;
	}

	a[href]:after {
		content: none !important;
	}
}
</style>
<div class="pp-checkout-container">
	<div class="pp-checkout-container__bd t-lg-mt--xl">
		<div class="pp-invoice-container t-lg-mt--xl">
			<div class="pp-invoice-container__hd t-lg-mb--xl pp-invoice-actions">
				<div class="o-card o-card--shadow">
					<div class="o-card__body">
						<div class="o-grid o-grid--center">
							<?php if ($this->config->get('enable_pdf_invoice')) { ?>
								<div class="o-grid__cell t-xs-mb--lg">
									<a href="<?php echo PPR::_('index.php?option=com_payplans&view=invoice&layout=download&invoice_key=' . $invoice->getKey());?>" class="btn btn-pp-default btn-block--is-mobile" data-invoice-download>
										<i class="fa fa-download"></i>&nbsp; <?php echo JText::_('COM_PP_DOWNLOAD_INVOICE');?>
									</a>
								</div>
							<?php } ?>

							<div class="o-grid__cell o-grid__cell--right">
								<a href="javascript:void(0);" class="btn btn-pp-primary btn-block--is-mobile" data-invoice-print>
									<i class="fa fa-print"></i>&nbsp; <?php echo JText::_('COM_PP_PRINT_INVOICE');?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="pp-invoice-container__bd">
				<div class="o-card o-card--shadow">
					<div class="o-card__body">
						<div class="pp-invoice-menu">
							<div class="pp-invoice-menu__hd">
								<div class="pp-invoice-menu__hd-id">
									<?php echo JText::_('COM_PP_INVOICE_LABEL');?> #<?php echo $invoice->getKey();?>
								</div>

								<div class="pp-invoice-menu__hd-date">
									<i class="far fa-calendar"></i>&nbsp; <?php echo PP::date($invoice->getCreatedDate())->format(JText::_('DATE_FORMAT_LC3'));?>
								</div>

								<div class="pp-invoice-menu__hd-state t-lg-ml--lg">
									<span class="o-label o-label--lg <?php echo $invoice->getStatusLabelClass();?>"><?php echo $invoice->getStatusName();?></span>
								</div>
							</div>

							<table class="pp-invoice-table">
								<thead>
									<tr>
										<th>
											<?php echo JText::_('COM_PP_INVOICE_FROM');?>
										</th>
										<td>
											<?php if ($this->config->get('companyName')) { ?>
												<?php echo $this->config->get('companyName'); ?><br />
											<?php } ?>

											<?php if ($this->config->get('companyAddress')) { ?>
												<?php echo nl2br($this->config->get('companyAddress'));?><br />
											<?php } ?>

											<?php if ($this->config->get('companyCityCountry')) { ?>
												<?php echo $this->config->get('companyCityCountry');?><br />
											<?php } ?>

											<?php if ($this->config->get('companyTaxId')) { ?>
												<?php echo JText::_('COM_PP_COMPANY_TAX_ID');?>: <?php echo $this->config->get('companyTaxId');?><br />
											<?php } ?>

											<?php if ($this->config->get('companyPhone')) { ?>
												<?php echo JText::_('COM_PP_TELEPHONE');?>: <?php echo $this->config->get('companyPhone');?>
											<?php } ?>
										</td>

										<td class="t-text--right">
											<?php if ($this->config->get('invoice_showlogo')) { ?>
											<div class="pp-invoice-logo">
												<img src="<?php echo PP::getCompanyLogo();?>" title="<?php echo $this->html('string.escape', $this->config->get('companyName'));?>" />
											</div>
											<?php } ?>
										</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<th>
											<?php echo JText::_('COM_PAYPLANS_INVOICE_BILL_TO');?>
										</th>
										<td>
											<?php echo $user->getDisplayName(); ?><br />
											<?php echo $user->getEmail(); ?><br />

											<div>
												<?php echo PP::rewriteContent($this->config->get('add_token'), $invoice, true); ?>
											</div>
										</td>

										<td class="t-text--right t-va--bottom">
											&nbsp;
										</td>
									</tr>
									<tr>
										<th>
											<?php echo JText::_('COM_PP_DETAILS');?>
										</th>
										<td>
											<b><?php echo JText::_($invoice->getTitle()); ?></b><br />

											<?php echo JText::sprintf('COM_PP_INVOICE_KEY', $invoice->getKey()); ?><br />

											<?php if ($invoice->isPaid() && $payment) { ?>
											<span><?php echo JText::_('COM_PAYPLANS_INVOICE_PAYMENT_METHOD'); ?>:</span>
											<b><?php echo $payment->getId() ? $payment->getAppName() : JText::_('COM_PAYPLANS_TRANSACTION_PAYMENT_GATEWAY_NONE');?></b>
											<?php } ?>
										</td>

										<td class="t-text--right">

										</td>
									</tr>

								</tbody>
							</table>

							<table class="pp-invoice-table">

								<tbody>
									<tr>
										<td>
											<?php echo JText::_('COM_PAYPLANS_INVOICE_PRICE'); ?>
										</td>

										<td class="t-text--right">
											<?php echo $this->html('html.amount', $invoice->getSubtotal(), $invoice->getCurrency()); ?>
										</td>
									</tr>

									<?php foreach ($modifiers as $modifier) { ?>
										<?php if (in_array($modifier->getSerial(), $discountablesSerials) ||
													in_array($modifier->getSerial(), $taxableSerials) ||
													in_array($modifier->getSerial(), $nonTaxesSerials)) { ?>
											<tr>
												<td>
													<?php $message = JText::_($modifier->getMessage()); ?>
													<?php if (in_array($modifier->getType() , array('eu-vat', 'basictax'))) { ?>
													 	<?php $message = $message. " ( ".round($modifier->getAmount())."% )"; ?>
													<?php } ?>
													
													 <div><?php echo $message; ?></div>
												</td>
												<td class="t-text--right">
													<?php echo ($modifier->isNegative()) ? '(-)&nbsp;' : '(+)&nbsp;'; ?>
													<?php $modifierAmount = str_replace('-', '', PPFormats::displayAmount($modifier->_modificationOf)); ?>
													<?php echo $this->html('html.amount', $modifierAmount, $invoice->getCurrency()); ?>
												</td>
											</tr>
										<?php } ?>
									<?php } ?>

									<?php if ($invoice->isPaid()) { ?>
									<tr>
										<td>
											<?php echo JText::_('COM_PP_AMOUNT_PAID'); ?>
										</td>

										<td class="t-text--right">
											<?php echo $this->html('html.amount', $invoice->getTotal(), $invoice->getCurrency()); ?>
										</td>
									</tr>
									<?php } ?>

									<tr>
										<th>
											<?php echo JText::_('COM_PAYPLANS_INVOICE_TOTAL'); ?>
										</th>

										<th class="t-text--right">
											<?php echo $this->html('html.amount', $invoice->getTotal(), $invoice->getCurrency()); ?>
										</th>
									</tr>

								</tbody>
							</table>

						</div>
					</div>
				</div>
			</div>

			<?php if (JString::trim($this->config->get('note'))) { ?>
			<div class="pp-invoice-container__bd t-lg-mt--xl">
				<div class="o-card o-card--shadow">
					<div class="o-card__body">
						<h5><?php echo JText::_('COM_PP_ADDITIONAL_NOTES');?></h5>
						<p class="t-lg-mt--md">
							<?php echo $this->config->get('note'); ?>
						</p>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
