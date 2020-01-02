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
		<?php echo EventbookingHelperHtml::getFieldLabel('show_certificate_sent_status', JText::_('EB_SHOW_CERTIFICATE_SENT_STATUS'), JText::_('EB_SHOW_CERTIFICATE_SENT_STATUS_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('show_certificate_sent_status', $config->show_certificate_sent_status); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('activate_certificate_feature', JText::_('EB_ACTIVATE_CERTIFICATE_FEATURE'), JText::_('EB_ACTIVATE_CERTIFICATE_FEATURE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_certificate_feature', $config->activate_certificate_feature); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('download_certificate_if_checked_in', JText::_('EB_DOWNLOAD_CERTIFICATE_IF_CHECKED_IN'), JText::_('EB_DOWNLOAD_CERTIFICATE_IF_CHECKED_IN_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('download_certificate_if_checked_in', $config->download_certificate_if_checked_in); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_prefix', JText::_('EB_CERTIFICATE_PREFIX'), JText::_('EB_CERTIFICATE_PREFIX_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="certificate_prefix" class="inputbox" value="<?php echo $config->get('certificate_prefix', 'CT'); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_number_length', JText::_('EB_CERTIFICATE_NUMBER_LENGTH'), JText::_('EB_CERTIFICATE_NUMBER_LENGTH_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="certificate_number_length" class="inputbox" value="<?php echo $config->get('certificate_number_length', 5); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_page_orientation', JText::_('EB_PAGE_ORIENTATION')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['certificate_page_orientation']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_page_format', JText::_('EB_PAGE_FORMAT')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['certificate_page_format']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_certificate_bg_image', JText::_('EB_DEFAULT_CERTIFICATE_BG_IMAGE'), JText::_('EB_DEFAULT_CERTIFICATE_BG_IMAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getMediaInput($config->get('default_certificate_bg_image'), 'default_certificate_bg_image'); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_certificate_bg_left', JText::_('EB_DEFAULT_BG_POSSITION')); ?>
    </div>
    <div class="controls">
		<?php echo JText::_('EB_LEFT') . '    ';?><input type="text" name="default_certificate_bg_left" class="input-mini" value="<?php echo (int) $config->default_certificate_bg_left; ?>" />
		<?php echo JText::_('EB_TOP') . '    ';?><input type="text" name="default_certificate_bg_top" class="input-mini" value="<?php echo (int) $config->default_certificate_bg_top; ?>" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_certificate_bg_width', JText::_('EB_DEFAULT_BG_SIZE')); ?>
    </div>
    <div class="controls">
		<?php echo JText::_('EB_WIDTH') . '    ';?><input type="text" name="default_certificate_bg_width" class="input-mini" value="<?php echo (int) $config->get('default_certificate_bg_width', 210); ?>" />
		<?php echo JText::_('EB_HEIGHT') . '    ';?><input type="text" name="default_certificate_bg_height" class="input-mini" value="<?php echo (int) $config->get('default_certificate_bg_height', 297); ?>" />
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_layout', JText::_('EB_DEFAULT_CERTIFICATE_LAYOUT'), JText::_('EB_DEFAULT_CERTIFICATE_LAYOUT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'certificate_layout',  $config->certificate_layout , '100%', '550', '75', '8' ) ;?>
	</div>
</div>