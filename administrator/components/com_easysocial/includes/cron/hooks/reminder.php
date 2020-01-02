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

class SocialCronHooksReminder
{
	public function execute(&$states)
	{
		$states[] = $this->processUserReminder();
	}

	/**
	 * archive stream items
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function processUserReminder()
	{
		$config = ES::config();

		if (!$config->get('users.reminder.enabled')) {
			return JText::_('Reminder for user inactivity disabled.');
		}

		$days = $config->get('users.reminder.duration', '30');
		$limit = 20;


		$model = ES::model('Users');
		$results = $model->getInactiveUsers($days, $limit);

		if ($results) {
			$state = $model->sendReminder( $results );

			if ($state) {
				return JText::sprintf( 'COM_EASYSOCIAL_CRONJOB_USERS_REMINDER_PROCESSED', $state );
			}
		}

		return JText::_( 'COM_EASYSOCIAL_CRONJOB_USERS_REMINDER_NOTHING_TO_EXECUTE' );
	}
}
