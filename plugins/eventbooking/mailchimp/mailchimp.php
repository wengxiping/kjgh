<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use DrewM\MailChimp\MailChimp;

class plgEventBookingMailchimp extends JPlugin
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
	 * Constructor.
	 *
	 * @param object   $subject
	 * @param Registry $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		JFactory::getLanguage()->load('plg_eventbooking_mailchimp', JPATH_ADMINISTRATOR);
	}

	/**
	 * Render settings form
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

		ob_start();
		$this->drawSettingForm($row);

		return array('title' => JText::_('PLG_EB_MAILCHIMP_SETTINGS'),
		             'form'  => ob_get_clean(),
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
		if (!$this->canRun($row))
		{
			return;
		}
		
		$params = new Registry($row->params);
		$params->set('mailchimp_list_ids', implode(',', $data['mailchimp_list_ids']));
		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * Run when registration record stored to database
	 *
	 * @param EventbookingTableRegistrant $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		$config = EventbookingHelper::getConfig();

		// In case subscriber doesn't want to subscribe to newsleter, stop
		if ($config->show_subscribe_newsletter_checkbox && empty($row->subscribe_newsletter))
		{
			return;
		}

		$db       = $this->db;
		$query    = $db->getQuery(true);
		$listIds  = [];
		$eventIds = [];
		$config   = EventbookingHelper::getConfig();
		$event    = JTable::getInstance('EventBooking', 'Event');

		if ($config->multiple_booking)
		{
			$query->clear()
				->select('event_id')
				->from('#__eb_registrants')
				->where('id = ' . $row->id . ' OR cart_id = ' . $row->id);
			$eventIds = $db->loadColumn();
		}
		else
		{
			$eventIds[] = $row->event_id;
		}

		foreach ($eventIds as $eventId)
		{
			$event->load($eventId);
			$params         = new Registry($event->params);
			$mailingListIds = $params->get('mailchimp_list_ids', '');

			if (empty($mailingListIds))
			{
				$mailingListIds = $this->params->get('default_list_ids');
			}

			if ($mailingListIds)
			{
				$listIds = array_merge($listIds, explode(',', $mailingListIds));
			}
		}

		$listIds = array_filter($listIds);

		if (empty($listIds))
		{
			return;
		}

		$this->subscribeToMailchimpMailingLists($row, $listIds);

		if ($row->is_group_billing && $this->params->get('add_group_members_to_newsletter'))
		{
			$query->clear()
				->select('user_id, first_name, last_name, email')
				->from('#__eb_registrants')
				->where('group_id = ' . (int) $row->id);
			$db->setQuery($query);
			$groupMembers = $db->loadObjectList();

			foreach ($groupMembers as $groupMember)
			{
				$this->subscribeToMailchimpMailingLists($groupMember, $listIds);
			}
		}
	}


	/**
	 * @param EventbookingTableRegistrant $row
	 * @param array                       $listIds
	 */
	private function subscribeToMailchimpMailingLists($row, $listIds)
	{
		if (!JMailHelper::isEmailAddress($row->email))
		{
			return;
		}

		require_once dirname(__FILE__) . '/api/MailChimp.php';

		try
		{
			$mailchimp = new MailChimp($this->params->get('api_key'));
		}
		catch (Exception $e)
		{
			$this->logError([], $e->getMessage());

			return;
		}

		if ($this->params->get('double_optin'))
		{
			$status = 'pending';
		}
		else
		{
			$status = 'subscribed';
		}

		foreach ($listIds as $listId)
		{
			$data = [
				'id'              => $listId,
				'email_address'   => $row->email,
				'merge_fields'    => [],
				'status'          => $status,
				'update_existing' => true,
			];

			if ($row->first_name)
			{
				$data['merge_fields']['FNAME'] = $row->first_name;
			}

			if ($row->last_name)
			{
				$data['merge_fields']['LNAME'] = $row->last_name;
			}

			if ($row->address)
			{
				$data['merge_fields']['ADDRESS'] = $row->address;
			}

			if ($row->phone)
			{
				$data['merge_fields']['PHONE'] = $row->phone;
			}

			$result = $mailchimp->post("lists/$listId/members", $data);

			if ($result === false)
			{
				$this->logError($data, $mailchimp->getLastError());
			}
		}
	}

	/**
	 * Display form allows users to change settings on event add/edit screen
	 *
	 * @param EventbookingTableEvent $row
	 */
	private function drawSettingForm($row)
	{
		require_once dirname(__FILE__) . '/api/MailChimp.php';

		try
		{
			$mailchimp = new MailChimp($this->params->get('api_key'));
		}
		catch (Exception $e)
		{
			$this->logError([], $e->getMessage());

			return;
		}


		$lists = $mailchimp->get('lists', ['count' => 1000]);

		if ($lists === false)
		{
			return;
		}

		$params = new Registry($row->params);

		if ($row->id)
		{
			$listIds = explode(',', $params->get('mailchimp_list_ids', ''));
		}
		else
		{
			$lists = explode(',', $this->params->get('default_list_ids', ''));
		}

		$options = array();

		foreach ($lists['lists'] as $list)
		{
			$options[] = JHtml::_('select.option', $list['id'], $list['name']);
		}
		?>
        <table class="admintable adminform" style="width: 90%;">
            <tr>
                <td width="220" class="key">
					<?php echo JText::_('PLG_EB_MAILCHIMP_ASSIGN_TO_LISTS'); ?>
                </td>
                <td>
					<?php echo JHtml::_('select.genericlist', $options, 'mailchimp_list_ids[]', 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $listIds) ?>
                </td>
                <td>
					<?php echo JText::_('PLG_EB_ACYMAILING_ASSIGN_TO_LISTS_EXPLAIN'); ?>
                </td>
            </tr>
        </table>
		<?php
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

	/**
	 * Log the error from API call
	 *
	 * @param array  $data
	 * @param string $error
	 */
	private function logError($data, $error)
	{
		$text = '[' . date('m/d/Y g:i A') . '] - ';

		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $keyValue => $valueValue)
				{
					$text .= "$keyValue=$valueValue, ";
				}
			}
			else
			{
				$text .= "$key=$value, ";
			}
		}

		$text .= $error;

		$ipnLogFile = JPATH_ROOT . '/components/com_eventbooking/mailchimp_api_errors.txt';
		$fp         = fopen($ipnLogFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}
}
