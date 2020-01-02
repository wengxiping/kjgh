<?php
// namespace administrator\components\com_jrealtimeanalytics\framework\renderers;
/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage renderers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage renderers
 * @since 2.3
 */
class JRealtimeRenderersBaseadapter {
	/**
	 * Mailer helper instance
	 *
	 * @access protected
	 * @var Object
	 */
	protected $mailer;
	
	/**
	 * Extension params
	 *
	 * @access protected
	 * @var Object
	 */
	protected $cParams;
	
	/**
	 * Joomla params
	 *
	 * @access protected
	 * @var Object
	 */
	protected $jConfig;
	
	/**
	 * Effettua l'invio della mail di risposta al customer
	 *
	 * @param string $body
	 * @param string $data
	 * @params string $fileName
	 * @params string $mimeType
	 * @access protected
	 * @return boolean
	 */
	protected function sendEmail($data, $fileName, $mimeType) {
		// Check for notify email addresses
		$validEmailAddresses = array();
		$emailAddresses = $this->cParams->get('report_addresses', '');
		$emailAddresses = explode(',', $emailAddresses);
		if(!empty($emailAddresses)) {
			foreach ($emailAddresses as $validEmail) {
				if(filter_var(trim($validEmail), FILTER_VALIDATE_EMAIL)) {
					$validEmailAddresses[] = trim($validEmail);
				}
			}
		}
	
		// If valid email addresses detected send the notification
		if(!empty($validEmailAddresses)) {
			// Build e-mail message format
			$this->mailer->setSender(array($this->cParams->get('report_mailfrom', $this->jConfig->get('mailfrom')), 
									 	   $this->cParams->get('report_fromname', $this->jConfig->get('fromname'))));
			$this->mailer->setSubject(JText::_('COM_JREALTIME_REPORT_SUBJECT'));
			$encoding = 'base' . 64;
			$this->mailer->addStringAttachment($data, $fileName, $encoding, $mimeType);
			
			/**
			 * Format a full body for the notification email
			 */
			$bodyText = JText::sprintf('COM_JREALTIME_REPORT_BODY', date('Y-m-d'));
			
			$this->mailer->setBody($bodyText);
			$this->mailer->IsHTML(true);
		
			// Add recipient
			$this->mailer->addRecipient($validEmailAddresses);
		
			// Send the Mail
			$rs	= $this->mailer->sendUsingExceptions();
		
			// Check for an error
			return $rs;
		}
		
		return false;
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param Object $cParams
	 * @param Object $mailer
	 * @return Object&
	 */
	public function __construct($cParams = null, $mailer = null) {
		// Store extension config
		$this->cParams = $cParams;
		if(is_null($cParams)) {
			$this->cParams = JComponentHelper::getParams('com_jrealtimeanalytics');
		}
		
		// Joomla config
		$this->jConfig = JFactory::getConfig();
		
		// Mailer object if any
		$this->mailer = $mailer;
	}
}