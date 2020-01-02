<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPMailer extends PayPlans
{
	/**
	 * Sends e-mail out using the mailer in Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function send($recipient, $subject, $namespace, $params = array(), $attachments = array(), $cc = array(), $bcc = array(), $html = true)
	{
		$subject = JText::_($subject);
		$contents = $this->getContents($namespace, $params);
		

		$jconfig = PP::jconfig();
		$from = $jconfig->get('mailfrom');
		$fromName = $jconfig->get('fromname');
		$replyTo = $jconfig->get('replyto', $from);

		$mailer = JFactory::getMailer();
		$mailer->setSubject($subject);
		$mailer->setSender(array($from, $fromName));
		$mailer->addReplyTo($replyTo, $fromName);
		$mailer->setBody($contents);
		$mailer->IsHTML($html);

		// Carbon Copy (CC)
		if (is_array($cc) && $cc) {
			foreach ($cc as $address) {
				$mailer->addCC($address);
			}
		}

		// Blind Carbon Copy (BCC)
		if (is_array($bcc) && $bcc) {
			foreach ($bcc as $address) {
				$mailer->addBCC($address);
			}
		}

		// Insert attachments
		if ($attachments) {
			foreach ($attachments as $attachment) {
				$mailer->addAttachment($attachment);
			}
		} 
			
		$mailer->addRecipient($recipient);
		$state = $mailer->send();

		return $state;
	}

	/**
	 * Retrieves the list of site admin e-mails
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAdminEmails()
	{
		static $emails = null;

		if (is_null($emails)) {
			$model = PP::model('User');
			$admins = $model->getSiteAdmins(true);
			
			$emails = array();

			if ($admins) {
				foreach ($admins as $admin) {
					$emails[] = $admin->getEmail();
				}
			}
		}

		return $emails;
	}

	/**
	 * Get contents of e-mail template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getContents($namespace, $vars = array())
	{
		$path = $this->resolve($namespace);

		extract($vars);

		ob_start();
		include($path);
		$contents = ob_get_contents();
		ob_end_clean();

		// If there is an intro set, we'll get the introtext
		$intro = PP::normalize($vars, 'intro', '');
		$outerFrame = PP::normalize($vars, 'outerFrame', 1);

		$theme = PP::themes();
		$theme->set('intro', $intro);
		$theme->set('outerFrame', $outerFrame);
		$theme->set('contents', $contents);
		$output = $theme->output('site/emails/template');

		return $output;
	}

	/**
	 * Resolves the namespace
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function resolve($namespace, $extension = 'php')
	{
		$parts = explode('/', $namespace);

		if (count($parts) == 1) {
			return $parts;
		}

		$path = JPATH_ROOT;

		// Plugin theme files resolver
		if ($parts[0] == 'plugins') {

			// Get the group and element of the plugin
			$path .= '/plugins/' . $parts[1] . '/' . $parts[2];

			unset($parts[0], $parts[1], $parts[2]);
			
			$path .= '/tmpl/' . implode('/', $parts) . '.' . $extension;
		}

		// TODO: Admin and site email template resolver

		// Default theme name
		$defaultThemeName = 'wireframe';

		// Emails
		if ($parts[0] == 'emails') {

			$fileName = implode('/', $parts) . '.' . $extension;

			$path .= '/components/com_payplans/themes/' . $defaultThemeName . '/' . $fileName;
		}

		return $path;
	}
}