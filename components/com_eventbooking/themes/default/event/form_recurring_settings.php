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

$dateFields = [
	'recurring_end_date',
];

foreach ($dateFields as $dateField)
{
	if ($this->item->{$dateField} == $this->nullDate)
	{
		$this->item->{$dateField} = '';
	}	
}
?>

    <div class="<?php echo $controlGroupClass; ?>">
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="recurring_type"><?php echo JText::_('EB_REPEAT_TYPE'); ?></label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo $this->lists['recurring_type']; ?>
        </div>
    </div>
<?php
$showOnData = array(
	'recurring_type' => array('1', '2', '3', '4')
);
?>
    <div class="<?php echo $controlGroupClass; ?>" data-showon='<?php echo EventbookingHelperHtml::renderShowOn($showOnData); ?>'>
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="recurring_frequency"><?php echo JText::_('EB_INTERVAL'); ?></label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="number" name="recurring_frequency" id="recurring_frequency" size="5" class="input-mini" value="<?php echo $this->item->recurring_frequency; ?>"/>
        </div>
    </div>
    <div class="<?php echo $controlGroupClass; ?>" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(['recurring_type' => '2']); ?>'>
        <div class="<?php echo $controlLabelClass; ?>">
            <strong><?php echo JText::_('EB_ON'); ?></strong>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php
			if (strlen($this->item->weekdays))
			{
				$weekDays   = explode(',', $this->item->weekdays);
			}
			else
			{
				$weekDays = [];
			}

			$daysOfWeek = array(0 => 'EB_SUN', 1 => 'EB_MON', 2 => 'EB_TUE', 3 => 'EB_WED', 4 => 'EB_THUR', 5 => 'EB_FRI', 6 => 'EB_SAT');

			foreach ($daysOfWeek as $key => $value)
			{
				?>
                <input type="checkbox" class="inputbox clearfloat"
                       value="<?php echo $key; ?>"
                       name="weekdays[]" <?php if (in_array($key, $weekDays)) echo ' checked'; ?> /> <?php echo JText::_($value); ?>&nbsp;&nbsp;
				<?php
			}
			?>
        </div>
    </div>
    <div class="<?php echo $controlGroupClass; ?>" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(['recurring_type' => '3']); ?>'>
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="monthdays"><?php echo JText::_('EB_ON'); ?></label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="monthdays" id="monthdays"
                   class="input-medium" size="10"
                   value="<?php echo $this->item->monthdays; ?>" /> <?php echo JText::_('EB_MONTH_DAYS_EXPLANATION'); ?>
        </div>
    </div>
    <div class="<?php echo $controlGroupClass; ?>" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(['recurring_type' => '4']); ?>'>
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="week_in_month"><?php echo JText::_('EB_ON'); ?></label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php
			$params     = new \Joomla\Registry\Registry($this->item->params);
			$options    = array();
			$options[]  = JHtml::_('select.option', 'first', JText::_('EB_FIRST'));
			$options[]  = JHtml::_('select.option', 'second', JText::_('EB_SECOND'));
			$options[]  = JHtml::_('select.option', 'third', JText::_('EB_THIRD'));
			$options[]  = JHtml::_('select.option', 'fourth', JText::_('EB_FOURTH'));
			$options[]  = JHtml::_('select.option', 'fifth', JText::_('EB_FIFTH'));
			$options[]  = JHtml::_('select.option', 'last', JText::_('EB_LAST'));

			$daysOfWeek = array(
				'Sun' => JText::_('EB_SUNDAY'),
				'Mon' => JText::_('EB_MONDAY'),
				'Tue' => JText::_('EB_TUESDAY'),
				'Wed' => JText::_('EB_WEDNESDAY'),
				'Thu' => JText::_('EB_THURSDAY'),
				'Fri' => JText::_('EB_FRIDAY'),
				'Sat' => JText::_('EB_SATURDAY')
			);

			echo JHtml::_('select.genericlist', $options, 'week_in_month', ' class="input-small" ', 'value', 'text', $params->get('week_in_month', 'first'));
			echo JHtml::_('select.genericlist', $daysOfWeek, 'day_of_week', ' class="input-small" ', 'value', 'text', $params->get('day_of_week', 'Sun'));
			?>
            of the month
        </div>
    </div>
    <div class="<?php echo $controlGroupClass; ?>" data-showon='<?php echo EventbookingHelperHtml::renderShowOn($showOnData); ?>'>
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="recurring_end_date"><?php echo JText::_('EB_REPEAT_UNTIL'); ?></label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo JHtml::_('calendar', $this->item->recurring_end_date, 'recurring_end_date', 'recurring_end_date', $this->datePickerFormat, array('class' => 'input-small')); ?>
        </div>
    </div>
    <div class="<?php echo $controlGroupClass; ?>" data-showon='<?php echo EventbookingHelperHtml::renderShowOn($showOnData); ?>'>
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="recurring_occurrencies"><?php echo JText::_('EB_REPEAT_COUNT'); ?></label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="number" name="recurring_occurrencies" size="5" class="input-small" value="<?php echo $this->item->recurring_occurrencies; ?>" />
        </div>
    </div>
<?php
if ($this->item->id)
{
?>
    <div class="<?php echo $controlGroupClass; ?>" data-showon='<?php echo EventbookingHelperHtml::renderShowOn($showOnData); ?>'>
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="update_children_event"><?php echo JText::_('EB_UPDATE_CHILD_EVENT'); ?></label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="checkbox" name="update_children_event" value="1"
                   class="checkbox" />
        </div>
    </div>
<?php
}