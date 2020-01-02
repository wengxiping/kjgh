<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

class EventbookingViewConfigurationHtml extends RADViewHtml
{
	public function display()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_eventbooking'))
		{
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		$options = array();

		if (version_compare(JVERSION, '4.0.0-dev', '<'))
		{
			$options[] = JHtml::_('select.option', 2, JText::_('EB_VERSION_2'));
			$options[] = JHtml::_('select.option', 3, JText::_('EB_VERSION_3'));
		}

		$options[] = JHtml::_('select.option', 4, JText::_('EB_VERSION_4'));
		$options[] = JHtml::_('select.option', 'uikit3', JText::_('EB_UIKIT_3'));

		// Get extra UI options
		$files = JFolder::files(JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/ui', '.php');

		foreach ($files as $file)
		{
			if (in_array($file, ['abstract.php', 'bootstrap2.php', 'uikit3.php', 'bootstrap3.php', 'bootstrap4.php', 'interface.php']))
			{
				continue;
			}

			$file      = str_replace('.php', '', $file);
			$options[] = JHtml::_('select.option', $file, ucfirst($file));
		}

		$lists['twitter_bootstrap_version'] = JHtml::_('select.genericlist', $options, 'twitter_bootstrap_version', '', 'value', 'text', $config->get('twitter_bootstrap_version', 2));

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SUNDAY'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_MONDAY'));

		$lists['calendar_start_date'] = JHtml::_('select.genericlist', $options, 'calendar_start_date', ' class="inputbox" ', 'value', 'text',
			$config->calendar_start_date);

		$options   = array();
		$options[] = JHtml::_('select.option', 1, JText::_('EB_ORDERING'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_EVENT_DATE'));

		$lists['order_events'] = JHtml::_('select.genericlist', $options, 'order_events', '  class="inputbox" ', 'value', 'text',
			$config->order_events);

		$options   = array();
		$options[] = JHtml::_('select.option', 'asc', JText::_('EB_ASC'));
		$options[] = JHtml::_('select.option', 'desc', JText::_('EB_DESC'));

		$lists['order_direction'] = JHtml::_('select.genericlist', $options, 'order_direction', '', 'value', 'text', $config->order_direction);

		$options   = array();
		$options[] = JHtml::_('select.option', '0', JText::_('EB_FULL_PAYMENT'));
		$options[] = JHtml::_('select.option', '1', JText::_('EB_DEPOSIT_PAYMENT'));

		$lists['default_payment_type'] = JHtml::_('select.genericlist', $options, 'default_payment_type', '', 'value', 'text', $config->get('default_payment_type', 0));

		$options                  = array();
		$options[]                = JHtml::_('select.option', 'asc', JText::_('EB_ASC'));
		$options[]                = JHtml::_('select.option', 'desc', JText::_('EB_DESC'));
		$lists['order_direction'] = JHtml::_('select.genericlist', $options, 'order_direction', '', 'value', 'text', $config->order_direction);

		$options                = array();
		$options[]              = JHtml::_('select.option', 'exact', JText::_('EB_EXACT_PHRASE'));
		$options[]              = JHtml::_('select.option', 'any', JText::_('EB_ANY_WORDS'));
		$lists['search_events'] = JHtml::_('select.genericlist', $options, 'search_events', '', 'value', 'text', $config->get('search_events', ''));

		//Get list of country
		$query->clear()
			->select('name AS value, name AS text')
			->from('#__eb_countries')
			->order('name');
		$db->setQuery($query);

		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_DEFAULT_COUNTRY'));
		$options   = array_merge($options, $db->loadObjectList());

		$lists['country_list'] = JHtml::_('select.genericlist', $options, 'default_country', '', 'value', 'text', $config->default_country);

		$options   = array();
		$options[] = JHtml::_('select.option', ',', JText::_('EB_COMMA'));
		$options[] = JHtml::_('select.option', ';', JText::_('EB_SEMICOLON'));

		$lists['csv_delimiter'] = JHtml::_('select.genericlist', $options, 'csv_delimiter', '', 'value', 'text', $config->csv_delimiter);

		$options   = array();
		$options[] = JHtml::_('select.option', 'csv', JText::_('EB_FILE_CSV'));
		$options[] = JHtml::_('select.option', 'xls', JText::_('EB_FILE_EXCEL_2003'));
		$options[] = JHtml::_('select.option', 'xlsx', JText::_('EB_FILE_EXCEL_2007'));

		$lists['export_data_format'] = JHtml::_('select.genericlist', $options, 'export_data_format', '', 'value', 'text', $config->get('export_data_format', 'xlsx'));

		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_DEFAULT'));
		$options[] = JHtml::_('select.option', 'simple', JText::_('EB_SIMPLE_FORM'));

		$lists['submit_event_form_layout'] = JHtml::_('select.genericlist', $options, 'submit_event_form_layout', '', 'value', 'text',
			$config->submit_event_form_layout);

		//Theme configuration
		$options = array();
		$themes  = JFolder::files(JPATH_ROOT . '/media/com_eventbooking/assets/css/themes', '.css');
		sort($themes);

		foreach ($themes as $theme)
		{
			$theme     = substr($theme, 0, strlen($theme) - 4);
			$options[] = JHtml::_('select.option', $theme, ucfirst($theme));
		}

		$lists['calendar_theme'] = JHtml::_('select.genericlist', $options, 'calendar_theme', ' class="inputbox" ', 'value', 'text',
			$config->calendar_theme);

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_BOTTOM'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_TOP'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_BOTH'));

		$lists['register_buttons_position'] = JHtml::_('select.genericlist', $options, 'register_buttons_position', '', 'value', 'text', $config->get('register_buttons_position'));

		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_POSITION'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_BEFORE_AMOUNT'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_AFTER_AMOUNT'));

		$lists['currency_position'] = JHtml::_('select.genericlist', $options, 'currency_position', ' class="inputbox"', 'value', 'text',
			$config->currency_position);

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('JNO'));
		$options[] = JHtml::_('select.option', 1, JText::_('JYES'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_SHOW_IF_LIMITED'));

		$lists['show_capacity'] = JHtml::_('select.genericlist', $options, 'show_capacity', '', 'value', 'text', $config->show_capacity);

		// Social sharing options
		$options   = array();
		$options[] = JHtml::_('select.option', 'Facebook', JText::_('Facebook'));
		$options[] = JHtml::_('select.option', 'Twitter', JText::_('Twitter'));
		$options[] = JHtml::_('select.option', 'LinkedIn', JText::_('LinkedIn'));
		$options[] = JHtml::_('select.option', 'Delicious', JText::_('Delicious'));
		$options[] = JHtml::_('select.option', 'Digg', JText::_('Digg'));
		$options[] = JHtml::_('select.option', 'Pinterest', JText::_('Pinterest'));

		$lists['social_sharing_buttons'] = JHtml::_('select.genericlist', $options, 'social_sharing_buttons[]', ' class="inputbox" multiple="multiple" ', 'value', 'text',
			explode(',', $config->social_sharing_buttons));

		$options   = array();
		$options[] = JHtml::_('select.option', 'tbl.id', JText::_('EB_ID'));
		$options[] = JHtml::_('select.option', 'tbl.register_date', JText::_('EB_REGISTRATION_DATE'));

		$query->clear()
			->select('name, title')
			->from('#__eb_fields')
			->where('published = 1')
			->where('(is_core = 1 OR is_searchable = 1 )')
			->order('title');
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $field)
		{
			$options[] = JHtml::_('select.option', 'tbl.' . $field->name, $field->title);
		}

		$lists['public_registrants_list_order'] = JHtml::_('select.genericlist', $options, 'public_registrants_list_order', '', 'value', 'text', $config->get('public_registrants_list_order', 'tbl.id'));

		$options   = array();
		$options[] = JHtml::_('select.option', 'asc', JText::_('EB_ASC'));
		$options[] = JHtml::_('select.option', 'desc', JText::_('EB_DESC'));

		$lists['public_registrants_list_order_dir'] = JHtml::_('select.genericlist', $options, 'public_registrants_list_order_dir', '', 'value', 'text', $config->get('public_registrants_list_order_dir', 'desc'));

		//Default settings when creating new events
		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_INDIVIDUAL_GROUP'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_ONLY'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_GROUP_ONLY'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_DISABLE_REGISTRATION'));

		$lists['registration_type']   = JHtml::_('select.genericlist', $options, 'registration_type', ' class="inputbox" ', 'value', 'text', $config->get('registration_type', 0));
		$lists['access']              = JHtml::_('access.level', 'access', $config->get('access', 1), 'class="inputbox"', false);
		$lists['registration_access'] = JHtml::_('access.level', 'registration_access', $config->get('registration_access', 1), 'class="inputbox"', false);

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_UNPUBLISHED'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PUBLISHED'));

		$lists['default_event_status'] = JHtml::_('select.genericlist', $options, 'default_event_status', ' class="inputbox"', 'value', 'text', $config->get('default_event_status', 0));

		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_FORMAT'));
		$options[] = JHtml::_('select.option', '%Y-%m-%d', 'Y-m-d');
		$options[] = JHtml::_('select.option', '%Y/%m/%d', 'Y/m/d');
		$options[] = JHtml::_('select.option', '%Y.%m.%d', 'Y.m.d');
		$options[] = JHtml::_('select.option', '%m-%d-%Y', 'm-d-Y');
		$options[] = JHtml::_('select.option', '%m/%d/%Y', 'm/d/Y');
		$options[] = JHtml::_('select.option', '%m.%d.%Y', 'm.d.Y');
		$options[] = JHtml::_('select.option', '%d-%m-%Y', 'd-m-Y');
		$options[] = JHtml::_('select.option', '%d/%m/%Y', 'd/m/Y');
		$options[] = JHtml::_('select.option', '%d.%m.%Y', 'd.m.Y');

		$lists['date_field_format'] = JHtml::_('select.genericlist', $options, 'date_field_format', '', 'value', 'text', $config->get('date_field_format', '%Y-%m-%d'));

		$options   = array();
		$options[] = JHtml::_('select.option', 'resize', JText::_('EB_RESIZE'));
		$options[] = JHtml::_('select.option', 'crop_resize', JText::_('EB_CROPRESIZE'));

		$lists['resize_image_method'] = JHtml::_('select.genericlist', $options, 'resize_image_method', '', 'value', 'text', $config->get('resize_image_method', 'resize'));

		$currencies = require_once JPATH_ROOT . '/components/com_eventbooking/helper/currencies.php';

		ksort($currencies);

		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_CURRENCY'));

		foreach ($currencies as $code => $title)
		{
			$options[] = JHtml::_('select.option', $code, $title);
		}

		$lists['currency_code'] = JHtml::_('select.genericlist', $options, 'currency_code', '', 'value', 'text', isset($config->currency_code) ? $config->currency_code : 'USD');

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_ALL_NESTED_CATEGORIES'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_ONLY_LAST_ONE'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_NO'));

		$lists['insert_category'] = JHtml::_('select.genericlist', $options, 'insert_category', ' class="inputbox"', 'value', 'text',
			$config->insert_category);

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_PRICE_WITHOUT_TAX'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PRICE_TAX_INCLUDED'));

		$lists['setup_price'] = JHtml::_('select.genericlist', $options, 'setup_price', ' class="inputbox"', 'value', 'text',
			$config->get('setup_price', '0'));

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_ENABLE'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_ONLY_TO_ADMIN'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_ONLY_TO_REGISTRANT'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_DISABLE'));

		$lists['send_emails'] = JHtml::_('select.genericlist', $options, 'send_emails', ' class="inputbox"', 'value', 'text',
			$config->send_emails);

		$options                             = array();
		$options[]                           = JHtml::_('select.option', '', JText::_('JNO'));
		$options[]                           = JHtml::_('select.option', 'first_group_member', JText::_('EB_FIRST_GROUP_MEMBER'));
		$options[]                           = JHtml::_('select.option', 'last_group_member', JText::_('EB_LAST_GROUP_MEMBER'));
		$lists['auto_populate_billing_data'] = JHtml::_('select.genericlist', $options, 'auto_populate_billing_data', '', 'value', 'text',
			$config->auto_populate_billing_data);

		$options   = array();
		$options[] = JHtml::_('select.option', 'new_registration_emails', JText::_('EB_NEW_REGISTRATION_EMAILS'));
		$options[] = JHtml::_('select.option', 'reminder_emails', JText::_('EB_REMINDER_EMAILS'));
		$options[] = JHtml::_('select.option', 'mass_mails', JText::_('EB_MASS_MAIL'));
		$options[] = JHtml::_('select.option', 'registration_approved_emails', JText::_('EB_REGISTRATION_APPROVED_EMAILS'));
		$options[] = JHtml::_('select.option', 'registration_cancel_emails', JText::_('EB_REGISTRATION_CANCEL_EMAILS'));
		$options[] = JHtml::_('select.option', 'new_event_notification_emails', JText::_('EB_NEW_EVENT_NOTIFICATION_EMAILS'));
		$options[] = JHtml::_('select.option', 'deposit_payment_reminder_emails', JText::_('EB_DEPOSIT_PAYMENT_REMINDER_EMAILS'));
		$options[] = JHtml::_('select.option', 'waiting_list_emails', JText::_('EB_WAITING_LIST_EMAILS'));
		$options[] = JHtml::_('select.option', 'offline_payment_reminder_emails', JText::_('EB_OFFLINE_PAYMENT_REMINDER_EMAILS'));
		$options[] = JHtml::_('select.option', 'event_approved_emails', JText::_('EB_EVENT_APPROVED_EMAILS'));

		$lists['log_email_types'] = JHtml::_('select.genericlist', $options, 'log_email_types[]', ' multiple="multiple" ', 'value', 'text', explode(',', $config->get('log_email_types')));

		$options   = array();
		$options[] = JHtml::_('select.option', 'KM', JText::_('EB_KM'));
		$options[] = JHtml::_('select.option', 'MILE', JText::_('EB_MILE'));

		$lists['radius_search_distance'] = JHtml::_('select.genericlist', $options, 'radius_search_distance', '', 'value', 'text', $config->get('radius_search_distance', 'KM'));

		$fontsPath = JPATH_ROOT . '/components/com_eventbooking/tcpdf/fonts/';
		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_FONT'));
		$options[] = JHtml::_('select.option', 'courier', JText::_('Courier'));
		$options[] = JHtml::_('select.option', 'helvetica', JText::_('Helvetica'));
		$options[] = JHtml::_('select.option', 'symbol', JText::_('Symbol'));
		$options[] = JHtml::_('select.option', 'times', JText::_('Times New Roman'));
		$options[] = JHtml::_('select.option', 'zapfdingbats', JText::_('Zapf Dingbats'));

		$additionalFonts = array(
			'aealarabiya',
			'aefurat',
			'dejavusans',
			'dejavuserif',
			'freemono',
			'freesans',
			'freeserif',
			'hysmyeongjostdmedium',
			'kozgopromedium',
			'kozminproregular',
			'msungstdlight',
			'opensans',
			'cid0jp',
			'DroidSansFallback',
			'PFBeauSansProthin',
			'PFBeauSansPro',
			'roboto',
		);

		foreach ($additionalFonts as $fontName)
		{
			if (file_exists($fontsPath . $fontName . '.php'))
			{
				$options[] = JHtml::_('select.option', $fontName, ucfirst($fontName));
			}
		}

		// Support True Type Font
		$trueTypeFonts = JFolder::files($fontsPath, '.ttf');

		foreach ($trueTypeFonts as $trueTypeFont)
		{
			$options[] = JHtml::_('select.option', $trueTypeFont, $trueTypeFont);
		}

		$lists['pdf_font'] = JHtml::_('select.genericlist', $options, 'pdf_font', ' class="inputbox"', 'value', 'text', empty($config->pdf_font) ? 'times' : $config->pdf_font);

		$options   = array();
		$options[] = JHtml::_('select.option', 'P', JText::_('Portrait'));
		$options[] = JHtml::_('select.option', 'L', JText::_('Landscape'));

		$lists['ticket_page_orientation']      = JHtml::_('select.genericlist', $options, 'ticket_page_orientation', '', 'value', 'text', $config->get('ticket_page_orientation', 'P'));
		$lists['certificate_page_orientation'] = JHtml::_('select.genericlist', $options, 'certificate_page_orientation', '', 'value', 'text', $config->get('certificate_page_orientation', 'P'));

		$options   = array();
		$options[] = JHtml::_('select.option', 'A4', JText::_('A4'));
		$options[] = JHtml::_('select.option', 'A5', JText::_('A5'));
		$options[] = JHtml::_('select.option', 'A6', JText::_('A6'));
		$options[] = JHtml::_('select.option', 'A7', JText::_('A7'));

		$lists['ticket_page_format']      = JHtml::_('select.genericlist', $options, 'ticket_page_format', '', 'value', 'text', $config->get('ticket_page_format', 'A4'));
		$lists['certificate_page_format'] = JHtml::_('select.genericlist', $options, 'certificate_page_format', '', 'value', 'text', $config->get('certificate_page_format', 'A4'));

		if (empty($config->default_ticket_layout))
		{
			$config->default_ticket_layout = $config->certificate_layout;
		}

		// Default menu item settings
		$menus     = JFactory::getApplication()->getMenu('site');
		$component = JComponentHelper::getComponent('com_eventbooking');
		$items     = $menus->getItems('component_id', $component->id);

		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT'));

		foreach ($items as $item)
		{
			if (!empty($item->query['view']) && in_array($item->query['view'], ['calendar', 'categories', 'upcomingevents', 'category', 'archive']))
			{
				$options[] = JHtml::_('select.option', $item->id, str_repeat('- ', $item->level) . $item->title);
			}
		}

		$lists['default_menu_item'] = JHtml::_('select.genericlist', $options, 'default_menu_item', '', 'value', 'text', $config->default_menu_item);
		$languages                  = EventbookingHelper::getLanguages();

		if (JLanguageMultilang::isEnabled() && count($languages))
		{
			foreach ($languages as $language)
			{
				$attributes = ['component_id', 'language'];
				$values     = [$component->id, [$language->lang_code, '*']];
				$items      = $menus->getItems($attributes, $values);

				$options   = array();
				$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT'));

				foreach ($items as $item)
				{
					if (!empty($item->query['view']) && in_array($item->query['view'], ['calendar', 'categories', 'upcomingevents', 'category', 'archive']))
					{
						$options[] = JHtml::_('select.option', $item->id, str_repeat('- ', $item->level) . $item->title);
					}
				}

				$key         = 'default_menu_item_' . $language->lang_code;
				$lists[$key] = JHtml::_('select.genericlist', $options, $key, '', 'value', 'text', $config->{$key});
			}
		}

		$options   = [];
		$options[] = JHtml::_('select.option', 'googlemap', 'Google Map');
		$options[] = JHtml::_('select.option', 'openstreetmap', 'OpenStreetMap');

		$lists['map_provider'] = JHtml::_('select.genericlist', $options, 'map_provider', '', 'value', 'text', $config->get('map_provider', 'googlemap'));

		$options   = [];
		$options[] = JHtml::_('select.option', 'use_tooltip', JText::_('EB_USE_TOOLTIP'));
		$options[] = JHtml::_('select.option', 'under_field_label', JText::_('EB_UNDER_FIELD_LABEL'));
		$options[] = JHtml::_('select.option', 'under_field_input', JText::_('EB_UNDER_FIELD_INPUT'));

		$lists['display_field_description'] = JHtml::_('select.genericlist', $options, 'display_field_description', '', 'value', 'text', $config->get('display_field_description', 'use_tooltip'));

		$options   = [];
		$options[] = JHtml::_('select.option', 'name', JText::_('EB_NAME'));
		$options[] = JHtml::_('select.option', 'ordering', JText::_('EB_ORDERING'));

		$lists['category_dropdown_ordering'] = JHtml::_('select.genericlist', $options, 'category_dropdown_ordering', '', 'value', 'text', $config->get('category_dropdown_ordering', 'name'));

		// Editor plugin for code editing
		$editorPlugin = null;

		if (JPluginHelper::isEnabled('editors', 'codemirror'))
		{
			$editorPlugin = 'codemirror';
		}
		elseif (JPluginHelper::isEnabled('editor', 'none'))
		{
			$editorPlugin = 'none';
		}

		if ($editorPlugin)
		{
			$this->editor = JEditor::getInstance($editorPlugin);
		}

		$this->lists     = $lists;
		$this->config    = $config;
		$this->languages = $languages;
		$this->addToolbar();

		parent::display();
	}

	/**
	 * Override addToolbar method to use custom buttons for this view
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('EB_CONFIGURATION'), 'generic.png');
		JToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
		JToolbarHelper::save('save');
		JToolbarHelper::cancel();
		JToolbarHelper::preferences('com_eventbooking');
	}
}
