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
<?php if ($this->config->get('show_billing_details')) { ?>
<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no">
		<?php echo JText::_('COM_PP_CHECKOUT_COMPANY_DETAILS');?>
	</div>

	<div class="o-card__body">
		<?php echo $this->html('floatLabel.lists', 'COM_PP_CHECKOUT_TRANSACTION_PURPOSE', 'preference[business_purpose]', $purposeValue, '', array('data-pp-transaction-purpose' => ''), array(
				array('title' => 'COM_PP_APP_EUVAT_USE_PURPOSE_SELECT', 'value' => 'none'),
				array('title' => 'COM_PP_CHECKOUT_BUSINESS', 'value' => 'business'),
				array('title' => 'COM_PP_CHECKOUT_PERSONAL', 'value' => 'personal')
			)); ?>

		<div class="<?php echo $purpose == PP_EUVAT_PURPOSE_BUSINESS ? '' : 't-hidden'; ?>" data-pp-business>
			<div class="o-grid o-grid--gutters">
				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_BUSINESS_NAME', 'preference[business_name]', $businessName, '', 'data-pp-company-bizname'); ?>
				</div>

				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_TAX_ID', 'preference[tin]', $businessVatno, '', 'data-pp-company-vatno'); ?>
				</div>
			</div>

			<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_ADDRESS', 'preference[business_address]', $businessAddress, '', 'data-pp-company-address'); ?>

			<div class="o-grid o-grid--gutters">
				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_CITY', 'preference[business_city]', $businessCity, '', 'data-pp-company-city'); ?>
				</div>

				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_STATE', 'preference[business_state]', $businessState, '', 'data-pp-company-state'); ?>
				</div>

				<div class="o-grid__cell">
					<?php echo $this->html('floatLabel.text', 'COM_PP_CHECKOUT_ZIP', 'preference[business_zip]', $businessZip, '', 'data-pp-company-zip'); ?>
				</div>
			</div>
		</div>

		<?php echo $this->html('floatlabel.country',  'COM_PP_CHECKOUT_COUNTRY', 'preference[business_country]', $country, '', array('data-pp-company-country' => '')); ?>

		<div>
			<div class="o-loader o-loader--sm o-loader--inline" data-pp-company-loader></div>
			<label class="" data-pp-company-message></label>
		</div>

	</div>
</div>
<?php } ?>
