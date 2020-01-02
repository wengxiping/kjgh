<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

$timeFormat        = $config->event_time_format ?: 'g:i a';
$dateFormat        = $config->date_format;
$rowFluidClass     = $bootstrapHelper->getClassMapping('row-fluid');
$span8Class        = $bootstrapHelper->getClassMapping('span8');
$span4Class        = $bootstrapHelper->getClassMapping('span4');
$btnClass          = $bootstrapHelper->getClassMapping('btn');
$btnInverseClass   = $bootstrapHelper->getClassMapping('btn-inverse');
$btnPrimaryClass   = $bootstrapHelper->getClassMapping('btn-primary');
$iconOkClass       = $bootstrapHelper->getClassMapping('icon-ok');
$iconRemoveClass   = $bootstrapHelper->getClassMapping('icon-remove');
$iconPencilClass   = $bootstrapHelper->getClassMapping('icon-pencil');
$iconDownloadClass = $bootstrapHelper->getClassMapping('icon-download');
$iconCalendarClass = $bootstrapHelper->getClassMapping('icon-calendar');
$iconMapMakerClass = $bootstrapHelper->getClassMapping('icon-map-marker');
$clearfixClass     = $bootstrapHelper->getClassMapping('clearfix');
$return = base64_encode(JUri::getInstance()->toString());
$baseUri = JUri::base(true);
$linkThumbToEvent   = $config->get('link_thumb_to_event_detail_page', 1);

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
<div id="eb-events" class="eb-events-timeline">
	<?php
		for ($i = 0 , $n = count($events) ;  $i < $n ; $i++)
		{
			$event = $events[$i];

			$layoutData = array(
				'item'              => $event,
				'config'            => $config,
				'isMultipleDate'    => $event->is_multiple_date,
				'canRegister'       => $event->can_register,
				'Itemid'            => $Itemid,
				'waitingList'       => $event->waiting_list,
				'ssl'               => $ssl,
				'btnClass'          => $btnClass,
				'iconOkClass'       => $iconOkClass,
				'iconRemoveClass'   => $iconRemoveClass,
				'iconDownloadClass' => $iconDownloadClass,
				'registrationOpen'  => $event->registration_open,
				'return'            => $return,
				'iconPencilClass'   => $iconPencilClass,
				'showInviteFriend'  => false,
			);

			$registerButtons = EventbookingHelperHtml::loadCommonLayout('common/buttons.php', $layoutData);
		?>
		<div class="eb-category-<?php echo $event->category_id; ?> eb-event-container<?php if ($event->featured) echo ' eb-featured-event'; ?>">
			<div class="eb-event-date-container">
				<div class="eb-event-date <?php echo $btnInverseClass; ?>">
					<?php
						if ($event->event_date != EB_TBC_DATE)
						{
						?>
							<div class="eb-event-date-day">
								<?php echo JHtml::_('date', $event->event_date, 'd', null); ?>
							</div>
							<div class="eb-event-date-month">
								<?php echo JHtml::_('date', $event->event_date, 'M', null); ?>
							</div>
							<div class="eb-event-date-year">
								<?php echo JHtml::_('date', $event->event_date, 'Y', null); ?>
							</div>
						<?php
						}
						else
						{
							echo JText::_('EB_TBC');
						}
					?>
				</div>
			</div>
			<h2 class="eb-even-title-container">
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
			<div class="eb-event-information <?php echo $rowFluidClass; ?>">
				<div class="<?php echo $span8Class; ?>">
					<div class="<?php echo $clearfixClass; ?>">
						<span class="eb-event-date-info">
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
						</span>
					</div>
					<?php
						if ($event->location_id)
						{
							$location = $event->location;
						?>
						<div class="<?php echo $clearfixClass; ?>">
							<i class="<?php echo $iconMapMakerClass; ?>"></i>
							<?php
								if ($event->location_address)
								{
									if ($location->image || EventbookingHelper::isValidMessage($location->description))
									{
									?>
										<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id.'&Itemid='.$Itemid); ?>"><span><?php echo $event->location_name ; ?></span></a>
									<?php
									}
									else
									{
									?>
										<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id.'&tmpl=component'); ?>" class="eb-colorbox-map"><span><?php echo $event->location_name ; ?></span></a>
									<?php
									}
								}
								else
								{
									echo $event->location_name;
								}
							?>
						</div>
						<?php
						}
					?>
				</div>
				<?php
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
					<div class="<?php echo $span4Class; ?>">
						<div class="eb-event-price-container <?php echo $btnPrimaryClass; ?>">
							<span class="eb-individual-price"><?php echo $priceDisplay; ?></span>
						</div>
					</div>
				<?php
				}
				?>
			</div>
			<?php
				if (in_array($config->get('register_buttons_position', 0), array(1,2)))
				{
				?>
					<div class="eb-taskbar eb-register-buttons-top <?php echo $clearfixClass; ?>">
						<ul>
							<?php
								echo $registerButtons;

								if ($config->hide_detail_button !== '1' || $event->is_multiple_date)
								{
								?>
									<li>
										<a class="<?php echo $btnClass; ?> btn-primary" href="<?php echo $event->url; ?>">
											<?php echo $event->is_multiple_date ? JText::_('EB_CHOOSE_DATE_LOCATION') : JText::_('EB_DETAILS'); ?>
										</a>
									</li>
								<?php
								}
							?>
						</ul>
					</div>
				<?php
				}
			?>
			<div class="eb-description-details <?php echo $clearfixClass; ?>">
				<?php
                    if (!empty($event->thumb_url))
                    {
                        if ($linkThumbToEvent)
                        {
                        ?>
                            <a href="<?php echo $event->url; ?>"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
                        <?php
                        }
                        else
                        {
                        ?>
                            <a href="<?php echo $event->image_url; ?>" class="eb-modal"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
                        <?php
                        }
                    }

                    echo $event->short_description;
				?>
			</div>
			<?php
				if ($config->display_ticket_types && !empty($event->ticketTypes))
				{
					echo EventbookingHelperHtml::loadCommonLayout('common/tickettypes.php', array('ticketTypes' => $event->ticketTypes, 'config' => $config, 'event' => $event));
				?>
					<div class="<?php echo $clearfixClass; ?>"></div>
				<?php
				}

				// Event message to tell user that they already registered, need to login to register or don't have permission to register...
			    echo EventbookingHelperHtml::loadCommonLayout('common/event_message.php', array('config' => $config, 'event' => $event));

				if (in_array($config->get('register_buttons_position', 0), array(0,2)))
				{
				?>
					<div class="eb-taskbar eb-register-buttons-bottom <?php echo $clearfixClass; ?>">
						<ul>
							<?php
							echo $registerButtons;

							if ($config->hide_detail_button !== '1' || $event->is_multiple_date)
							{
							?>
								<li>
									<a class="<?php echo $btnClass.' '. $btnPrimaryClass; ?>" href="<?php echo $event->url; ?>">
										<?php echo $event->is_multiple_date ? JText::_('EB_CHOOSE_DATE_LOCATION') : JText::_('EB_DETAILS'); ?>
									</a>
								</li>
							<?php
							}
							?>
						</ul>
					</div>
				<?php
				}
			?>
		</div>
		<?php
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