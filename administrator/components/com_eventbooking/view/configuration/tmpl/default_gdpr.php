<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
?>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('EB_GDPR_SETTINGS'); ?></legend>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('show_privacy_policy_checkbox', JText::_('EB_SHOW_PRIVACY_POLICY_CHECKBOX'), JText::_('EB_SHOW_PRIVACY_POLICY_CHECKBOX_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('show_privacy_policy_checkbox', $config->get('show_privacy_policy_checkbox', 0)); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('privacy_policy_article_id', JText::_('EB_PRIVACY_ARTICLE'), JText::_('EB_PRIVACY_ARTICLE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelper::getArticleInput($this->config->privacy_policy_article_id, 'privacy_policy_article_id'); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('privacy_policy_url', JText::_('EB_PRIVACY_URL'), JText::_('EB_PRIVACY_URL_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="privacy_policy_url" class="input-xxlarge" value="<?php echo $config->privacy_policy_url; ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('show_subscribe_newsletter_checkbox', JText::_('EB_SHOW_SUBSCRIBE_NEWSLETTER_CHECKBOX'), JText::_('EB_SHOW_SUBSCRIBE_NEWSLETTER_CHECKBOX_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('show_subscribe_newsletter_checkbox', $config->get('show_subscribe_newsletter_checkbox', 0)); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('show_agreement_on_email', JText::_('EB_SHOW_AGREEMENT_ON_EMAIL'), JText::_('EB_SHOW_AGREEMENT_ON_EMAIL_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('show_agreement_on_email', $config->get('show_agreement_on_email', 0)); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('store_user_ip', JText::_('EB_STORE_USER_IP'), JText::_('EB_STORE_USER_IP_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('store_user_ip', $config->get('store_user_ip', 1)); ?>
        </div>
    </div>
</fieldset>
