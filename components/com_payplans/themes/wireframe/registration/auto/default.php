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
<?php echo $this->output('site/checkout/default/login', array('showTwoFactor' => $showTwoFactor, 'twoFactors' => $twoFactors)); ?>

<div class="o-card o-card--borderless t-lg-mb--lg t-hidden" data-pp-register>
	<div class="o-card__header o-card__header--nobg t-lg-pl--no">
		<div class="o-grid">
			<div class="o-grid__cell">
				<?php echo JText::_('COM_PP_CHECKOUT_CREATE_NEW_ACCOUNT');?>
			</div>
			<div class="o-grid__cell t-text--right">
				<div style="font-weight: normal;">
					<?php echo JText::_('COM_PP_CHECKOUT_ALREADY_HAVE_ACCOUNT');?> <a href="javascript:void(0);" data-pp-login-link><?php echo JText::_('COM_PP_CHECKOUT_LOGIN');?></a>
				</div>
			</div>
		</div>
	</div>
	<div class="o-card__body">
		<?php if ($this->config->get('show_fullname')) { ?>
			<?php echo $this->html('floatlabel.text', 'COM_PP_CHECKOUT_YOUR_NAME', 'register_name', $data['register_name'], '', array('autocomplete' => 'off')); ?>
		<?php } ?>

		<?php if ($this->config->get('show_username')) { ?>
			<?php echo $this->html('floatlabel.text', 'COM_PP_CHECKOUT_USERNAME', 'register_username', $data['register_username'], '', array('autocomplete' => 'off')); ?>
		<?php } ?>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_CHECKOUT_EMAIL_ADDRESS', 'register_email', $data['register_email'], '', array('autocomplete' => 'off')); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.password', 'COM_PP_CHECKOUT_REGISTER_PASSWORD', 'register_password', '', '', array('autocomplete' => 'off')); ?>
			</div>

			<?php if ($this->config->get('show_confirmpassword')) { ?>
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.password', 'COM_PP_CHECKOUT_REGISTER_RECONFIRM_PASSWORD', 'register_password2', '', '', array('autocomplete' => 'off')); ?>
			</div>
			<?php } ?>
		</div>

		<?php if ($this->config->get('show_address')) { ?>
			<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_ADDRESS', 'address', $data['address']); ?>

			<div class="o-grid o-grid--gutters">
				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_CITY', 'city', $data['city']); ?>
				</div>

				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_STATE', 'state', $data['state']); ?>
				</div>

				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_ZIP', 'zip', $data['zip']); ?>
				</div>
			</div>
		<?php } ?>

		<?php if ($this->config->get('show_country')) { ?>
			<?php echo $this->html('floatlabel.country',  'COM_PP_CHECKOUT_COUNTRY', 'country', $data['country']); ?>
		<?php } ?>

		<?php if ($this->config->get('show_captcha')) { ?>
			<?php echo PP::captcha()->html();?>
		<?php } ?>
	</div>
</div>
