<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class plgSystemEBReminder extends JPlugin
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
	
	public function onAfterRender()
	{
		if (!$this->canRun())
		{
			return;
		}

		//Store last run time
		$query = $this->db->getQuery(true);
		$this->params->set('last_run', time());
		$query->update('#__extensions')
			->set('params=' . $this->db->quote($this->params->toString()))
			->where('`element`="ebreminder"')
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

		$bccEmail                = $this->params->get('bcc_email', '');
		$numberEmailSendEachTime = (int) $this->params->get('number_registrants', 0);

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		// Send first reminder
		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendReminder', [$numberEmailSendEachTime, $bccEmail, $this->params]);

		// Send second reminder
		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendSecondReminder', [$numberEmailSendEachTime, $bccEmail, $this->params]);

		return true;
	}

	/**
	 * Method to check whether this plugin should be run
	 *
	 * @return bool
	 */
	private function canRun()
	{
		if (!file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			return false;
		}

		// If trigger reminder code is set, we will only process sending reminder from cron job
		if (trim($this->params->get('trigger_reminder_code')))
		{
			if ($this->params->get('trigger_reminder_code') == $this->app->input->getString('trigger_reminder_code'))
			{
				return true;
			}

			return false;
		}

		// Process sending reminder on every page load if debug mode enabled
		if ($this->params->get('debug', 0))
		{
			return true;
		}

		// Send reminder if the last time reminder emails are sent was more than 20 minutes ago
		$lastRun = (int) $this->params->get('last_run', 0);

		if ((time() - $lastRun) < 1200)
		{
			return false;
		}

		return true;
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
