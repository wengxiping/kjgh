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
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data">
	<div class="row o-form-horizontal">
		<div class="col-lg-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_PP_HEADING_EXPORT_REPORTS_CSV'); ?>

				<div class="panel-body">
					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_TYPE'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.lists', 'type', '', 'type', 'data-export-report-type', $exportTypes); ?>
						</div>
					</div>

					<div class="o-form-group t-hidden" data-subscription-status-wrapper>
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_STATUS'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.status', 'subsStatus[]', '', 'subscription', '', true, 'data-subscription-status'); ?>
						</div>
					</div>

					<div class="o-form-group" data-invoice-status-wrapper>
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_STATUS'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.status', 'invStatus[]', '', 'invoice', '', true, 'data-invoice-status', array(PP_INVOICE_WALLET_RECHARGE)); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_PLANS'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.plans', 'plans', '', true, true, 'data-export-plans'); ?>
						</div>
					</div>

					<div class="o-form-group" data-payment-gateway-wrapper>
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_PAYMENT_GATEWAY'); ?>

						<div class="o-control-input col-md-7">
							<select class="o-form-control" name="gateway[]"  multiple="multiple" style="<?php count($gateways) > 4 ? 'min-height: 100px;' : ''; ?>">
								<?php foreach ($gateways as $gateway) { ?>
									<option value="<?php echo $gateway->app_id;?>"> 
										<?php echo $gateway->title;?>
									</option>
								<?php } ?>
							</select>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_PDF_TRANSACTION_DATE_RANGE'); ?>
						
						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.dateRange'); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_LIMIT'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.text', 'limit', '50', '', '', array('postfix' => 'Items', 'class' => 't-text--center', 'size' => 8)); ?>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'reports', 'export'); ?>
</form>
