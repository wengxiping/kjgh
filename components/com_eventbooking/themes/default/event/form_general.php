<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$iconCalendar      = $bootstrapHelper->getClassMapping('icon-calendar');

$dateFields = [
	'event_date',
	'event_end_date',
	'registration_start_date',
	'cut_off_date',
];

foreach ($dateFields as $dateField)
{
	if ($this->item->{$dateField} == $this->nullDate)
	{
		$this->item->{$dateField} = '';
	}	
}
?>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>"><?php echo JText::_('EB_TITLE') ; ?></div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="title" value="<?php echo $this->escape($this->item->title); ?>" class="input-xlarge" size="70" />
	</div>
</div>
<?php
if ($this->config->get('fes_show_alias', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>"><?php echo JText::_('EB_ALIAS') ; ?></div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="alias" value="<?php echo $this->item->alias; ?>" class="input-xlarge" size="70" />
        </div>
    </div>
<?php
}
?>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>"><?php echo JText::_('EB_MAIN_EVENT_CATEGORY') ; ?></div>
	<div class="<?php echo $controlsClass; ?>">
		<div style="float: left;"><?php echo $this->lists['main_category_id'] ; ?></div>
	</div>
</div>

<?php
if ($this->config->get('fes_show_additional_categories', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>"><?php echo JText::_('EB_ADDITIONAL_CATEGORIES') ; ?></div>
        <div class="<?php echo $controlsClass; ?>">
            <div style="float: left;"><?php echo $this->lists['category_id'] ; ?></div>
            <div style="float: left; padding-top: 25px; padding-left: 10px;"><?php echo JText::_('EB_SELECT_MULTIPLE_CATEGORIES'); ?></div>
        </div>
    </div>
<?php
}
?>

<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>"><?php echo JText::_('EB_THUMB_IMAGE') ; ?></div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="file" class="inputbox" name="thumb_image" size="60" />
		<?php
		if ($this->item->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $this->item->thumb))
		{
			$baseUri = JUri::base(true);

			if ($this->item->image && file_exists(JPATH_ROOT . '/' . $this->item->image))
			{
				$largeImageUri = $baseUri . '/' . $this->item->image;
			}
			elseif (file_exists(JPATH_ROOT . '/media/com_eventbooking/images/' . $this->item->thumb))
			{
				$largeImageUri = $baseUri . '/media/com_eventbooking/images/' . $this->item->thumb;
			}
			else
			{
				$largeImageUri = $baseUri . '/media/com_eventbooking/images/thumbs/' . $this->item->thumb;
			}
		?>
			<a href="<?php echo $largeImageUri; ?>" class="modal"><img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $this->item->thumb; ?>" class="img_preview" /></a>
			<input type="checkbox" name="del_thumb" value="1" /><?php echo JText::_('EB_DELETE_CURRENT_THUMB'); ?>
		<?php
		}
		?>
	</div>
</div>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>"><?php echo JText::_('EB_LOCATION') ; ?></div>
	<div class="<?php echo $controlsClass; ?>">
		<?php
		echo $this->lists['location_id'];

		if (JFactory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
		?>
			<button type="button" class="btn btn-small btn-success eb-colorbox-addlocation" href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=location&layout=popup&tmpl=component&Itemid='.$this->Itemid)?>"><span class="icon-new icon-white"></span><?php echo JText::_('EB_ADD_NEW_LOCATION') ; ?></button>
		<?php
		}
		?>
	</div>
</div>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>">
		<?php echo JText::_('EB_EVENT_START_DATE'); ?>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<?php echo str_replace('icon-calendar', $iconCalendar, JHtml::_('calendar', $this->item->event_date, 'event_date', 'event_date', $this->datePickerFormat)); ?>
		<?php echo $this->lists['event_date_hour'].' '.$this->lists['event_date_minute']; ?>
	</div>
</div>
<?php
if ($this->config->get('fes_show_event_end_date', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_EVENT_END_DATE'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo str_replace('icon-calendar', $iconCalendar, JHtml::_('calendar', $this->item->event_end_date, 'event_end_date', 'event_end_date', $this->datePickerFormat)); ?>
			<?php echo $this->lists['event_end_date_hour'].' '.$this->lists['event_end_date_minute'] ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_registration_start_date', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_REGISTRATION_START_DATE'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo str_replace('icon-calendar', $iconCalendar, JHtml::_('calendar', $this->item->registration_start_date, 'registration_start_date', 'registration_start_date', $this->datePickerFormat)); ?>
			<?php echo $this->lists['registration_start_hour'].' '.$this->lists['registration_start_minute'] ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_cut_off_date', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('cut_off_date', JText::_( 'EB_CUT_OFF_DATE' ), JText::_('EB_CUT_OFF_DATE_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo str_replace('icon-calendar', $iconCalendar, JHtml::_('calendar', $this->item->cut_off_date, 'cut_off_date', 'cut_off_date', $this->datePickerFormat)); ?>
			<?php echo $this->lists['cut_off_hour'] . ' ' . $this->lists['cut_off_minute']; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_price', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_PRICE'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="individual_price" id="individual_price" class="input-mini" size="10" value="<?php echo $this->item->individual_price; ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_price_text', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('price_text', JText::_( 'EB_PRICE_TEXT' ), JText::_('EB_PRICE_TEXT_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="price_text" id="price_text" class="input-xlarge" value="<?php echo $this->escape($this->item->price_text); ?>" />
        </div>
    </div>
<?php
}
?>

<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>">
		<?php echo JText::_('EB_TAX_RATE'); ?>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="tax_rate" id="tax_rate" class="input-small" size="10" value="<?php echo $this->item->tax_rate; ?>" />
	</div>
</div>

<?php
if ($this->config->get('fes_show_capacity', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('event_capacity', JText::_( 'EB_EVENT_CAPACITY' ), JText::_('EB_CAPACITY_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="event_capacity" id="event_capacity" class="input-mini" size="10" value="<?php echo $this->item->event_capacity; ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_registration_type', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>"><?php echo JText::_('EB_REGISTRATION_TYPE'); ?></div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo $this->lists['registration_type'] ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_custom_registration_handle_url', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('registration_handle_url', JText::_( 'EB_CUSTOM_REGISTRATION_HANDLE_URL' ), JText::_('EB_CUSTOM_REGISTRATION_HANDLE_URL_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="registration_handle_url" id="registration_handle_url"
                   class="input-xxlarge" size="10" value="<?php echo $this->item->registration_handle_url; ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_attachment', 0))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo EventbookingHelperHtml::getFieldLabel('attachment', JText::_( 'EB_ATTACHMENT' ), JText::_('EB_ATTACHMENT_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="file" name="attachment" />
            <?php

            if (JFactory::getUser()->authorise('core.admin', 'com_eventbooking'))
            {
	            echo $this->lists['available_attachment'];
            }

            if ($this->item->attachment)
            {
	            JText::_('EB_CURRENT_ATTACHMENT');
	        ?>
                <a href="<?php echo JUri::root() . 'media/com_eventbooking/' . $this->item->attachment; ?>" target="_blank"><?php echo $this->item->attachment; ?></a>
                <input type="checkbox" name="del_attachment" value="1"/><?php echo JText::_('EB_DELETE_CURRENT_ATTACHMENT'); ?>
	        <?php
            }
            ?>
        </div>
    </div>
<?php
}
?>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>">
		<?php echo EventbookingHelperHtml::getFieldLabel('max_group_number', JText::_( 'EB_MAX_NUMBER_REGISTRANTS' ), JText::_('EB_MAX_NUMBER_REGISTRANTS_EXPLAIN')); ?>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="max_group_number" id="max_group_number" class="input-mini" size="10" value="<?php echo $this->item->max_group_number; ?>" />
	</div>
</div>
<div class="<?php echo $controlGroupClass;?>">
    <div class="<?php echo $controlLabelClass; ?>">
        <?php echo  JText::_('EB_SEND_FIRST_REMINDER'); ?>
    </div>
    <div class="<?php echo $controlsClass; ?>">
        <input type="text" class="input-mini" name="send_first_reminder" value="<?php echo $this->item->send_first_reminder; ?>" size="5" /><span><?php echo ' ' . JText::_('EB_DAYS') . ' ' . $this->lists['send_first_reminder_time']; ?></span><?php echo JText::_('EB_EVENT_STARTED'); ?>
    </div>
</div>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
        <?php echo  JText::_('EB_SEND_SECOND_REMINDER'); ?>
    </div>
    <div class="<?php echo $controlsClass; ?>">
        <input type="text" class="input-mini" name="send_second_reminder" value="<?php echo $this->item->send_second_reminder; ?>" size="5" /><span><?php echo ' ' . JText::_('EB_DAYS') . ' ' . $this->lists['send_second_reminder_time']; ?></span><?php echo JText::_('EB_EVENT_STARTED'); ?>
    </div>
</div>
<?php
if ($this->config->get('fes_show_published', 1) && EventbookingHelperAcl::canChangeEventStatus($this->item->id))
{
?>
	<div class="<?php echo $controlGroupClass;?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_PUBLISHED'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php
				if (isset($this->lists['published']))
				{
					echo $this->lists['published'];
				}
				else
				{
					echo EventbookingHelperHtml::getBooleanInput('published', $this->item->published);
				}
			?>
		</div>
	</div>
<?php
}

if ($this->config->get('fes_show_short_description', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_SHORT_DESCRIPTION'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo $editor->display( 'short_description',  $this->item->short_description , '100%', '180', '90', '6' ) ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_description', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_DESCRIPTION'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo $editor->display( 'description',  $this->item->description , '100%', '250', '90', '10' ) ; ?>
        </div>
    </div>
<?php
}

if ($this->showCaptcha)
{
	if ($this->captchaPlugin == 'recaptcha_invisible')
	{
		$style = ' style="display:none;"';
	}
	else
	{
		$style = '';
	}
?>
	<div class="<?php echo $controlGroupClass;?>"<?php echo $style; ?>>
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  JText::_('EB_CAPTCHA'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo $this->captcha; ?>
		</div>
	</div>
<?php
}
