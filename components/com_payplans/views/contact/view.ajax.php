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

class PayPlansViewContact extends PayPlansView
{
	/**
	 * Renders the contact form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$theme = PP::themes();
		$output = $theme->output('site/contact/dialogs/form');

		return $this->resolve($output);
	}	

	/**
	 * Sends e-mail to site administrators
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function send()
	{
		$contents = $this->input->get('contents', '', 'default');
		$subject = $this->input->get('subject', '', 'word');

		$params = array(
			'contents' => JText::sprintf('COM_PAYPLANS_SUPPORT_EMAIL_BODY',  $this->my->username, $this->my->email, $contents)
		);

		$mailer = PP::mailer();
		$emails = $mailer->getAdminEmails();

		if ($emails) {
			foreach ($emails as $email) {
				$mailer->send($email, $subject, 'emails/custom/blank', $params);
			}
		}

		return $this->resolve();
	}
}