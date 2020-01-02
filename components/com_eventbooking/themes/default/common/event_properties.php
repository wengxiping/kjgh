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

/**
 * Layout variables
 * -----------------
 * @var   EventbookingTableEvent $item
 * @var   RADConfig              $config
 * @var   boolean                $showLocation
 * @var   stdClass               $location
 * @var   boolean                $isMultipleDate
 * @var   string                 $nullDate
 * @var   int                    $Itemid
 */

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<table class="<?php echo $bootstrapHelper->getClassMapping('table table-bordered table-striped'); ?>">
	<tbody>
	<?php
	if (!$isMultipleDate)
	{
	?>
		<tr class="eb-event-property">
			<td style="width: 30%;" class="eb-event-property-label">
				<?php echo JText::_('EB_EVENT_DATE') ?>
			</td>
			<td class="eb-event-property-value">
				<?php
				if ($item->event_date == EB_TBC_DATE)
				{
					echo JText::_('EB_TBC');
				}
				else
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
		</tr>

		<?php
		if ($config->get('show_event_end_date', '1') && $item->event_end_date != $nullDate)
		{
			if (strpos($item->event_end_date, '00:00:00') !== false)
			{
				$dateFormat = $config->date_format;
			}
			else
			{
				$dateFormat = $config->event_date_format;
			}
			?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_EVENT_END_DATE'); ?>
				</td>
				<td class="eb-event-property-value">
					<?php echo JHtml::_('date', $item->event_end_date, $dateFormat, null); ?>
				</td>
			</tr>
			<?php
		}

		if ($config->get('show_registration_start_date', '1') && $item->registration_start_date != $nullDate)
		{
			if (strpos($item->registration_start_date, '00:00:00') !== false)
			{
				$dateFormat = $config->date_format;
			}
			else
			{
				$dateFormat = $config->event_date_format;
			}
			?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_REGISTRATION_START_DATE'); ?>
				</td>
				<td class="eb-event-property-value">
					<?php echo JHtml::_('date', $item->registration_start_date, $dateFormat, null); ?>
				</td>
			</tr>
			<?php
		}

		if ($config->show_capacity == 1 || ($config->show_capacity == 2 && $item->event_capacity))
		{
		?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_CAPACITY'); ?>
				</td>
				<td class="eb-event-property-value">
					<?php
					if ($item->event_capacity)
					{
						echo $item->event_capacity;
					}
					else
					{
						echo JText::_('EB_UNLIMITED');
					}
					?>
				</td>
			</tr>
		<?php
		}

		if ($config->show_registered && $item->registration_type != 3)
		{
		?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_REGISTERED'); ?>
				</td>
				<td class="eb-event-property-value">
					<?php
					echo $item->total_registrants . ' ';

					if ($config->show_list_of_registrants && ($item->total_registrants > 0) && EventbookingHelperAcl::canViewRegistrantList())
					{
					?>
						<a href="index.php?option=com_eventbooking&view=registrantlist&id=<?php echo $item->id ?>&tmpl=component"
						   class="eb-colorbox-register-lists"><span
								class="view_list"><?php echo JText::_("EB_VIEW_LIST"); ?></span></a>
					<?php
					}
					?>
				</td>
			</tr>
		<?php
		}

		if ($config->show_available_place && $item->event_capacity && $item->registration_type != 3)
		{
		?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_AVAILABLE_PLACE'); ?>
				</td>
				<td class="eb-event-property-label">
					<?php echo $item->event_capacity - $item->total_registrants; ?>
				</td>
			</tr>
		<?php
		}

		if ($config->get('show_cut_off_date', '1') && $nullDate != $item->cut_off_date)
		{
			if (strpos($item->cut_off_date, '00:00:00') !== false)
			{
				$dateFormat = $config->date_format;
			}
			else
			{
				$dateFormat = $config->event_date_format;
			}
			?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_CUT_OFF_DATE'); ?>
				</td>
				<td>
					<?php echo JHtml::_('date', $item->cut_off_date, $dateFormat, null); ?>
				</td>
			</tr>
			<?php
		}
	}

	if ($item->individual_price > 0 || ($config->show_price_for_free_event))
	{
		$showPrice = true;
	}
	else
	{
		$showPrice = false;
	}

	if ($config->show_discounted_price && ($item->individual_price != $item->discounted_price))
	{
		if ($showPrice)
		{
		?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_ORIGINAL_PRICE'); ?>
				</td>
				<td class="eb-event-property-value eb_price">
					<?php
					if ($item->individual_price > 0)
					{
						echo EventbookingHelper::formatCurrency($item->individual_price, $config, $item->currency_symbol);
					}
					else
					{
						echo '<span class="eb_free">' . JText::_('EB_FREE') . '</span>';
					}
					?>
				</td>
			</tr>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_DISCOUNTED_PRICE'); ?>
				</td>
				<td class="eb-event-property-value eb_price">
					<?php
					if ($item->discounted_price > 0)
					{
						echo EventbookingHelper::formatCurrency($item->discounted_price, $config, $item->currency_symbol);

						if ($item->early_bird_discount_amount > 0 && $item->early_bird_discount_date != $nullDate)
						{
							echo ' <em>' . JText::sprintf('EB_UNTIl_DATE', JHtml::_('date', $item->early_bird_discount_date, $config->date_format.' H:i', null)) . '</em>';
						}
					}
					else
					{
						echo '<span class="eb_free">' . JText::_('EB_FREE') . '</span>';
					}
					?>
				</td>
			</tr>
		<?php
		}
	}
	else
	{
		if ($showPrice)
		{
		?>
            <tr class="eb-event-property">
                <td class="eb-event-property-label">
					<?php echo JText::_('EB_INDIVIDUAL_PRICE'); ?>
				</td>
				<td class="eb-event-property-value eb_price">
					<?php
					if ($item->price_text)
					{
						echo $item->price_text;
					}
					elseif ($item->individual_price > 0)
					{
						echo EventbookingHelper::formatCurrency($item->individual_price, $config, $item->currency_symbol);
					}
					else
					{
						echo '<span class="eb_free">' . JText::_('EB_FREE') . '</span>';
					}
					?>
				</td>
			</tr>
			<?php
		}
	}

	if ($item->fixed_group_price > 0)
	{
	?>
        <tr class="eb-event-property">
            <td class="eb-event-property-label">
				<?php echo JText::_('EB_FIXED_GROUP_PRICE'); ?>
			</td>
			<td class="eb-event-property-value eb_price">
				<?php echo EventbookingHelper::formatCurrency($item->fixed_group_price, $config, $item->currency_symbol); ?>
			</td>
		</tr>
	<?php
	}

	if ($item->late_fee_amount > 0)
	{
	?>
		<tr class="eb-event-property">
			<td class="eb-event-property-label">
				<?php echo JText::_('EB_LATE_FEE'); ?>
			</td>
			<td class="eb-event-property-value">
				<?php
					echo EventbookingHelper::formatCurrency($item->late_fee_amount, $config, $item->currency_symbol);
					echo '<em> ' . JText::sprintf('EB_FROM_DATE', JHtml::_('date', $item->late_fee_date, $config->date_format.' H:i', null)) . '</em>';
				?>
			</td>
		</tr>
	<?php
	}

	if ($config->show_event_creator)
	{
	?>
		<tr class="eb-event-property">
			<td class="eb-event-property-label">
				<?php echo JText::_('EB_CREATED_BY'); ?>
			</td>
			<td class="eb-event-property-value">
				<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=search&created_by=' . $item->created_by . '&Itemid=' . $Itemid); ?>"><?php echo $item->creator_name; ?></a>
			</td>
		</tr>
	<?php
	}

	if (isset($item->paramData))
	{
		foreach ($item->paramData as $param)
		{
			if ($param['value'])
			{
				$paramValue = $param['value'];

				// Make the link click-able
				if (filter_var($paramValue, FILTER_VALIDATE_URL))
				{
					$paramValue = '<a href="' . $paramValue . '" target="_blank">' . $paramValue . '<a/>';
				}
			?>
				<tr class="eb-event-property">
					<td class="eb-event-property-label">
						<?php echo JText::_($param['title']); ?>
					</td>
					<td class="eb-event-property-value">
						<?php echo JText::_($paramValue); ?>
					</td>
				</tr>
			<?php
			}
		}
	}

	if ($showLocation && $location)
	{
		$width = (int) $config->map_width;

		if (!$width)
		{
			$width = 500;
		}

		$height = (int) $config->map_height;

		if (!$height)
		{
			$height = 450;
		}
		?>
		<tr class="eb-event-property">
			<td class="eb-event-property-label">
				<?php echo JText::_('EB_LOCATION'); ?>
			</td>
			<td class="eb-event-property-value">
				<?php
				if ($location->address)
				{
					if ($location->image || EventbookingHelper::isValidMessage($location->description))
					{
					?>
						<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id=' . $item->location_id . '&Itemid='.$Itemid); ?>"
						   title="<?php echo $location->name; ?>"><?php echo $location->name; ?></a>
					<?php
					}
					else
					{
					?>
						<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id=' . $item->location_id . '&tmpl=component&format=html'); ?>"
						   class="eb-colorbox-map" title="<?php echo $location->name; ?>"><?php echo $location->name; ?></a>
					<?php
					}
				}
				else
				{
					echo $location->name;
				}
				?>
			</td>
		</tr>
		<?php
	}

	if ($config->show_event_categories)
    {
    ?>
        <tr class="eb-event-property">
            <td class="eb-event-property-label">
                <?php echo JText::_('EB_CATEGORIES'); ?>
            </td>
            <td class="eb-event-property-value">
                <?php
                    $categories = [];

                    foreach ($item->categories as $category)
                    {
                        $categoryUrl  = JRoute::_(EventbookingHelperRoute::getCategoryRoute($category->id, $Itemid));
                        $categories[] = '<a href="' . $categoryUrl . '">' . $category->name . '</a>';
                    }

                    echo implode(',' , $categories);
                ?>
            </td>
        </tr>
    <?php
    }

	if ($item->attachment && !empty($config->show_attachment_in_frontend))
	{
	?>
	<tr class="eb-event-property">
		<td class="eb-event-property-label">
			<?php echo JText::_('EB_ATTACHMENT'); ?>
		</td>
		<td class="eb-event-property-value">
			<?php
			$attachments = explode('|', $item->attachment);
			$baseUri =  JUri::base(true);

			for ($i = 0, $n = count($attachments); $i < $n; $i++)
			{
				$attachment = $attachments[$i];

				if ($i > 0)
				{
					echo '<br />';
				}
				?>
					<a href="<?php echo $baseUri . '/media/com_eventbooking/' . $attachment; ?>" target="_blank"><?php echo $attachment; ?></a>
				<?php
			}
			?>
		</td>
	</tr>
	<?php
	}
	?>
	</tbody>
</table>
