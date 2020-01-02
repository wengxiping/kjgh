<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

class EventbookingModelFullcalendar extends RADModel
{
	/**
	 * Fields which will be returned from SQL query
	 *
	 * @var array
	 */
	public static $fields = array(
		'a.id',
		'a.main_category_id',
		'a.parent_id',
		'a.location_id',
		'a.title',
		'a.event_type',
		'a.event_date',
		'a.event_end_date',
		'a.short_description',
		'a.description',
		'a.access',
		'a.registration_access',
		'a.individual_price',
		'a.price_text',
		'a.event_capacity',
		'a.created_by',
		'a.cut_off_date',
		'a.registration_type',
		'a.min_group_number',
		'a.discount_type',
		'a.discount',
		'a.early_bird_discount_type',
		'a.early_bird_discount_date',
		'a.early_bird_discount_amount',
		'a.enable_cancel_registration',
		'a.cancel_before_date',
		'a.params',
		'a.published',
		'a.custom_fields',
		'a.discount_groups',
		'a.discount_amounts',
		'a.registration_start_date',
		'a.registration_handle_url',
		'a.fixed_group_price',
		'a.attachment',
		'a.late_fee_type',
		'a.late_fee_date',
		'a.late_fee_amount',
		'a.event_password',
		'a.currency_code',
		'a.currency_symbol',
		'a.thumb',
		'a.image',
		'a.language',
		'a.alias',
		'a.tax_rate',
		'a.featured',
		'a.has_multiple_ticket_types',
		'a.activate_waiting_list',
		'a.collect_member_information',
		'a.prevent_duplicate_registration',
	);

	/**
	 * The view parameter
	 *
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->state->insert('id', 'int', 0)
			->insert('start', 'string', '')
			->insert('end', 'string');

		$this->params = EventbookingHelper::getViewParams(JFactory::getApplication()->getMenu()->getActive(), array('fullcalendar'));
	}

	/**
	 * Get monthly events
	 *
	 * @return array|mixed
	 */
	public function getData()
	{
		$config             = EventbookingHelper::getConfig();
		$db                 = $this->getDbo();
		$query              = $db->getQuery(true);
		$date               = JFactory::getDate('now', JFactory::getConfig()->get('offset'));
		$params             = $this->params;
		$categoryIds        = $params->get('category_ids');
		$excludeCategoryIds = $params->get('exclude_category_ids');
		$locationId         = (int) $params->get('location_id');

		$excludeCategoryIds = array_filter(ArrayHelper::toInteger($excludeCategoryIds));
		$categoryIds        = array_filter(ArrayHelper::toInteger($categoryIds));
		$year               = $params->get('default_year') ?: $date->format('Y');
		$month              = $params->get('default_month') ?: $date->format('m');

		// Calculate start date and end date of the given month
		if (EventbookingHelper::isValidDate($this->state->start))
		{
			$startDate = $this->state->start;
		}
		else
		{
			$date->setDate($year, $month, 1);
			$date->setTime(0, 0, 0);
			$startDate = $db->quote($date->toSql(true));
		}

		if (EventbookingHelper::isValidDate($this->state->end))
		{
			$endDate = $this->state->end;
		}
		else
		{
			$date->setDate($year, $month, $date->daysinmonth);
			$date->setTime(23, 59, 59);
			$endDate = $db->quote($date->toSql(true));
		}

		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$currentDate = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());

		$query->select(static::$fields)
			->select("DATEDIFF(a.early_bird_discount_date, $currentDate) AS date_diff")
			->select("DATEDIFF(a.event_date, $currentDate) AS number_event_dates")
			->select("TIMESTAMPDIFF(MINUTE, a.late_fee_date, $currentDate) AS late_fee_date_diff")
			->select("TIMESTAMPDIFF(MINUTE, a.event_date, $currentDate) AS event_start_minutes")
			->select("TIMESTAMPDIFF(SECOND, a.registration_start_date, $currentDate) AS registration_start_minutes")
			->select("TIMESTAMPDIFF(MINUTE, a.cut_off_date, $currentDate) AS cut_off_minutes")
			->select('SUM(b.number_registrants) AS total_registrants')
			->select($db->quoteName(['a.event_date', 'a.event_end_date'], ['start', 'end']))
			->select($db->quoteName(['c.name' . $fieldSuffix, 'c.alias' . $fieldSuffix], ['location_name', 'location_alias']))
			->select('c.address AS location_address')
			->select($db->quoteName(['d.color_code', 'd.text_color']))
			->from('#__eb_events AS a')
			->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id ) AND b.group_id = 0 AND (b.published=1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3)))')
			->leftJoin('#__eb_locations AS c ON a.location_id = c.id ')
			->innerJoin('#__eb_categories as d ON a.main_category_id = d.id')
			->where('a.published = 1')
			->where('a.hidden = 0')
			->where('a.access in (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')')
			->group('a.id')
			->order('a.event_date ASC, a.ordering ASC');

		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('a.title', 'a.alias', 'a.short_description', 'a.price_text', 'a.registration_handle_url'), $fieldSuffix);
		}

		if ($this->params->get('hide_children_events', 0))
		{
			$query->where('a.parent_id = 0');
		}

		if ($categoryIds)
		{
			$query->where('a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $categoryIds) . '))');
		}

		if ($categoryId = $this->state->get('id'))
		{
			if ($config->show_events_from_all_children_categories)
			{
				$childrenCategories = EventbookingHelperData::getAllChildrenCategories($categoryId);
				$query->where(' a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $childrenCategories) . '))');
			}
			else
			{
				$query->where(' a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id = ' . $categoryId . ')');
			}
		}

		if ($categoryIds)
		{
			$query->where('a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $categoryIds) . '))');
		}

		if ($excludeCategoryIds)
		{
			$query->where('a.id NOT IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $excludeCategoryIds) . '))');
		}

		if ($locationId)
		{
			$query->where('a.location_id = ' . (int) $locationId);
		}

		$startDate = $db->quote($startDate);
		$endDate   = $db->quote($endDate);
		$query->where("`event_date` BETWEEN $startDate AND $endDate");

		$hidePastEventsParam = $this->params->get('hide_past_events', 2);

		if ($hidePastEventsParam == 1 || ($hidePastEventsParam == 2 && $config->hide_past_events))
		{
			$currentDate = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));

			if ($config->show_until_end_date)
			{
				$query->where('(DATE(a.event_date) >= ' . $currentDate . ' OR DATE(a.event_end_date) >= ' . $currentDate . ')');
			}
			else
			{
				$query->where('(DATE(a.event_date) >= ' . $currentDate . ' OR DATE(a.cut_off_date) >= ' . $currentDate . ')');
			}
		}

		// Handle publish up and publish down
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
