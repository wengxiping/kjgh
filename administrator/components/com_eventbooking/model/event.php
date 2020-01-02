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

class EventbookingModelEvent extends EventbookingModelCommonEvent
{
	/**
	 * @param $file
	 *
	 * @return int
	 * @throws Exception
	 */
	public function import($file)
	{
		$events = EventbookingHelperData::getDataFromFile($file);

		if (!count($events))
		{
			return 0;
		}

		$config = EventbookingHelper::getConfig();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id, name')
			->from('#__eb_categories');
		$db->setQuery($query);
		$categories = $db->loadObjectList('name');

		$query->clear()
			->select('id, name')
			->from('#__eb_locations');
		$db->setQuery($query);
		$locations = $db->loadObjectList('name');

		$imported    = 0;
		$eventFields = array();

		if ($config->event_custom_field)
		{
			$xml    = JFactory::getXML(JPATH_ROOT . '/components/com_eventbooking/fields.xml');
			$fields = $xml->fields->fieldset->children();

			foreach ($fields as $field)
			{
				$eventFields[] = (string) $field->attributes()->name;
			}
		}

		foreach ($events as $event)
		{
			if (empty($event['id'])
				&& (empty($event['title']) || empty($event['category']) || empty($event['event_date'])))
			{
				continue;
			}

			/* @var EventbookingTableEvent $row */
			$row = $this->getTable();

			if (!empty($event['id']))
			{
				$row->load($event['id']);
			}

			if(isset($event['location']))
			{
				if (is_numeric($event['location']))
				{
					$event['location_id'] = $event['location'];
				}
				else
				{
					$locationName         = trim($event['location']);
					$event['location_id'] = isset($locations[$locationName]) ? $locations[$locationName]->id : 0;
				}
			}

			if (!empty($event['image']) && JFile::exists(JPATH_ROOT . '/' . $event['image']))
			{
				$fileName = JFile::makeSafe(basename($event['image']));

				if (!JFile::exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $fileName))
				{
					$imagePath = JPATH_ROOT . '/media/com_eventbooking/images/' . $fileName;
					$thumbPath = JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $fileName;

					JFile::copy(JPATH_ROOT . '/' . $event['image'], $imagePath);

					$image = new JImage($imagePath);
					$image->cropResize($config->thumb_width, $config->thumb_height, false)
						->toFile($thumbPath);

					$event['thumb'] = $fileName;
				}
				else
				{
					if (!$row->thumb)
					{
						$event['thumb'] = $fileName;
					}
				}
			}

			if (empty($event['id']) && !isset($event['access']))
			{
				$event['access'] = $config->get('access', 1);
			}

			if (empty($event['id']) && !isset($event['registration_access']))
			{
				$event['registration_access'] = $config->get('registration_access', 1);
			}

			$row->bind($event, array('id'));

			if (!empty($eventFields))
			{
				$params = new Registry();
				$params->loadString($row->custom_fields, 'JSON');

				foreach ($eventFields as $fieldName)
				{
					$params->set($fieldName, isset($event[$fieldName]) ? $event[$fieldName] : '');
				}

				$row->custom_fields = $params->toString();
			}

			if (empty($event['id']) || isset($event['category']))
			{
				// Main category
				if (is_numeric($event['category']))
				{
					$categoryId = $event['category'];
				}
				else
				{
					$categoryName = trim($event['category']);
					$categoryId   = isset($categories[$categoryName]) ? $categories[$categoryName]->id : 0;
				}

				$row->main_category_id = $categoryId;
			}

			$this->prepareTable($row, 'save');
			$row->store();
			$eventId = $row->id;

			if (!empty($event['id']) && isset($event['category']))
			{
				$query->clear()
					->delete('#__eb_event_categories')
					->where('event_id = ' . (int) $event['id'])
					->where('main_category = 1');
				$db->setQuery($query);
				$db->execute();
			}

			if (!empty($event['id']) && isset($event['additional_categories']))
			{
				$query->clear()
					->delete('#__eb_event_categories')
					->where('event_id = ' . (int) $event['id'])
					->where('main_category = 0');
				$db->setQuery($query);
				$db->execute();
			}

			if (!empty($categoryId))
			{
				$query->clear()
					->insert('#__eb_event_categories')
					->columns('event_id, category_id, main_category')
					->values("$eventId, $categoryId, 1");
				$db->setQuery($query);
				$db->execute();
			}

			$eventCategories = isset($event['additional_categories']) ? $event['additional_categories'] : '';
			$eventCategories = explode(' | ', $eventCategories);

			for ($i = 0, $n = count($eventCategories); $i < $n; $i++)
			{
				$category = trim($eventCategories[$i]);

				if ($category && isset($categories[$category]))
				{
					$categoryId = $categories[$category]->id;
					$query->clear()
						->insert('#__eb_event_categories')
						->columns('event_id, category_id, main_category')
						->values("$eventId, $categoryId, 0");
					$db->setQuery($query);
					$db->execute();
				}
			}

			$imported++;
		}

		return $imported;
	}
}
