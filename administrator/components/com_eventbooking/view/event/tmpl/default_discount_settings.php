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
	'early_bird_discount_date',
	'late_fee_date',
];

foreach ($dateTimeFields as $dateField)
{
	if ($this->item->{$dateField} == $this->nullDate)
	{
		$this->item->{$dateField} = '';
	}
}
?>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo JText::_('EB_MEMBER_DISCOUNT_GROUPS'); ?>::<?php echo JText::_('EB_MEMBER_DISCOUNT_GROUPS_EXPLAIN'); ?>"><?php echo JText::_('EB_MEMBER_DISCOUNT_GROUPS'); ?></span>
	</div>
	<div class="controls">
		<?php echo $this->lists['discount_groups']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo JText::_('EB_MEMBER_DISCOUNT'); ?>::<?php echo JText::_('EB_MEMBER_DISCOUNT_EXPLAIN'); ?>"><?php echo JText::_('EB_MEMBER_DISCOUNT'); ?></span>
	</div>
	<div class="controls">
		<input type="text" name="discount_amounts" id="discount_amounts" class="input-mini" size="5"
		       value="<?php echo $this->item->discount_amounts; ?>" />&nbsp;&nbsp;<?php echo $this->lists['discount_type']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo JText::_('EB_EARLY_BIRD_DISCOUNT'); ?>::<?php echo JText::_('EB_EARLY_BIRD_DISCOUNT_EXPLAIN'); ?>"><?php echo JText::_('EB_EARLY_BIRD_DISCOUNT'); ?></span>
	</div>
	<div class="controls">
		<input type="number" step="0.01" name="early_bird_discount_amount" id="early_bird_discount_amount" class="input-mini"
		       size="5"
		       value="<?php echo $this->item->early_bird_discount_amount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['early_bird_discount_type']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo JText::_('EB_EARLY_BIRD_DISCOUNT_DATE'); ?>::<?php echo JText::_('EB_EARLY_BIRD_DISCOUNT_DATE_EXPLAIN'); ?>"><?php echo JText::_('EB_EARLY_BIRD_DISCOUNT_DATE'); ?></span>
	</div>
	<div class="controls">
		<?php echo JHtml::_('calendar', $this->item->early_bird_discount_date, 'early_bird_discount_date', 'early_bird_discount_date', $this->datePickerFormat . ' %H:%M:%S', ['class' => 'input-medium']); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo JText::_('EB_LATE_FEE'); ?>::<?php echo JText::_('EB_LATE_FEE_EXPLAIN'); ?>"><?php echo JText::_('EB_LATE_FEE'); ?></span>
	</div>
	<div class="controls">
		<input type="number" step="0.01" name="late_fee_amount" id="late_fee_amount" class="input-mini" size="5"
		       value="<?php echo $this->item->late_fee_amount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['late_fee_type']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo JText::_('EB_LATE_FEE_DATE'); ?>::<?php echo JText::_('EB_LATE_FEE_DATE_EXPLAIN'); ?>"><?php echo JText::_('EB_LATE_FEE_DATE'); ?></span>
	</div>
	<div class="controls">
		<?php echo JHtml::_('calendar', $this->item->late_fee_date, 'late_fee_date', 'late_fee_date', $this->datePickerFormat . ' %H:%M:%S', ['class' => 'input-medium']); ?>
	</div>
</div>
