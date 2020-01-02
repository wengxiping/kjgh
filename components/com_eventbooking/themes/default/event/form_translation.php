<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$bootstrapHelper   = EventbookingHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$rootUri           = JUri::root();

echo JHtml::_('bootstrap.addTab', 'event', 'translation-page', JText::_('EB_TRANSLATION', true));
echo JHtml::_('bootstrap.startTabSet', 'event-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo JHtml::_('bootstrap.addTab', 'event-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . 'media/com_eventbooking/flags/' . $sef . '.png" />');
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo EventbookingHelperHtml::getFieldLabel('use_data_from_default_language_'.$sef, JText::_('EB_USE_DATA_FROM_DEFAULT_LANGUAGE'), JText::_('EB_USE_DATA_FROM_DEFAULT_LANGUAGE_EXPLAIN')) ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input type="checkbox" name="use_data_from_default_language_<?php echo $sef; ?>" value="1" />
		</div>
    </div>
	
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_TITLE'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input class="input-xlarge" type="text" name="title_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->escape($this->item->{'title_'.$sef}); ?>" />
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_ALIAS'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input class="input-xlarge" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_'.$sef}; ?>" />
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_PRICE_TEXT'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input class="input-xlarge" type="text" name="price_text_<?php echo $sef; ?>" id="price_text_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->escape($this->item->{'price_text_'.$sef}); ?>" />
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_CUSTOM_REGISTRATION_HANDLE_URL'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input class="input-xlarge" type="text" name="registration_handle_url_<?php echo $sef; ?>" id="registration_handle_url_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'registration_handle_url_'.$sef}; ?>" />
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_SHORT_DESCRIPTION'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo $editor->display( 'short_description_'.$sef,  $this->item->{'short_description_'.$sef} , '100%', '250', '75', '10' ) ; ?>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_DESCRIPTION'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo $editor->display( 'description_'.$sef,  $this->item->{'description_'.$sef} , '100%', '250', '75', '10' ) ; ?>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_META_KEYWORDS'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<textarea rows="5" cols="30" class="input-lage" name="meta_keywords_<?php echo $sef; ?>"><?php echo $this->item->{'meta_keywords_'.$sef}; ?></textarea>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_META_DESCRIPTION'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<textarea rows="5" cols="30" class="input-lage" name="meta_description_<?php echo $sef; ?>"><?php echo $this->item->{'meta_description_'.$sef}; ?></textarea>
		</div>
	</div>
	<?php
	echo JHtml::_('bootstrap.endTab');
}

echo JHtml::_('bootstrap.endTabSet');
echo JHtml::_('bootstrap.endTab');

