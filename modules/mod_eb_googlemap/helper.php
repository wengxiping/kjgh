<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class modEventBookingGoogleMapHelper
{
	/**
	 * @param Joomla\Registry\Registry $params
	 * @param int $Itemid
	 *
	 * @return array
	 */
	public static function loadAllLocations($params, $Itemid)
	{
		$user   = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		$categoryIds    = $params->get('category_ids');
		$locationIds    = $params->get('location_ids');
		$numberEvents   = $params->get('number_events', 10);
		$hidePastEvents = $params->get('hide_past_events', 1);
		$currentDate    = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));

		$nullDate    = $db->quote($db->getNullDate());
		$nowDate     = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$query->select('id, `lat`, `long`, address')
			->select($db->quoteName('name' . $fieldSuffix, 'name'))
			->from('#__eb_locations')
			->where('`lat` != ""')
			->where('`long` != ""')
			->where('published = 1');

		if ($locationIds)
		{
			$query->where('id IN (' . implode(',', $locationIds) . ')');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$query->clear()
			->select('a.id, a.title, a.main_category_id')
			->from('#__eb_events AS a')
			->order('a.event_date');

		foreach ($rows as $row)
		{
			$query->clear('where')
				->where('a.location_id = ' . $row->id)
				->where('a.published = 1')
				->where('a.hidden = 0')
				->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
				->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

			if ($categoryIds)
			{
				$query->where(' a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $categoryIds) . '))');
			}

			if ($hidePastEvents)
			{
				if ($config->show_until_end_date)
				{
					$query->where('(DATE(a.event_date) >= ' . $currentDate . ' OR DATE(a.event_end_date) >= ' . $currentDate . ')');
				}
				else
				{
					$query->where('(DATE(a.event_date) >= ' . $currentDate . ' OR DATE(a.cut_off_date) >= ' . $currentDate . ')');
				}
			}

			$query->order('a.event_date, a.ordering');

			$db->setQuery($query, 0, $numberEvents);
			$row->events = $db->loadObjectList();
		}

		// Remove locations without events
		$rows = array_filter($rows, function ($row) {
			return count($row->events) > 0;
		});

		reset($rows);

		foreach ($rows as $row)
		{
			$popupContent   = [];
			$popupContent[] = '<div class="row-fluid">';
			$popupContent[] = '<ul class="bubble">';
			$popupContent[] = '<li class="location_name"><h4>' . $row->name . '</h4></li>';
			$popupContent[] = '<p class="location_address">' . $row->address . '</p>';
			$popupContent[] = '</ul>';

			$popupContent[] = '<ul>';

			foreach ($row->events as $event)
			{
				$popupContent[] = '<li><h4>' . JHtml::link(JRoute::_(EventbookingHelperRoute::getEventRoute($event->id, $event->main_category_id, $Itemid)), addslashes($event->title)) . '</h4></li>';
			}

			$popupContent[] = '</ul>';

			$row->popupContent = addslashes(implode("", $popupContent));
		}

		return array_values($rows);
	}
}