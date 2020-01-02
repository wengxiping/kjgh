<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die ;
?>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_alias', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_ALIAS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_alias', $config->get('fes_show_alias', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_additional_categories', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_ADDITIONAL_CATEGORIES'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_additional_categories', $config->get('fes_show_additional_categories', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_event_end_date', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_EVENT_END_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_event_end_date', $config->get('fes_show_event_end_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_registration_start_date', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_REGISTRATION_START_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_registration_start_date', $config->get('fes_show_registration_start_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_cut_off_date', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_CUT_OFF_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_cut_off_date', $config->get('fes_show_cut_off_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_price', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_PRICE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_price', $config->get('fes_show_price', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_price_text', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_PRICE_TEXT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_price_text', $config->get('fes_show_price_text', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_capacity', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_CAPACITY'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_capacity', $config->get('fes_show_capacity', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_registration_type', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_REGISTRATION_TYPE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_registration_type', $config->get('fes_show_registration_type', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_custom_registration_handle_url', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_CUSTOM_REGISTRATION_HANDLE_URL'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_custom_registration_handle_url', $config->get('fes_show_custom_registration_handle_url', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_attachment', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_ATTACHMENT'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_attachment', $config->get('fes_show_attachment', 0)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_notification_emails', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_NOTIFICATION_EMAILS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_notification_emails', $config->get('fes_show_notification_emails', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_paypal_email', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_PAYPAL_EMAIL'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_paypal_email', $config->get('fes_show_paypal_email', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_event_password', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_EVENT_PASSWORD'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_event_password', $config->get('fes_show_event_password', 0)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_access', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_ACCESS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_access', $config->get('fes_show_access', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_registration_access', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_REGISTRATION_ACCESS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_registration_access', $config->get('fes_show_registration_access', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_published', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_PUBLISHED'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_published', $config->get('fes_show_published', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_short_description', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_SHORT_DESCRIPTION'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_short_description', $config->get('fes_show_short_description', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_description', JText::sprintf('EB_FES_SUBMIT_EVENT', JText::_('EB_DESCRIPTION'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_description', $config->get('fes_show_description', 1)); ?>
    </div>
</div>