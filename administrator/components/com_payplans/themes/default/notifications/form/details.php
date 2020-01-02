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
	<div class="col-lg-5">
		<?php echo $this->output('admin/app/generic/form', array('app' => $app)); ?>
	</div>

	<div class="col-lg-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_NOTIFICATIONS_BEHAVIOR'); ?>

			<div class="panel-body">

				<?php if ($when == 'on_status') { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_STATUS', '', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.status', 'app_params[on_status]', $params->get('on_status'), 'both', '', false, '', array(PP_SUBSCRIPTION_NONE)); ?>
					</div>
				</div>
				<?php } ?>

				<?php if ($when == 'on_preexpiry') { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_ON_PRE_EXPIRY_LABEL', 'COM_PAYPLANS_APP_EMAIL_ON_PRE_EXPIRY_DESC', 3, true, true); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.timer', 'app_params[on_preexpiry]', $params->get('on_preexpiry', '000000000000')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_LAST_CYCLE_LABEL', 'COM_PAYPLANS_APP_EMAIL_LAST_CYCLE_LABEL_DESC', 3, true, true); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.toggler', 'app_params[on_lastcycle]', $params->get('on_lastcycle', false)); ?>
					</div>
				</div>
				<?php } ?>

				<?php if ($when == 'on_postexpiry') { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_ON_POST_EXPIRY_LABEL', 'COM_PAYPLANS_APP_EMAIL_ON_POST_EXPIRY_DESC', 3, true, true); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.timer', 'app_params[on_postexpiry]', $params->get('on_postexpiry', '000000000000')); ?>
					</div>
				</div>
				<?php } ?>

				<?php if ($when == 'on_postactivation') { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_ON_POST_ACTIVATION_LABEL', 'COM_PAYPLANS_APP_EMAIL_ON_POST_ACTIVATION_DESC', 3, true, true); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.timer', 'app_params[on_postactivation]', $params->get('on_postactivation', '000000000000')); ?>
					</div>
				</div>
				<?php } ?>

				<?php if ($when == 'on_cart_abondonment') { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_ON_CART_ABONDONMENT_LABEL', 'COM_PAYPLANS_APP_EMAIL_ON_CART_ABONDONMENT_DESC', 3, true, true); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.timer', 'app_params[on_cart_abondonment]', $params->get('on_cart_abondonment', '000000000000')); ?>
					</div>
				</div>
				<?php } ?>
				
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_SUBJECT', '', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.text', 'app_params[subject]', $params->get('subject', '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_CC_LIST', '', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.textarea', 'app_params[send_cc]', $params->get('send_cc', '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_BCC_LIST', '', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.textarea', 'app_params[send_bcc]', $params->get('send_bcc', '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_ATTACHMENT_LABEL', 'COM_PAYPLANS_APP_EMAIL_ATTACHMENT_DESC', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.filelist', 'app_params[attachment]', $params->get('attachment', ''), '', '', 'media:/emails/attachments', '.', array(), false); ?>

					</div>
				</div>
		
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_INCLUDE_INVOICE_IN_ATTACHMENTS', '', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.toggler', 'app_params[send_invoice]', $params->get('send_invoice', false)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_SEND_AS_HTML', '', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.toggler', 'app_params[html_format]', $params->get('html_format', true)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_CONTENT_SOURCE', '', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.dependency', 'app_params[email_template]', $params->get('email_template', 'custom'), '', '', array(
							array('title' => 'COM_PP_NOTIFICATIONS_CUSTOM_CONTENT', 'value' => 'custom', 'for' => 'data-custom-content'),
							array('title' => 'COM_PP_NOTIFICATIONS_USE_EXISTING_TEMPLATE', 'value' => 'choose_template', 'for' => 'data-email-templates'),
							array('title' => 'COM_PP_NOTIFICATIONS_USE_EXISTING_JOOMLA_ARTICLE', 'value' => 'choose_joomla_article', 'for' => 'data-content-joomlaarticle')
						)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_CONTENT_LABEL', 'COM_PAYPLANS_APP_EMAIL_CONTENT_DESC', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.editor', 'app_params[content]', $params->get('content', ''), '', 'data-custom-content'); ?>

					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_APP_EMAIL_TEMPLATE_LABEL', 'COM_PAYPLANS_APP_EMAIL_TEMPLATE_DESC', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.filelist', 'app_params[choose_template]', $params->get('choose_template', 'subscription_active.php'), '', 'data-email-templates', 'media:/emails/templates'); ?>

					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_NOTIFICATIONS_JOOMLA_ARTICLE_LABEL', 'COM_PP_NOTIFICATIONS_JOOMLA_ARTICLE_DESC', 3); ?>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.joomlaArticle', 'app_params[choose_joomla_article]', $params->get('choose_joomla_article'), '', 'data-content-joomlaarticle', array('multiple' => false)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<label class="col-md-3"></label>

					<div class="o-control-input col-md-9">
						<?php echo $this->html('form.rewriter'); ?>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>