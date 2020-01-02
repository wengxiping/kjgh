<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/events_table.css");

$hiddenPhoneClass    = $bootstrapHelper->getClassMapping('hidden-phone');
$btnClass            = $bootstrapHelper->getClassMapping('btn');
$btnPrimary          = $bootstrapHelper->getClassMapping('btn btn-primary');
$baseUri             = JUri::base(true);
$cols                = 3;
$linkThumbToEvent    = $config->get('link_thumb_to_event_detail_page', 1);
$showAddEventsButton = false;

if (!empty($category->id))
{
	$activeCategoryId = $category->id;
}
else
{
	$activeCategoryId = 0;
}

EventbookingHelperData::prepareDisplayData($items, $activeCategoryId, $config, $Itemid);
?>
<table style='display: none;' class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-condensed eb-responsive-table">
	<thead class='table-head'>
		<tr>
			<th>序号</th>
		<?php
			if ($config->show_image_in_table_layout)
			{
			    $cols++;
			?>
				<th class="<?php echo $hiddenPhoneClass; ?>">
					<?php echo JText::_('EB_EVENT_IMAGE'); ?>
				</th>
			<?php
			}
		?>
		<th>
			<?php echo JText::_('EB_EVENT_TITLE'); ?>
		</th>
		<th class="date_col">
			<?php echo JText::_('EB_EVENT_DATE'); ?>
		</th>
		<?php
			if ($config->show_event_end_date_in_table_layout)
			{
				$cols++;
			?>
				<th class="date_col">
					<?php echo JText::_('EB_EVENT_END_DATE'); ?>
				</th>
			<?php
			}

			if ($config->show_location_in_category_view)
			{
				$cols++;
			?>
				<th class="location_col">
					<?php echo JText::_('EB_LOCATION'); ?>
				</th>
			<?php
			}

			if ($config->show_price_in_table_layout)
			{
				$cols++;
			?>
				<th class="table_price_col">
					<?php echo JText::_('EB_INDIVIDUAL_PRICE'); ?>
				</th>
			<?php
			}

			if ($config->show_capacity)
			{
				$cols++;
			?>
				<th class="capacity_col">
					<?php echo JText::_('EB_CAPACITY'); ?>
				</th>
			<?php
			}

			if ($config->show_registered)
			{
				$cols++;
			?>
				<th class="registered_col">
					<?php echo JText::_('EB_REGISTERED'); ?>
				</th>
			<?php
			}

			if ($config->show_available_place)
			{
				$cols++;
			?>
				<th class="center available-place-col">
					<?php echo JText::_('EB_AVAILABLE_PLACE'); ?>
				</th>
			<?php
			}
			?>
			<th class="center actions-col">
				<?php echo JText::_('EB_REGISTER'); ?>
			</th>
		</tr>
	</thead>
	<tbody class='table-body'>
	<?php
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item = $items[$i];
		?>
			<tr class="eb-category-<?php echo $item->category_id; ?><?php if ($item->featured) echo ' eb-featured-event'; ?>">
				<td>1</td>
				<?php
					if ($config->show_image_in_table_layout)
					{
					?>
						<td class="eb-image-column <?php echo $hiddenPhoneClass; ?>">
						<?php
						    if (!empty($item->thumb_url))
							{
								if ($linkThumbToEvent)
								{
								?>
                                    <a href="<?php echo $item->url; ?>"><img src="<?php echo $item->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $item->title; ?>"/></a>
								<?php
								}
								else
								{
								?>
                                    <a href="<?php echo $item->image_url; ?>" class="eb-modal"><img src="<?php echo $item->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $item->title; ?>"/></a>
								<?php
								}
							}
							else
							{
								echo ' ';
							}
						?>
					</td>
					<?php
					}
				?>
				<td class="tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_EVENT_TITLE'); ?>">
					<?php
						if ($config->hide_detail_button !== '1')
						{
						?>
							<a href="<?php echo $item->url;?>" class="eb-event-link"><?php echo $item->title ; ?></a>
						<?php
						}
						else
						{
							echo $item->title;
						}
					?>
				</td>
				<td class="tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_EVENT_DATE'); ?>">
					<?php
						if ($item->event_date == EB_TBC_DATE)
						{
							echo JText::_('EB_TBC');
						}
						elseif($item->event_date != $nullDate)
						{
							if (strpos($item->event_date, '00:00:00') !== false)
							{
								$dateFormat = $config->date_format;
							}
							else
							{
								$dateFormat = $config->event_date_format;
							}

							echo JHtml::_('date', $item->event_date, $dateFormat, null);
						}
					?>
				</td>
				<?php
					if ($config->show_event_end_date_in_table_layout)
					{
					?>
						<td class="tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_EVENT_END_DATE'); ?>">
							<?php
								if ($item->event_end_date == EB_TBC_DATE)
								{
									echo JText::_('EB_TBC');
								}
								elseif($item->event_end_date != $nullDate)
								{
									if (strpos($item->event_end_date, '00:00:00') !== false)
									{
										$dateFormat = $config->date_format;
									}
									else
									{
										$dateFormat = $config->event_date_format;
									}

									echo JHtml::_('date', $item->event_end_date, $dateFormat, null);
								}
							?>
						</td>
					<?php
					}

					if ($config->show_location_in_category_view)
					{
					?>
					<td class="tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_LOCATION'); ?>">
						<?php
							if ($item->location_id)
							{
								if ($item->location_address)
								{
									$location = $item->location;

									if ($location->image || EventbookingHelper::isValidMessage($location->description))
									{
									?>
										<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$item->location_id.'&Itemid='.$Itemid); ?>"><?php echo $item->location_name ; ?></a>
									<?php
									}
									else
									{
									?>
										<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$item->location_id.'&Itemid='.$Itemid.'&tmpl=component'); ?>" class="eb-colorbox-map"><?php echo $item->location_name ; ?></a>
									<?php
									}
								}
								else
								{
									echo $item->location_name;
								}
							}
							else
							{
								echo ' ';
							}
						?>
					</td>
					<?php
					}

					if ($config->show_price_in_table_layout)
					{
						if ($item->price_text)
						{
							$price = $item->price_text;
						}
						elseif ($config->show_discounted_price)
						{
							$price = EventbookingHelper::formatCurrency($item->discounted_price, $config, $item->currency_symbol);
						}
						else
						{
							$price = EventbookingHelper::formatCurrency($item->individual_price, $config, $item->currency_symbol);
						}
					?>
						<td class="tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_INDIVIDUAL_PRICE'); ?>">
							<?php echo $price; ?>
						</td>
					<?php
					}

					if ($config->show_capacity)
					{
					?>
						<td class="center tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_CAPACITY'); ?>">
							<?php
								if ($item->event_capacity)
								{
									echo $item->event_capacity ;
								}
								elseif ($config->show_capacity != 2)
								{
									echo JText::_('EB_UNLIMITED') ;
								}
							?>
						</td>
					<?php
					}

					if ($config->show_registered)
					{
					?>
						<td class="center tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_REGISTERED'); ?>">
							<?php
								if ($item->registration_type != 3)
								{
									echo $item->total_registrants ;
								}
								else
								{
									echo ' ';
								}

							?>
						</td>
					<?php
					}

					if ($config->show_available_place)
					{
					?>
						<td class="center tdno<?php echo $i; ?>" data-content="<?php echo JText::_('EB_AVAILABLE_PLACE'); ?>">
							<?php
								if ($item->event_capacity)
								{
									echo $item->event_capacity - $item->total_registrants;
								}
							?>
						</td>
					<?php
					}
				?>
				<td class="center">
					<?php
						if (!$item->is_multiple_date && ($item->waiting_list || $item->can_register || ($item->registration_type != 3 && $config->display_message_for_full_event)))
						{
							if ($item->can_register)
							{
							?>
							<div class="eb-taskbar">
								<ul>
									<?php
										$registrationUrl = trim($item->registration_handle_url);

										if ($registrationUrl)
										{
										?>
											<li>
												<a class="<?php echo $btnClass.' eb-register-button eb-external-registration-link'; ?> a-btn" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo JText::_('EB_REGISTER');; ?></a>
											</li>
										<?php
										}
										elseif ($config->multiple_booking && $config->enable_add_multiple_events_to_cart)
                                        {
                                            $showAddEventsButton = true;
                                        ?>
                                            <input type="checkbox" class="checkbox eb-event-checkbox" name="event_ids[]" value="<?php echo $item->id ?>" />
                                        <?php
                                        }
										else
										{
											if ($item->registration_type == 0 || $item->registration_type == 1)
											{
												$cssClasses = [$btnClass, 'eb-register-button'];

												if ($config->multiple_booking && !$item->has_multiple_ticket_types)
												{
													$url        = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $item->id . '&Itemid=' . (int) $Itemid;

													if (!$item->event_password)
													{
														$cssClasses[] = 'eb-colorbox-addcart';
													}

													$text       = JText::_('EB_REGISTER');
												}
												else
												{
													$url        = JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl);
													$cssClasses[] = 'eb-individual-registration-button';

													if ($item->has_multiple_ticket_types)
													{
														$text       = JText::_('EB_REGISTER');
													}
													else
													{
														$text       = JText::_('EB_REGISTER_INDIVIDUAL');
													}

													$extraClass = '';
												}
												?>
												<li>
                                                    <a class="<?php echo implode(' ', $cssClasses);?>"
                                                       href="<?php echo $url; ?>"><?php echo $text; ?></a>
												</li>
											<?php
											}

											if ($item->min_group_number > 0)
											{
												$minGroupNumber = $item->min_group_number;
											}
											else
											{
												$minGroupNumber = 2;
											}

											if ($item->event_capacity > 0 && (($item->event_capacity - $item->total_registrants) < $minGroupNumber))
											{
												$groupRegistrationAvailable = false;
											}
											else
											{
												$groupRegistrationAvailable = true;
											}

											if ($groupRegistrationAvailable && ($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking && !$item->has_multiple_ticket_types)
											{
												$cssClasses = [$btnClass, 'eb-register-button', 'eb-group-registration-button'];
											?>
												<li>
													<a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP');; ?></a>
												</li>
											<?php
											}
										}
									?>
								</ul>
							</div>
							<?php
							}
							elseif ($item->registration_start_date != $nullDate && $item->registration_start_minutes < 0)
							{
								if (strpos($item->registration_start_date, '00:00:00') !== false)
								{
									$dateFormat = $config->date_format;
								}
								else
								{
									$dateFormat = $config->event_date_format;
								}

								echo JText::sprintf('EB_REGISTRATION_STARTED_ON', JHtml::_('date', $item->registration_start_date, $dateFormat, null));
							}
							elseif($item->waiting_list && $item->registration_type != 3)
							{
							?>
							<div class="eb-taskbar">
								<ul>
									<?php
									if ($item->registration_type == 0 || $item->registration_type == 1)
									{
										$cssClasses = [$btnClass, 'eb-register-button', 'eb-join-waiting-list-individual-button'];
									?>
										<li>
											<a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl);?>"><?php echo JText::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST'); ; ?></a>
										</li>
									<?php
									}

									if (($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking)
									{
										$cssClasses = [$btnClass, 'eb-register-button', 'eb-join-waiting-list-group-button'];
									?>
										<li>
											<a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP_WAITING_LIST'); ; ?></a>
										</li>
									<?php
									}
									?>
								</ul>
							</div>
							<?php
							}
							elseif($item->registration_type != 3 && $config->display_message_for_full_event && !$item->waiting_list && $item->registration_start_minutes >= 0)
							{
								// Event message to tell user that they already registered, need to login to register or don't have permission to register...
								echo EventbookingHelperHtml::loadCommonLayout('common/event_message.php', array('config' => $config, 'event' => $item));
							}
						}

						if ($item->is_multiple_date)
						{
						?>
							<div class="eb-taskbar">
                                <ul>
                                    <li>
                                        <a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_(EventbookingHelperRoute::getEventRoute($item->id, $categoryId, $Itemid));?>"><?php echo JText::_('EB_CHOOSE_DATE_LOCATION'); ; ?></a>
                                    </li>
                                </ul>
							</div>
						<?php
						}
					?>
				</td>
			</tr>
		<?php
		}

		if ($showAddEventsButton)
        {
        ?>
            <tr>
                <td colspan="<?php echo $cols ?>" style="text-align: right;"><input type="button" class="<?php echo $btnPrimary; ?>" onclick="addSelectedEventsToCart();" value="<?php echo JText::_('EB_ADD_EVENTS_TO_CART'); ?>" /></td>
            </tr>
        <?php
        }
	?>
	</tbody>
</table>
<?php
for ($i = 0, $n = count($items); $i < $n; $i++)
{
    $item = $items[$i];
    ?>
<div class='pc-container'>
	<div class='row activity-container-div'>
		<div class='col-md-2 col-lg-2 col-sm-12 col-xs-12 row-content'>
			<img src="components/com_jblance/images/activity-test.jpg" alt="" style='height:100%;width:100%;'>
		</div>
		<div class='col-md-10 col-lg-10 col-sm-12 col-xs-12 row-content row-content-desc'>
			<p class='activity-title-p'><a href="<?php echo $item->url;?>" class="eb-event-link"><?php echo $item->title ; ?></a></p>
			<p class='activity-time-p'>活动时间：
                <?php
                if ($item->event_date == EB_TBC_DATE)
                {
                    echo JText::_('EB_TBC');
                }
                elseif($item->event_date != $nullDate)
                {
                    if (strpos($item->event_date, '00:00:00') !== false)
                    {
                        $dateFormat = $config->date_format;
                    }
                    else
                    {
                        $dateFormat = $config->event_date_format;
                    }

                    echo JHtml::_('date', $item->event_date, $dateFormat, null);
                }
                ?>
            </p>
			<div class='foot-content'>
				<div>
					<div class='clear'>
						<ul class='activity-quantity-ul clear'>
							<li><?php echo $item->event_capacity;?></li>
							<li><?php echo $item->total_registrants;?></li>
							<li><?php echo $item->event_capacity - $item->total_registrants;?></li>
						</ul>
					</div>
					<div class='clear'>
						<ul class='activity-quantity-name clear'>
								<li>总名额</li>
								<li>报名人数</li>
								<li>剩余名额</li>
						</ul>
					</div>
				</div>
				<div class='btn-container'>
                        <?php
                        if (!$item->is_multiple_date && ($item->waiting_list || $item->can_register || ($item->registration_type != 3 && $config->display_message_for_full_event)))
                        {
                            if ($item->can_register)
                            {
                                ?>
                                        <?php
                                        $registrationUrl = trim($item->registration_handle_url);

                                        if ($registrationUrl)
                                        {
                                            ?>
                                            <button>
                                                <a class="<?php echo $btnClass.' eb-register-button eb-external-registration-link'; ?> a-btn" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo JText::_('EB_REGISTER');; ?></a>
                                            </button>
                                            <?php
                                        }
                                        elseif ($config->multiple_booking && $config->enable_add_multiple_events_to_cart)
                                        {
                                            $showAddEventsButton = true;
                                            ?>
                                            <input type="checkbox" class="checkbox eb-event-checkbox" name="event_ids[]" value="<?php echo $item->id ?>" />
                                            <?php
                                        }
                                        else
                                        {
                                            if ($item->registration_type == 0 || $item->registration_type == 1)
                                            {
                                                $cssClasses = [$btnClass, 'eb-register-button'];

                                                if ($config->multiple_booking && !$item->has_multiple_ticket_types)
                                                {
                                                    $url        = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $item->id . '&Itemid=' . (int) $Itemid;

                                                    if (!$item->event_password)
                                                    {
                                                        $cssClasses[] = 'eb-colorbox-addcart';
                                                    }

                                                    $text       = JText::_('EB_REGISTER');
                                                }
                                                else
                                                {
                                                    $url        = JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl);
                                                    $cssClasses[] = 'eb-individual-registration-button';

                                                    if ($item->has_multiple_ticket_types)
                                                    {
                                                        $text       = JText::_('EB_REGISTER');
                                                    }
                                                    else
                                                    {
                                                        $text       = JText::_('EB_REGISTER_INDIVIDUAL');
                                                    }

                                                    $extraClass = '';
                                                }
                                                ?>
                                                <button>
                                                    <a class="<?php echo implode(' ', $cssClasses);?>"
                                                       href="<?php echo $url; ?>"><?php echo $text; ?></a>
                                                </button>
                                                <?php
                                            }

                                            if ($item->min_group_number > 0)
                                            {
                                                $minGroupNumber = $item->min_group_number;
                                            }
                                            else
                                            {
                                                $minGroupNumber = 2;
                                            }

                                            if ($item->event_capacity > 0 && (($item->event_capacity - $item->total_registrants) < $minGroupNumber))
                                            {
                                                $groupRegistrationAvailable = false;
                                            }
                                            else
                                            {
                                                $groupRegistrationAvailable = true;
                                            }

                                            if ($groupRegistrationAvailable && ($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking && !$item->has_multiple_ticket_types)
                                            {
                                                $cssClasses = [$btnClass, 'eb-register-button', 'eb-group-registration-button'];
                                                ?>
                                                <button>
                                                    <a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP');; ?></a>
                                                </button>
                                                <?php
                                            }
                                        }
                                        ?>
                                <?php
                            }
                            elseif ($item->registration_start_date != $nullDate && $item->registration_start_minutes < 0)
                            {
                                if (strpos($item->registration_start_date, '00:00:00') !== false)
                                {
                                    $dateFormat = $config->date_format;
                                }
                                else
                                {
                                    $dateFormat = $config->event_date_format;
                                }

                                echo JText::sprintf('EB_REGISTRATION_STARTED_ON', JHtml::_('date', $item->registration_start_date, $dateFormat, null));
                            }
                            elseif($item->waiting_list && $item->registration_type != 3)
                            {
                                ?>

                                        <?php
                                        if ($item->registration_type == 0 || $item->registration_type == 1)
                                        {
                                            $cssClasses = [$btnClass, 'eb-register-button', 'eb-join-waiting-list-individual-button'];
                                            ?>
                                            <button>
                                                <a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl);?>"><?php echo JText::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST'); ; ?></a>
                                            </button>
                                            <?php
                                        }

                                        if (($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking)
                                        {
                                            $cssClasses = [$btnClass, 'eb-register-button', 'eb-join-waiting-list-group-button'];
                                            ?>
                                            <button>
                                                <a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP_WAITING_LIST'); ; ?></a>
                                            </button>
                                            <?php
                                        }
                                        ?>

                                <?php
                            }
                            elseif($item->registration_type != 3 && $config->display_message_for_full_event && !$item->waiting_list && $item->registration_start_minutes >= 0)
                            {
                                // Event message to tell user that they already registered, need to login to register or don't have permission to register...
                                echo EventbookingHelperHtml::loadCommonLayout('common/event_message.php', array('config' => $config, 'event' => $item));
                            }
                        }

                        if ($item->is_multiple_date)
                        {
                            ?>
                            <div class="eb-taskbar">
                                <ul>
                                    <li>
                                        <a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_(EventbookingHelperRoute::getEventRoute($item->id, $categoryId, $Itemid));?>"><?php echo JText::_('EB_CHOOSE_DATE_LOCATION'); ; ?></a>
                                    </li>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>

				</div>
			</div>
		</div>
		<div class='col-sm-12 col-xs-12 row-foot-content'>
			<div class='btn-container1'>
                <?php
                if (!$item->is_multiple_date && ($item->waiting_list || $item->can_register || ($item->registration_type != 3 && $config->display_message_for_full_event)))
                {
                    if ($item->can_register)
                    {
                        ?>
                        <?php
                        $registrationUrl = trim($item->registration_handle_url);

                        if ($registrationUrl)
                        {
                            ?>
                            <button>
                                <a class="<?php echo $btnClass.' eb-register-button eb-external-registration-link'; ?> a-btn" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo JText::_('EB_REGISTER');; ?></a>
                            </button>
                            <?php
                        }
                        elseif ($config->multiple_booking && $config->enable_add_multiple_events_to_cart)
                        {
                            $showAddEventsButton = true;
                            ?>
                            <input type="checkbox" class="checkbox eb-event-checkbox" name="event_ids[]" value="<?php echo $item->id ?>" />
                            <?php
                        }
                        else
                        {
                            if ($item->registration_type == 0 || $item->registration_type == 1)
                            {
                                $cssClasses = [$btnClass, 'eb-register-button'];

                                if ($config->multiple_booking && !$item->has_multiple_ticket_types)
                                {
                                    $url        = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $item->id . '&Itemid=' . (int) $Itemid;

                                    if (!$item->event_password)
                                    {
                                        $cssClasses[] = 'eb-colorbox-addcart';
                                    }

                                    $text       = JText::_('EB_REGISTER');
                                }
                                else
                                {
                                    $url        = JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl);
                                    $cssClasses[] = 'eb-individual-registration-button';

                                    if ($item->has_multiple_ticket_types)
                                    {
                                        $text       = JText::_('EB_REGISTER');
                                    }
                                    else
                                    {
                                        $text       = JText::_('EB_REGISTER_INDIVIDUAL');
                                    }

                                    $extraClass = '';
                                }
                                ?>
                                <button>
                                    <a class="<?php echo implode(' ', $cssClasses);?>"
                                       href="<?php echo $url; ?>"><?php echo $text; ?></a>
                                </button>
                                <?php
                            }

                            if ($item->min_group_number > 0)
                            {
                                $minGroupNumber = $item->min_group_number;
                            }
                            else
                            {
                                $minGroupNumber = 2;
                            }

                            if ($item->event_capacity > 0 && (($item->event_capacity - $item->total_registrants) < $minGroupNumber))
                            {
                                $groupRegistrationAvailable = false;
                            }
                            else
                            {
                                $groupRegistrationAvailable = true;
                            }

                            if ($groupRegistrationAvailable && ($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking && !$item->has_multiple_ticket_types)
                            {
                                $cssClasses = [$btnClass, 'eb-register-button', 'eb-group-registration-button'];
                                ?>
                                <button>
                                    <a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP');; ?></a>
                                </button>
                                <?php
                            }
                        }
                        ?>
                        <?php
                    }
                    elseif ($item->registration_start_date != $nullDate && $item->registration_start_minutes < 0)
                    {
                        if (strpos($item->registration_start_date, '00:00:00') !== false)
                        {
                            $dateFormat = $config->date_format;
                        }
                        else
                        {
                            $dateFormat = $config->event_date_format;
                        }

                        echo JText::sprintf('EB_REGISTRATION_STARTED_ON', JHtml::_('date', $item->registration_start_date, $dateFormat, null));
                    }
                    elseif($item->waiting_list && $item->registration_type != 3)
                    {
                        ?>

                        <?php
                        if ($item->registration_type == 0 || $item->registration_type == 1)
                        {
                            $cssClasses = [$btnClass, 'eb-register-button', 'eb-join-waiting-list-individual-button'];
                            ?>
                            <button>
                                <a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl);?>"><?php echo JText::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST'); ; ?></a>
                            </button>
                            <?php
                        }

                        if (($item->registration_type == 0 || $item->registration_type == 2) && !$config->multiple_booking)
                        {
                            $cssClasses = [$btnClass, 'eb-register-button', 'eb-join-waiting-list-group-button'];
                            ?>
                            <button>
                                <a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP_WAITING_LIST'); ; ?></a>
                            </button>
                            <?php
                        }
                        ?>

                        <?php
                    }
                    elseif($item->registration_type != 3 && $config->display_message_for_full_event && !$item->waiting_list && $item->registration_start_minutes >= 0)
                    {
                        // Event message to tell user that they already registered, need to login to register or don't have permission to register...
                        echo EventbookingHelperHtml::loadCommonLayout('common/event_message.php', array('config' => $config, 'event' => $item));
                    }
                }

                if ($item->is_multiple_date)
                {
                    ?>
                    <div class="eb-taskbar">
                        <ul>
                            <li>
                                <a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_(EventbookingHelperRoute::getEventRoute($item->id, $categoryId, $Itemid));?>"><?php echo JText::_('EB_CHOOSE_DATE_LOCATION'); ; ?></a>
                            </li>
                        </ul>
                    </div>
                    <?php
                }
                ?>
            </div>
		</div>
	</div>
</div>
<?php }?>
<?php
if ($showAddEventsButton)
{
?>
     <form name="addEventsToCart" id="addEventsToCart" action="<?php echo JRoute::_('index.php?option=com_eventbooking&task=cart.add_events_to_cart&Itemid='.$Itemid); ?>" method="post">
        <input type="hidden" name="event_ids" id="selected_event_ids" value="" />
     </form>
    <script language="javascript">
        (function ($) {
            addSelectedEventsToCart = function()
            {
                var selectedEventIds = $('input[name="event_ids[]"]:checked').map(
                    function () {return this.value;}).get().join(",");


                if (selectedEventIds.length == 0)
                {
                    alert("<?php echo JText::_('EB_PLEASE_SELECT_EVENTS', true); ?>");

                    return;
                }

                var form = document.addEventsToCart;
                form.selected_event_ids.value = selectedEventIds;
                form.submit();
            }
        })(Eb.jQuery);
    </script>
<?php
}

// Add Google Structured Data
JPluginHelper::importPlugin('eventbooking');
JFactory::getApplication()->triggerEvent('onDisplayEvents', [$items]);
