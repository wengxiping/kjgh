<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class plgEventbookingAttachments extends JPlugin
{
    /**
     * Application object.
     *
     * @var    JApplicationCms
     */
    protected $app;

    /**
     * Database object.
     *
     * @var    JDatabaseDriver
     */
    protected $db;

    /**
     * Render setting form
     *
     * @param EventbookingTableEvent $row
     *
     * @return array
     */
    public function onEditEvent($row)
    {
        if (!$this->canRun($row))
        {
            return;
        }

        return array('title' => JText::_('EB_ATTCHMENTS'),
            'form'  => $this->drawSettingForm($row),
        );
    }

    /**
     * Store setting into database, in this case, use params field of plans table
     *
     * @param EventbookingTableEvent $row
     * @param Boolean                $isNew true if create new plan, false if edit
     */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (empty($data['attachments_plugin_rendered']))
		{
			return;
		}

		$app               = JFactory::getApplication();
		$config            = EventbookingHelper::getConfig();
		$attachments       = $app->input->files->get('attachments', [], 'raw');
		$pathUpload        = JPATH_ROOT . '/media/com_eventbooking';
		$allowedExtensions = $config->attachment_file_types;

		if (!$allowedExtensions)
		{
			$allowedExtensions = 'doc|docx|ppt|pptx|pdf|zip|rar|bmp|gif|jpg|jepg|png|swf|zipx';
		}

		$allowedExtensions = explode('|', $allowedExtensions);
		$allowedExtensions = array_map('trim', $allowedExtensions);
		$allowedExtensions = array_map('strtolower', $allowedExtensions);

		$attachmentFiles = [];

		foreach ($attachments as $file)
		{
			$attachment = $file['attachment_file'];

			if ($attachment['name'])
			{
				$fileName = $attachment['name'];
				$fileExt  = JFile::getExt($fileName);

				if (in_array(strtolower($fileExt), $allowedExtensions))
				{
					$fileName = JFile::makeSafe($fileName);

					if ($app->isClient('administrator'))
					{
						JFile::upload($attachment['tmp_name'], $pathUpload . '/' . $fileName, false, true);
					}
					else
					{
						JFile::upload($attachment['tmp_name'], $pathUpload . '/' . $fileName);
					}

					$attachmentFiles[] = $fileName;
				}
			}
		}

		if (isset($data['existing_attachments']))
		{
			$attachmentFiles = array_merge($attachmentFiles, array_filter($data['existing_attachments']));
		}

		$row->attachment = implode('|', $attachmentFiles);
		$row->store();
	}

    /**
     * Display form allows users to change settings on subscription plan add/edit screen
     *
     * @param EventbookingTableEvent $row
     *
     * @return string
     */
    private function drawSettingForm($row)
    {
    	$config = EventbookingHelper::getConfig();
	    $form                 = JForm::getInstance('attachments', JPATH_ROOT . '/plugins/eventbooking/attachments/form/attachments.xml');

	    // List existing attachments here
	    $layoutData = [
		    'existingAttachmentsList' => EventbookingHelper::attachmentList(explode('|', $row->attachment), $config, 'existing_attachments'),
		    'form'               => $form,
	    ];

	    return EventbookingHelperHtml::loadCommonLayout('plugins/attachments.php', $layoutData);
    }

	/**
	 * Method to get form xml definition. Change some field attributes base on Events Booking config and the event
	 * is being edited
	 *
	 * @param EventbookingTableEvent $row
	 *
	 * @return string
	 */
	private function getFormXML($row)
	{
		$config = EventbookingHelper::getConfig();
		// Set some default value for form xml base on component settings
		$xml = simplexml_load_file(JPATH_ROOT . '/plugins/eventbooking/dates/form/dates.xml');

		if ($this->app->isClient('site'))
		{
			// Remove fields which are disabled on submit event form
			$removeFields = [];

			if (!$config->get('fes_show_event_end_date', 1))
			{
				$removeFields[] = 'event_end_date';
			}

			if (!$config->get('fes_show_registration_start_date', 1))
			{
				$removeFields[] = 'registration_start_date';
			}

			if (!$config->get('fes_show_cut_off_date', 1))
			{
				$removeFields[] = 'cut_off_date';
			}

			if (!$config->get('fes_show_capacity', 1))
			{
				$removeFields[] = 'event_capacity';
			}

			for ($i = 0, $n = count($xml->field->form->field); $i < $n; $i++)
			{
				$field = $xml->field->form->field[$i];

				if (in_array($field['name'], $removeFields))
				{
					unset($xml->field->form->field[$i]);
				}
			}

			reset($xml->field->form->field);
		}

		$datePickerFormat = $config->get('date_field_format', '%Y-%m-%d') . ' %H:%M';

		foreach ($xml->field->form->children() as $field)
		{
			if ($field->getName() != 'field')
			{
				continue;
			}

			if ($field['type'] == 'calendar')
			{
				$field['format'] = $datePickerFormat;
			}

			if ($row->id > 0)
			{
				if ($field['name'] == 'location_id')
				{
					$field['default'] = $row->location_id;
				}

				if ($field['name'] == 'event_capacity')
				{
					$field['default'] = $row->event_capacity;
				}
			}
		}

		return $xml->asXML();
	}

    /**
     * Method to check to see whether the plugin should run
     *
     * @param EventbookingTableEvent $row
     *
     * @return bool
     */
    private function canRun($row)
    {
        if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
        {
            return false;
        }

        return true;
    }
}
