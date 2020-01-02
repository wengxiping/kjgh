<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingViewRegistrantsHtml extends RADViewList
{
	protected function prepareView()
	{
		parent::prepareView();

		$user = JFactory::getUser();

		if (!$user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$app = JFactory::getApplication();

			if ($user->get('guest'))
			{
				$this->requestLogin();
			}
			else
			{
				$app->enqueueMessage(JText::_('NOT_AUTHORIZED'), 'error');
				$app->redirect(JUri::root(), 403);
			}
		}

		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$config      = EventbookingHelper::getConfig();

		//Get list of events
		$query->select('id, event_date')
			->select($db->quoteName('title' . $fieldSuffix, 'title'))
			->from('#__eb_events')
			->where('published = 1')
			->order($config->sort_events_dropdown);

		if ($config->hide_past_events_from_events_dropdown)
		{
			$currentDate = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));
			$query->where('(DATE(event_date) >= ' . $currentDate . ' OR DATE(event_end_date) >= ' . $currentDate . ')');
		}

		if ($config->only_show_registrants_of_event_owner && !$user->authorise('core.admin', 'com_eventbooking'))
		{
			$query->where('created_by = ' . (int) $user->id);
		}

		$query->where('registration_type != 3');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$this->lists['filter_event_id'] = EventbookingHelperHtml::getEventsDropdown($rows, 'filter_event_id', 'onchange="submit();"', $this->state->filter_event_id);

		$options   = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_REGISTRATION_STATUS'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));

		if ($config->activate_waitinglist_feature)
		{
			$options[] = JHtml::_('select.option', 3, JText::_('EB_WAITING_LIST'));
		}

		$options[]                       = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));
		$this->lists['filter_published'] = JHtml::_('select.genericlist', $options, 'filter_published', ' class="input-medium" onchange="submit()" ', 'value', 'text',
			$this->state->filter_published);

		if ($config->activate_checkin_registrants)
		{
			$options                          = array();
			$options[]                        = JHtml::_('select.option', -1, JText::_('EB_CHECKIN_STATUS'));
			$options[]                        = JHtml::_('select.option', 1, JText::_('EB_CHECKED_IN'));
			$options[]                        = JHtml::_('select.option', 0, JText::_('EB_NOT_CHECKED_IN'));
			$this->lists['filter_checked_in'] = JHtml::_('select.genericlist', $options, 'filter_checked_in', ' class="input-medium" onchange="submit()" ', 'value', 'text',
				$this->state->filter_checked_in);
		}

		$query->clear()
			->select('id, name, title, fieldtype, is_core')
			->from('#__eb_fields')
			->where('published = 1')
			->where('show_on_registrants = 1')
			->where('name != "first_name"')
			->order('ordering');
		$db->setQuery($query);
		$fields = $db->loadObjectList('id');

		if (count($fields))
		{
			$this->fieldsData = $this->model->getFieldsData(array_keys($fields));
		}

		$this->findAndSetActiveMenuItem();

		$this->config     = $config;
		$this->coreFields = EventbookingHelperRegistration::getPublishedCoreFields();
		$this->fields     = $fields;

		$this->addToolbar();
	}

	/**
	 * Override addToolbar method to add custom csv export function
	 * @see RADViewList::addToolbar()
	 */
	protected function addToolbar()
	{
		JLoader::register('JToolbarHelper', JPATH_ADMINISTRATOR . '/includes/toolbar.php');

		if (!EventbookingHelperAcl::canDeleteRegistrant())
		{
			$this->hideButtons[] = 'delete';
		}

		parent::addToolbar();

		$config = EventbookingHelper::getConfig();

		if (!in_array('cancel_registrations', $this->hideButtons))
		{
			JToolbarHelper::custom('cancel_registrations', 'cancel', 'cancel', 'EB_CANCEL_REGISTRATIONS', true);
		}

		if ($config->activate_checkin_registrants)
		{
			if (!in_array('checkin_multiple_registrants', $this->hideButtons))
			{
				JToolbarHelper::checkin('checkin_multiple_registrants');
			}

			if (!in_array('check_out', $this->hideButtons))
			{
				JToolbarHelper::unpublish('check_out', JText::_('EB_CHECKOUT'), true);
			}
		}

		if (!in_array('batch_mail', $this->hideButtons))
		{
			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$bar   = JToolbar::getInstance('toolbar');
			$dhtml = $layout->render(array('title' => JText::_('EB_MASS_MAIL')));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if (!in_array('resend_email', $this->hideButtons))
		{
			JToolbarHelper::custom('resend_email', 'envelope', 'envelope', 'EB_RESEND_EMAIL', true);
		}

		if (!in_array('export', $this->hideButtons))
		{
			JToolbarHelper::custom('export', 'download', 'download', 'EB_EXPORT_REGISTRANTS', false);
		}

		if ($config->activate_certificate_feature)
		{
			if (!in_array('download_certificates', $this->hideButtons))
			{
				JToolbarHelper::custom('download_certificates', 'download', 'download', 'EB_DOWNLOAD_CERTIFICATES', true);
			}

			if (!in_array('send_certificates', $this->hideButtons))
			{
				JToolbarHelper::custom('send_certificates', 'envelope', 'envelope', 'EB_SEND_CERTIFICATES', true);
			}
		}

		if ($config->activate_waitinglist_feature)
		{
			if (!in_array('request_payment', $this->hideButtons))
			{
				JToolbarHelper::custom('request_payment', 'envelope', 'envelope', 'EB_REQUEST_PAYMENT', true);
			}
		}
	}
}
