<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
?>
<div class="filter-search btn-group pull-left">
    <label for="filter_search" class="element-invisible"><?php echo JText::_('EB_FILTER_SEARCH_REGISTRANTS_DESC');?></label>
    <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('EB_SEARCH_REGISTRANTS_DESC'); ?>" />
</div>
<div class="btn-group pull-left">
    <button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" onclick="document.getElementById('task').value=''; return true;"><span class="icon-search"></span></button>
    <button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value=''; document.getElementById('task').value=''; this.form.submit();"><span class="icon-remove"></span></button>
</div>
<div class="btn-group pull-left hidden-phone">
	<?php
	echo $this->lists['filter_event_id'] ;
	echo $this->lists['filter_published'];

	if ($this->config->activate_checkin_registrants)
	{
		echo $this->lists['filter_checked_in'];
	}
	?>
</div>
