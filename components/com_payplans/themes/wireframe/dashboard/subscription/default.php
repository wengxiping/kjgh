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
<div class="o-card t-lg-mb--xl">
	<div class="o-card__body">
		<div class="o-alert o-alert--danger t-lg-mb--lg t-hidden">
			<?php echo JText::_('COM_PP_SUBSCRIPTION_ALREADY_EXPIRED'); ?>
		</div>
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="o-card__title"><?php echo JText::_($subscription->getTitle());?></div>
				<div class="o-card__desc">
					<span>
						#<?php echo $subscription->getKey();?>
					</span>

					<?php if ($subscription->getSubscriptionDate()) { ?>
					<span class="t-lg-ml--md">
						<i class="far fa-calendar"></i>&nbsp; <?php echo $subscription->getSubscriptionDate()->format(JText::_('DATE_FORMAT_LC3'));?>
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
				<?php if (!$subscription->isNotActive()) { ?>
					<?php echo $this->html('subscription.status', $subscription); ?>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="o-card-list-group">
		<div class="o-card-list-group__item">
			<div class="o-card--meta">
				<div class="o-grid">
					<div class="o-grid__cell">
						<?php echo JText::_('COM_PP_INVOICES');?>
					</div>
					<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
						
					</div>
				</div>
			</div>
		</div>

		<div class="o-card-list-group__item">
			<div class="table-responsive-lg">
				<table class="table table--borderless table--vertical-middle">
					<thead>
						<td width="5%" class="t-text--center"><?php echo JText::_('#');?></td>
						<td><?php echo JText::_('COM_PP_KEY');?></td>
						<td width="15%" class="t-text--center">
							<?php echo JText::_('COM_PP_TOTAL'); ?>
						</td>
						<td width="15%" class="t-text--center">
							<?php echo JText::_('COM_PP_STATUS');?>
						</td>
						<?php if (!$this->isMobile()) { ?>
						<td width="10%">
							&nbsp;
						</td>
						<?php } ?>
					</thead>
					<tbody>
						<?php $i = 1; ?>
						<?php foreach ($invoices as $invoice) { ?>
							<tr>
								<td class="t-text--center">
									<?php echo $i;?>
								</td>
								<td>
									<a href="<?php echo $invoice->getPermalink();?>" target="_blank">
										<?php echo $invoice->getKey();?>
									</a>

									<?php if ($this->isMobile()) { ?>
									<div class="t-xs-mt--lg">
										<?php if ($this->config->get('enable_pdf_invoice')) { ?>
											<a href="<?php echo $invoice->getDownloadLink();?>" target="_blank">
												<i class="fa fa-cloud-download-alt"></i>
											</a>
											&nbsp;
										<?php } ?>
										
										<a href="<?php echo $invoice->getPrintLink();?>" target="_blank">
											<i class="fa fa-print"></i>
										</a>
									</div>
									<?php } ?>

								</td>
								<td class="t-text--center">
									<?php echo $this->html('html.amount', $invoice->getTotal(), $invoice->getCurrency());?>
								</td>
								<td class="t-text--center">
									<label class="o-label <?php echo $invoice->getStatusLabelClass();?>"><?php echo $invoice->getStatusName();?></label>
								</td>

								<td class="t-text--center">
									<?php if (in_array($invoice->getStatus(), array(PP_INVOICE_CONFIRMED, PP_NONE))) { ?>
										<div class="o-btn-group">
											<a href="<?php echo PPR::_('index.php?option=com_payplans&view=checkout&invoice_key=' . $invoice->getKey() . '&tmpl=component'); ?>" class="btn btn-pp-primary btn-xs" target="_blank">
												<?php echo JText::_('COM_PP_COMPLETE_PAYMENT_NOW');?>
											</a>
										</div>
									<?php } ?>
								</td>

								<?php if (!$this->isMobile()) { ?>
								<td class="t-text--center">
									<?php if ($this->config->get('enable_pdf_invoice')) { ?>
										<a href="<?php echo $invoice->getDownloadLink();?>" target="_blank">
											<i class="fa fa-cloud-download-alt"></i>
										</a>
										&nbsp;
									<?php } ?>
									
									<a href="<?php echo $invoice->getPrintLink();?>" target="_blank">
										<i class="fa fa-print"></i>
									</a>
								</td>
								<?php } ?>
							</tr>
							<?php $i++;?>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<?php if ($customDetails) { ?>
	<div class="o-card-list-group">
		<div class="o-card-list-group__item">
			<div class="o-card--meta">
				<div class="o-grid">
					<div class="o-grid__cell">
						<a href="javascript:void(0);"><?php echo JText::_('COM_PP_SUBSCRIPTION_DETAILS');?></a>
					</div>
					<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
						
					</div>
				</div>
			</div>
		</div>

		<div class="o-card-list-group__item">
			<div class="">
				<table class="table table--borderless">
					<tbody>
					<?php foreach ($customDetails as $details) { ?>
						<?php foreach ($details->getFieldsOutput($subscriptionParams) as $field) { ?>
							<tr>
								<td>
									<?php echo $field->label;?>
								</td>
								<td width="50%">
									<?php echo $field->value;?>
								</td>
							</tr>
							<?php $i++;?>
						<?php } ?>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php } ?>
</div>