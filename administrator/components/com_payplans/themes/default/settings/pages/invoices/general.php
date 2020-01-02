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
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_INVOICE'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.textbox', 'expert_invoice_serial_format', 'COM_PAYPLANS_INVOICE_INVOICE_SERIAL_FORMAT'); ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_SKIP_FREE_INVOICES'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.toggler', 'skip_free_invoices', $this->config->get('skip_free_invoices')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ENABLE_PDF_INVOICE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.toggler', 'enable_pdf_invoice', $this->config->get('enable_pdf_invoice')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_PDF_FONTS'); ?>

					<div class="o-control-input col-md-7">

						<?php echo $this->html('form.lists', 'pdf_font', $this->config->get('pdf_font'), '', '', array(
									array('title' => 'COM_PP_PDF_FONT_TIMES', 'value' => 'times'),
									array('title' => 'COM_PP_PDF_FONT_DEJAVU', 'value' => 'dejavu sans'),
									array('title' => 'COM_PP_PDF_FONT_HELVETICA', 'value' => 'helvetica')
								)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_SHOW_BILLING_DETAILS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.toggler', 'show_billing_details', $this->config->get('show_billing_details')); ?>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_INVOICE_LAYOUT'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'invoice_showlogo', 'COM_PP_CONFIG_INVOICE_SHOW_LOGO'); ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_COMPANY_LOGO'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.imagefile', 'companyLogo', $this->config->get('companyLogo', '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_COMPANY_NAME'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'companyName', $this->config->get('companyName', 'Stack Ideas Private Limited')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_COMPANY_ADDRESS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.textarea', 'companyAddress', $this->config->get('companyAddress', 'B-11-5 Level 11, Tower B North Point Office Tower')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_COMPANY_CITY'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'companyCityCountry', $this->config->get('companyCityCountry', 'Kuala Lumpur')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_TELEPHONE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'companyPhone', $this->config->get('companyPhone', '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_COMPANY_TAX_ID'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'companyTaxId', $this->config->get('companyTaxId', '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_ADD_CUSTOM_CONTENT'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.textarea', 'add_token', $this->config->get('add_token', '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<label class="o-control-label">&nbsp;</label>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.rewriter'); ?>
					</div>
				</div>
				

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_DISPLAY_BLANK_TOKEN'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.toggler', 'show_blank_token', $this->config->get('show_blank_token', 1)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_INVOICE_NOTE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.textarea', 'note', $this->config->get('note', '')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>