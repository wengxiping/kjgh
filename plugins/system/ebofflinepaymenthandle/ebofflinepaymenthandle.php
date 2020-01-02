<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class plgSystemEBOfflinePaymentHandle extends JPlugin
{
	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;


	public function onAfterRender()
	{
		if (!file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			return;
		}

		$lastRun                  = (int) $this->params->get('last_run', 0);
		$numberDaysToSendReminder = (int) $this->params->get('number_days_to_send_reminders', 7);
		$numberDaysToCancel       = (int) $this->params->get('number_days_to_cancel', 10);
		$numberRegistrants        = (int) $this->params->get('number_registrants', 15);
		$now                      = time();
		$cacheTime                = 1200; // 60 minutes

		// No need to send reminder or cancel offline payment registration, don't process further
		if ($numberDaysToSendReminder == 0 && $numberDaysToCancel == 0)
		{
			return;
		}

		if (($now - $lastRun) < $cacheTime)
		{
			return;
		}

		//Store last run time
		$query = $this->db->getQuery(true);
		$this->params->set('last_run', $now);
		$params = $this->params->toString();
		$query->clear();
		$query->update('#__extensions')
			->set('params=' . $this->db->quote($params))
			->where('`element`="ebofflinepaymenthandle"')
			->where('`folder`="system"');

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$this->db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risk continuing execution
			return;
		}

		try
		{
			// Update the plugin parameters
			$result = $this->db->setQuery($query)->execute();
			$this->clearCacheGroups(array('com_plugins'), array(0, 1));
		}
		catch (Exception $exc)
		{
			// If we failed to execite
			$this->db->unlockTables();
			$result = false;
		}
		try
		{
			// Unlock the tables after writing
			$this->db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}
		// Abort on failure
		if (!$result)
		{
			return;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';


		if ($numberDaysToSendReminder > 0)
		{
			EventbookingHelper::callOverridableHelperMethod('mail', 'sendOfflinePaymentReminder', [$numberDaysToSendReminder, $numberRegistrants, $this->params]);
		}

		if ($numberDaysToCancel > 0)
		{
			$this->cancelRegistrations($numberDaysToCancel, $numberRegistrants);
		}

		return true;
	}

	/**
	 * Cancel registrations if no payment for offline payment received
	 *
	 * @param int $numberDaysToCancel
	 * @param int $numberRegistrants
	 */
	private function cancelRegistrations($numberDaysToCancel, $numberRegistrants)
	{
		$db = $this->db;

		$query = $db->getQuery(true)
			->select('a.id')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.published = 0')
			->where('a.group_id = 0')
			->where('a.payment_method LIKE "os_offline%"')
			->order('a.register_date');

		$baseOn = $this->params->get('base_on', 0);

		if ($baseOn == 0)
		{
			$query->where('DATEDIFF(NOW(), a.register_date) >= ' . $numberDaysToCancel)
				->where('(DATEDIFF(b.event_date, NOW()) > 0 OR DATEDIFF(b.cut_off_date, NOW()) > 0)');
		}
		else
		{
			$query->where('DATEDIFF(b.event_date, NOW()) <= ' . $numberDaysToCancel)
				->where('DATEDIFF(b.event_date, NOW()) >= 0');
		}

		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $e)
		{
			$ids = [];
		}

		if (count($ids))
		{
			/* @var EventbookingModelRegistrant $model */
			$model = RADModel::getInstance('Registrant', 'EventbookingModel', ['remember_states' => false, 'ignore_request' => true]);

			$model->cancelRegistrations($ids);
		}
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array $clearGroups  The cache groups to clean
	 * @param   array $cacheClients The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   2.0.4
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = array(0, 1))
	{
		$conf = JFactory::getConfig();
		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase'    => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache'),
					);
					$cache   = JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}
