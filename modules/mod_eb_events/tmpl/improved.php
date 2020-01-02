<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

if (count($rows))
{
	$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

	$rowFluidClass      = $bootstrapHelper->getClassMapping('row-fluid');
	$span3Class         = $bootstrapHelper->getClassMapping('span3');
	$span9Class         = $bootstrapHelper->getClassMapping('span9');
	$iconFolderClass    = $bootstrapHelper->getClassMapping('icon-folder-open');
	$iconMapMarkerClass = $bootstrapHelper->getClassMapping('icon-map-marker');

    $monthNames = array(
        1 => JText::_('EB_JAN_SHORT'),
        2 => JText::_('EB_FEB_SHORT'),
        3 => JText::_('EB_MARCH_SHORT'),
        4 => JText::_('EB_APR_SHORT'),
        5 => JText::_('EB_MAY_SHORT'),
        6 => JText::_('EB_JUNE_SHORT'),
        7 => JText::_('EB_JULY_SHORT'),
        8 => JText::_('EB_AUG_SHORT'),
        9 => JText::_('EB_SEP_SHORT'),
        10 => JText::_('EB_OCT_SHORT'),
        11 => JText::_('EB_NOV_SHORT'),
        12 => JText::_('EB_DEC_SHORT')
    );
?>
    <ul class="ebm-upcoming-events ebm-upcoming-events-improved">
        <?php
        $k = 0 ;
        $baseUri = JUri::base(true);

        foreach ($rows as  $row)
        {
            $k = 1 - $k ;
            $date = JHtml::_('date', $row->event_date, 'd', null);
            $month = JHtml::_('date', $row->event_date, 'n', null);
        ?>
            <li class="<?php echo $rowFluidClass; ?>">
                <div class="<?php echo $span3Class; ?>">
                    <div class="ebm-event-date">
                        <?php
                            if ($row->event_date == '2099-12-31 00:00:00')
                            {
                                echo JText::_('EB_TBC');
                            }
                            else
                            {
                            ?>
                                <div class="ebm-event-month"><?php echo $monthNames[$month];?></div>
                                <div class="ebm-event-day"><?php echo $date; ?></div>
                            <?php
                            }
                        ?>
                    </div>
                </div>
                <div class="<?php echo $span9Class; ?>">
                    <?php
                        if ($titleLinkable)
                        {
                        ?>
                            <a class="url ebm-event-link" href="<?php echo JRoute::_(EventbookingHelperRoute::getEventRoute($row->id, $row->main_category_id, $itemId), false); ?>">
		                        <?php
		                        if ($showThumb && $row->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$row->thumb))
		                        {
			                    ?>
                                    <img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $row->thumb; ?>" class="ebm-event-thumb" />
			                    <?php
		                        }

		                        echo $row->title;
		                        ?>
                            </a>
                        <?php
                        }
                        else
                        {
                            if ($showThumb && $row->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$row->thumb))
                            {
                            ?>
                                <img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $row->thumb; ?>" class="ebm-event-thumb" />
                            <?php
                            }

                            echo $row->title;
                        }

                        if ($showCategory)
                        {
                        ?>
                            <br />
                            <i class="<?php echo $iconFolderClass; ?>"></i>
                            <span class="ebm-event-categories"><?php echo $row->categories ; ?></span>
                        <?php
                        }

                        if ($showLocation && strlen($row->location_name))
                        {
                        ?>
                            <br />
                            <i class="<?php echo $iconMapMarkerClass; ?>"></i>
                            <?php
                            if ($row->location_address)
                            {
                            ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$row->location_id.'&tmpl=component&format=html&Itemid='.$itemId); ?>" class="eb-colorbox-map">
                                    <?php echo $row->location_name ; ?>
                                </a>
                            <?php
                            }
                            else
                            {
                            ?>
                                <span class="ebm-location-name"><?php echo $row->location_name; ?></span>
                            <?php
                            }
                        }

                        if ($showPrice)
                        {
	                        $price = $row->price_text ?: EventbookingHelper::formatCurrency($row->individual_price, $config);
                        ?>
                            <br/>
                            <?php echo '<strong>'.JText::_('EB_PRICE').'</strong>' . ': ' . EventbookingHelper::formatCurrency($row->individual_price, $config); ?>
                        <?php
                        }
                        ?>
                </div>
            </li>
        <?php
        }
        ?>
    </ul>
<?php
}
else
{
?>
    <div class="eb_empty"><?php echo JText::_('EB_NO_UPCOMING_EVENTS') ?></div>
<?php
}