<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPHelperEmail extends PPHelperStandardApp
{
	/**
	 * Retrieves a list of abandoned invoices
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAbandonedInvoices($expiry)
	{
		$config = PP::config();

		$cronTime = PP::date($config->get('cronAcessTime'));
		$current = PP::date();

		$cronTime->subtractExpiration($expiry);
		$current->subtractExpiration($expiry);

		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_invoice');
		$query[] = 'WHERE ' . $db->qn('status') . '=' . $db->Quote(PP_INVOICE_CONFIRMED);
		$query[] = 'AND ' . $db->qn('created_date') . '>' . $db->Quote($cronTime->toSql()) . ' AND ' . $db->qn('created_date') . '<' . $db->Quote($current->toSql());
		$query = implode(' ', $query);

		$db->setQuery($query);
		$invoices = $db->loadObjectList();

		return $invoices;
	}

	/**
	 * Determines on which status should the emails be sent to
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStatusToSend()
	{
		if ($this->getWhenToSend() == 'on_cancellation') {
			return PP_ORDER_CANCEL;
		}

		return $this->params->get('on_status', PP_NONE);
	}

	/**
	 * Retrieves the subject used for e-mail
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getWhenToSend()
	{
		$when = $this->params->get('when_to_email', '');

		return $when;
	}

	/**
	 * Get the list of attachments that should be included in the email
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAttachments()
	{
		$attachment = $this->params->get('attachment', '');
		
		if (!$attachment || $attachment == -1) {
			return array();
		}

		$model = PP::model('Notifications');
		$path = $model->getAttachmentsFolder() . '/'. $attachment;

		$attachments = array($path);
		return $attachments;
	}

	/**
	 * Retrieve the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPdfInvoice($object)
	{
		$invoice = $object;

		if ($object instanceof PPOrder) {
			$invoices = $object->getInvoices();
			$invoice = array_pop($invoices);
		}
		
		//get the invoice object from subscription
		if ($object instanceof PPSubscription) {
			$order = $object->getOrder();
			$invoices = $order->getInvoices();
			$invoice = array_pop($invoices);
		}
		
		// If we can't get the invoice, skip this altogether
		if (!($invoice instanceof PPInvoice)) {
			return false;
		}

		// Get the path to the pdf invoice file
		$pdf = PP::pdf($invoice);
		$pdf->generateFile();

		return $pdf;
	}

	/**
	 * Get the list of carbon copy (CC emails)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCC()
	{
		$emails = $this->params->get('send_cc', '');

		if ($emails) {
			$emails = explode(',', $emails);
		}

		return $emails;
	}

	/**
	 * Get the list of blind carbon copy (BCC emails)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBCC()
	{
		$emails = $this->params->get('send_bcc', '');

		if ($emails) {
			$emails = explode(',', $emails);
		}

		return $emails;
	}

	/**
	 * Retrieves the subject used for e-mail
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubject($object)
	{
		// Retrieve the subject value from the app table
		$subject = $this->params->get('subject', '');

		// support for the multilingual subject title
		$subject = JText::_($subject);

		// Convert the token to proper value
		$subject = $this->replaceTokens($subject, $object);

		return $subject;
	}

	/**
	 * Retrieves the subject used for e-mail
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEmailContents($object)
	{
		if ($this->isUsingCustomContent() || $this->isUsingJoomlaArticle()) {

			// Retrieve custom content source
			$contents = $this->params->get('content', '');
			$contents = base64_decode($contents);

			// Retrieve Joomla article content source
			if ($this->isUsingJoomlaArticle()) {

				$articleId = $this->params->get('choose_joomla_article');

				if ($articleId) {

					$article = JTable::getInstance('Content');
					$state = $article->load($articleId);

					// Determine if the site enable multilingual language
					if ($state && JLanguageAssociations::isEnabled()) {

						// Retrieve all the association article data e.g. English, French and etc
						$termsAssociated = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $articleId);

						// Determine the current site language
						$currentLang = JFactory::getLanguage()->getTag();

						// Only come inside this checking if the current site language not match with the selected article language
						// And see whether this tearmAssociated got detect got other association article or not
						if (isset($termsAssociated) && $currentLang !== $article->language && array_key_exists($currentLang, $termsAssociated)) {

							foreach ($termsAssociated as $term) {

								// Retrieve the associated article id
								if ($term->language == $currentLang) {
									$articleId = explode(':', $term->id);
									$articleId = $articleId[0];
									break;
								}
							}
						}

						// Reload the new associated article id
						$state = $article->load($articleId);
					}

					// Only assign the Joomla article content here if the article exist
					if ($state) {
						$contents = $article->introtext . $article->fulltext;
					}
				}
			}

			// Replace the token to proper value
			$contents = $this->replaceTokens($contents, $object);

			return $contents;
		}

		// Here we assume that we should be using an e-mail template
		$templateFile = $this->params->get('choose_template');

		$model = PP::model('Notifications');

		// Determine if the email template file has already been overriden.
		$overridePath = $model->getOverrideFolder($templateFile) . '.php';

		$overrideExist = JFile::exists($overridePath);
		$path = $model->getFolder() . '/' . $templateFile . '.php';

		if ($overrideExist) {
			$path = $overridePath;
		}

		$exists = JFile::exists($path);

		if (!$exists) {
			return false;
		}

		$contents = JFile::read($path);
		$contents = $this->replaceTokens($contents, $object);

		return $contents;
	}

	/**
	 * Determines if we should be using a template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUsingHtml()
	{
		$html = $this->params->get('html_format', true) ? true : false;

		return $html;
	}

	/**
	 * Determines if we should be using a template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUsingTemplate()
	{
		$template = $this->params->get('email_template', 'custom') == 'custom' ? false : true;

		return $template;
	}

	/**
	 * Determines if we should be using a template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUsingCustomContent()
	{
		$template = $this->params->get('email_template', 'custom') == 'custom' ? true : false;

		return $template;
	}

	/**
	 * Determines if we should be use Joomla article
	 *
	 * @since	4.0.12
	 * @access	public
	 */
	public function isUsingJoomlaArticle()
	{
		$template = $this->params->get('email_template', 'custom') == 'choose_joomla_article' ? true : false;

		return $template;
	}

	/**
	 * Sends out e-mail. Object could be a subscription or an invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function send($object)
	{
		$user = $object->getBuyer(true);
		$subject = $this->getSubject($object);
		$recipient = $user->getEmail();
		$cc = $this->getCC();
		$bcc = $this->getBCC();

		$attachments = $this->getAttachments();
		$sendInvoice = $this->shouldIncludeInvoice();
		$pdfInvoice = false;

		if ($sendInvoice) {
			$pdfInvoice = $this->getPdfInvoice($object);

			if ($pdfInvoice !== false) {
				$attachments = array_merge($attachments, array($pdfInvoice->getFilePath()));
			}
		}
		
		$contents = $this->getEmailContents($object);
		$html = $this->isUsingHtml();

		if (!$html) {
			$contents = strip_tags($contents);
		}

		// Try to send
		$mailer = PP::mailer();
		$state = $mailer->send($recipient, $subject, 'emails/custom/blank', array('contents' => $contents, 'outerFrame' => 0), $attachments, $cc, $bcc);

		// For logging purposes
		$log = array(
			'user_id' => $user->getId(),
			'subject' => $subject,
			'body' => $contents
		);

		// We should delete the file to save space.
		// If the user need to retrieve the file in the future,
		// he can just go to the dashboard and download it.
		if ($pdfInvoice) {
			$pdfInvoice->delete();
		}

		if ($state == false || $state instanceof Exception) {
			PPLog::log(PPLogger::LEVEL_INFO, JText::_('COM_PAYPLANS_EMAIL_SENDING_FAILED'), $this, $log, 'PayplansAppEmailFormatter', '', true);
			return false;
		}

		// Otherwise we assume sending was success
		PPLog::log(PPLogger::LEVEL_INFO, JText::_('COM_PAYPLANS_EMAIL_SEND_SUCCESSFULLY'), $this, $log, 'PayplansAppEmailFormatter');
		return true;
	}

	/**
	 * Mark a subscription as sent
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function markSent(PPSubscription $subscription, $event, $expiryTime, $invoiceCount = '')
	{
		$params = $subscription->getParams();
		$params->set($event . $expiryTime . $invoiceCount, true);
		$subscription->params = $params->toString();

		return $subscription->save();
	}

	/**
	 * Determines if we should include invoice in the attachments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function shouldIncludeInvoice()
	{
		$send = (bool) $this->params->get('send_invoice', false);
		$config = PP::config();
		$enabled = $config->get('enable_pdf_invoice');

		if ($send && $enabled) {
			return true;
		}

		return false;
	}

	/**
	 * Given the previous and new object, determines if 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sendEmailForRecurringLastCycle()
	{
		$send = (bool) $this->params->get('on_lastcycle', false);

		return $send;
	}

	/**
	 * Given the previous and new object, determines if 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function shouldSendEmail($prev, $new)
	{
		// These events should only be executed by cronjob
		$cronItems = array('on_preexpiry', 'on_postexpiry', 'on_postactivation', 'on_cart_abondonment');

		if (in_array($this->getWhenToSend(), $cronItems)) {
			return false;
		}

		// no need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return false;
		}

		// check the status
		if ($new->getStatus() != $this->getStatusToSend()){
			return false;
		}

		$results = PPEvent::trigger('onPayplansBeforeSendEmail', array($prev, $new));

		if (in_array(false, $results)) {
			return false;
		}
		
		return true;
	}

	/**
	 * Replace tokens with the proper values
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function replaceTokens($content, $object)
	{
		$rewriter = PP::rewriter();
		$result = $rewriter->rewrite($content, $object);

		return $result;
	}
}