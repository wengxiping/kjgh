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
<div class="o-card o-card--borderless t-lg-mt--lg t-hidden">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_APP_EUVAT_TITLE');?></div>

	<div class="o-card__body" data-pp-euvat-wrapper>
		<p><?php echo JText::_('COM_PP_APP_EUVAT_DESC'); ?></p>

		<div class="o-form-group t-lg-mt--xl">
			<div class="o-input-group">
				<div class="o-select-group">
					<?php echo $this->html('form.country', 'app_euvat_country_id', $country, 'app_euvat_country_id', array('data-pp-euvat-country' => '')); ?>
				</div>
			</div>

		</div>

		<div class="o-form-group t-lg-mt--xl">
			<div class="o-input-group">
				<?php echo $this->html('form.lists', 'app_euvat_purpose', $purpose, 'app_euvat_purpose', array('data-pp-euvat-purpose' => ''), $purposeOptions); ?>
			</div>
		</div>

		<div class="<?php echo $purpose != PP_EUVAT_PURPOSE_BUSINESS ? 't-hidden' : ''; ?>" data-pp-euvat-company>

			<div class="o-form-group t-lg-mt--xl">
				<div class="o-input-group">
					<?php echo $this->html('form.text', 'app_euvat_businessname', $business_name, 'app_euvat_businessname', array('placeholder' => 'Enter Business Name here...', 'data-pp-euvat-businessname' => '')); ?>
				</div>
			</div>

			<div class="o-form-group t-lg-mt--xl">
				<div class="o-input-group">
					<?php echo $this->html('form.text', 'app_euvat_vatnumber', $business_vatno, 'app_euvat_vatnumber', array('placeholder' => 'Enter VAT Number here...', 'data-pp-euvat-vatnumber' => '')); ?>
				</div>

			</div>
		</div>

		<div class="t-text--danger" data-pp-euvat-message></div>

		<div class="o-grid-sm">
			<div class="o-grid-sm__cell o-grid-sm__cell--right">
				<button type="button" class="btn btn-pp-default" data-pp-euvat-update><?php echo JText::_('COM_PP_APP_EUVAT_UPDATE_TAX_BUTTON'); ?></button>
			</div>
		</div>

	</div>
</div>
