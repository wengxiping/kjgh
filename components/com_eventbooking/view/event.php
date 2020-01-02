<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

trait EventbookingViewEvent
{
	/**
	 * Build data use on submit event form
	 *
	 * @param EventbookingTableEvent $item
	 * @param array                  $categories
	 * @param array                  $locations
	 */
	public function buildFormData($item, $categories, $locations)
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		//Locations dropdown
		$options                    = array();
		$options[]                  = JHtml::_('select.option', 0, JText::_('EB_SELECT_LOCATION'), 'id', 'name');
		$options                    = array_merge($options, $locations);
		$this->lists['location_id'] = JHtml::_('select.genericlist', $options, 'location_id', ' class="advancedSelect" ', 'id', 'name', $item->location_id);

		// Categories dropdown
		$children = [];

		// first pass - collect children
		foreach ($categories as $v)
		{
			$pt   = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : [];
			array_push($list, $v);
			$children[$pt] = $list;
		}

		$list    = JHtml::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options = array();

		if ($this->getLayout() == 'simple')
		{
			$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_CATEGORY'));
		}
		else
		{
			$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_CATEGORY'));
		}

		foreach ($list as $listItem)
		{
			$options[] = JHtml::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
		}

		if ($item->id)
		{
			$query->clear()
				->select('category_id')
				->from('#__eb_event_categories')
				->where('event_id = ' . $item->id)
				->where('main_category = 0');
			$db->setQuery($query);
			$additionalCategories = $db->loadColumn();
		}
		else
		{
			$additionalCategories = [];
		}

		$this->lists['main_category_id'] = JHtml::_('select.genericlist', $options, 'main_category_id', array(
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="advancedSelect input-xlarge validate[required]"',
			'list.select'        => (int) $item->main_category_id,
		));

		array_shift($options);

		$this->lists['category_id'] = JHtml::_('select.genericlist', $options, 'category_id[]', array(
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="advancedSelect input-xlarge"  size="5" multiple="multiple"',
			'list.select'        => $additionalCategories,
		));

		$options                                 = array();
		$options[]                               = JHtml::_('select.option', 1, JText::_('%'));
		$options[]                               = JHtml::_('select.option', 2, $config->currency_symbol);
		$this->lists['discount_type']            = JHtml::_('select.genericlist', $options, 'discount_type', ' class="input-small" ', 'value', 'text', $item->discount_type);
		$this->lists['early_bird_discount_type'] = JHtml::_('select.genericlist', $options, 'early_bird_discount_type', 'class="input-small"', 'value', 'text', $item->early_bird_discount_type);
		$this->lists['late_fee_type']            = JHtml::_('select.genericlist', $options, 'late_fee_type', 'class="input-small"', 'value', 'text', $item->late_fee_type);

		if ($config->activate_deposit_feature)
		{
			$this->lists['deposit_type'] = JHtml::_('select.genericlist', $options, 'deposit_type', ' class="input-small" ', 'value', 'text', $item->deposit_type);
		}

		if (!$item->id)
		{
			$item->registration_type = $config->registration_type;
		}

		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('EB_INDIVIDUAL_GROUP'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_ONLY'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_GROUP_ONLY'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_DISABLE_REGISTRATION'));

		$this->lists['registration_type'] = JHtml::_('select.genericlist', $options, 'registration_type', ' class="input-xlarge" ', 'value', 'text', $item->registration_type);

		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('EB_EACH_MEMBER'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_EACH_REGISTRATION'));

		$this->lists['members_discount_apply_for'] = JHtml::_('select.genericlist', $options, 'members_discount_apply_for', '', 'value', 'text', $item->members_discount_apply_for);

		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('EB_USE_GLOBAL'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_ONLY'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_GROUP_ONLY'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_INDIVIDUAL_GROUP'));
		$options[] = JHtml::_('select.option', 4, JText::_('EB_DISABLE'));

		$this->lists['enable_coupon'] = JHtml::_('select.genericlist', $options, 'enable_coupon', ' class="inputbox" ', 'value', 'text', $item->enable_coupon);

		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('No'));
		$options[] = JHtml::_('select.option', 1, JText::_('Yes'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_USE_GLOBAL'));

		$this->lists['activate_waiting_list'] = JHtml::_('select.genericlist', $options, 'activate_waiting_list', ' class="inputbox" ', 'value', 'text', $item->activate_waiting_list);

		$this->lists['access']              = JHtml::_('access.level', 'access', $item->access, 'class="inputbox"', false);
		$this->lists['registration_access'] = JHtml::_('access.level', 'registration_access', $item->registration_access, 'class="inputbox"', false);

		if ($item->event_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->event_date));
			$selectedMinute = date('i', strtotime($item->event_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['event_date_hour']   = JHtml::_('select.integerlist', 0, 23, 1, 'event_date_hour', ' class="inputbox input-mini" ', $selectedHour);
		$this->lists['event_date_minute'] = JHtml::_('select.integerlist', 0, 55, 5, 'event_date_minute', ' class="inputbox input-mini" ', $selectedMinute, '%02d');

		if ($item->event_end_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->event_end_date));
			$selectedMinute = date('i', strtotime($item->event_end_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['event_end_date_hour']   = JHtml::_('select.integerlist', 0, 23, 1, 'event_end_date_hour', ' class="inputbox input-mini" ', $selectedHour);
		$this->lists['event_end_date_minute'] = JHtml::_('select.integerlist', 0, 55, 5, 'event_end_date_minute', ' class="inputbox input-mini" ', $selectedMinute, '%02d');

		// Cut off time
		if ($item->cut_off_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->cut_off_date));
			$selectedMinute = date('i', strtotime($item->cut_off_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['cut_off_hour']   = JHtml::_('select.integerlist', 0, 23, 1, 'cut_off_hour', ' class="inputbox input-mini" ', $selectedHour);
		$this->lists['cut_off_minute'] = JHtml::_('select.integerlist', 0, 55, 5, 'cut_off_minute', ' class="inputbox input-mini" ', $selectedMinute, '%02d');

		// Registration start time
		if ($item->registration_start_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->registration_start_date));
			$selectedMinute = date('i', strtotime($item->registration_start_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['registration_start_hour']   = JHtml::_('select.integerlist', 0, 23, 1, 'registration_start_hour', ' class="inputbox input-mini" ', $selectedHour);
		$this->lists['registration_start_minute'] = JHtml::_('select.integerlist', 0, 55, 5, 'registration_start_minute', ' class="inputbox input-mini" ', $selectedMinute, '%02d');

		$nullDate = $db->getNullDate();

		//Custom field handles
		if ($config->event_custom_field)
		{
			$registry = new Registry();
			$registry->loadString($item->custom_fields);
			$data         = new stdClass();
			$data->params = $registry->toArray();
			$form         = JForm::getInstance('pmform', JPATH_ROOT . '/components/com_eventbooking/fields.xml', array(), false, '//config');
			$form->bind($data);
			$this->form = $form;
		}

		$options   = [];
		$options[] = JHtml::_('select.option', '', JText::_('EB_ALL_PAYMENT_METHODS'), 'id', 'title');

		$query->clear()
			->select('id, title')
			->from('#__eb_payment_plugins')
			->where('published=1');
		$db->setQuery($query);
		$this->lists['payment_methods'] = JHtml::_('select.genericlist', array_merge($options, $db->loadObjectList()), 'payment_methods[]', ' class="inputbox" multiple="multiple" ', 'id', 'title', explode(',', $item->payment_methods));

		$currencies = require JPATH_ROOT . '/components/com_eventbooking/helper/currencies.php';
		ksort($currencies);
		$options   = [];
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_CURRENCY'));

		foreach ($currencies as $code => $title)
		{
			$options[] = JHtml::_('select.option', $code, $title);
		}

		$this->lists['currency_code'] = JHtml::_('select.genericlist', $options, 'currency_code', '', 'value', 'text', $item->currency_code);

		$this->lists['discount_groups'] = JHtml::_('access.usergroup', 'discount_groups[]', explode(',', $item->discount_groups),
			' multiple="multiple" size="6" ', false);

		$this->lists['available_attachment'] = EventbookingHelper::attachmentList(explode('|', $item->attachment), $config);

		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('JNO'));
		$options[] = JHtml::_('select.option', 1, JText::_('JYES'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_USE_GLOBAL'));

		$this->lists['enable_terms_and_conditions'] = JHtml::_('select.genericlist', $options, 'enable_terms_and_conditions', ' class="inputbox" ', 'value', 'text', $item->enable_terms_and_conditions);

		$options   = [];
		$options[] = JHtml::_('select.option', '', JText::_('EB_USE_GLOBAL'));
		$options[] = JHtml::_('select.option', 0, JText::_('JNO'));
		$options[] = JHtml::_('select.option', 1, JText::_('JYES'));

		$this->lists['prevent_duplicate_registration'] = JHtml::_('select.genericlist', $options, 'prevent_duplicate_registration', '', 'value', 'text', $item->prevent_duplicate_registration);
		$this->lists['collect_member_information']     = JHtml::_('select.genericlist', $options, 'collect_member_information', '', 'value', 'text', $item->collect_member_information);

		$options   = [];
		$options[] = JHtml::_('select.option', -1, JText::_('EB_USE_GLOBAL'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_ENABLE'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_ONLY_TO_ADMIN'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_ONLY_TO_REGISTRANT'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_DISABLE'));

		$this->lists['send_emails'] = JHtml::_('select.genericlist', $options, 'send_emails', ' class="inputbox"', 'value', 'text',
			$item->send_emails);

		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));

		$this->lists['free_event_registration_status'] = JHtml::_('select.genericlist', $options, 'free_event_registration_status', '', 'value', 'text', $item->id ? $item->free_event_registration_status : 1);

		$options   = [];
		$options[] = JHtml::_('select.option', '1', JText::_('EB_BEFORE'));
		$options[] = JHtml::_('select.option', '-1', JText::_('EB_AFTER'));

		$this->lists['send_first_reminder_time']  = JHtml::_('select.genericlist', $options, 'send_first_reminder_time', ' class="input-small" ', 'value', 'text',
			$item->send_first_reminder >= 0 ? 1 : -1);
		$this->lists['send_second_reminder_time'] = JHtml::_('select.genericlist', $options, 'send_second_reminder_time', ' class="input-small" ', 'value', 'text',
			$item->send_second_reminder >= 0 ? 1 : -1);

		$item->send_first_reminder  = abs($item->send_first_reminder);
		$item->send_second_reminder = abs($item->send_second_reminder);

		// Recurring settings
		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('EB_NO_REPEAT'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_DAILY'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_WEEKLY'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_MONHLY_BY_DAYS'));
		$options[] = JHtml::_('select.option', 4, JText::_('EB_MONHLY_BY_WEEKDAY'));

		$this->lists['recurring_type'] = JHtml::_('select.genericlist', $options, 'recurring_type', ' class="input-large" ', 'value', 'text', $item->recurring_type);


		#Plugin support
		JPluginHelper::importPlugin('eventbooking');
		$results = JFactory::getApplication()->triggerEvent('onEditEvent', array($item));


		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');
		$this->dateFormat       = str_replace('%', '', $this->datePickerFormat);
		$this->prices           = EventbookingHelperDatabase::getGroupRegistrationRates($item->id);
		$this->nullDate         = $nullDate;
		$this->config           = $config;
		$this->plugins          = $results;
	}
}