<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class EventbookingModelList extends RADModelList
{
	/**
	 * Menu parameters
	 *
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * Fields which will be returned from SQL query
	 *
	 * @var array
	 */
	public static $fields = array(
		'tbl.id',
		'tbl.main_category_id',
		'tbl.location_id',
		'tbl.title',
		'tbl.event_type',
		'tbl.event_date',
		'tbl.event_end_date',
		'tbl.short_description',
		'tbl.description',
		'tbl.access',
		'tbl.registration_access',
		'tbl.individual_price',
		'tbl.price_text',
		'tbl.event_capacity',
		'tbl.created_by',
		'tbl.cut_off_date',
		'tbl.registration_type',
		'tbl.min_group_number',
		'tbl.discount_type',
		'tbl.discount',
		'tbl.early_bird_discount_type',
		'tbl.early_bird_discount_date',
		'tbl.early_bird_discount_amount',
		'tbl.enable_cancel_registration',
		'tbl.cancel_before_date',
		'tbl.params',
		'tbl.published',
		'tbl.custom_fields',
		'tbl.discount_groups',
		'tbl.discount_amounts',
		'tbl.registration_start_date',
		'tbl.registration_handle_url',
		'tbl.fixed_group_price',
		'tbl.attachment',
		'tbl.late_fee_type',
		'tbl.late_fee_date',
		'tbl.late_fee_amount',
		'tbl.event_password',
		'tbl.currency_code',
		'tbl.currency_symbol',
		'tbl.thumb',
		'tbl.image',
		'tbl.language',
		'tbl.alias',
		'tbl.tax_rate',
		'tbl.featured',
		'tbl.has_multiple_ticket_types',
		'tbl.activate_waiting_list',
		'tbl.collect_member_information',
		'tbl.prevent_duplicate_registration',
	);

	/**
	 * Fields which could be translated
	 *
	 * @var array
	 */
	protected static $translatableFields = array(
		'tbl.title',
		'tbl.short_description',
		'tbl.description',
		'tbl.price_text',
		'tbl.registration_handle_url',
	);

	/**
	 * The fields which can be used for soring events
	 *
	 * @var array
	 */
	public static $sortableFields = array(
		'tbl.event_date',
		'tbl.ordering',
		'tbl.title',
	);

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 */
	public function __construct($config = array())
	{
		$config['table']           = '#__eb_events';
		$config['remember_states'] = false;

		parent::__construct($config);

		$this->state->insert('id', 'int', 0);

		$ebConfig     = EventbookingHelper::getConfig();
		$this->params = EventbookingHelper::getViewParams(JFactory::getApplication()->getMenu()->getActive(), array($this->getName()));

		if ((int) $this->params->get('display_num'))
		{
			$this->setState('limit', (int) $this->params->get('display_num'));
		}
		elseif ((int ) $ebConfig->number_events)
		{
			$this->state->setDefault('limit', (int) $ebConfig->number_events);
		}

		if ($ebConfig->order_events == 2)
		{
			$this->state->set('filter_order', 'tbl.event_date');
		}
		else
		{
			$this->state->set('filter_order', 'tbl.ordering');
		}

		if ($ebConfig->order_direction == 'desc')
		{
			$this->state->set('filter_order_Dir', 'DESC');
		}
		else
		{
			$this->state->set('filter_order_Dir', 'ASC');
		}

		$this->state->insert('search', 'string', '')
			->insert('filter_duration', 'string', $this->params->get('default_duration_filter'))
			->insert('location_id', 'int', 0);
	}

	/**
	 * Method to get the current parent category
	 *
	 * @return stdClass|null
	 * @throws Exception
	 */
	public function getCategory()
	{
		if ($categoryId = (int) $this->getState('id'))
		{
			$category = EventbookingHelperDatabase::getCategory($categoryId);

			if ($category)
			{
				// Process content plugin for category description
				$category->description = JHtml::_('content.prepare', $category->description);
			}

			return $category;
		}

		return null;
	}

	/**
	 * Pre-process data before returning to the view for displaying
	 *
	 * @param array $rows
	 */
	protected function beforeReturnData($rows)
	{
		if (empty($rows))
		{
			return;
		}

		EventbookingHelper::callOverridableHelperMethod('Data', 'preProcessEventData', [$rows, 'list']);
	}

	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$db          = $this->getDbo();
		$config      = EventbookingHelper::getConfig();
		$currentDate = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$fieldsToSelect = static::$fields;

		if ($fieldSuffix)
		{
			$fieldsToSelect = array_diff($fieldsToSelect, static::$translatableFields);
		}

		$query->select($fieldsToSelect)
			->select("DATEDIFF(tbl.early_bird_discount_date, $currentDate) AS date_diff")
			->select("DATEDIFF(tbl.event_date, $currentDate) AS number_event_dates")
			->select("TIMESTAMPDIFF(MINUTE, tbl.late_fee_date, $currentDate) AS late_fee_date_diff")
			->select("TIMESTAMPDIFF(MINUTE, tbl.event_date, $currentDate) AS event_start_minutes")
			->select("TIMESTAMPDIFF(SECOND, tbl.registration_start_date, $currentDate) AS registration_start_minutes")
			->select("TIMESTAMPDIFF(MINUTE, tbl.cut_off_date, $currentDate) AS cut_off_minutes")
			->select($db->quoteName(['c.name' . $fieldSuffix, 'c.alias' . $fieldSuffix], ['location_name', 'location_alias']))
			->select('c.address AS location_address')
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants');

		if ($config->show_event_creator)
		{
			$query->select('u.name as creator_name');
		}

		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, static::$translatableFields, $fieldSuffix);
		}


		return $this;
	}

	/**
	 * Builds JOINS clauses for the query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(JDatabaseQuery $query)
	{
		$config = EventbookingHelper::getConfig();

		$query->leftJoin(
			'#__eb_registrants AS b ON (tbl.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')->leftJoin(
			'#__eb_locations AS c ON tbl.location_id = c.id ');

		if ($config->show_event_creator)
		{
			$query->leftJoin('#__users as u ON tbl.created_by = u.id');
		}

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		/* @var JApplicationSite $app */
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$state  = $this->getState();
		$config = EventbookingHelper::getConfig();

		$categoryIds        = $this->params->get('category_ids');
		$excludeCategoryIds = $this->params->get('exclude_category_ids');
		$locationIds        = $this->params->get('location_ids');

		$categoryIds        = array_filter(ArrayHelper::toInteger($categoryIds));
		$excludeCategoryIds = array_filter(ArrayHelper::toInteger($excludeCategoryIds));
		$locationIds        = array_filter(ArrayHelper::toInteger($locationIds));

		$this->applyChildrenEventsFilter($query);

		$query->where('tbl.published = 1')
			->where('tbl.hidden = 0')
			->where('tbl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

		$categoryId = $this->state->id ? $this->state->id : $this->state->category_id;

		if ($categoryId)
		{
			if ($config->show_events_from_all_children_categories)
			{
				$childrenCategories = EventbookingHelperData::getAllChildrenCategories($categoryId);
				$query->where(' tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $childrenCategories) . '))');
			}
			else
			{
				$query->where(' tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id = ' . $categoryId . ')');
			}
		}

		if ($categoryIds)
		{
			$query->where('tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $categoryIds) . '))');
		}

		if ($excludeCategoryIds)
		{
			$query->where('tbl.id NOT IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $excludeCategoryIds) . '))');
		}

		if ($locationIds)
		{
			$query->where('tbl.location_id IN (' . implode(',', $locationIds) . ')');
		}

		if ($state->location_id)
		{
			$query->where('tbl.location_id=' . $state->location_id);
		}

		if ($state->filter_city)
		{
			$query->where(' tbl.location_id IN (SELECT id FROM #__eb_locations WHERE LOWER(`city`) = ' . $db->quote(StringHelper::strtolower($state->filter_city)) . ')');
		}

		if ($state->filter_state)
		{
			$query->where(' tbl.location_id IN (SELECT id FROM #__eb_locations WHERE LOWER(`state`) = ' . $db->quote(StringHelper::strtolower($state->filter_state)) . ')');
		}

		if ($state->created_by)
		{
			$query->where('tbl.created_by =' . $state->created_by);
		}

		switch ($state->filter_duration)
		{
			case 'today':
				$date = JFactory::getDate('now', $config->get('offset'));
				$query->where('DATE(tbl.event_date) = ' . $db->quote($date->format('Y-m-d', true)));
				break;
			case 'tomorrow':
				$date = JFactory::getDate('tomorrow', $config->get('offset'));
				$query->where('DATE(tbl.event_date) = ' . $db->quote($date->format('Y-m-d', true)));
				break;
			case 'this_week':
				$date   = JFactory::getDate('now', $config->get('offset'));
				$monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
				$monday->setTime(0, 0, 0);
				$fromDate = $monday->toSql(true);
				$sunday   = clone $date->modify('Sunday this week');
				$sunday->setTime(23, 59, 59);
				$toDate = $sunday->toSql(true);
				$query->where('tbl.event_date >= ' . $db->quote($fromDate))
					->where('tbl.event_date <= ' . $db->quote($toDate));
				break;
			case 'next_week':
				$date   = JFactory::getDate('now', $config->get('offset'));
				$monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday this week' : 'Monday next week');
				$monday->setTime(0, 0, 0);
				$fromDate = $monday->toSql(true);
				$sunday   = clone $date->modify('Sunday next week');
				$sunday->setTime(23, 59, 59);
				$toDate = $sunday->toSql(true);
				$query->where('tbl.event_date >= ' . $db->quote($fromDate))
					->where('tbl.event_date <= ' . $db->quote($toDate));
				break;
			case 'this_month':
				$date = JFactory::getDate('first day of this month', $config->get('offset'));
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(true);
				$date     = JFactory::getDate('last day of this month', $config->get('offset'));
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(true);
				$query->where('tbl.event_date >= ' . $db->quote($fromDate))
					->where('tbl.event_date <= ' . $db->quote($toDate));
				break;
			case 'next_month':
				$date = JFactory::getDate('first day of next month', $config->get('offset'));
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(true);
				$date     = JFactory::getDate('last day of next month', $config->get('offset'));
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(true);
				$query->where('tbl.event_date >= ' . $db->quote($fromDate))
					->where('tbl.event_date <= ' . $db->quote($toDate));
				break;
		}

		$this->applyKeywordFilter($query);

		if ($app->getLanguageFilter())
		{
			$query->where('tbl.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ', "")');
		}

		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$query->where('(tbl.publish_up = ' . $nullDate . ' OR tbl.publish_up <= ' . $nowDate . ')')
			->where('(tbl.publish_down = ' . $nullDate . ' OR tbl.publish_down >= ' . $nowDate . ')');


		if ($fieldSuffix = EventbookingHelper::getFieldSuffix())
		{
			$query->where('LENGTH(' . $db->quoteName('tbl.title' . $fieldSuffix) . ') > 0');
		}

		return $this;
	}

	/**
	 * Builds a GROUP BY clause for the query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryGroup(JDatabaseQuery $query)
	{
		$query->group('tbl.id');

		return $this;
	}

	/**
	 * Builds a generic ORDER BY clause based on the model's state
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryOrder(JDatabaseQuery $query)
	{
		$config = EventbookingHelper::getConfig();

		$sort = $this->state->filter_order;

		if (!in_array($sort, static::$sortableFields))
		{
			$sort = 'tbl.event_date';
		}

		$direction = strtoupper($this->state->filter_order_Dir);

		if (!in_array($direction, ['ASC', 'DESC']))
		{
			$direction = '';
		}

		// Display featured events at the top if configured
		if ($config->display_featured_events_on_top)
		{
			$query->order('tbl.featured DESC');
		}

		if ($sort)
		{
			$query->order(trim($sort . ' ' . $direction));
		}

		return $this;
	}

	/**
	 * Method to apply hide past events filter
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return void
	 */
	protected function applyHidePastEventsFilter(JDatabaseQuery $query)
	{
		$db          = $this->getDbo();
		$config      = EventbookingHelper::getConfig();
		$currentDate = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));

		if ($config->show_children_events_under_parent_event)
		{
			if ($config->show_until_end_date)
			{
				$query->where('(DATE(tbl.event_date) >= ' . $currentDate . ' OR DATE(tbl.event_end_date) >= ' . $currentDate . ' OR DATE(tbl.max_end_date) >= ' . $currentDate . ')');
			}
			else
			{
				$query->where('(DATE(tbl.event_date) >= ' . $currentDate . ' OR DATE(tbl.cut_off_date) >= ' . $currentDate . ' OR DATE(tbl.max_end_date) >= ' . $currentDate . ')');
			}
		}
		else
		{
			if ($config->show_until_end_date)
			{
				$query->where('(DATE(tbl.event_date) >= ' . $currentDate . ' OR DATE(tbl.event_end_date) >= ' . $currentDate . ')');
			}
			else
			{
				$query->where('(DATE(tbl.event_date) >= ' . $currentDate . ' OR DATE(tbl.cut_off_date) >= ' . $currentDate . ')');
			}
		}
	}

	/**
	 * Method to apply keyword filter, make it easier to customize keyword search behavior
	 *
	 * @param JDatabaseQuery $query
	 */
	protected function applyKeywordFilter(JDatabaseQuery $query)
	{
		if (!$this->state->search)
		{
			return;
		}

		$db          = $this->getDbo();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		if ($config->get('search_events', 'exact') == 'exact')
		{
			$search = $db->quote('%' . $db->escape($this->state->search, true) . '%', false);
			$query->where("(LOWER(" . $db->quoteName('tbl.title' . $fieldSuffix) . ") LIKE $search OR LOWER(" . $db->quoteName('tbl.short_description' . $fieldSuffix) . ") LIKE $search OR LOWER(" . $db->quoteName('tbl.description' . $fieldSuffix) . ") LIKE $search)");
		}
		else
		{
			$words = explode(' ', $this->state->search);

			$wheres = array();

			foreach ($words as $word)
			{
				$word     = $db->quote('%' . $db->escape($word, true) . '%', false);
				$wheres[] = 'LOWER(' . $db->quoteName('tbl.title' . $fieldSuffix) . ') LIKE LOWER(' . $word . ')';
				$wheres[] = 'LOWER(' . $db->quoteName('tbl.short_description' . $fieldSuffix) . ') LIKE LOWER(' . $word . ')';
				$wheres[] = 'LOWER(' . $db->quoteName('tbl.description' . $fieldSuffix) . ') LIKE LOWER(' . $word . ')';
			}

			$query->where('(' . implode(' OR ', $wheres) . ')');
		}

		if ($this->params->get('only_show_featured_events'))
		{
			$query->where('tbl.featured = 1');
		}
	}

	/**
	 * Method to children events filter
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return void
	 */
	protected function applyChildrenEventsFilter($query)
	{
		$config = EventbookingHelper::getConfig();

		if ($this->params->get('hide_children_events', 0) || $config->show_children_events_under_parent_event)
		{
			$query->where('tbl.parent_id = 0');
		}
	}
}
