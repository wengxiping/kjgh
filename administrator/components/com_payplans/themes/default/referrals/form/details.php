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
	<div class="col-lg-6">
		<?php echo $this->output('admin/app/generic/form', array('app' => $app)); ?>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_REFERRALS_BEHAVIOUR'); ?>

			<div class="panel-body">

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_REFERRAL_LIMIT', '', 5); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'app_params[referral_limit]', $params->get('referral_limit', 5), '', array(), array('postfix' => 'Times', 'class' => 't-text--center', 'size' => 10)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_REFERRAL_WHEN_TO_SEND_EMAIL', '', 5); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'app_params[after_invoice_paid]', $params->get('after_invoice_paid', true), '', '', array(
							array('title' => 'When Invoice Marked As Paid', 'value' => 1),
							array('title' => 'Immediately After Code is Used', 'value' => 0)
						)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_REFERRAL_AMOUNT_TYPE', '', 5); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'app_params[referral_amount_type]', $params->get('referral_amount_type', ''), '', '', array(
							array('title' => 'COM_PP_FIXED', 'value' => 'fixed'),
							array('title' => 'COM_PP_PERCENTAGE', 'value' => 'percentage')
						)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_REFERRAL_SHARER_DISCOUNT', '', 5); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'app_params[referrar_amount]', $params->get('referrar_amount', '5.00'), '', array(), array('class' => 't-text--center', 'size' => 10)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_REFERRAL_PURCHASER_DISCOUNT', '', 5); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'app_params[referral_amount]', $params->get('referral_amount', '5.00'), '', array(), array('class' => 't-text--center', 'size' => 10)); ?>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>