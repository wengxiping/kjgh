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
<form action="<?php echo JRoute::_('index.php');?>" method="post">
<div class="o-row">
	<div class="o-col--12 o-col--top">
			
		<?php if ($this->config->get('discounts_referral')) { ?>
		<div class="o-card o-card--borderless t-lg-mb--lg">
			<div class="o-card__header o-card__header--nobg t-lg-pl--no">
				<?php echo JText::_('COM_PP_YOUR_REFERRAL_CODE');?>
			</div>

			<div class="o-card__body">
				<p class="t-lg-mb--xl"><?php echo JText::_('COM_PP_REFERRAL_DESC_USER');?></p>
				<div class="o-input-group">
					<input type="text" class="o-form-control" disabled="disabled" value="<?php echo PP::getKeyFromID($user->id);?>" data-pp-referral-code />
					<span class="o-input-group__append">
						<button class="btn btn-pp-default-o" type="button" data-pp-referral-copy>
							<i class="far fa-clipboard"></i>&nbsp; <?php echo JText::_('COM_PP_COPY');?>
						</button>
					</span>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ($this->config->get('user_edit_preferences')) { ?>
		<div class="o-card o-card--borderless">
			<div class="o-card__header o-card__header--nobg t-lg-pl--no">
				<?php echo JText::_('COM_PP_ACCOUNT_PREFERENCES');?>
			</div>

			<div class="o-card__body">

				<?php echo $this->html('floatLabel.text', 'COM_PP_NAME', 'name',  $user->getName()); ?>

				<div class="o-grid o-grid--gutters">
					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_USERNAME', 'username',  $user->getUsername()); ?>
					</div>
					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_EMAIL_ADDRESS', 'email',  $user->getEmail()); ?>
					</div>
				</div>
				

				<?php echo $this->html('floatLabel.lists', 'COM_PP_CHECKOUT_TRANSACTION_PURPOSE', 'preference[business_purpose]',  $preferences->get('business_purpose', ''), 'business_purpose', array('data-pp-transaction-purpose' => ''), array(
						array('title' => 'COM_PP_CHECKOUT_BUSINESS', 'value' => PP_EUVAT_PURPOSE_BUSINESS),
						array('title' => 'COM_PP_CHECKOUT_PERSONAL', 'value' => PP_EUVAT_PURPOSE_PERSONAL)
					)); ?>

				<div class="o-grid o-grid--gutters <?php echo $preferences->get('business_purpose', '') == PP_EUVAT_PURPOSE_BUSINESS ? '' : 't-hidden';?>" data-userpreference-business>
					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_BUSINESS_NAME', 'preference[business_name]', $preferences->get('business_name', '')); ?>
					</div>

					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_TAX_ID', 'preference[tin]',  $preferences->get('tin', '')); ?>
					</div>
				</div>

				<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_SHIPPING_ADDRESS', 'preference[shipping_address]',  $preferences->get('shipping_address', '')); ?>


				<div class="o-grid o-grid--gutters <?php echo $preferences->get('business_purpose', '') == PP_EUVAT_PURPOSE_BUSINESS ? '' : 't-hidden';?>" data-userpreference-business>
					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_BUSINESS_ADDRESS', 'preference[business_address]',  $preferences->get('business_address', '')); ?>
					</div>

					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_CITY', 'preference[business_city]',  $preferences->get('business_city', '')); ?>
					</div>
				</div>

				<div class="o-grid o-grid--gutters <?php echo $preferences->get('business_purpose', '') == PP_EUVAT_PURPOSE_BUSINESS ? '' : 't-hidden';?>" data-userpreference-business>
					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_STATE', 'preference[business_state]',  $preferences->get('business_state', '')); ?>
					</div>

					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_ZIP', 'preference[business_zip]',  $preferences->get('business_zip', '')); ?>
					</div>
				</div>

				<?php echo $this->html('floatlabel.country',  'COM_PP_CHECKOUT_COUNTRY', 'country',  $user->getCountry()); ?>
			</div>
		</div>

		<div class="o-card o-card--borderless">
			<div class="o-card__header o-card__header--nobg t-lg-pl--no">
				<?php echo JText::_('COM_PP_ACCOUNT_CHANGE_PASSWORD');?>
			</div>

			<div class="o-card__body">
				<p class="t-lg-mb--xl"><?php echo JText::_('COM_PP_ACCOUNT_CHANGE_PASSWORD_INFO'); ?></p>

				<div class="o-grid o-grid--gutters">
					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.password', 'COM_PP_PASSWORD', 'password', ''); ?>		
					</div>

					<div class="o-grid__cell">
						<?php echo $this->html('floatLabel.password', 'COM_PP_RECONFIRM_PASSWORD', 'password2', ''); ?>		
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ($this->config->get('user_edit_customdetails') && $customDetails) { ?>
			<?php foreach ($customDetails as $customDetail) { ?>
				<?php echo $customDetail->renderForm($params, true); ?>
			<?php } ?>
		<?php } ?>

		<hr />

		<div class="o-card o-card--borderless t-lg-mb--lg">
			<div class="o-card__body">
				<div class="o-grid-sm">
					<div class="o-grid-sm__cell o-grid-sm__cell--right">
						<button type="submit" class="btn btn-pp-primary btn--lg">
							<?php echo JText::_('COM_PP_UPDATE_PREFERENCES_BUTTON');?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php echo $this->html('form.action', '', 'user.save'); ?>
</form>