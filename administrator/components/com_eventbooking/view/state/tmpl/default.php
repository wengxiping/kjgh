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

JHtml::_('formbehavior.chosen', 'select');
?>
<form action="index.php?option=com_eventbooking&view=state" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_COUNTRY_NAME'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['country_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_STATE_NAME'); ?>
		</div>
		<div class="controls">
			<input class="text_area" type="text" name="state_name" id="state_name" size="40" maxlength="250"
			       value="<?php echo $this->item->state_name; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_STATE_CODE_3'); ?>
		</div>
		<div class="controls">
			<input class="text_area" type="text" name="state_3_code" id="state_3_code" maxlength="250"
			       value="<?php echo $this->item->state_3_code; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_STATE_CODE_2'); ?>
		</div>
		<div class="controls">
			<input class="text_area" type="text" name="state_2_code" id="state_2_code" maxlength="250"
			       value="<?php echo $this->item->state_2_code; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('EB_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
</form>