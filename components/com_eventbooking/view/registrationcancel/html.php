<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingViewRegistrationcancelHtml extends RADViewHtml
{
	public $hasModel = false;

	public function display()
	{
		$layout = $this->getLayout();

		if ($layout == 'confirmation')
		{
			$this->displayConfirmationForm();

			return;
		}

		$this->setLayout('default');

		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$id          = $this->input->getInt('id', 0);
		$query->select('a.*')
			->select($db->quoteName('b.title' . $fieldSuffix, 'event_title'))
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.id=' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (!$rowRegistrant)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('EB_INVALID_REGISTRATION_CODE'), 'error');
			$app->redirect(JUri::root(), 404);
		}

		if ($rowRegistrant->amount > 0)
		{
			if (strlen(trim(strip_tags($message->{'registration_cancel_message_paid' . $fieldSuffix}))))
			{
				$cancelMessage = $message->{'registration_cancel_message_paid' . $fieldSuffix};
			}
			else
			{
				$cancelMessage = $message->registration_cancel_message_paid;
			}
		}
		else
		{
			if (strlen(trim(strip_tags($message->{'registration_cancel_message_free' . $fieldSuffix}))))
			{
				$cancelMessage = $message->{'registration_cancel_message_free' . $fieldSuffix};
			}
			else
			{
				$cancelMessage = $message->registration_cancel_message_free;
			}
		}

		$cancelMessage = str_replace('[EVENT_TITLE]', $rowRegistrant->event_title, $cancelMessage);
		$this->message = $cancelMessage;

		parent::display();
	}

	/**
	 * Display confirm cancel registration form
	 */
	protected function displayConfirmationForm()
	{
		$message     = EventbookingHelper::getMessages();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'registration_cancel_confirmation_message' . $fieldSuffix}))
		{
			$this->message = $message->{'registration_cancel_confirmation_message' . $fieldSuffix};
		}
		else
		{
			$this->message = $message->registration_cancel_confirmation_message;
		}

		$this->registrationCode = $this->input->getString('cancel_code');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.event_date, b.cancel_before_date')
			->select($db->quoteName('b.title' . $fieldSuffix, 'event_title'))
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.registration_code = ' . $db->quote($this->registrationCode));
		$db->setQuery($query);
		$row = $db->loadObject();

		if (!$row)
		{
			JFactory::getApplication()->redirect(JUri::root(), JText::_('EB_INVALID_REGISTRATION_CODE'));
		}

		// Cancel before date is passed, user is not allowed to cancel registration anymore
		if (!EventbookingHelperRegistration::canCancelRegistrationNow($row))
		{
			if ($row->cancel_before_date !== JFactory::getDbo()->getNullDate())
			{
				$cancelBeforeDate = JFactory::getDate($row->cancel_before_date, JFactory::getConfig()->get('offset'));
			}
			else
			{
				$cancelBeforeDate = JFactory::getDate($row->event_date, JFactory::getConfig()->get('offset'));
			}

			echo JText::sprintf('EB_CANCEL_DATE_PASSED', $cancelBeforeDate->format($config->event_date_format, true));

			return;
		}

		$query->clear()
			->select('*')
			->from('#__eb_events')
			->where('id = ' . (int) $row->event_id);

		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('title'), $fieldSuffix);
		}

		$db->setQuery($query);
		$rowEvent = $db->loadObject();

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$replaces = EventbookingHelper::callOverridableHelperMethod('Registration', 'buildTags', [$row, $form, $rowEvent, $config], 'Helper');

		foreach ($replaces as $key => $value)
		{
			$this->message = str_ireplace("[$key]", $value, $this->message);
		}

		parent::display();
	}
}
