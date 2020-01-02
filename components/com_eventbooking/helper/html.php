<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

abstract class EventbookingHelperHtml
{
	/**
	 * Render ShowOn string
	 *
	 * @param array $fields
	 *
	 * @return string
	 */
	public static function renderShowOn($fields)
	{
		$output = array();

		$i = 0;

		foreach ($fields as $name => $values)
		{
			$i++;

			$values = (array) $values;

			$data = array(
				'field'  => $name,
				'values' => $values,
				'sign'   => '=',
			);

			$data['op'] = $i > 1 ? 'AND' : '';

			$output[] = json_encode($data);
		}

		return '[' . implode(',', $output) . ']';
	}

	/***
	 * Get javascript code for showing calendar form field on ajax request result
	 *
	 * @param $fields
	 *
	 * @return string
	 */
	public static function getCalendarSetupJs($fields = array())
	{
		return 'calendarElements = document.querySelectorAll(".field-calendar");
                    for (i = 0; i < calendarElements.length; i++) {
                    JoomlaCalendar.init(calendarElements[i]);
                    }';
	}

	/**
	 * Build category dropdown
	 *
	 * @param int    $selected
	 * @param string $name
	 * @param string $attr Extra attributes need to be passed to the dropdown
	 * @param string $fieldSuffix
	 *
	 * @return string
	 */
	public static function buildCategoryDropdown($selected, $name = "parent", $attr = null, $fieldSuffix = null)
	{
		$config = EventbookingHelper::getConfig();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->select('id, parent AS parent_id')
			->select($db->quoteName('name' . $fieldSuffix, 'title'))
			->from('#__eb_categories')
			->where('published=1')
			->order($config->get('category_dropdown_ordering', $db->quoteName('name' . $fieldSuffix)));

		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = array();
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list      = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$options   = array();
		$options[] = JHtml::_('select.option', '0', JText::_('EB_SELECT_CATEGORY'));
		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}

		return JHtml::_('select.genericlist', $options, $name,
			array(
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="inputbox" ' . $attr,
				'list.select'        => $selected,));
	}

	/**
	 * Function to render a common layout which is used in different views
	 *
	 * @param string $layout
	 * @param array  $data
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function loadCommonLayout($layout, $data = array())
	{
		$app        = JFactory::getApplication();
		$deviceType = EventbookingHelper::getDeviceType();
		$theme      = EventbookingHelper::getDefaultTheme();
		$layout     = str_replace('/tmpl', '', $layout);
		$filename   = basename($layout);
		$filePath   = substr($layout, 0, strlen($layout) - strlen($filename));
		$layouts    = EventbookingHelperHtml::getPossibleLayouts($filename);

		if ($deviceType !== 'desktop')
		{
			$deviceLayouts = [];

			foreach ($layouts as $layout)
			{
				$deviceLayouts[] = $layout . '.' . $deviceType;
			}

			$layouts = array_merge($deviceLayouts, $layouts);
		}

		// Build paths array to get layout from
		$paths   = [];
		$paths[] = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_eventbooking';
		$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name;

		if ($theme->name != 'default')
		{
			$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/default';
		}

		$foundLayout = '';

		foreach ($layouts as $layout)
		{
			if ($filePath)
			{
				$layout = $filePath . $layout;
			}

			foreach ($paths as $path)
			{
				if (file_exists($path . '/' . $layout))
				{
					$foundLayout = $path . '/' . $layout;
					break;
				}
			}
		}


		if (empty($foundLayout))
		{
			throw new RuntimeException(JText::sprintf('The given common layout %s does not exist', $layout));
		}

		// Start an output buffer.
		ob_start();
		extract($data);

		// Load the layout.
		include $foundLayout;

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Get label of the field (including tooltip)
	 *
	 * @param        $name
	 * @param        $title
	 * @param string $tooltip
	 *
	 * @return string
	 */
	public static function getFieldLabel($name, $title, $tooltip = '')
	{
		$label = '';
		$text  = $title;

		// Build the class for the label.
		$class = !empty($tooltip) ? 'hasTooltip hasTip' : '';

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $name . '-lbl" for="' . $name . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($tooltip))
		{
			$label .= ' title="' . JHtml::tooltipText(trim($text, ':'), $tooltip, 0) . '"';
		}

		$label .= '>' . $text . '</label>';

		return $label;
	}

	/**
	 * Get bootstrapped style boolean input
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	public static function getBooleanInput($name, $value)
	{
		JHtml::_('jquery.framework');
		$value = (int) $value;
		$field = JFormHelper::loadFieldType('Radio');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $name);

		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$element->addAttribute('class', 'switcher');
		}
		else
		{
			$element->addAttribute('class', 'radio btn-group btn-group-yesno');
		}

		$element->addAttribute('default', '0');

		$node = $element->addChild('option', 'JNO');
		$node->addAttribute('value', '0');

		$node = $element->addChild('option', 'JYES');
		$node->addAttribute('value', '1');

		$field->setup($element, $value);

		return $field->input;
	}

	/**
	 * Render radio group input
	 *
	 * @param $name
	 * @param $options
	 * @param $value
	 *
	 * @return string
	 */
	public static function getRadioGroupInput($name, $options, $value)
	{
		$html = array();

		// Start the radio field output.
		$html[] = '<fieldset id="' . $name . '" class="radio btn-group btn-group-yesno">';

		$count = 0;

		foreach ($options as $optionValue => $optionText)
		{
			$checked = ($optionValue == $value) ? ' checked="checked"' : '';
			$html[]  = '<input type="radio" id="' . $name . $count . '" name="' . $name . '" value="' . $optionValue . '"' . $checked . ' />';
			$html[]  = '<label for="' . $name . $count . '">' . $optionText . '</label>';

			$count++;
		}

		// End the radio field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 * Get available fields tags using in the email messages & invoice
	 *
	 * @param bool $defaultTags
	 *
	 * @return array|string
	 */
	public static function getAvailableMessagesTags($defaultTags = true)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('name')
			->from('#__eb_fields')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		if ($defaultTags)
		{
			$fields = array('registration_detail', 'date', 'event_title', 'event_date', 'event_end_date', 'short_description', 'description', 'total_amount', 'tax_amount', 'discount_amount', 'late_fee', 'payment_processing_fee', 'amount', 'location', 'number_registrants', 'invoice_number', 'transaction_id', 'id', 'payment_method');
		}
		else
		{
			$fields = array();
		}

		$fields = array_merge($fields, $db->loadColumn());

		$fields = array_map('strtoupper', $fields);
		$fields = '[' . implode('], [', $fields) . ']';

		return $fields;
	}

	/**
	 * Get URL to add the given event to Google Calendar
	 *
	 * @param $row
	 *
	 * @return string
	 */
	public static function getAddToGoogleCalendarUrl($row)
	{
		$eventData = self::getEventDataArray($row);

		$queryString['title']       = "text=" . $eventData['title'];
		$queryString['dates']       = "dates=" . $eventData['dates'];
		$queryString['location']    = "location=" . $eventData['location'];
		$queryString['trp']         = "trp=false";
		$queryString['websiteName'] = "sprop=" . $eventData['sitename'];
		$queryString['websiteURL']  = "sprop=name:" . $eventData['siteurl'];
		$queryString['details']     = "details=" . $eventData['details'];

		return "http://www.google.com/calendar/event?action=TEMPLATE&" . implode("&", $queryString);
	}

	/**
	 * Get URL to add the given event to Yahoo Calendar
	 *
	 * @param $row
	 *
	 * @return string
	 */
	public static function getAddToYahooCalendarUrl($row)
	{
		$eventData = self::getEventDataArray($row);

		$urlString['title']      = "title=" . $eventData['title'];
		$urlString['st']         = "st=" . $eventData['st'];
		$urlString['et']         = "et=" . $eventData['et'];
		$urlString['rawdetails'] = "desc=" . $eventData['details'];
		$urlString['location']   = "in_loc=" . $eventData['location'];

		return "http://calendar.yahoo.com/?v=60&view=d&type=20&" . implode("&", $urlString);
	}

	/**
	 * Get event data
	 *
	 * @param EventbookingTableEvent $row
	 *
	 * @return mixed
	 */
	public static function getEventDataArray($row)
	{
		static $cache = [];

		if (!isset($cache[$row->id]))
		{
			$config       = JFactory::getConfig();
			$dateFormat   = "Ymd\THis\Z";
			$eventDate    = JFactory::getDate($row->event_date, new DateTimeZone($config->get('offset')));
			$eventEndDate = JFactory::getDate($row->event_end_date, new DateTimeZone($config->get('offset')));

			$data['title']    = urlencode($row->title);
			$data['dates']    = $eventDate->format($dateFormat) . "/" . $eventEndDate->format($dateFormat);
			$data['st']       = $eventDate->format($dateFormat);
			$data['et']       = $eventEndDate->format($dateFormat);
			$data['duration'] = abs(strtotime($row->event_end_date) - strtotime($row->event_date));

			$locationInformation = array();

			if (property_exists($row, 'location_name'))
			{
				if ($row->location_name)
				{
					$locationInformation[] = $row->location_name;

					if ($row->location_address)
					{
						$locationInformation[] = $row->location_address;
					}
				}
			}
			else
			{
				// Get location data
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.*')
					->from('#__eb_locations AS a')
					->innerJoin('#__eb_events AS b ON a.id=b.location_id')
					->where('b.id=' . $row->id);

				$db->setQuery($query);
				$rowLocation = $db->loadObject();

				if ($rowLocation)
				{
					$locationInformation[] = $rowLocation->name;

					if ($rowLocation->address)
					{
						$locationInformation[] = $rowLocation->address;
					}

					$data['location'] = implode(', ', $locationInformation);
				}
			}

			if (count($locationInformation) > 0)
			{
				$data['location'] = implode(', ', $locationInformation);
			}
			else
			{
				$data['location'] = '';
			}

			$data['sitename']   = urlencode($config->get('sitename'));
			$data['siteurl']    = urlencode(JUri::root());
			$data['rawdetails'] = urlencode($row->description);
			$data['details']    = strip_tags($row->description);

			if (strlen($data['details']) > 100)
			{
				$data['details'] = \Joomla\String\StringHelper::substr($data['details'], 0, 100) . ' ...';
			}

			$data['details'] = urlencode($data['details']);

			$cache[$row->id] = $data;
		}

		return $cache[$row->id];
	}

	/**
	 * Filter and only return the available options for a quantity field
	 *
	 * @param array $values
	 * @param array $quantityValues
	 * @param int   $eventId
	 * @param int   $fieldId
	 * @param bool  $multiple
	 * @param array $multilingualValues
	 *
	 * @return array
	 */
	public static function getAvailableQuantityOptions(&$values, &$quantityValues, $eventId, $fieldId, $multiple = false, $multilingualValues = array())
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// First, we need to get list of registration records of this event
		$query->select('id')
			->from('#__eb_registrants')
			->where('event_id = ' . $eventId)
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');
		$db->setQuery($query);
		$registrantIds = $db->loadColumn();

		if (count($registrantIds))
		{
			$registrantIds = implode(',', $registrantIds);

			if ($multiple)
			{
				$fieldValuesQuantity = array();
				$query->clear()
					->select('field_value')
					->from('#__eb_field_values')
					->where('field_id = ' . $fieldId)
					->where('registrant_id IN (' . $registrantIds . ')');
				$db->setQuery($query);
				$rowFieldValues = $db->loadObjectList();

				if (count($rowFieldValues))
				{
					foreach ($rowFieldValues as $rowFieldValue)
					{
						$fieldValue = $rowFieldValue->field_value;

						if ($fieldValue)
						{
							if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
							{
								$selectedOptions = json_decode($fieldValue);
							}
							else
							{
								$selectedOptions = array($fieldValue);
							}

							foreach ($selectedOptions as $selectedOption)
							{
								if (isset($fieldValuesQuantity[$selectedOption]))
								{
									$fieldValuesQuantity[$selectedOption]++;
								}
								else
								{
									$fieldValuesQuantity[$selectedOption] = 1;
								}
							}
						}
					}
				}
			}

			for ($i = 0, $n = count($values); $i < $n; $i++)
			{
				$value = trim($values[$i]);

				if ($multiple)
				{
					$total = isset($fieldValuesQuantity[$value]) ? $fieldValuesQuantity[$value] : 0;
				}
				else
				{
					$query->clear()
						->select('COUNT(*)')
						->from('#__eb_field_values')
						->where('field_id = ' . $fieldId)
						->where('registrant_id IN (' . $registrantIds . ')');

					if (!empty($multilingualValues))
					{
						$allValues = array_map(array($db, 'quote'), $multilingualValues[$i]);
						$query->where('field_value IN (' . implode(',', $allValues) . ')');
					}
					else
					{
						$query->where('field_value=' . $db->quote($value));
					}

					$db->setQuery($query);
					$total = $db->loadResult();
				}

				if (!empty($quantityValues[$value]))
				{
					$quantityValues[$value] -= $total;

					if ($quantityValues[$value] <= 0)
					{
						unset($values[$i]);
					}
				}
			}
		}

		return $values;
	}

	/**
	 * Helper method to prepare meta data for the document
	 *
	 * @param \Joomla\Registry\Registry $params
	 *
	 * @param null                      $item
	 */
	public static function prepareDocument($params, $item = null)
	{
		$document         = JFactory::getDocument();
		$siteNamePosition = JFactory::getConfig()->get('sitename_pagetitles');
		$pageTitle        = $params->get('page_title');
		if ($pageTitle)
		{
			if ($siteNamePosition == 0)
			{
				$document->setTitle($pageTitle);
			}
			elseif ($siteNamePosition == 1)
			{
				$document->setTitle(JFactory::getConfig()->get('sitename') . ' - ' . $pageTitle);
			}
			else
			{
				$document->setTitle($pageTitle . ' - ' . JFactory::getConfig()->get('sitename'));
			}
		}

		if (!empty($item->meta_keywords))
		{
			$document->setMetaData('keywords', $item->meta_keywords);
		}
		elseif ($params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if (!empty($item->meta_description))
		{
			$document->setMetaData('description', $item->meta_description);
		}
		elseif ($params->get('menu-meta_description'))
		{
			$document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}
	}

	/**
	 * Method to escape field data before displaying to avoid xss attack
	 *
	 * @param array $rows
	 * @param array $fields
	 */
	public static function antiXSS($rows, $fields)
	{
		$config = EventbookingHelper::getConfig();

		// Do not escape data if the system is configured to allow using HTML code on event title
		if ($config->allow_using_html_on_title)
		{
			return;
		}

		if (is_object($rows))
		{
			$rows = [$rows];
		}

		$fields = (array) $fields;

		foreach ($rows as $row)
		{
			foreach ($fields as $field)
			{
				$row->{$field} = htmlspecialchars($row->{$field}, ENT_COMPAT);
			}
		}
	}

	/**
	 * Function to add dropdown menu
	 *
	 * @param string $vName
	 */
	public static function renderSubmenu($vName = 'dashboard')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_menus')
			->where('published = 1')
			->where('menu_parent_id = 0')
			->order('ordering');
		$db->setQuery($query);
		$menus = $db->loadObjectList();
		$html  = '';

		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$html .= '<ul id="mp-dropdown-menu" class="nav nav-tabs nav-hover eb-joomla4">';
		}
		else
		{
			$html .= '<ul id="mp-dropdown-menu" class="nav nav-tabs nav-hover">';
		}


		$currentLink = 'index.php' . JUri::getInstance()->toString(array('query'));
		for ($i = 0; $n = count($menus), $i < $n; $i++)
		{
			$menu = $menus[$i];
			$query->clear();
			$query->select('*')
				->from('#__eb_menus')
				->where('published = 1')
				->where('menu_parent_id = ' . intval($menu->id))
				->order('ordering');
			$db->setQuery($query);
			$subMenus = $db->loadObjectList();
			$class    = '';
			if (!count($subMenus))
			{
				$class      = '';
				$extraClass = '';
				if ($menu->menu_link == $currentLink)
				{
					$class      = ' class="active"';
					$extraClass = 'active';
				}
				$html .= '<li' . $class . '><a class="nav-link dropdown-item ' . $extraClass . '" href="' . $menu->menu_link . '"><span class="icon-' . $menu->menu_class . '"></span> ' . JText::_($menu->menu_name) .
					'</a></li>';
			}
			else
			{
				$class = ' class="dropdown"';
				for ($j = 0; $m = count($subMenus), $j < $m; $j++)
				{
					$subMenu = $subMenus[$j];
					if ($subMenu->menu_link == $currentLink)
					{
						$class = ' class="dropdown active"';
						break;
					}
				}
				$html .= '<li' . $class . '>';
				$html .= '<a id="drop_' . $menu->id . '" href="#" data-toggle="dropdown" role="button" class="dropdown-toggle nav-link dropdown-toggle"><span class="icon-' . $menu->menu_class . '"></span> ' .
					JText::_($menu->menu_name) . ' <b class="caret"></b></a>';
				$html .= '<ul aria-labelledby="drop_' . $menu->id . '" role="menu" class="dropdown-menu" id="menu_' . $menu->id . '">';
				for ($j = 0; $m = count($subMenus), $j < $m; $j++)
				{
					$subMenu    = $subMenus[$j];
					$class      = '';
					$extraClass = '';
					if ($subMenu->menu_link == $currentLink)
					{
						$class      = ' class="active"';
						$extraClass = 'active';
					}

					$html .= '<li' . $class . '><a class="nav-link dropdown-item ' . $extraClass . '" href="' . $subMenu->menu_link .
						'" tabindex="-1"><span class="icon-' . $subMenu->menu_class . '"></span> ' . JText::_($subMenu->menu_name) . '</a></li>';
				}
				$html .= '</ul>';
				$html .= '</li>';
			}
		}

		$html .= '</ul>';
		echo $html;
	}

	/**
	 * Get media input field type
	 *
	 * @param string $value
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public static function getMediaInput($value, $fieldName = 'image')
	{
		$xml  = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_eventbooking/forms/mediaInput.xml');
		$xml  = str_replace('name="image"', 'name="' . $fieldName . '"', $xml);
		$form = JForm::getInstance('com_eventbooking' . $fieldName, $xml);

		$data[$fieldName] = $value;

		$app = JFactory::getApplication();
		$app->triggerEvent('onContentPrepareForm', array($form, $data));

		$form->bind($data);

		return $form->getField($fieldName)->input;
	}

	/**
	 * Get events list dropdown
	 *
	 * @param array  $rows
	 * @param string $name
	 * @param string $attributes
	 * @param mixed  $selected
	 *
	 * @return string
	 */
	public static function getEventsDropdown($rows, $name, $attributes = '', $selected = 0)
	{
		$config    = EventbookingHelper::getConfig();
		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_EVENT'), 'id', 'title');

		if ($config->show_event_date)
		{
			foreach ($rows as $row)
			{
				$eventDate = JHtml::_('date', $row->event_date, $config->date_format, null);
				$options[] = JHtml::_('select.option', $row->id, $row->title . ' (' . $eventDate . ')', 'id', 'title');
			}
		}
		else
		{
			$options = array_merge($options, $rows);
		}

		return JHtml::_('select.genericlist', $options, $name, $attributes, 'id', 'title', $selected);
	}

	/**
	 * Get list of possible layouts, base on the used UI framework
	 *
	 * @param string $layout
	 *
	 * @return array
	 */
	public static function getPossibleLayouts($layout)
	{
		$layouts = [$layout];

		$config = EventbookingHelper::getConfig();

		if (empty($config->twitter_bootstrap_version))
		{
			$twitterBootstrapVersion = 2;
		}
		else
		{
			$twitterBootstrapVersion = $config->twitter_bootstrap_version;
		}

		switch ($twitterBootstrapVersion)
		{
			case 2:
				break;
			case 3;
				break;
			case 4:
				array_unshift($layouts, $layout . '.bootstrap' . $twitterBootstrapVersion);
				break;
			default:
				array_unshift($layouts, $layout . '.' . $twitterBootstrapVersion);
				break;
		}

		return $layouts;
	}

	/**
	 * Get BootstrapHelper class for admin UI
	 *
	 * @return EventbookingHelperBootstrap
	 */
	public static function getAdminBootstrapHelper()
	{
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			return new EventbookingHelperBootstrap('4');
		}

		return new EventbookingHelperBootstrap('2');
	}
}
