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
	$baseUri = JUri::base(true);
	$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

	$iconFolderClass    = $bootstrapHelper->getClassMapping('icon-folder-open');
	$iconMapMarkerClass = $bootstrapHelper->getClassMapping('icon-map-marker');
	$iconCalendarClass    = $bootstrapHelper->getClassMapping('icon-calendar');
	?>
	<ul class="ebm-upcoming-events ebm-upcoming-events-default">
	<?php
		foreach ($rows as $row)
		{
			$url = JRoute::_(EventbookingHelperRoute::getEventRoute($row->id, $row->main_category_id, $itemId));
		?>
                <li>
                    <?php
                        if ($titleLinkable)
                        {
                        ?>
                            <a href="<?php echo $url; ?>" class="ebm-event-link">
		                        <?php
		                        if ($showThumb && $row->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $row->thumb))
		                        {
			                        ?>
                                    <img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $row->thumb; ?>"
                                         class="ebm-event-thumb"/>
			                        <?php
		                        }

		                        echo $row->title;
		                        ?>
                            </a>
                        <?php
                        }
                        else
                        {
	                        if ($showThumb && $row->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $row->thumb))
	                        {
		                        ?>
                                <img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $row->thumb; ?>"
                                     class="ebm-event-thumb"/>
		                        <?php
	                        }

	                        echo $row->title;
                        }
                    ?>
                    <br/>
                    <span class="ebm-event-date">
                        <i class="<?php echo $iconCalendarClass; ?>"></i>
                        <?php
                            if ($row->event_date == '2099-12-31 00:00:00')
                            {
                                echo JText::_('EB_TBC');
                            }
                            else
                            {
                                echo JHtml::_('date', $row->event_date, $config->event_date_format, null);
                            }
                        ?>
						</span>
					<?php
					if ($showCategory)
					{
					?>
                        <br/>
                        <i class="<?php echo $iconFolderClass; ?>"></i>
                        <span class="ebm-event-categories"><?php echo $row->categories; ?></span>
					<?php
					}

					if ($showLocation && strlen($row->location_name))
					{
					?>
                        <br/>
                        <i class="<?php echo $iconMapMarkerClass; ?>"></i>
					<?php
						if ($row->location_address)
						{
						?>
                            <a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id=' . $row->location_id . '&tmpl=component&format=html&Itemid=' . $itemId); ?>"
                               class="eb-colorbox-map"><?php echo $row->location_name; ?></a>
						<?php
						}
						else
						{
							echo $row->location_name;
						}
					}

					if ($showPrice)
					{
					    $price = $row->price_text ?: EventbookingHelper::formatCurrency($row->individual_price, $config);
					?>
                        <br/>
						<?php echo '<strong>'.JText::_('EB_PRICE').'</strong>' . ': ' . $price; ?>
					<?php
					}
					?>
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
