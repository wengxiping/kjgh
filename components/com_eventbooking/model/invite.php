<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingModelInvite extends RADModel
{
	/**
	 * Send invitation to users
	 *
	 * @param $data
	 *
	 * @throws Exception
	 */
	public function sendInvite($data)
	{
		$config      = EventbookingHelper::getConfig();
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = JFactory::getConfig()->get('fromname');
		}

		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = JFactory::getConfig()->get('mailfrom');
		}

		$event    = EventbookingHelperDatabase::getEvent((int) $data['event_id']);

		$replaces                      = EventbookingHelperRegistration::buildEventTags($event, $config);
		$replaces['sender_name']       = $data['name'];
		$replaces['PERSONAL_MESSAGE']  = $data['message'];
		$replaces['event_detail_link'] = '<a href="' . $replaces['event_link'] . '">' . $event->title . '</a>';;

		if (strlen($message->{'invitation_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'invitation_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->invitation_email_subject;
		}

		if (strlen(strip_tags($message->{'invitation_email_body' . $fieldSuffix})))
		{
			$body = $message->{'invitation_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->invitation_email_body;
		}

		$subject = str_replace('[EVENT_TITLE]', $event->title, $subject);

		foreach ($replaces as $key => $value)
		{
			$key  = strtoupper($key);
			$body = str_replace("[$key]", $value, $body);
		}

		$emails = explode("\r\n", $data['friend_emails']);
		$names  = explode("\r\n", $data['friend_names']);
		$mailer = JFactory::getMailer();

		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			$emailBody = $body;
			$email     = $emails[$i];
			$name      = $names[$i];

			if ($name && JMailHelper::isEmailAddress($email))
			{
				$emailBody = str_replace('[NAME]', $name, $emailBody);
				$mailer->sendMail($fromEmail, $fromName, $email, $subject, $emailBody, 1);
				$mailer->ClearAllRecipients();
			}
		}
	}
}
