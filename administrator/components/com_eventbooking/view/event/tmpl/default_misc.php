<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$dateTimeFields = [
	'publish_up',
	'publish_down',
    'cancel_before_date',
    'registrant_edit_close_date',
];

foreach ($dateTimeFields as $dateField)
{
	if ($this->item->{$dateField} == $this->nullDate)
	{
		$this->item->{$dateField} = '';
	}	
}
?>
<fieldset class="adminform">
	<legend class="adminform"><?php echo JText::_('EB_MISC'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<span class="editlinktip hasTip" title="<?php echo JText::_('EB_EVENT_PASSWORD'); ?>::<?php echo JText::_('EB_EVENT_PASSWORD_EXPLAIN'); ?>"><?php echo JText::_('EB_EVENT_PASSWORD'); ?></span>
		</div>
		<div class="controls">
			<input type="text" name="event_password" id="event_password" class="input-small" size="10" value="<?php echo $this->item->event_password; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="editlinktip hasTip" title="<?php echo JText::_('EB_ACCESS'); ?>::<?php echo JText::_('EB_ACCESS_EXPLAIN'); ?>"><?php echo JText::_('EB_ACCESS'); ?></span>
		</div>
		<div class="controls">
			<?php echo $this->lists['access']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="editlinktip hasTip" title="<?php echo JText::_('EB_REGISTRATION_ACCESS'); ?>::<?php echo JText::_('EB_REGISTRATION_ACCESS_EXPLAIN'); ?>"><?php echo JText::_('EB_REGISTRATION_ACCESS'); ?></span>
		</div>
		<div class="controls">
			<?php echo $this->lists['registration_access']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_FEATURED'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('featured', $this->item->featured); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
            <?php echo EventbookingHelperHtml::getFieldLabel('hidden', JText::_('EB_HIDDEN'), JText::_('EB_HIDDEN_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('hidden', $this->item->hidden); ?>
        </div>
    </div>
	<?php
	if (JLanguageMultilang::isEnabled())
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_LANGUAGE'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['language']; ?>
			</div>
		</div>
	<?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo JText::_('EB_CREATED_BY'); ?></div>
		<div class="controls">
			<?php echo EventbookingHelper::getUserInput($this->item->created_by, 'created_by', 1); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="editlinktip hasTip" title="<?php echo JText::_('EB_MIN_NUMBER_REGISTRANTS'); ?>::<?php echo JText::_('EB_MIN_NUMBER_REGISTRANTS_EXPLAIN'); ?>"><?php echo JText::_('EB_MIN_NUMBER_REGISTRANTS'); ?></span>
		</div>
		<div class="controls">
			<input type="number" name="min_group_number" id="min_group_number" class="input-mini" size="10" value="<?php echo $this->item->min_group_number; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="editlinktip hasTip" title="<?php echo JText::_('EB_MAX_NUMBER_REGISTRANTS'); ?>::<?php echo JText::_('EB_MAX_NUMBER_REGISTRANTS_EXPLAIN'); ?>"><?php echo JText::_('EB_MAX_NUMBER_REGISTRANT_GROUP'); ?></span>
		</div>
		<div class="controls">
			<input type="number" name="max_group_number" id="max_group_number" class="input-mini" size="10" value="<?php echo $this->item->max_group_number; ?>"/>
		</div>
	</div>
    <?php
    if (!$this->config->multiple_booking)
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo EventbookingHelperHtml::getFieldLabel('free_event_registration_status', JText::_('EB_FREE_EVENT_REGISTRATION_STATUS'), JText::_('EB_FREE_EVENT_REGISTRATION_STATUS_EXPLAIN')); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['free_event_registration_status']; ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
			    <?php echo JText::_('EB_MEMBERS_DISCOUNT_APPLY_FOR'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['members_discount_apply_for']; ?>
            </div>
        </div>
    <?php
    }
    ?>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('ENABLE_COUPON'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_coupon']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_ENABLE_WAITING_LIST'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['activate_waiting_list']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_COLLECT_MEMBER_INFORMATION'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['collect_member_information']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_PREVENT_DUPLICATE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['prevent_duplicate_registration']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_SEND_NOTIFICATION_EMAILS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['send_emails']; ?>
		</div>
	</div>
	<?php
	if ($this->config->activate_deposit_feature)
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<span class="editlinktip hasTip" title="<?php echo JText::_('EB_DEPOSIT_AMOUNT'); ?>::<?php echo JText::_('EB_DEPOSIT_AMOUNT_EXPLAIN'); ?>"><?php echo JText::_('EB_DEPOSIT_AMOUNT'); ?></span>
			</div>
			<div class="controls">
				<input type="number" name="deposit_amount" id="deposit_amount" class="input-mini" size="5" value="<?php echo $this->item->deposit_amount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['deposit_type']; ?>
			</div>
		</div>
	<?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_ENABLE_CANCEL'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('enable_cancel_registration', $this->item->enable_cancel_registration); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_CANCEL_BEFORE_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo JHtml::_('calendar', $this->item->cancel_before_date, 'cancel_before_date', 'cancel_before_date', $this->datePickerFormat.' %H:%M:%S', array('class' => 'input-medium')); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_PUBLISH_UP'); ?>
		</div>
		<div class="controls">
			<?php echo JHtml::_('calendar', $this->item->publish_up, 'publish_up', 'publish_up', $this->datePickerFormat.' %H:%M:%S', array('class' => 'input-medium')); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_PUBLISH_DOWN'); ?>
		</div>
		<div class="controls">
			<?php echo JHtml::_('calendar', $this->item->publish_down, 'publish_down', 'publish_down', $this->datePickerFormat . ' %H:%M:%S', array('class' => 'input-medium')); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('registrant_edit_close_date', JText::_('EB_REGISTRANT_EDIT_CLOSE_DATE'), JText::_('EB_REGISTRANT_EDIT_CLOSE_DATE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo JHtml::_('calendar', $this->item->registrant_edit_close_date, 'registrant_edit_close_date', 'registrant_edit_close_date', $this->datePickerFormat . ' %H:%M:%S', array('class' => 'input-medium')); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  JText::_('EB_REGISTRATION_COMPLETE_URL'); ?>
        </div>
        <div class="controls">
            <input type="url" class="input-large" name="registration_complete_url" value="<?php echo $this->item->registration_complete_url; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  JText::_('EB_OFFLINE_PAYMENT_REGISTRATION_COMPLETE_URL'); ?>
        </div>
        <div class="controls">
            <input type="url" class="input-large" name="offline_payment_registration_complete_url" value="<?php echo $this->item->offline_payment_registration_complete_url; ?>" size="50" />
        </div>
    </div>
	<div class="control-group">
			<div class="control-label">
			<?php echo JText::_('EB_ENABLE_TERMS_CONDITIONS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_terms_and_conditions']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_TERMS_CONDITIONS'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelper::getArticleInput($this->item->article_id); ?>
		</div>
	</div>
</fieldset>
