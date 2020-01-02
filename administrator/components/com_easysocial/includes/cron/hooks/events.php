<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialCronHooksEvents
{
	public function execute(&$states)
	{
		// Set all past event to unfeatured state 
		$states[] = $this->unfeaturedEvents();

		$config = ES::config();

		if ($config->get('events.reminder.enabled')) {
			$states[] = $this->processUpcomingEventReminder();	
		}
	}

	/**
	 * Notify users about their upcoming event
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function processUpcomingEventReminder()
	{
		$model = ES::model('Events');
		$events = $model->getUpcomingReminder();

		if ($events) {
			$state = $model->sendUpcomingReminder($events);

			if ($state) {
				return JText::sprintf( 'COM_EASYSOCIAL_CRONJOB_EVENT_UPCOMING_REMINDER_PROCESSED', $state );
			}
		}

		return JText::_( 'COM_EASYSOCIAL_CRONJOB_EVENT_UPCOMING_REMINDER_NOTHING_TO_EXECUTE' );
	}

	/**
	 * Change the featured state of the past event
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unfeaturedEvents()
	{
		$config = ES::config();

		$model = ES::model('Events');

		$ids = $model->getEventToUnfeatured();

		if ($ids) {
			$total = $model->unfeaturedEvents($ids);
			return JText::sprintf('COM_EASYSOCIAL_CRONJOB_EVENT_UNFEATURED_SUCCESSFULLY', $total);
		}

		return JText::_('COM_EASYSOCIAL_CRONJOB_EVENT_NOTHING_TO_UNFEATURED');
	}
}
