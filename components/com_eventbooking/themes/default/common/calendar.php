<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

if ($config->display_event_in_tooltip)
{
	JHtml::_('bootstrap.tooltip');
	JFactory::getDocument()->addStyleDeclaration(".hasTip{display:block !important}");
}

EventbookingHelperJquery::equalHeights();

$timeFormat = $config->event_time_format ? $config->event_time_format : 'g:i a';
$rootUri    = JUri::root(true);

$bootstrapHelper  = EventbookingHelperBootstrap::getInstance();
$angleDoubleLeft  = $bootstrapHelper->getClassMapping('icon-angle-double-left');
$angleDoubleRight = $bootstrapHelper->getClassMapping('icon-angle-double-right');;
$hiddenPhoneClass = $bootstrapHelper->getClassMapping('hidden-phone');
$clearFixClass    = $bootstrapHelper->getClassMapping('clearfix');

if ($bootstrapHelper->getBootstrapVersion() === 'uikit3')
{
    $hiddenPhoneClass = '';
}

$params = EventbookingHelper::getViewParams(JFactory::getApplication()->getMenu()->getActive(), array('calendar'));
?>
<div class="eb-calendar">
	<ul class="eb-month-browser regpro-calendarMonthHeader clearfix">
		<li class="eb-calendar-nav">
            <a href="<?php echo $previousMonthLink; ?>" rel="nofollow"><i class="fa fa-angle-double-left eb-calendar-navigation"></i></a>
		</li>
		<li id="eb-current-month">
			<?php echo $searchMonth; ?>
			<?php echo $searchYear; ?>
		</li>
		<li class="eb-calendar-nav">
            <a href="<?php echo $nextMonthLink ; ?>" rel="nofollow"><i class="fa fa-angle-double-right  eb-calendar-navigation"></i></a>
		</li>
	</ul>
	<ul class="eb-weekdays">
		<?php
		foreach ($data["daynames"] as $dayName)
		{
		?>
			<li class="eb-day-of-week regpro-calendarWeekDayHeader">
				<?php echo $dayName; ?>
			</li>
		<?php
		}
		?>
	</ul>
	<ul class="eb-days <?php echo $clearFixClass; ?>">
	<?php
		$eventIds = array();
		$dataCount = count($data["dates"]);
		$dn=0;

		for ($w=0; $w<6 && $dn < $dataCount; $w++)
		{
			$rowClass = 'eb-calendar-row-'.$w;

			for ($d=0; $d<7 && $dn < $dataCount; $d++)
			{
				$currentDay = $data["dates"][$dn];

                switch ($currentDay["monthType"])
				{
					case "prior":
					case "following":
					?>
						<li class="eb-calendarDay calendar-day regpro-calendarDay <?php echo $rowClass; if (empty($currentDay['events'])) echo ' '.$hiddenPhoneClass; ?>"></li>
					<?php
					break;
					case "current":
					?>
					<li class="eb-calendarDay calendar-day regpro-calendarDay <?php echo $rowClass; if (empty($currentDay['events'])) echo ' ' . $hiddenPhoneClass;?>">
						<div class="date day_cell"><span class="day"><?php echo $data["daynames"][$d] ?>,</span> <span class="month"><?php echo $listMonth[$month - 1]; ?></span> <?php echo $currentDay['d']; ?></div>
						<?php
						foreach ($currentDay["events"] as $key=> $event)
						{
							$mainCategory = EventbookingHelper::getEventMainCategory($event->id);
							$eventIds[] = $event->id;

							if ($config->show_thumb_in_calendar && $event->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $event->thumb))
							{
								$thumbSource = $rootUri . '/media/com_eventbooking/images/thumbs/' . $event->thumb;
							}
							else
							{
								$thumbSource = $rootUri . '/media/com_eventbooking/assets/images/calendar_event.png';
							}

							$eventId = $event->id;

							if ($config->show_children_events_under_parent_event && $event->parent_id > 0)
							{
								$eventId = $event->parent_id;
							}

							$eventClasses = [];

							if ($config->display_event_in_tooltip)
							{
								$eventClasses[] = 'eb_event_link hasTooltip hasTip';

								EventbookingHelper::callOverridableHelperMethod('Data', 'preProcessEventData', [[$event], 'list']);

								$layoutData = array(
									'item'     => $event,
									'config'   => $config,
									'nullDate' => JFactory::getDbo()->getNullDate(),
									'Itemid'   => $Itemid,
								);

								$eventProperties = EventbookingHelperHtml::loadCommonLayout('common/calendar_tooltip.php', $layoutData);
								$eventLinkTitle  = JHtml::tooltipText('', $eventProperties, false, true);
							}
							else
							{
								$eventClasses[] = 'eb_event_link';
								$eventLinkTitle = $event->title;
							}

							$eventInlineStyle = '';

							if ($mainCategory->text_color || $mainCategory->color_code)
							{
								$eventInlineStyle = ' style="';

								if ($mainCategory->text_color)
								{
									$eventInlineStyle .= 'color:#'.$mainCategory->text_color.';';
								}

								if ($mainCategory->color_code)
								{
									$eventInlineStyle .= 'background-color:#'.$mainCategory->color_code.';';
								}

								$eventInlineStyle .='"';
							}

							if ($event->event_capacity > 0 && $event->total_registrants >= $event->event_capacity)
                            {
                                $eventClasses[] = ' eb-event-full';
                            }

                            if ($params->get('link_event_to_registration_form') && EventbookingHelperRegistration::acceptRegistration($event))
                            {
                                if ($event->registration_handle_url)
                                {
                                    $url = $event->registration_handle_url;
                                }
                                else
                                {
	                                $url = JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid);
                                }
                            }
                            else
                            {
	                            $url = JRoute::_(EventbookingHelperRoute::getEventRoute($eventId, isset($categoryId) ? $categoryId : 0, $Itemid));
                            }
							?>
							<div class="date day_cell">
								<a class="<?php echo implode(' ', $eventClasses); ?>" href="<?php echo $url; ?>" title="<?php echo $eventLinkTitle; ?>"<?php if ($eventInlineStyle) echo $eventInlineStyle; ; ?>>
									<img border="0" align="top" title="<?php echo $event->title; ?>" src="<?php echo $thumbSource; ?>" />
									<?php
										if ($config->show_event_time && strpos($event->event_date, '00:00:00') === false)
										{
											echo $event->title.' ('.JHtml::_('date', $event->event_date, $timeFormat, null).')' ;
										}
										else
										{
											echo $event->title ;
										}
									?>
								</a>
							</div>
						<?php
						}
					echo "</li>\n";
					break;
				}
				$dn++;
			}
		}
	?>
	</ul>
</div>
<?php
	if ($config->show_calendar_legend && empty($categoryId))
	{
		$categories = EventbookingHelper::getCategories($eventIds);
	?>
		<div id="eb-calendar-legend" class="<?php echo $clearFixClass; ?>">
			<ul>
				<?php
					foreach ($categories as $category)
					{
					?>
						<li>
							<span class="eb-category-legend-color" style="background: #<?php echo $category->color_code; ?>"></span>
							<a href="<?php echo JRoute::_(EventbookingHelperRoute::getCategoryRoute($category->id, $Itemid)); ?>"><?php echo $category->name; ?></a>
						</li>
					<?php
					}
				?>
			</ul>
		</div>
	<?php
	}
?>
<script type="text/javascript">
        <?php
            if ($config->show_thumb_in_calendar)
            {
            ?>
                Eb.jQuery(window).load(function() {
                    <?php
                        for ($i = 0 ; $i < $w; $i++)
                        {
                        ?>
                            Eb.jQuery("ul.eb-days li.<?php echo 'eb-calendar-row-'.$i ?>").equalHeights(100);
                        <?php
                        }
                    ?>
                });
            <?php
            }
            else
            {
            ?>
                Eb.jQuery(document).ready(function($) {
                    <?php
                    for ($i = 0 ; $i < $w; $i++)
                    {
                    ?>
                        $("ul.eb-days li.<?php echo 'eb-calendar-row-'.$i ?>").equalHeights(100);
                    <?php
                    }
                    ?>
                });
            <?php
            }
        ?>
</script>