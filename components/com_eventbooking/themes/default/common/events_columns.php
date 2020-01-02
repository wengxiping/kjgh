<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

$return     = base64_encode(JUri::getInstance()->toString());
$timeFormat = $config->event_time_format ?: 'g:i a';
$dateFormat = $config->date_format;

/* @var EventbookingHelperBootstrap $bootstrapHelper */
$rowFluidClass     = $bootstrapHelper->getClassMapping('row-fluid');
$btnClass          = $bootstrapHelper->getClassMapping('btn');
$btnInverseClass   = $bootstrapHelper->getClassMapping('btn-inverse');
$iconOkClass       = $bootstrapHelper->getClassMapping('icon-ok');
$iconRemoveClass   = $bootstrapHelper->getClassMapping('icon-remove');
$iconPencilClass   = $bootstrapHelper->getClassMapping('icon-pencil');
$iconDownloadClass = $bootstrapHelper->getClassMapping('icon-download');
$iconCalendarClass = $bootstrapHelper->getClassMapping('icon-calendar');
$iconMapMakerClass = $bootstrapHelper->getClassMapping('icon-map-marker');
$clearfixClass     = $bootstrapHelper->getClassMapping('clearfix');
$btnPrimaryClass   = $bootstrapHelper->getClassMapping('btn-primary');

$linkThumbToEvent   = $config->get('link_thumb_to_event_detail_page', 1);

$numberColumns = JFactory::getApplication()->getParams()->get('number_columns', 2);

if (!$numberColumns)
{
	$numberColumns = 2;
}

$baseUri      = JUri::base(true);
$span         = 'span' . intval(12 / $numberColumns);
$span         = $bootstrapHelper->getClassMapping($span);
$numberEvents = count($events);
$count        = 0;

if (!empty($category->id))
{
	$activeCategoryId = $category->id;
}
else
{
	$activeCategoryId = 0;
}

EventbookingHelperData::prepareDisplayData($events, $activeCategoryId, $config, $Itemid);
?>
<div id="eb-events" class="<?php echo $rowFluidClass . ' ' . $clearfixClass; ?>">
	<?php	    
		for ($i = 0 , $n = count($events) ;  $i < $n ; $i++)
		{
			$count++;
			$event = $events[$i];
		?>
			<div class="<?php echo $span; ?> eb-category-<?php echo $event->category_id; ?><?php if ($event->featured) echo ' eb-featured-event'; ?> eb-event-box eb-event-<?php echo $event->id; ?> clearfix">
				<h2 class="eb-event-title-container">
					<?php
					if ($config->hide_detail_button !== '1')
					{
					?>
						<a class="eb-event-title" href="<?php echo $event->url; ?>"><?php echo $event->title; ?></a>
					<?php
					}
					else
					{
						echo $event->title;
					}
					?>
				</h2>
				<?php
				if (!empty($event->thumb_url))
				{
					if ($linkThumbToEvent)
					{
					?>
						<div class="<?php echo $clearfixClass; ?>">
                            <a href="<?php echo $event->url; ?>"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
						</div>
					<?php
					}
					else
					{
					?>
                        <div class="<?php echo $clearfixClass; ?>">
                            <a href="<?php echo $event->image_url; ?>" class="eb-modal"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
                        </div>
					<?php
					}
				}
				?>
				<div class="eb-event-date-time <?php echo $clearfixClass; ?>">
					<i class="<?php echo $iconCalendarClass; ?>"></i>
					<?php
					if ($event->event_date != EB_TBC_DATE)
					{
						echo JHtml::_('date', $event->event_date, $dateFormat, null);
					}
					else
					{
						echo JText::_('EB_TBC');
					}

					if (strpos($event->event_date, '00:00:00') === false)
					{
					?>
						<span class="eb-time"><?php echo JHtml::_('date', $event->event_date, $timeFormat, null) ?></span>
					<?php
					}

					if ($event->event_end_date != $nullDate)
					{
						if (strpos($event->event_end_date, '00:00:00') === false)
						{
							$showTime = true;
						}
						else
						{
							$showTime = false;
						}

						$startDate =  JHtml::_('date', $event->event_date, 'Y-m-d', null);
						$endDate   = JHtml::_('date', $event->event_end_date, 'Y-m-d', null);

						if ($startDate == $endDate)
						{
							if ($showTime)
							{
							?>
								-<span class="eb-time"><?php echo JHtml::_('date', $event->event_end_date, $timeFormat, null) ?></span>
							<?php
							}
						}
						else
						{
							echo " - " .JHtml::_('date', $event->event_end_date, $dateFormat, null);

							if ($showTime)
							{
							?>
								<span class="eb-time"><?php echo JHtml::_('date', $event->event_end_date, $timeFormat, null) ?></span>
							<?php
							}
						}
					}
					?>
				</div>
				<div class="eb-event-location-price <?php echo $rowFluidClass . ' ' . $clearfixClass; ?>">
					<?php
					if ($event->location_id)
					{
					?>
						<div class="eb-event-location <?php echo $bootstrapHelper->getClassMapping('span9'); ?>">
							<i class="icon-location <?php echo $iconMapMakerClass; ?>"></i>
							<?php
							if ($event->location_address)
							{
							?>
								<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id.'&tmpl=component'); ?>" class="eb-colorbox-map"><span><?php echo $event->location_name ; ?></span></a>
							<?php
							}
							else
							{
								echo $event->location_name;
							}
							?>
						</div>
					<?php
					}

					if ($config->show_discounted_price)
					{
						$price = $event->discounted_price;
					}
					else
					{
						$price = $event->individual_price;
					}

					if ($event->price_text)
					{
						$priceDisplay = $event->price_text;
					}
					elseif ($price > 0)
					{
						$symbol        = $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;
						$priceDisplay  = EventbookingHelper::formatCurrency($price, $config, $symbol);
					}
					elseif ($config->show_price_for_free_event)
					{
						$priceDisplay = JText::_('EB_FREE');
					}
					else
					{
						$priceDisplay = '';
					}

					if ($priceDisplay)
					{
					?>
						<div class="eb-event-price <?php echo $btnPrimaryClass . ' ' . $bootstrapHelper->getClassMapping('span3'); ?> pull-right">
							<span class="eb-individual-price"><?php echo $priceDisplay; ?></span>
						</div>
					<?php
					}
					?>
				</div>
				<div class="eb-event-short-description <?php echo $clearfixClass; ?>">
					<?php echo $event->short_description; ?>
				</div>
				<?php
				    // Event message to tell user that they already registered, need to login to register or don't have permission to register...
				    echo EventbookingHelperHtml::loadCommonLayout('common/event_message.php', array('config' => $config, 'event' => $event));
				?>
				<div class="eb-taskbar <?php echo $clearfixClass; ?>">
					<ul>
						<?php
						if (!$event->is_multiple_date)
						{
							if ($event->can_register)
							{
								$registrationUrl = trim($event->registration_handle_url);

								if ($registrationUrl)
								{
								?>
									<li>
										<a class="<?php echo $btnClass; ?>" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo JText::_('EB_REGISTER');; ?></a>
									</li>
								<?php
								}
								else
								{
									if ($event->registration_type == 0 || $event->registration_type == 1)
									{
										if ($config->multiple_booking && !$event->has_multiple_ticket_types)
										{
											$url = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $event->id . '&Itemid=' . (int) $Itemid;

											if ($event->event_password)
											{
												$extraClass = '';
											}
											else
											{
												$extraClass = 'eb-colorbox-addcart';
											}

											$text = JText::_('EB_REGISTER');
										}
										else
										{
											$url = JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl);

											if ($event->has_multiple_ticket_types)
											{
												$text = JText::_('EB_REGISTER');
											}
											else
											{
												$text = JText::_('EB_REGISTER_INDIVIDUAL');
											}

											$extraClass = '';
										}
										?>
                                            <li>
                                                <a class="<?php echo $btnClass . ' ' . $extraClass; ?>" href="<?php echo $url; ?>"><?php echo $text; ?></a>
                                            </li>
										<?php
									}

									if (($event->registration_type == 0 || $event->registration_type == 2) && !$config->multiple_booking && !$event->has_multiple_ticket_types)
									{
									?>
										<li>
											<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo JText::_('EB_REGISTER_GROUP');; ?></a>
										</li>
									<?php
									}
								}
							}
							elseif ($event->waiting_list)
							{
								if ($event->registration_type == 0 || $event->registration_type == 1)
								{
								?>
									<li>
										<a class="<?php echo $btnClass; ?>"
										   href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo JText::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST');; ?></a>
									</li>
								<?php
								}

								if (($event->registration_type == 0 || $event->registration_type == 2) && !$config->multiple_booking)
								{
								?>
									<li>
										<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo JText::_('EB_REGISTER_GROUP_WAITING_LIST');; ?></a>
									</li>
								<?php
								}
							}
						}

						if ($config->hide_detail_button !== '1' || $event->is_multiple_date)
						{
						?>
							<li>
								<a class="<?php echo $btnClass . ' ' . $btnPrimaryClass; ?>" href="<?php echo $event->url; ?>">
									<?php echo $event->is_multiple_date ? JText::_('EB_CHOOSE_DATE_LOCATION') : JText::_('EB_DETAILS');?>
								</a>
							</li>
						<?php
						}
						?>
					</ul>
				</div>
			</div>
		<?php
			if ($count % $numberColumns == 0 && $count < $numberEvents)
			{
			?>
				</div>
				<div class="<?php echo $rowFluidClass . ' ' . $clearfixClass; ?>">
			<?php
			}
		}
	?>
</div>
<script type="text/javascript">
	function cancelRegistration(registrantId) {
		var form = document.adminForm ;
		if (confirm("<?php echo JText::_('EB_CANCEL_REGISTRATION_CONFIRM'); ?>")) {
			form.task.value = 'registrant.cancel' ;
			form.id.value = registrantId ;
			form.submit() ;
		}
	}
</script>

<?php
// Add Google Structured Data
JPluginHelper::importPlugin('eventbooking');
JFactory::getApplication()->triggerEvent('onDisplayEvents', [$events]);