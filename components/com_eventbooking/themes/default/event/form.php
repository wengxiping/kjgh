<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.tabstate');
JHtml::_('behavior.modal');
JHtml::_('jquery.framework');
JHtml::_('script', 'jui/cms.js', false, true);

$editor = JEditor::getInstance(JFactory::getConfig()->get('editor', 'none'));
$bootstrapHelper   = EventbookingHelperBootstrap::getInstance();
$rowFluidClass     = $bootstrapHelper->getClassMapping('row-fluid');
$btnPrimary        = $bootstrapHelper->getClassMapping('btn btn-primary');
?>
<script type="text/javascript">
	function checkData(pressbutton)
	{
		var form = document.adminForm;

		if (pressbutton == 'event.cancel')
		{
			Joomla.submitform( pressbutton );
		}
		else
		{
			// Check event title
			if (form.title.value == '')
			{
				alert("<?php echo JText::_('EB_PLEASE_ENTER_TITLE'); ?>");
				form.title.focus();
				return ;
			}

			// Check event date
			if (form.event_date.value == '') {
				alert("<?php echo JText::_('EB_ENTER_EVENT_DATE'); ?>");
				form.event_date.focus();
				return ;
			}

			// Force user to select at least one category
			if (form.main_category_id.value == 0)
			{
				alert("<?php echo JText::_("EB_CHOOSE_CATEGORY");  ?>");
				return ;
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
            ?>
			Joomla.submitform( pressbutton );
		}
	}
</script>
<div class="eb_form_header" style="width:100%;">
	<div style="float: left; width: 40%;"><?php echo $this->escape(JText::_('EB_ADD_EDIT_EVENT')); ?></div>
	<div style="float: right; width: 50%; text-align: right;">
		<input type="button" name="btnSave" value="<?php echo JText::_('EB_SAVE'); ?>" onclick="checkData('event.save');" class="<?php echo $btnPrimary; ?>" />
		<input type="button" name="btnSave" value="<?php echo JText::_('EB_CANCEL_EVENT'); ?>" onclick="checkData('event.cancel');" class="<?php echo $btnPrimary; ?>" />
	</div>
</div>
<div class="clearfix"></div>
<form action="<?php echo JRoute::_('index.php?Itemid='.$this->Itemid); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
<div class="<?php echo $rowFluidClass; ?> eb-container">
	<?php
		echo JHtml::_('bootstrap.startTabSet', 'event', array('active' => 'basic-information-page'));
		echo JHtml::_('bootstrap.addTab', 'event', 'basic-information-page', JText::_('EB_BASIC_INFORMATION', true));
		echo $this->loadTemplate('general', array('editor' => $editor));
		echo JHtml::_('bootstrap.endTab');

		if ($this->config->activate_recurring_event && (!$this->item->id || $this->item->event_type == 1))
		{
			echo JHtml::_('bootstrap.addTab', 'event', 'recurring-settings-page', JText::_('EB_RECURRING_SETTINGS', true));
			echo $this->loadTemplate('recurring_settings');
			echo JHtml::_('bootstrap.endTab');
		}

		echo JHtml::_('bootstrap.addTab', 'event', 'group-registration-rates-page', JText::_('EB_GROUP_REGISTRATION_RATES', true));
		echo $this->loadTemplate('group_rates');
		echo JHtml::_('bootstrap.endTab');

		echo JHtml::_('bootstrap.addTab', 'event', 'misc-page', JText::_('EB_MISC', true));
		echo $this->loadTemplate('misc');
		echo JHtml::_('bootstrap.endTab');

		echo JHtml::_('bootstrap.addTab', 'event', 'discount-page', JText::_('EB_DISCOUNT_SETTING', true));
		echo $this->loadTemplate('discount_settings');
		echo JHtml::_('bootstrap.endTab');

		if ($this->config->event_custom_field)
		{
			echo JHtml::_('bootstrap.addTab', 'event', 'fields-page', JText::_('EB_EXTRA_INFORMATION', true));
			echo $this->loadTemplate('fields');
			echo JHtml::_('bootstrap.endTab');
		}

        if ($this->isMultilingual)
        {
            echo $this->loadTemplate('translation', ['editor' => $editor]);
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

        echo JHtml::_('bootstrap.endTabSet');
	?>
</div>
	<input type="hidden" name="option" value="com_eventbooking" />
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
    <input type="hidden" name="activate_tickets_pdf" value="<?php echo $this->item->activate_tickets_pdf; ?>"/>
    <input type="hidden" name="send_tickets_via_email" value="<?php echo $this->item->send_tickets_via_email; ?>"/>
	<?php echo JHtml::_( 'form.token' ); ?>
	<script type="text/javascript">
		function addRow() {
			var table = document.getElementById('price_list');
			var newRowIndex = table.rows.length - 1 ;
			var row = table.insertRow(newRowIndex);
			var registrantNumber = row.insertCell(0);
			var price = row.insertCell(1);
			registrantNumber.innerHTML = '<input type="text" class="inputbox" name="registrant_number[]" size="10" />';
			price.innerHTML = '<input type="text" class="inputbox" name="price[]" size="10" />';

		}
		function removeRow() {
			var table = document.getElementById('price_list');
			var deletedRowIndex = table.rows.length - 2 ;
			if (deletedRowIndex >= 1) {
				table.deleteRow(deletedRowIndex);
			} else {
				alert("<?php echo JText::_('EB_NO_ROW_TO_DELETE'); ?>");
			}
		}

		function setDefaultData() {
			var form = document.adminForm ;
			if (form.recurring_type[1].checked) {
				if (form.number_days.value == '') {
					form.number_days.value =1 ;
				}
			} else if (form.recurring_type[2].checked) {
				if (form.number_weeks.value == '') {
					form.number_weeks.value = 1 ;
				}
			} else if (form.recurring_type[3].checked) {
				if (form.number_months.value == '') {
					form.number_months.value = 1 ;
				}
			}
		}
	</script>
</form>