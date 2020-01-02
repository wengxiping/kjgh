<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$btnPrimary      = $bootstrapHelper->getClassMapping('btn btn-primary');
?>
<form name="eb-event-search" method="post" id="eb-event-search">
    <div class="filters btn-toolbar eb-search-bar-container clearfix">
        <div class="filter-search pull-left">
            <input type="text" name="search" class="input-large" value="<?php echo htmlspecialchars($search, ENT_COMPAT, 'UTF-8'); ?>"
                   placeholder="<?php echo JText::_('EB_KEY_WORDS'); ?>"/>
        </div>
        <div class="btn-group pull-left">
	        <?php
	        $locations = EventbookingHelperDatabase::getAllLocations();

	        if (count($locations) > 1)
	        {
		        $options   = [];
		        $options[] = JHtml::_('select.option', 0, JText::_('EB_ALL_LOCATIONS'), 'id', 'name');
		        $options   = array_merge($options, $locations);

		        echo JHtml::_('select.genericlist', $options, 'location_id', ' class="input-large" onchange="submit();" ', 'id', 'name', $locationId);
	        }

	        $options   = [];
	        $options[] = JHtml::_('select.option', '', JText::_('EB_ALL_DATES'));
	        $options[] = JHtml::_('select.option', 'today', JText::_('EB_TODAY'));
	        $options[] = JHtml::_('select.option', 'tomorrow', JText::_('EB_TOMORROW'));
	        $options[] = JHtml::_('select.option', 'this_week', JText::_('EB_THIS_WEEK'));
	        $options[] = JHtml::_('select.option', 'next_week', JText::_('EB_NEXT_WEEK'));
	        $options[] = JHtml::_('select.option', 'this_month', JText::_('EB_THIS_MONTH'));
	        $options[] = JHtml::_('select.option', 'next_month', JText::_('EB_NEXT_MONTH'));
	        echo JHtml::_('select.genericlist', $options, 'filter_duration', ' class="input-large" onchange="submit();" ', 'value', 'text', $filterDuration);
	        ?>
        </div>
        <div class="btn-group pull-left">
            <input type="submit" class="<?php echo $btnPrimary; ?> eb-btn-search" value="<?php echo JText::_('EB_SEARCH'); ?>"/>
        </div>
    </div>
</form>