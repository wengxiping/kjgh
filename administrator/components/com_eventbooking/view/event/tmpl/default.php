<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', '.advancedSelect', null, array('placeholder_text_multiple' => JText::_('EB_SELECT_CATEGORIES')));
JHtml::_('behavior.tabstate');
JHtml::_('jquery.framework');
JHtml::_('script', 'jui/cms.js', false, true);

$translatable    = JLanguageMultilang::isEnabled() && count($this->languages);
$editor          = JEditor::getInstance(JFactory::getConfig()->get('editor'));
$bootstrapHelper = EventbookingHelperHtml::getAdminBootstrapHelper();
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$span8Class      = $bootstrapHelper->getClassMapping('span8');
$span4Class      = $bootstrapHelper->getClassMapping('span4');
?>
<form action="index.php?option=com_eventbooking&view=event" method="post" name="adminForm" id="adminForm"
      class="form form-horizontal" enctype="multipart/form-data">
	<?php echo JHtml::_('bootstrap.startTabSet', 'event', array('active' => 'basic-information-page')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'event', 'basic-information-page', JText::_('EB_BASIC_INFORMATION', true)); ?>
	<div class="<?php echo $rowFluidClass; ?>">
		<div class="<?php echo $span8Class; ?>">
			<?php echo $this->loadTemplate('general', array('editor' => $editor)); ?>
		</div>
		<div class="<?php echo $span4Class; ?>">
			<?php
			echo $this->loadTemplate('group_rates');
			echo $this->loadTemplate('misc');

			if ($this->config->activate_recurring_event && (!$this->item->id || $this->item->event_type == 1))
			{
				echo $this->loadTemplate('recurring_settings');
			}
			?>
			<fieldset class="adminform">
				<legend class="adminform"><?php echo JText::_('EB_META_DATA'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_PAGE_TITLE'); ?>
					</div>
					<div class="controls">
						<input class="input-large" type="text" name="page_title" id="page_title" size="" maxlength="250" value="<?php echo $this->item->page_title; ?>"/>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_PAGE_HEADING'); ?>
					</div>
					<div class="controls">
						<input class="input-large" type="text" name="page_heading" id="page_heading" size="" maxlength="250" value="<?php echo $this->item->page_heading; ?>"/>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_META_KEYWORDS'); ?>
					</div>
					<div class="controls">
						<textarea rows="5" cols="30" class="input-lage"
						          name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_META_DESCRIPTION'); ?>
					</div>
					<div class="controls">
						<textarea rows="5" cols="30" class="input-lage"
						          name="meta_description"><?php echo $this->item->meta_description; ?></textarea>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<?php

	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'event', 'discount-page', JText::_('EB_DISCOUNT_SETTING', true));
	echo $this->loadTemplate('discount_settings');
	echo JHtml::_('bootstrap.endTab');

	if ($this->config->event_custom_field)
	{
		echo JHtml::_('bootstrap.addTab', 'event', 'extra-information-page', JText::_('EB_EXTRA_INFORMATION', true));
	?>
		<table class="admintable" width="100%">
			<?php
				foreach ($this->form->getFieldset('basic') as $field)
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php
				}
			?>
		</table>
	<?php
		echo JHtml::_('bootstrap.endTab');
	}

	if ($this->config->activate_tickets_pdf)
	{
		echo JHtml::_('bootstrap.addTab', 'event', 'tickets-page', JText::_('EB_TICKETS_SETTINGS', true));
		echo $this->loadTemplate('tickets', array('editor' => $editor));
		echo JHtml::_('bootstrap.endTab');
	}

	if ($this->config->activate_certificate_feature)
	{
		echo JHtml::_('bootstrap.addTab', 'event', 'certificate-page', JText::_('EB_CERTIFICATE_SETTINGS', true));
		echo $this->loadTemplate('certificate', array('editor' => $editor));
		echo JHtml::_('bootstrap.endTab');
	}

	echo JHtml::_('bootstrap.addTab', 'event', 'advance-settings-page', JText::_('EB_ADVANCED_SETTINGS', true));
	echo $this->loadTemplate('advanced_settings', array('editor' => $editor));
	echo JHtml::_('bootstrap.endTab');

	echo JHtml::_('bootstrap.addTab', 'event', 'messages-page', JText::_('EB_MESSAGES', true));
	echo $this->loadTemplate('messages', array('editor' => $editor));
	echo JHtml::_('bootstrap.endTab');

	if ($translatable)
	{
		echo $this->loadTemplate('translation', array('editor' => $editor));
	}

	if (count($this->plugins))
	{
		$count = 0;

		foreach ($this->plugins as $plugin)
		{
			$count++;
			echo JHtml::_('bootstrap.addTab', 'event', 'tab_' . $count, JText::_($plugin['title'], true));
			echo $plugin['form'];
			echo JHtml::_('bootstrap.endTab');
		}
	}

	// Add support for custom settings layout
	if (file_exists(__DIR__ . '/default_custom_settings.php'))
	{
		echo JHtml::_('bootstrap.addTab', 'event', 'custom-settings-page', JText::_('EB_EVENT_CUSTOM_SETTINGS', true));
		echo $this->loadTemplate('custom_settings', array('editor' => $editor));
		echo JHtml::_('bootstrap.endTab');
	}

	echo JHtml::_('bootstrap.endTabSet');
	?>
	<input type="hidden" name="option" value="com_eventbooking"/>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
	Joomla.submitbutton = function (pressbutton)
    {
		var form = document.adminForm;

		if (pressbutton == 'cancel')
		{
			Joomla.submitform(pressbutton);
		}
		else
		{
			if (form.title.value == '')
			{
				alert("<?php echo JText::_('EB_PLEASE_ENTER_TITLE'); ?>");
				form.title.focus();
				return;
			}

			if (form.main_category_id.value == 0)
			{
				alert("<?php echo JText::_("EB_CHOOSE_CATEGORY"); ?>");
				form.category_id.focus();
				return;
			}

			if (form.event_date.value == '')
			{
				alert("<?php echo JText::_('EB_ENTER_EVENT_DATE'); ?>");
				form.event_date.focus();
				return;
			}

            <?php
                if ($this->config->activate_recurring_event)
                {
                ?>
                    var recurringType = jQuery('#recurring_type').val();

                    if (recurringType > 0)
                    {
                        if (form.recurring_frequency.value == '')
                        {
                            alert("<?php echo JText::_("EB_ENTER_RECURRING_INTERVAL"); ?>");
                            form.recurring_frequency.focus();
                            return;
                        }

                        // Weekly recurring, at least one weekday needs to be selected
                        if (recurringType == 2)
                        {
                            //Check whether any days in the week
                            var checked = false;

                            for (var i = 0; i < form['weekdays[]'].length; i++)
                            {
                                if (form['weekdays[]'][i].checked)
                                {
                                    checked = true;
                                }
                            }

                            if (!checked)
                            {
                                alert("<?php echo JText::_("EB_CHOOSE_ONEDAY"); ?>");
                                form['weekdays[]'][0].focus();
                                return;
                            }
                        }

                        if (recurringType == 3)
                        {
                            if (form.monthdays.value == '')
                            {
                                alert("<?php echo JText::_("EB_ENTER_DAY_IN_MONTH"); ?>");
                                form.monthdays.focus();

                                return;
                            }
                        }

                        if (form.recurring_end_date.value == '' && form.recurring_occurrencies.value == '')
                        {
                            alert("<?php echo JText::_("EB_ENTER_RECURRING_ENDING_SETTINGS"); ?>");
                            form.recurring_end_date.focus();

                            return;
                        }
                    }
                <?php
                }

                if (method_exists($editor, 'save'))
                {
                    $editorFields = array('short_description', 'description', 'user_email_body', 'user_email_body_offline', 'thanks_message', 'thanks_message_offline', 'registration_approved_email_body');

                    foreach ($editorFields as $editorField)
                    {
                        echo $editor->save($editorField);
                    }
                }
			?>
			Joomla.submitform(pressbutton);
		}
	};

	function addRow() {
		var table = document.getElementById('price_list');
		var newRowIndex = table.rows.length - 1;
		var row = table.insertRow(newRowIndex);
		var registrantNumber = row.insertCell(0);
		var price = row.insertCell(1);
		registrantNumber.innerHTML = '<input type="text" class="input-mini" name="registrant_number[]" size="10" />';
		price.innerHTML = '<input type="text" class="input-mini" name="price[]" size="10" />';
	}

	function removeRow()
    {
		var table = document.getElementById('price_list');
		var deletedRowIndex = table.rows.length - 2;

		if (deletedRowIndex >= 1)
		{
			table.deleteRow(deletedRowIndex);
		}
		else
		{
			alert("<?php echo JText::_('EB_NO_ROW_TO_DELETE'); ?>");
		}
	}
</script>