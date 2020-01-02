<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

class EventbookingControllerScan extends EventbookingController
{
	/**
	 * Method to checkin registrant using EB QRCODE Checkin APP
	 *
	 * @return void
	 */
	public function eb_qrcode_checkin()
	{
		$config = EventbookingHelper::getConfig();

		if ($config->get('checkin_api_key') && $config->get('checkin_api_key') != $this->input->getString('api_key'))
		{
			$response = [
				'success' => false,
				'message' => JText::_('EB_INVALID_API_KEY'),
			];

			echo json_encode($response);

			$this->app->close();

			return;
		}

		$ticketCode = $this->input->getString('value');

		$success = false;
		$message = '';

		if ($ticketCode)
		{
			$db         = JFactory::getDbo();
			$query      = $db->getQuery(true);
			$ticketCode = $db->quote($ticketCode);
			$query->select('a.*, b.title AS event_title')
				->from('#__eb_registrants AS a')
				->innerJoin('#__eb_events AS b ON a.event_id = b.id')
				->where('(a.ticket_qrcode = ' . $ticketCode . ' OR a.ticket_code = ' . $ticketCode . ')');
			$db->setQuery($query);
			$rowRegistrant = $db->loadObject();

			if ($rowRegistrant->id)
			{
				/* @var EventbookingModelRegistrant $model */
				$model  = $this->getModel('Registrant');
				$result = $model->checkinRegistrant($rowRegistrant->id);

				switch ($result)
				{
					case 0:
						$message = JText::_('EB_INVALID_REGISTRATION_RECORD');
						break;
					case 1:
						$message = JText::_('EB_REGISTRANT_ALREADY_CHECKED_IN');
						break;
					case 3:
						$message = JText::_('EB_CHECKED_IN_FAIL_REGISTRATION_CANCELLED');
						break;
					case 2:
						$message = JText::_('EB_CHECKED_IN_SUCCESSFULLY');
						$success = true;
						break;
					case 4:
						$message = JText::_('EB_CHECKED_IN_REGISTRATION_PENDING');
						$success = true;
						break;
				}
			}
			else
			{
				$message = JText::_('EB_INVALID_TICKET_CODE');
			}
		}
		else
		{
			$message = JText::_('EB_TICKET_CODE_IS_EMPTY');
		}

		if (!empty($rowRegistrant))
		{
			$replaces = array(
				'FIRST_NAME'    => $rowRegistrant->first_name,
				'LAST_NAME'     => $rowRegistrant->last_name,
				'EVENT_TITLE'   => $rowRegistrant->event_title,
				'REGISTRANT_ID' => $rowRegistrant->id,
			);

			foreach ($replaces as $key => $value)
			{
				$message = str_replace('[' . $key . ']', $value, $message);
			}
		}

		$response = [
			'success' => $success,
			'message' => $message,
		];

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Checkin registrant using ICODY APP
	 *
	 * @return void
	 */
	public function icody()
	{
		$config = EventbookingHelper::getConfig();

		if ($config->get('checkin_api_key') && $config->get('checkin_api_key') != $this->input->getString('api_key'))
		{
			$title   = JText::_('EB_CHECKIN_FAILURE');
			$message = JText::_('EB_INVALID_API_KEY');

			echo static::getIcodyMessage($title, $message);

			$this->app->close();

			return;
		}

		$ticketCode = $this->input->getString('value');

		$success = false;
		$message = '';

		if ($ticketCode)
		{
			$db         = JFactory::getDbo();
			$query      = $db->getQuery(true);
			$ticketCode = $db->quote($ticketCode);
			$query->select('a.*, b.title AS event_title')
				->from('#__eb_registrants AS a')
				->innerJoin('#__eb_events AS b ON a.event_id = b.id')
				->where('(a.ticket_qrcode = ' . $ticketCode . ' OR a.ticket_code = ' . $ticketCode . ')');
			$db->setQuery($query);
			$rowRegistrant = $db->loadObject();

			if ($rowRegistrant->id)
			{
				/* @var EventbookingModelRegistrant $model */
				$model  = $this->getModel('Registrant');
				$result = $model->checkinRegistrant($rowRegistrant->id);

				switch ($result)
				{
					case 0:
						$message = JText::_('EB_INVALID_REGISTRATION_RECORD');
						break;
					case 1:
						$message = JText::_('EB_REGISTRANT_ALREADY_CHECKED_IN');
						break;
					case 3:
						$message = JText::_('EB_CHECKED_IN_FAIL_REGISTRATION_CANCELLED');
						break;
					case 2:
						$message = JText::_('EB_CHECKED_IN_SUCCESSFULLY');
						$success = true;
						break;
					case 4:
						$message = JText::_('EB_CHECKED_IN_REGISTRATION_PENDING');
						$success = true;
						break;
				}
			}
			else
			{
				$message = JText::_('EB_INVALID_TICKET_CODE');
			}
		}
		else
		{
			$message = JText::_('EB_TICKET_CODE_IS_EMPTY');
		}

		if ($success)
		{
			$title = JText::_('EB_CHECKIN_SUCCESS');
		}
		else
		{
			$title = JText::_('EB_CHECKIN_FAILURE');
		}

		if (!empty($rowRegistrant))
		{
			$replaces = array(
				'FIRST_NAME'    => $rowRegistrant->first_name,
				'LAST_NAME'     => $rowRegistrant->last_name,
				'EVENT_TITLE'   => $rowRegistrant->event_title,
				'REGISTRANT_ID' => $rowRegistrant->id,
			);

			foreach ($replaces as $key => $value)
			{
				$message = str_replace('[' . $key . ']', $value, $message);
				$title   = str_replace('[' . $key . ']', $value, $title);
			}
		}

		echo static::getIcodyMessage($title, $message);

		$this->app->close();
	}

	/**
	 * @param $title
	 * @param $msg
	 *
	 * @return string
	 */
	public static function getIcodyMessage($title, $msg)
	{
		$message = '<?xml version="1.0" encoding="UTF-8"?>';
		$message .= '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">';
		$message .= '<plist version="1.0">';
		$message .= '<dict>';
		$message .= '    <key>type</key>';
		$message .= '    <string>alert</string>';
		$message .= '    <key>title</key>';
		$message .= '   <string>' . $title . '</string>';
		$message .= '   <key>message</key>';
		$message .= '    <string>' . $msg . '</string>';
		$message .= '</dict>';
		$message .= '</plist>';

		return $message;
	}
}
