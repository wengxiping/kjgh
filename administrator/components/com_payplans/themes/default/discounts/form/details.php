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
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_DISCOUNT_GENERAL'); ?>

			<div class="panel-body">
				<?php if (!$generator) { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_TITLE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'title', $discount->title, '', array('placeholder' => JText::_('COM_PP_DISCOUNT_COUPON_PLACEHOLDER'))); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_PUBLISHED'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'published', $discount->published); ?>
					</div>
				</div>
				<?php } ?>

				<?php if ($generator) { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_TOTAL_GENERATOR'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'generator_total', 10); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_GENERATOR_CODE_PREFIX'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'generator_prefix', 'COUPON_'); ?>
					</div>
				</div>
				<?php } ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_ALL_PLANS'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.allPlans', 'core_discount', $discount->isCoreDiscount(), '', array('[data-discount-plans]')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $discount->isCoreDiscount() ? 't-hidden' : '';?>" data-discount-plans>
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_PLANS'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.plans', 'plans', $discount->getPlans(), true, true, array('data-discount-plans' => '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_USAGE_TYPE'); ?>
					<?php $discountType = $discount->getCouponType();
						$disabled = "";						
						if ($discountType == 'referral') {
							$disabled = "disabled='disabled'";
						} else {
							unset($types['referral']);
						}
					?>
					<div class="o-control-input">
						<select name="coupon_type" class="o-form-control" data-discount-coupon-type <?php echo $disabled; ?>>
							<?php foreach ($types as $key => $value) { ?>
							<option value="<?php echo $key;?>" <?php echo $discountType == $key ? 'selected="selected"' : '';?>><?php echo $value;?></option>
							<?php } ?>
						</select>
					</div>
				</div>

				<?php if (!$generator) { ?>
				<div class="o-form-group <?php echo in_array($discount->getCouponType(), array(null, 'firstinvoice', 'eachrecurring', 'discount_for_time_extend')) ? '' : 't-hidden';?>" data-discount-options="code">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_COUPON_CODE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'coupon_code', $discount->getCouponCode(), '', array('placeholder' => JText::_('COM_PP_DISCOUNT_COUPON_CODE_PLACEHOLDER'))); ?>
					</div>
				</div>
				<?php } ?>

				<div class="o-form-group <?php echo in_array($discount->getCouponType(), array(null, 'firstinvoice', 'eachrecurring', 'autodiscount_onrenewal', 'autodiscount_onupgrade', 'autodiscount_oninvoicecreation')) ? '' : 't-hidden';?>"
					data-discount-options="type">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_TYPE'); ?>

					<div class="o-control-input">
						<select name="params[coupon_amount_type]" class="o-form-control">
							<option value="fixed" <?php echo $params->get('coupon_amount_type') == 'fixed' ? 'selected="selected"' : '';?>><?php echo JText::_('Fixed Amount'); ?></option>
							<option value="percentage" <?php echo $params->get('coupon_amount_type') == 'percentage' ? 'selected="selected"' : '';?>><?php echo JText::_('Percentage'); ?></option>
						</select>
					</div>
				</div>

				<div class="o-form-group <?php echo in_array($discount->getCouponType(), array(null, 'firstinvoice', 'eachrecurring', 'autodiscount_onupgrade', 'autodiscount_oninvoicecreation')) ? '' : 't-hidden';?>" data-discount-options="amount">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_AMOUNT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'coupon_amount', $discount->getCouponAmount(), '', array('placeholder' => '5.00')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo in_array($discount->getCouponType(), array('autodiscount_onrenewal')) ? '' : 't-hidden';?>" data-discount-options="preexpiry">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_PRE_EXPIRY'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[amount_pre_expiry]', $params->get('amount_pre_expiry')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo in_array($discount->getCouponType(), array('autodiscount_onrenewal')) ? '' : 't-hidden';?>" data-discount-options="postexpiry">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_POST_EXPIRY'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[amount_post_expiry]', $params->get('amount_post_expiry')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo in_array($discount->getCouponType(), array('discount_for_time_extend')) ? '' : 't-hidden';?>" data-discount-options="extendtime">
					<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_EXTEND_SUBSCRIPTION'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.timer', 'params[extend_time_discount]', $params->get('extend_time_discount')); ?>
					</div>
				</div>

			</div>

		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php if ($discountType != 'referral') { ?>
				<?php echo $this->html('panel.heading', 'COM_PP_DISCOUNT_ADVANCED'); ?>

				<div class="panel-body">
					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_START_DATE'); ?>

						<div class="o-control-input">
							<?php echo JHtml::_('calendar', $discount->getStartDate(), 'start_date', 'start_date', '%Y-%m-%d %H:%M:%S', array('class' => 'hello')); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_END_DATE'); ?>

						<div class="o-control-input">
							<?php echo JHtml::_('calendar', $discount->getEndDate(), 'end_date', 'end_date', '%Y-%m-%d %H:%M:%S', array('class' => 'hello')); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_USAGE_LIMIT'); ?>

						<div class="o-control-input">
							<?php echo $this->html('form.text', 'params[allowed_quantity]', $params->get('allowed_quantity')); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_REUSABLE_BY_USER'); ?>

						<div class="o-control-input">
							<?php echo $this->html('form.toggler', 'params[reusable]', $params->get('reusable', true)); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_DISCOUNT_ALLOW_COMBINING_DISCOUNTS'); ?>

						<div class="o-control-input">
							<?php echo $this->html('form.toggler', 'params[allow_clubbing]', $params->get('allow_clubbing', false)); ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
