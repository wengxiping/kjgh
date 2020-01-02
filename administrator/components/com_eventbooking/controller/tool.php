<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingControllerTool extends RADController
{
	/**
	 * Reset the urls table
	 */
	public function reset_urls()
	{
		JFactory::getDbo()->truncateTable('#__eb_urls');
		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard', JText::_('Urls have successfully reset'));
	}

	/**
	 * Setup multilingual fields
	 */
	public function setup_multilingual_fields()
	{
		EventbookingHelper::setupMultilingual();
	}

	/**
	 * Remove multilingual fields
	 */
	public function remove_multilingual()
	{
		$db = JFactory::getDbo();

		$categoryTableFields = array_keys($db->getTableColumns('#__eb_categories'));
		$eventTableFields    = array_keys($db->getTableColumns('#__eb_events'));
		$fieldTableFields    = array_keys($db->getTableColumns('#__eb_fields'));
		$locationTableFields = array_keys($db->getTableColumns('#__eb_locations'));

		$suffixes = ['_fr', '_vi', '_pt', '_es-co', '_es', '_ms', '_ko', '_ja'];

		$fields = [
			'name',
			'alias',
			'page_title',
			'page_heading',
			'meta_keywords',
			'meta_description',
			'description',
		];

		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;


				if (in_array($fieldName, $categoryTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_categories` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}

		$fields = [
			'title',
			'alias',
			'page_title',
			'page_heading',
			'meta_keywords',
			'meta_description',
			'price_text',
			'registration_handle_url',
			'short_description',
			'description',
			'registration_form_message',
			'registration_form_message_group',
			'admin_email_body',
			'user_email_body',
			'user_email_body_offline',
			'thanks_message',
			'thanks_message_offline',
			'registration_approved_email_body',
			'invoice_format',
			'ticket_layout',
		];


		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;

				if (in_array($fieldName, $eventTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_events` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}


		$fields = [
			'title',
			'description',
			'values',
			'default_values',
			'depend_on_options',
			'place_holder',
		];

		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;

				if (in_array($fieldName, $fieldTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_fields` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}

		$fields = [
			'name',
			'alias',
			'description',
		];

		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;

				if (in_array($fieldName, $locationTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_locations` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}

	}

	/**
	 * Add more decimal number to price related fields
	 */
	public function add_more_decimal_numbers()
	{
		$db = JFactory::getDbo();

		$fieldsToChange = [
			'#__eb_events'             => ['individual_price', 'discount', 'early_bird_discount_amount', 'late_fee_amount', 'tax_rate'],
			'#__eb_event_group_prices' => ['price'],
			'#__eb_coupons'            => 'discount',
		];

		foreach ($fieldsToChange as $table => $fields)
		{
			$table = $db->quoteName($table);

			foreach ($fields as $field)
			{
				$field = $db->quoteName($field);
				$sql   = "ALTER TABLE  $table  CHANGE  $field $field  DECIMAL (15,8)";
				$db->setQuery($sql)
					->execute();
			}
		}

		echo 'Done';
	}

	/**
	 * Method to allow sharing language files for Events Booking
	 */
	public function share_translation()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('lang_code')
			->from('#__languages')
			->where('published = 1')
			->where('lang_code != "en-GB"')
			->order('ordering');
		$db->setQuery($query);
		$languages = $db->loadObjectList();

		if (count($languages))
		{
			$mailer   = JFactory::getMailer();
			$jConfig  = JFactory::getConfig();
			$mailFrom = $jConfig->get('mailfrom');
			$fromName = $jConfig->get('fromname');
			$mailer->setSender([$mailFrom, $fromName]);
			$mailer->addRecipient('tuanpn@joomdonation.com');
			$mailer->setSubject('Language Packages for Events Booking shared by ' . JUri::root());
			$mailer->setBody('Dear Tuan \n. I am happy to share my language packages for Events Booking.\n Enjoy!');
			foreach ($languages as $language)
			{
				$tag = $language->lang_code;
				if (file_exists(JPATH_ROOT . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini'))
				{
					$mailer->addAttachment(JPATH_ROOT . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini', $tag . '.com_eventbooking.ini');
				}

				if (file_exists(JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini'))
				{
					echo JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini';
					$mailer->addAttachment(JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini', 'admin.' . $tag . '.com_eventbooking.ini');
				}
			}

			require_once JPATH_COMPONENT . '/libraries/vendor/dbexporter/dumper.php';

			$tables = [$db->replacePrefix('#__eb_fields'), $db->replacePrefix('#__eb_messages')];

			try
			{

				$sqlFile = $tag . '.com_eventbooking.sql';
				$options = [
					'host'           => $jConfig->get('host'),
					'username'       => $jConfig->get('user'),
					'password'       => $jConfig->get('password'),
					'db_name'        => $jConfig->get('db'),
					'include_tables' => $tables,
				];
				$dumper  = Shuttle_Dumper::create($options);
				$dumper->dump(JPATH_ROOT . '/tmp/' . $sqlFile);

				$mailer->addAttachment(JPATH_ROOT . '/tmp/' . $sqlFile, $sqlFile);

			}
			catch (Exception $e)
			{
				//Do nothing
			}

			$mailer->Send();

			$msg = 'Thanks so much for sharing your language files to Events Booking Community';
		}
		else
		{
			$msg = 'Thanks so willing to share your language files to Events Booking Community. However, you don"t have any none English langauge file to share';
		}

		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard', $msg);
	}

	/**
	 * Method to make a given field search and sortable easier
	 */
	public function make_field_search_sort_able()
	{
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$fieldId = $this->input->getInt('field_id');

		$query->select('*')
			->from('#__eb_fields')
			->where('id = ' . (int) $fieldId);
		$db->setQuery($query);
		$field = $db->loadObject();

		if (!$field)
		{
			throw new Exception('The field does not exist');
		}

		// Add new field to #__eb_registrants
		$fields = array_keys($db->getTableColumns('#__eb_registrants'));

		if (!in_array($field->name, $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `$field->name` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql);
			$db->execute();

			// Mark the field as searchable
			$query->clear()
				->update('#__eb_fields')
				->set('is_searchable = 1')
				->where('id = ' . (int) $fieldId);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear()
			->select('*')
			->from('#__eb_field_values')
			->where('field_id = ' . $fieldId);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$fieldName = $db->quoteName($field->name);

		foreach ($rows as $row)
		{
			$query->clear()
				->update('#__eb_registrants')
				->set($fieldName . ' = ' . $db->quote($row->field_value))
				->where('id = ' . $row->registrant_id);
			$db->setQuery($query);
			$db->execute();
		}

		echo 'Done !';
	}

	/**
	 * Resize large event image to the given size
	 */
	public function resize_large_images()
	{
		$config = EventbookingHelper::getConfig();
		$width  = (int) $config->large_image_width ?: 800;
		$height = (int) $config->large_image_height ?: 600;

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('image')
			->from('#__eb_events')
			->where('published = 1')
			->order('id DESC')
			->where('LENGTH(image) > 0');
		$db->setQuery($query);
		$images = $db->loadColumn();

		foreach ($images as $image)
		{
			$path = JPATH_ROOT . '/' . $image;

			if (!file_exists($path))
			{
				continue;
			}

			EventbookingHelper::resizeImage($path, $path, $width, $height);
		}
	}

	/**
	 * Resize large event image to the given size
	 */
	public function resize_thumb_images()
	{
		$config = EventbookingHelper::getConfig();
		$width  = (int) $config->thumb_width ?: 200;
		$height = (int) $config->thumb_height ?: 200;

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('image')
			->from('#__eb_events')
			->where('published = 1')
			->order('id DESC')
			->where('LENGTH(image) > 0');
		$db->setQuery($query);
		$images = $db->loadColumn();

		foreach ($images as $image)
		{
			$path = JPATH_ROOT . '/' . $image;

			if (!file_exists($path))
			{
				continue;
			}

			$fileName  = basename($image);
			$thumbPath = JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $fileName;

			EventbookingHelper::resizeImage($path, $thumbPath, $width, $height);
		}
	}


	/**
	 * Fix "Row size too large" issue
	 */
	public function fix_row_size()
	{
		$db = JFactory::getDbo();
		$db->setQuery('ALTER TABLE `#__eb_events` ENGINE = MYISAM ROW_FORMAT = DYNAMIC');
		$db->execute();
	}

	/**
	 * Method for finding menu linked to the extension
	 */
	public function find_menus()
	{
		$component = JComponentHelper::getComponent('com_eventbooking');
		$menus     = JFactory::getApplication()->getMenu('site');
		$items     = $menus->getItems('component_id', $component->id);
		?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Alias</th>
                <th>Link</th>
                <th>Menu</th>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ($items as $item)
			{
				?>
                <tr>
                    <td>
						<?php echo $item->id; ?>
                    </td>
                    <td><?php echo $item->title; ?></td>
                    <td><?php echo $item->alias; ?></td>
                    <td><?php echo $item->link ?></td>
                    <td><?php echo $item->menutype; ?></td>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>
		<?php
	}

	/**
	 * The second option to fix row size
	 */
	public function fix_row_size2()
	{
		$db        = JFactory::getDbo();
		$languages = EventbookingHelper::getLanguages();

		if (count($languages))
		{
			$categoryTableFields = array_keys($db->getTableColumns('#__eb_categories'));
			$eventTableFields    = array_keys($db->getTableColumns('#__eb_events'));
			$fieldTableFields    = array_keys($db->getTableColumns('#__eb_fields'));
			$locationTableFields = array_keys($db->getTableColumns('#__eb_locations'));

			foreach ($languages as $language)
			{
				$prefix = $language->sef;

				$fields = [
					'name',
					'alias',
					'page_title',
					'page_heading',
					'meta_keywords',
					'meta_description',
					'description',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $categoryTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_categories` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_categories` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_categories'));
					}
				}


				$fields = [
					'title',
					'alias',
					'page_title',
					'page_heading',
					'meta_keywords',
					'meta_description',
					'price_text',
					'registration_handle_url',
					'short_description',
					'description',
					'registration_form_message',
					'registration_form_message_group',
					'user_email_body',
					'user_email_body_offline',
					'thanks_message',
					'thanks_message_offline',
					'registration_approved_email_body',
					'invoice_format',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $eventTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_events` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_events'));
					}
				}


				$fields = [
					'title',
					'description',
					'values',
					'default_values',
					'depend_on_options',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $fieldTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_fields` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_fields` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_fields'));
					}
				}

				$fields = [
					'name',
					'alias',
					'description',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $locationTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_locations` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_locations` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_locations'));
					}
				}
			}
		}
	}
}
