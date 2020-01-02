<?php
// namespace components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::REALSTATS::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Realstats class frontend implementation
 *
 * @package JREALTIMEANALYTICS::REALSTATS::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.0
 */
class JRealtimeModelRealstatsObsrv extends JRealtimeModelObserver {
	/**
	 * Session ID utente in refresh
	 *
	 * @var Object&
	 * @access private
	 */
	private $session;
	
	/**
	 * Component config
	 *
	 * @access private
	 * @var Object &
	 */
	private $config;
	
	/**
	 * Ottiene dalla POST AJAX il current URL dove si trova l'utente
	 *
	 * @access protected
	 * @return string
	 */
	protected function getNowPage() {
		// Get current user page
		$currentUserPage = $this->app->input->post->getString ('nowpage', null);
		
		// Do url decoding
		$currentUserPage = urldecode($currentUserPage);
		
		return $currentUserPage;
	}
	
	/**
	 * Si occupa di aggiornare il record contestuale alla sessione utente connesso
	 * nella tabella 1 a 1 #__realtimeanalytics_realstats -> #__session
	 *
	 * @param IObservableModel $subject
	 * @access public
	 * @return mixed If some exceptions occur return an Exception object otherwise boolean true
	 */
	public function update(IObservableModel $subject) {
		// Current subject Observable object
		$this->subject = $subject;
		
		// Ottenimento pagina utente corrente
		$nowPage = $this->getNowPage ();
		
		$currentName = $this->subject->getState('username');
		$field = null;
		$value = null;
		$update = null;
		if ($currentName) {
			$field = ", \n " . $this->_db->quoteName ( 'current_name' );
			$value = "," . $this->_db->quote ( $currentName );
			$update = ", \n " . $this->_db->quoteName ( 'current_name' ) . "=" . $this->_db->quote ( $currentName );
		}
		
		try {
			// Build query insert/update
			$insertUpdateQuery = "INSERT INTO #__realtimeanalytics_realstats" .
								 "\n ( " . $this->_db->quoteName ( 'session_id_person' ) . "," .
								 "\n " . $this->_db->quoteName ( 'nowpage' ) . "," .
								 "\n " . $this->_db->quoteName ( 'lastupdate_time' ) . $field . " )" .
								 "\n VALUES ( " . $this->_db->quote ( $this->session->session_id ) . "," . $this->_db->quote ( $nowPage ) . "," . $this->_db->quote ( time () ) . $value . " )" .
								 "\n ON DUPLICATE KEY UPDATE" . "\n " . $this->_db->quoteName ( 'nowpage' ) . " = " . $this->_db->quote ( $nowPage ) . "," .
								 "\n " . $this->_db->quoteName ( 'lastupdate_time' ) . "=" . $this->_db->quote ( time () ) . $update;
			$this->_db->setQuery ( $insertUpdateQuery );
			$this->_db->execute ();
			if($this->_db->getErrorNum()) {
				throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_WRITING_REALTIME_STREAM', $this->_db->getErrorMsg()), 'error', 'Realtime stats');
			}
			
			// Increment impulse timing only after first initialize, aka initialize = 0
			if(!$this->subject->getState('initialize')) {
				$impulseUpdateQuery = "UPDATE #__realtimeanalytics_serverstats" . "\n SET impulse = impulse  + 1" . "\n WHERE " .
									  "\n " . $this->_db->quoteName ( 'session_id_person' ) . "=" . $this->_db->quote ( $this->session->session_id ) .
									  "\n AND " . $this->_db->quoteName ( 'visitdate' ) . "=" . $this->_db->quote ( date ( 'Y-m-d' ) ) .
									  "\n AND " . $this->_db->quoteName ( 'visitedpage' ) . "=" . $this->_db->quote ( $nowPage );
				$this->_db->setQuery ( $impulseUpdateQuery );
				$this->_db->execute ();
				if($this->_db->getErrorNum()) {
					throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_INCREMENT_SERVERSTATS', $this->_db->getErrorMsg()), 'error', 'Realtime stats');
				}
			}
		} catch (JRealtimeException $e) {
			return $e;
				
		} catch (Exception $e) {
			$jrealtimeException = new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_ONDATABASE_REALSTATS', $e->getMessage()), 'error', 'Realtime stats');
			return $jrealtimeException;
		}
		
		return true;
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		$this->session = $config ['sessiontable'];
		$this->config = JComponentHelper::getParams ( 'com_jrealtimeanalytics' );
		
		parent::__construct ( $config );
	}
}