<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/** 
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.application.component.model' );

/**
 * Model responsibilities contract
 *
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
interface IRealstatsModel {
	/**
	 * Config accessor method
	 *
	 * @access public
	 * @return Object&
	 */
	public function &getConfig();
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData($pieRequest = false, $initRequest = false);
}

/**
 * Realtime stats concrete implementation
 * Incorpora in un single operational responsibility tutto il calcolo dei dati
 * da fornire in JSON format alla JS APP di frontend per il visual rendering
 * <<testable_behavior>>
 *
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
class JRealtimeModelRealstats extends JRealtimeModel implements IRealstatsModel {
	/**
	 * Tempo oltre il quale considerare un utente in #__realtimeanalytics_realstats
	 * come non più presente sul sito e quindi da non inserire più
	 * nelle statistiche
	 *
	 * @access private
	 * @var int
	 */
	private $maxInactivityTime;
	
	/**
	 * Data container da renderizzare in JSON ad opera della view
	 *
	 * @access private
	 * @var array
	 */
	private $data;
	
	/**
	 * Component configuration pointer
	 *
	 * @access private
	 * @var Object
	 */
	private $cParams;
	
	/**
	 * Sess SQL query for the client id
	 * @access private
	 * @var string
	 */
	private $sessClientId;
	
	/**
	 * Numero di utenti totale attualmente presenti sul sito per pagina
	 * Si basa su una COUNT sui records validi di #__realtimeanalytics_realstats con GROUP BY nowpage
	 *
	 * @access protected
	 * @return array
	 */
	protected function buildListQueryPagesUserOn() {
		// Build query
		$query = "SELECT COUNT(session_id_person) AS numusers, MAX(lastupdate_time) AS lastvisit, nowpage" .
				 "\n FROM #__realtimeanalytics_realstats" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time()-$this->maxInactivityTime) .
				 "\n GROUP BY " . $this->_db->quoteName('nowpage');
		 
		return $query;
	}
	
	/**
	 * Numero di utenti totali attualmente presenti sul sito e pagina in cui si trovano
	 * Si basa su una SELECT incondizionata sui records validi di #__realtimeanalytics_realstats 
	 *
	 * @access protected
	 * @return array
	 */
	protected function buildListQueryUsersPageOn() {
		// Build query
		$query = "SELECT stats.nowpage," .
				 "\n stats.current_name," .
				 "\n stats.lastupdate_time AS lastupdatetime," .
				 "\n sess.username," .
				 "\n userg.title AS usertype," .
				 "\n users.name," .
				 "\n serverstats.geolocation," .
				 "\n serverstats.device" .
				 "\n FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n LEFT JOIN #__users AS users" .
				 "\n ON sess.userid = users.id" .
				 "\n LEFT JOIN #__user_usergroup_map AS map" .
				 "\n ON map.user_id = users.id" .
				 "\n LEFT JOIN #__usergroups AS userg" .
				 "\n ON map.group_id = userg.id" .
				 "\n LEFT JOIN #__realtimeanalytics_serverstats AS serverstats" .
				 "\n ON stats.session_id_person = serverstats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time()-$this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId .
				 "\n GROUP BY sess.session_id";
			
		return $query;
	}
	
	/**
	 * Numero di utenti totali attualmente presenti sul sito
	 * Si basa su una COUNT sui records validi di #__realtimeanalytics_realstats
	 *
	 * @access protected
	 * @return int
	 */
	protected function buildListQueryTotalUserCount() {
		// Build query
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats" .
				"\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time()-$this->maxInactivityTime);
	
		return $query;
	}
	
	/**
	 * Numero di utenti customers visitatori
	 * Si basa su una COUNT sui records validi di #__realtimeanalytics_realstats 
	 * in JOIN con #__session di cui si valuta il guest = 1 e la doppia
	 * condizione di id utente non presente tra quelli degli agents designati
	 * e group id compreso tra i gruppi customers designed
	 *
	 * @access protected
	 * @return int 
	 */
	protected function buildListQueryTotalVisitorsCustomers() {
		// Build query
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time()-$this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId .
				 "\n AND sess.guest = 1";;
		
		return $query;
	}
	
	/**
	 * Numero di utenti customers loggati
	 * Si basa su una COUNT sui records validi di #__realtimeanalytics_realstats 
	 * in JOIN con #__session di cui si valuta il guest = 0 e la doppia
	 * condizione di id utente non presente tra quelli degli agents designati
	 * e group id compreso tra i gruppi customers designed
	 *
	 * @access protected
	 * @return int 
	 */
	protected function buildListQueryTotalLoggedCustomers() {
		// Build query
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time()-$this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId .
				 "\n AND sess.guest = 0";
		
		return $query;
	}
	
	/**
	 * Modules mobile list entities query
	 *
	 * @access protected
	 * @param string $field
	 * @param string $value
	 * @return string
	 */
	protected function buildListQueryStatsMap() {
		$query = "SELECT COUNT(DISTINCT(r.session_id_person)) AS numusers, s.geolocation" .
				 "\n FROM #__realtimeanalytics_realstats AS r" .
				 "\n INNER JOIN #__realtimeanalytics_serverstats AS s" .
				 "\n ON r.session_id_person = s.session_id_person" .
				 "\n WHERE r.lastupdate_time > " . (int)(time()-$this->maxInactivityTime) .
				 "\n GROUP BY s.geolocation";
		
		return $query;
	}
 
	/**
	 * Config accessor method
	 *
	 * @access public 
	 * @return Object&
	 */
	public function &getConfig() {
		return $this->cParams;
	}
	  
	/**
	 * Main get data method Single Responsibility operation
	 *
	 * @access public
	 * @param boolean $pieRequest
	 * @param boolean $initRequest
	 * @return Object[]
	 */
	public function getData($pieRequest = false, $initRequest = false) { 
	  	// Build queries
	  	try {
	  		// Esclusione pieRequest data per total
	  		if(!$pieRequest) {
	  			if(!$initRequest) {
	  				// Users page on stats
	  				$query = $this->buildListQueryUsersPageOn ();
	  				$this->_db->setQuery ( $query );
	  				$this->data[] = $this->_db->loadObjectList ();
	  				if($this->_db->getErrorNum()) {
	  					throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
	  				}
	  				
	  				// Pages user on stats
	  				$query = $this->buildListQueryPagesUserOn ();
	  				$this->_db->setQuery ( $query );
	  				$this->data[] = $this->_db->loadObjectList ();
	  				if($this->_db->getErrorNum()) {
	  					throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
	  				}
	  				
		  			// Geo map data
	  				$query = $this->buildListQueryStatsMap();
	  				$this->_db->setQuery ( $query );
	  				$geolocationData = $this->_db->loadAssocList ('geolocation');
	  				if($this->_db->getErrorNum()) {
	  					throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
	  				}
	  				// Normalize DB Data
	  				$normalizedGeoData = array();
	  				if(is_array($geolocationData) && count($geolocationData)) {
		  				foreach ($geolocationData as $country=>$singleData) {
		  					$normalizedGeoData[strtolower($country)] = (int)$singleData['numusers'];
		  				}
	  				} else {
	  					$normalizedGeoData = (object)$normalizedGeoData;
	  				}
	  				$this->data[] = $normalizedGeoData;
	  			}
	  			
	  			// Users page on stats
	  			$query = $this->buildListQueryTotalUserCount ();
	  			$this->_db->setQuery ( $query );
	  			$countedResults = $this->_db->loadResult ();
	  			if($this->_db->getErrorNum()) {
	  				throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
	  			}
	  			// Assign object
	  			$totalUsers = new stdClass();
	  			$totalUsers->source = JText::_('COM_JREALTIME_TOTALUSERS');
	  			$totalUsers->value = $countedResults;
	  			$this->data[] = $totalUsers;
	  		}
	  		
	  		// START Totale pie
	  		// Total Visitors Customers stats
	  		$query = $this->buildListQueryTotalVisitorsCustomers ();
	  		$this->_db->setQuery ( $query );
	  		$countedResults = $this->_db->loadResult ();
	  		if($this->_db->getErrorNum()) {
	  			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
	  		}
	  		// Assign object
	  		$customersVisitor = new stdClass();
	  		$customersVisitor->source = JText::_('COM_JREALTIME_CUSTOMERSVISITOR');
	  		$customersVisitor->value = (int)$countedResults;
	  		$this->data[] = $customersVisitor;
	  		
	  		
	  		// Total Logged Customers stats
	  		$query = $this->buildListQueryTotalLoggedCustomers ();
	  		$this->_db->setQuery ( $query );
	  		$countedResults = $this->_db->loadResult ();
	  		if($this->_db->getErrorNum()) {
	  			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
	  		}
	  		// Assign object
	  		$customersLogged = new stdClass();
			$customersLogged->source = JText::_('COM_JREALTIME_CUSTOMERSLOGGED');
			$customersLogged->value = (int)$countedResults;
	  		$this->data[] = $customersLogged;
	  		// END Totale pie
	  	} catch (JRealtimeException $e) {
	  		$this->data = array('error'=>true, 'exception'=>$e->getMessage());
	  	} catch (Exception $e) {
	  		$this->data = array('error'=>true, 'exception'=>$e->getMessage());
	  	}
		return $this->data;
	}
	
	/**
	 * Class contructor
	 *  
	 * @access public
	 * @return Object&
	 */
	public function __construct() {
		parent::__construct();
		
		$this->data = array();
		$this->cParams = $this->getComponentParams(); 
		$this->maxInactivityTime = $this->cParams->get('maxlifetime_session'); 
		
		// Evaluate the shared session option for SQL queries on Joomla 3.7+
		$sharedSession = (int)$this->app->get('shared_session', null);
		if($sharedSession == 1 && $this->cParams->get('shared_session_support', 1)) {
			$this->sessClientId = '(sess.client_id = 0 OR ISNULL(sess.client_id))';
		} else {
			$this->sessClientId = 'sess.client_id = 0';
		}
	}
} 