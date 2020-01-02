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
		<?php echo EventbookingHelperHtml::getFieldLabel('export_group_billing_records', JText::_('EB_EXPORT_GROUP_BILLING_RECORDS')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_group_billing_records', $config->get('export_group_billing_records', $config->get('include_group_billing_in_registrants', 1))); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_group_member_records', JText::_('EB_EXPORT_GROUP_MEMBERS_RECORDS')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_group_member_records', $config->get('export_group_member_records', $config->get('include_group_members_in_registrants', 0))); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_event_date', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_EVENT_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_event_date', $config->get('export_event_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_event_end_date', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_EVENT_END_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_event_end_date', $config->get('export_event_end_date', 0)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_user_id', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_USER_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_user_id', $config->get('export_user_id', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_number_registrants', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_NUMBER_REGISTRANTS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_number_registrants', $config->get('export_number_registrants', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_amount', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_amount', $config->get('export_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_discount_amount', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_DISCOUNT_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_discount_amount', $config->get('export_discount_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_late_fee', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_LATE_FEE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_late_fee', $config->get('export_late_fee', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_tax_amount', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_TAX'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_tax_amount', $config->get('export_tax_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_gross_amount', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_GROSS_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_gross_amount', $config->get('export_gross_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_registration_date', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_REGISTRATION_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_registration_date', $config->get('export_registration_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_payment_method', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_PAYMENT_METHOD'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_payment_method', $config->get('export_payment_method', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_transaction_id', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_TRANSACTION_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_transaction_id', $config->get('export_transaction_id', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_payment_status', JText::sprintf('EB_EXPORT_FIELD', JText::_('EB_PAYMENT_STATUS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_payment_status', $config->get('export_payment_status', 1)); ?>
    </div>
</div>