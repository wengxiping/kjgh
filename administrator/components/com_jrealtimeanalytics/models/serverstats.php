<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.application.component.model' );

/**
 * Server stats model responsibilities contract
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
interface IServerstatsModel {
	/**
	 * Dependency injection setter del graph instance object generator
	 *
	 * @param Object& $graphInstance
	 * @access public
	 * @return void
	 */
	public function setGraphRenderer(&$graphInstance);
	
	/**
	 * Get geolocation translations from DB
	 *
	 * @access public
	 * @return array[] &
	 */
	public function &getGeoTranslations();

	/**
	 * Main fetch data method already populated
	 *
	 * @access public
	 * @return Object[]
	 */
	public function fetchData(); 
	
	/**
	 * Secondary get data method to obtain only counters data used for example for the module view
	 *
	 * @access public
	 * @return mixed Array of data if successful or an exception on errors
	 */
	public function getDataCounters();
	
	/**
	 * Load details entity
	 *
	 * @access public
	 * @param string $identifier
	 * @param string $detailType
	 * @return Object[]
	 */
	public function loadStatsEntity($identifier, $detailType);
}

/**
 * Main global data stats model <<testable_behavior>>
 * 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
class JRealtimeModelServerstats extends JRealtimeModel implements IServerstatsModel {
	/**
	 * Data inizio periodo statistiche
	 *
	 * @access private
	 * @var string
	 */
	private $intervalFrom;
	
	/**
	 * Data termine periodo statistiche
	 *
	 * @access private
	 * @var string
	 */
	private $intervalTo;
	
	/**
	 * Data structure container
	 * 
	 * @access private
	 * @var array 
	 */
	private $data;
	
	/**
	 * Component configuration object
	 *
	 * @access private
	 * @var Object
	 */
	private $cParams;
	
	/**
	 * Graph generator object
	 * 
	 * @access private
	 * @var Object
	 */
	private $graphGenerator;
	
	/**
	 * Query snippet WHERE in cui tutto viene filtrato in base al periodo
	 *
	 * @access private
	 * @var string
	 */
	private $whereQuery;
	
	/**
	 * Maps the requested reports to the user choice
	 *
	 * @access private
	 * @var array
	 */
	private $mappingReports;

	/** 
	 * Numero di visite GROUPED BY visitedpage
	 * 
	 * @access private
	 * @return array
	 */
	private function visitsPerPage() {
		$query = "SELECT COUNT(*) AS numvisits, MAX(". $this->_db->quoteName('visit_timestamp') . "), " . $this->_db->quoteName('visitedpage') .  
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('visitedpage') .
				 "\n ORDER BY numvisits DESC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $results;
	}

	/**
	 * Numero di pagine visitate complessivamente nel periodo
	 * 
	 * @access private
	 * @return int 
	 */
	private function totalVisitedPages() {
		$query = "SELECT COUNT(*)" .  
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery;
		$this->_db->setQuery($query);
		$result = (int) $this->_db->loadResult();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $result;
	}

	/**
	 * Array contenente il numero di pagine, last visit date, browser, os,  GROUP BY session_id_person
	 * 
	 * @access private
	 * @return array 
	 */
	private function totalVisitedPagesPerUser() {
		// Init empty vars
		$selectReferral = null;
		$selectReferReferral = null;
		$leftJoinReferral = null;
		
		if($this->cParams->get('show_usergroup', 0)) {
			// Parametric referral JOIN
			if($this->cParams->get('show_referral', 0)) {
				$selectReferral = $this->_db->quoteName('referral') . ",";
				$selectReferReferral = 'refer.referral,';
				$leftJoinReferral = "\n LEFT JOIN" .
									"\n (SELECT " .  $this->_db->quoteName('session_id_person') . ", " . $this->_db->quoteName('referral') .
									"\n FROM " .  $this->_db->quoteName('#__realtimeanalytics_referral') . 
									"\n WHERE " .  $this->_db->quoteName('record_date') . " >= " . $this->_db->quote($this->intervalFrom) .
			  						"\n AND " .  $this->_db->quoteName('record_date') . " <= " . $this->_db->quote( $this->intervalTo ) . 
			  						"\n GROUP BY " .  $this->_db->quoteName('session_id_person') . ") refer" .
				 					"\n ON sess.session_id_person = refer.session_id_person";
			}
			
			$query = "SELECT COUNT(*), " . $this->_db->quoteName('customer_name') . "," .
					 "\n MAX(". $this->_db->quoteName('visit_timestamp') . ")," .
					 $this->_db->quoteName('browser') . "," .
					 $this->_db->quoteName('os') . "," .
					 $this->_db->quoteName('session_id_person') . "," .
					 $this->_db->quoteName('ip') . "," .
					 "\n SUM(" . $this->_db->quoteName('impulse') . ")," .
					 $this->_db->quoteName('geolocation') . "," .
					 $this->_db->quoteName('device') . "," .
					 $selectReferral .
					 $this->_db->quoteName('usergroup') .
					 "\n FROM ( SELECT sess.*, $selectReferReferral userg.title AS usergroup FROM #__realtimeanalytics_serverstats AS sess" .
					 $leftJoinReferral .
					 "\n LEFT JOIN #__users AS users" .
					 "\n ON sess.user_id_person = users.id" .
					 "\n LEFT JOIN (SELECT user_id, MAX(group_id) AS group_id FROM #__user_usergroup_map GROUP BY user_id) map" .
					 "\n ON map.user_id = users.id" .
					 "\n LEFT JOIN #__usergroups AS userg" .
					 "\n ON map.group_id = userg.id" .
					 $this->whereQuery . " ORDER BY sess.visit_timestamp DESC) AS INTABLE" .
					 $this->whereQuery .
					 "\n GROUP BY " . $this->_db->quoteName('session_id_person') .
					 "\n ORDER BY " . $this->_db->quoteName('customer_name');
		} else {
			// Parametric referral JOIN
			if($this->cParams->get('show_referral', 0)) {
				$selectReferral = "," . $this->_db->quoteName('referral');
				$selectReferReferral = ', refer.referral';
				$leftJoinReferral = "\n LEFT JOIN" .
									"\n (SELECT " .  $this->_db->quoteName('session_id_person') . ", " . $this->_db->quoteName('referral') .
									"\n FROM " .  $this->_db->quoteName('#__realtimeanalytics_referral') . 
									"\n WHERE " .  $this->_db->quoteName('record_date') . " >= " . $this->_db->quote($this->intervalFrom) .
			  						"\n AND " .  $this->_db->quoteName('record_date') . " <= " . $this->_db->quote( $this->intervalTo ) . 
			  						"\n GROUP BY " .  $this->_db->quoteName('session_id_person') . ") refer" .
				 					"\n ON sess.session_id_person = refer.session_id_person";
			}
			
			$query = "SELECT COUNT(*), " . $this->_db->quoteName('customer_name') . "," .
					 "\n MAX(". $this->_db->quoteName('visit_timestamp') . ")," .
					 $this->_db->quoteName('browser') . "," .
					 $this->_db->quoteName('os') . "," .
					 $this->_db->quoteName('session_id_person') . "," .
					 $this->_db->quoteName('ip') . "," .
					 "\n SUM(" . $this->_db->quoteName('impulse') . ")," .
					 $this->_db->quoteName('geolocation') . "," .
					 $this->_db->quoteName('device') .
					 $selectReferral .
					 "\n FROM ( SELECT sess.* $selectReferReferral FROM #__realtimeanalytics_serverstats AS sess" .
					 $leftJoinReferral .
					 $this->whereQuery . " ORDER BY sess.visit_timestamp DESC) AS INTABLE" .
					 $this->whereQuery .
					 "\n GROUP BY " . $this->_db->quoteName('session_id_person') .
					 "\n ORDER BY " . $this->_db->quoteName('customer_name');
		}
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $results;
	}
	
	/**
	 * Array contenente il numero di pagine, last visit date, browser, os,  GROUP BY session_id_person
	 *
	 * @access private
	 * @return array
	 */
	private function totalVisitedPagesPerIPAddress() {
		$query = "SELECT COUNT(*), " .
				 "\n MAX(". $this->_db->quoteName('visit_timestamp') . ")," .
				 $this->_db->quoteName('ip') . "," .
				 "\n SUM(" . $this->_db->quoteName('impulse') . ")," .
				 $this->_db->quoteName('geolocation') .
				 "\n FROM ( SELECT * FROM #__realtimeanalytics_serverstats " . $this->whereQuery . " ORDER BY " . $this->_db->quoteName('visit_timestamp') . " DESC) AS INTABLE" .
				 $this->whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('ip') .
				 "\n ORDER BY " . $this->_db->quoteName('ip');
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
	
		return $results;
	}
	
	/**
	 * Numero di utenti totali DISTINCT per il periodo
	 *
	 * @access private
	 * @return int
	 */
	private function totalVisitors() {
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . "))" .  
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery;
		$this->_db->setQuery($query);
		$result = (int)$this->_db->loadResult();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $result;
	}
	
	/**
	 * Numero di visitatori unici totali IP DISTINCT per il periodo
	 *
	 * @access private
	 * @return int
	 */
	private function totalUniqueVisitors() {
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('ip') . "))" .
				"\n FROM  #__realtimeanalytics_serverstats" .
				$this->whereQuery;
		$this->_db->setQuery($query);
		$result = (int)$this->_db->loadResult();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
	
		return $result;
	}
	
	/**
	 * Calcolo del bounce rate, rapporto tra utenti totali e utenti che hanno visitato una sola pagina
	 *
	 * @access private
	 * @param int $totalVisitors
	 * @return string
	 */
	private function bounceRate($totalVisitors) {
		// Avoid division by zero
		if(!$totalVisitors) {
			return 0;
		}
		
		$query = "SELECT COUNT(*) AS numusers FROM (" .
				  	"\n SELECT COUNT(*) AS " . $this->_db->quoteName('pages') . "," . 
				  	$this->_db->quoteName('impulse') .
				  	"\n FROM " . $this->_db->quoteName('#__realtimeanalytics_serverstats') . 
				  	$this->whereQuery .
				  	"\n GROUP BY " . $this->_db->quoteName('session_id_person') .
				 "\n ) AS " . $this->_db->quoteName('innertable') . 
				 "\n WHERE " . $this->_db->quoteName('pages') . " = 1" .
				 "\n AND " . $this->_db->quoteName('impulse') . " <= 5";
		$this->_db->setQuery($query);
		$onepageVisitorsCount = (int)$this->_db->loadResult();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		$bounceRate = round (($onepageVisitorsCount / $totalVisitors ) * 100, 2);
	
		return $bounceRate . '%';
	}
	
	/**
	 * Durata media della visita per il singolo utente
	 *
	 * @access private
	 * @return int in secondi
	 */
	private function mediumVisitTime() {
		$query = "SELECT SUM(". $this->_db->quoteName('impulse') . ")".
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('session_id_person');
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		$mediumVisitTime = 0;
		
		if(count($results)) {
			// Ciclo di calcolo per determinare il tempo medio di visita
			$totalVisitedTime = 0;
			$daemonRefresh = $this->cParams->get('daemonrefresh', 2);
			foreach ($results as $result) {
				$totalVisitedTime += $result[0] * $daemonRefresh;
			}
			
			// Media di visita
			$totalVisitors = count($results);
			$mediumVisitTime = (int)$totalVisitedTime / $totalVisitors; 
			$mediumVisitTime = gmdate('H:i:s', $mediumVisitTime);
		}
		
		return $mediumVisitTime;
	}
	
	/**
	 * Numero medio di pagine viste per il singolo utente
	 *
	 * @access private
	 * @return int
	 */
	private function mediumVisitedPagesPerSingleUser() {
		$query = "SELECT COUNT(" . $this->_db->quoteName('visitedpage') . ")," . 
				 "\n COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . "))" .
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery;
		$this->_db->setQuery($query);
		$result = $this->_db->loadRow();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		// Calcolo pagine medie viste per utente
		$mediumVisistedPage = 0;
		if($result[0] && $result[1]) {
			$mediumVisistedPage = sprintf('%.2f', $result[0] / $result[1]);
		}
		
		return $mediumVisistedPage;
	}

	/**
	 * Array contenente il numero di utenti GROUP BY geolocation
	 * 
	 * @access private
	 * @return array 
	 */
	private function numUsersGeoGrouped() {
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . ")) AS numusers," .  
				 $this->_db->quoteName('geolocation') .
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('geolocation') .
				 "\n ORDER BY " . $this->_db->quoteName('numusers') . " DESC," .
				 				  $this->_db->quoteName('geolocation') . " ASC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $results;
	}

	/**
	 * Array contenente il numero di utenti GROUP BY browser
	 * 
	 * @access private
	 * @return array 
	 */
	private function numUsersBrowserGrouped() {
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . "))  AS numusers," .     
				 $this->_db->quoteName('browser') .
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('browser') .
				 "\n ORDER BY " . $this->_db->quoteName('numusers') . " DESC," .
				 				  $this->_db->quoteName('browser') . " ASC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $results;
	}

	/**
	 * Array contenente il numero di utenti GROUP BY os
	 * 
	 * @access private
	 * @return array 
	 */
	private function numUsersOSGrouped() {
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . "))  AS numusers," .    
				 $this->_db->quoteName('os') .
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('os') .
				 "\n ORDER BY " . $this->_db->quoteName('numusers') . " DESC," .
				 				  $this->_db->quoteName('os') . " ASC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
		
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $results;
	}
	
	/**
	 * Array contenente il numero di utenti GROUP BY device family
	 *
	 * @access private
	 * @return array
	 */
	private function numUsersDeviceGrouped() {
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . "))  AS numusers," .
				 $this->_db->quoteName('device') .
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery .
				 "\n AND NOT ISNULL(" . $this->_db->quoteName('device') . ") AND " . $this->_db->quoteName('device') . ' != ""' .
				 "\n GROUP BY " . $this->_db->quoteName('device') .
				 "\n ORDER BY " . $this->_db->quoteName('numusers') . " DESC," .
				 $this->_db->quoteName('device') . " ASC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
	
		return $results;
	}
	
	/**
	 * Get records count for referral traffic
	 *
	 * @access private
	 * @return array
	 */
	private function numReferral() {
		$whereQuery = "\n WHERE " .  $this->_db->quoteName('record_date') . " >= " . $this->_db->quote($this->intervalFrom) .
					  "\n AND " .  $this->_db->quoteName('record_date') . " <= " . $this->_db->quote( $this->intervalTo );
		
		$query = "SELECT " . 
				 $this->_db->quoteName('referral') . ", " .
				 $this->_db->quoteName('ip') . ", " .
				 "\n COUNT(DISTINCT " . $this->_db->quoteName('ip') . ") AS " . $this->_db->quoteName('ipcounter') . ", " .
				 "\n COUNT(*) AS " . $this->_db->quoteName('counter') . ", " .
				 "\n (SELECT COUNT(*) FROM #__realtimeanalytics_referral " . $whereQuery . ") AS " .  $this->_db->quoteName('totalcounter') .
				 "\n FROM  #__realtimeanalytics_referral" .
				 $whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('referral') .
				 "\n ORDER BY " . $this->_db->quoteName('counter') . " DESC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
	
		return $results;
	}
	
	/**
	 * Get records for searches keywords phrase
	 *
	 * @access private
	 * @return array
	 */
	private function searchedPhrase() {
		$whereQuery = "\n WHERE " .  $this->_db->quoteName('record_date') . " >= " . $this->_db->quote($this->intervalFrom) .
					  "\n AND " .  $this->_db->quoteName('record_date') . " <= " . $this->_db->quote( $this->intervalTo );
		
		$query = "SELECT " . $this->_db->quoteName('phrase') . ", " .
				 "\n COUNT(*) AS " . $this->_db->quoteName('counter') . ", " .
				 "\n (SELECT COUNT(*) FROM #__realtimeanalytics_searches " . $whereQuery . ") AS " .  $this->_db->quoteName('totalcounter') .
				 "\n FROM #__realtimeanalytics_searches" .
				 $whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('phrase') .
				 "\n ORDER BY " . $this->_db->quoteName('counter') . " DESC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
	
		return $results;
	}
	
	/**
	 * Classifica delle pagine più abbandonate, AKA le ultime
	 * pagine viste dagli utenti dalla più ultima visitata
	 * Leave off page - Necessita di una COUNT GROUPED BY session_id_customer
	 * delle pagine con MAX visit_timestamp
	 *
	 * @access private
	 * @return array
	 */
	private function hitLeaveOffPages() { 
		$query = "SELECT COUNT(" . $this->_db->quoteName('visitedpage') . ") AS mostleaved," . 
				$this->_db->quoteName('visitedpage') .
				"\n FROM  #__realtimeanalytics_serverstats" .
				$this->whereQuery .
				"\n AND " . $this->_db->quoteName('visit_timestamp') . " IN (" .
				"\n SELECT MAX(" . $this->_db->quoteName('visit_timestamp') . ")" .
				"\n FROM " . $this->_db->quoteName('#__realtimeanalytics_serverstats') .
				$this->whereQuery .
				"\n	GROUP BY " . $this->_db->quoteName('session_id_person') . ")" . 
				"\n GROUP BY " . $this->_db->quoteName('visitedpage') .
				"\n ORDER BY " . $this->_db->quoteName('mostleaved') . " DESC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $results;
	}

	/**
	 * Classifica delle landing page, AKA le prime
	 * pagine viste dagli utenti in arrivo 
	 *
	 * @access private
	 * @return array
	 */
	private function hitLandingPages() {
		$query = "SELECT COUNT(" . $this->_db->quoteName('visitedpage') . ") AS mostlanding," .
				$this->_db->quoteName('visitedpage') .
				"\n FROM  #__realtimeanalytics_serverstats" .
				$this->whereQuery .
				"\n AND " . $this->_db->quoteName('visit_timestamp') . " IN (" .
				"\n SELECT MIN(" . $this->_db->quoteName('visit_timestamp') . ")" .
				"\n FROM " . $this->_db->quoteName('#__realtimeanalytics_serverstats') .
				$this->whereQuery .
				"\n	GROUP BY " . $this->_db->quoteName('session_id_person') . ")" .
				"\n GROUP BY " . $this->_db->quoteName('visitedpage') .
				"\n ORDER BY " . $this->_db->quoteName('mostlanding') . " DESC";
		$this->_db->setQuery($query);
		$results = $this->_db->loadRowList();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
		}
		
		return $results;
	}

	/**
	 * Esplica la generazione delle graph images successivamente richiamate
	 * e visualizzate dalla view templates preposta entro ogni stats box
	 * 
	 * @access private
	 * @return boolean
	 */
	private function graphRender() {   
		$this->graphGenerator->buildBars($this->data);
		$this->graphGenerator->buildPies($this->data, $this->getGeoTranslations());
	}

	/**
	 * Esplica il garbage collector delle immagini generate per i grafici
	 * contestualmente all'utente ad esempio 62_graphbar1.png, dwe6tf67wetfwewefew_pie1.png ecc
	 * 
	 * @access protected
	 * @return array Un array dei file immagini cancellati
	 */
	protected function imagesGarbage() {
		// Deleting all files in a directory older than latency = 24h
		$directory = JPATH_COMPONENT_ADMINISTRATOR . '/cache';
	    $filenames = array();
	    $latencyTime = time() - ($this->cParams->get ( 'maxlifetime_file' )*24*60*60);
	    $iterator = new DirectoryIterator($directory);
	    foreach ($iterator as $fileinfo) {
	    	$fileName = $fileinfo->getFilename();
	        if ($fileinfo->isFile() && $fileName != 'index.html') {
	            $filenames[] = array($fileinfo->getMTime(), $fileName);
	        }
	    }
 		
	    $deletedFiles = array();
	    if(sizeof($filenames) > 1) {
	        foreach ($filenames as $fileElem) {
	            if($fileElem[0] < $latencyTime){ 
	                if(unlink($directory."/".$fileElem[1])) {
	                	$deletedFiles[] = $fileElem[1];
	                } 
	            } 
	        }
	    } 
	    return $deletedFiles;
	}
	
	/**
	 * Dependency injection setter del graph instance object generator
	 *
	 * @param Object& $graphInstance <<mockable_di>>
	 * @access public
	 * @return void
	 */
	public function setGraphRenderer(&$graphInstance) {
		$this->graphGenerator = $graphInstance;
	}
	
	/**
	 * Get geolocation translations from DB
	 * 
	 * @access public
	 * @return array[] &
	 */
	public function &getGeoTranslations() {
		static $resultTranslations;
		
		if($resultTranslations) {
			return $resultTranslations;
		}
		$query = "SELECT" .
				$this->_db->quoteName('iso1_code') . "," .
				$this->_db->quoteName('name') . 
				"\n FROM  #__realtimeanalytics_countries_map";
		$this->_db->setQuery($query);
		
		try {
			$resultTranslations = $this->_db->loadAssocList('iso1_code');
			if ($this->_db->getErrorNum()) {
				throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg()), 'error');
			}
		} catch (JRealtimeException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
			$result = array();
		} catch (Exception $e) {
			$jRealtimeException = new JRealtimeException($e->getMessage(), 'error');
			$this->app->enqueueMessage($jRealtimeException->getMessage(), $jRealtimeException->getErrorLevel());
			$result = array();
		}
		
		return $resultTranslations;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array();
		
		// Supported graph theme
		$themes = array();
		$themes[] = JHtml::_('select.option', 'Universal', 'Universal');
		$themes[] = JHtml::_('select.option', 'Aqua', 'Aqua');
		$themes[] = JHtml::_('select.option', 'Orange', 'Orange');
		$themes[] = JHtml::_('select.option', 'Pastel', 'Pastel');
		$themes[] = JHtml::_('select.option', 'Rose', 'Rose');
		$themes[] = JHtml::_('select.option', 'Softy', 'Softy');
		$themes[] = JHtml::_('select.option', 'Vivid', 'Vivid');
		
		$lists ['graphTheme'] = JHtml::_ ( 'select.genericlist', $themes, 'graphtheme', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState('graphTheme', 'Universal'));
		
		return $lists;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData() {
		// Manage stats report building match as the first operation
		$statsReport = $this->getState ( 'statsReport', 'full' );
		if(array_key_exists($statsReport, $this->mappingReports)) {
			foreach ($this->mappingReports[$statsReport] as $excludedStats) {
				$this->cParams->set($excludedStats, false);
			}
		}
		
		// Images graph garbage collector start
		$randomNumber = rand(0, 100);
		if(0 < $randomNumber && $randomNumber <= (int) $this->cParams->get ( 'probability' )){
			$this->imagesGarbage();
		}

		// Store period nelle class properties
		$this->intervalFrom = $this->getState('fromPeriod');
		$this->intervalTo = $this->getState('toPeriod');
		$this->whereQuery = "\n WHERE " .  $this->_db->quoteName('visitdate') . " >= " . $this->_db->quote($this->intervalFrom) .
		 					"\n AND " .  $this->_db->quoteName('visitdate') . " <= " . $this->_db->quote( $this->intervalTo );
		
		// Calculation dei dati da presentare all'utente
		try {
			if($this->cParams->get('visitsbypage_stats', true)) {
				$this->data[] = $this->visitsPerPage();
			} else {
				$this->data[] = array();
			}
			
		  	$this->data[] = $this->totalVisitedPages();
		  	
		  	if($this->cParams->get('visitsbyuser_stats', true)) {
		  		$this->data[] = $this->totalVisitedPagesPerUser();  
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
		  	$this->data[] = $totalRangeVisitors = $this->totalVisitors();
		  	$this->data[] = $this->mediumVisitTime();
		  	$this->data[] = $this->mediumVisitedPagesPerSingleUser();
		  	
		  	if($this->cParams->get('geolocation_stats', true)) {
				$serverStatsGeoUsers = $this->numUsersGeoGrouped();
				$clientStatsGeoUsers = array();
				foreach ($serverStatsGeoUsers as $data) {
					$clientStatsGeoUsers[strtolower($data[1])] = $data[0];
				}
				$this->data[] = array('serverside'=>$serverStatsGeoUsers, 'clientside'=>$clientStatsGeoUsers);
			} else {
				$this->data[] = array();
			}
		  	
		  	if($this->cParams->get('browser_stats', true)) {
		  		$this->data[] = $this->numUsersBrowserGrouped();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
			if($this->cParams->get('os_stats', true)) {
		  		$this->data[] = $this->numUsersOSGrouped();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
		  	if($this->cParams->get('leaveoff_stats', true)) {
			  	$this->data[] = $this->hitLeaveOffPages();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
		  	if($this->cParams->get('landing_stats', true)) {
			  	$this->data[] = $this->hitLandingPages();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
		  	if($this->cParams->get('referral_stats', true)) {
		  		$this->data[] = $this->numReferral();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
		  	if($this->cParams->get('searchkeys_stats', true)) {
		  		$this->data[] = $this->searchedPhrase();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
		  	if($this->cParams->get('visitsbyip_stats', true)) {
		  		$this->data[] = $this->totalVisitedPagesPerIPAddress();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
		  	// Bounce rate calculation
		  	$this->data[] = $this->bounceRate($totalRangeVisitors);

		  	// Total unique visitors
		  	$this->data[] = $this->totalUniqueVisitors();
		  	
		  	if($this->cParams->get('device_stats', true)) {
		  		$this->data[] = $this->numUsersDeviceGrouped();
		  	} else {
		  		$this->data[] = array();
		  	}
		  	
			// Generazione nuove immagini grafici su filesystem richiamabili dalla view in base all'id utente
			$this->graphRender();
		} catch (JRealtimeException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
			$this->data = array();
		} catch (Exception $e) {
			$jRealtimeException = new JRealtimeException($e->getMessage(), 'error');
			$this->app->enqueueMessage($jRealtimeException->getMessage(), $jRealtimeException->getErrorLevel());
			$this->data = array();
		}
		
		return $this->data;
	}
	
	/**
	 * Secondary get data method to obtain only counters data used for example for the module view
	 *
	 * @access public
	 * @return mixed Array of data if successful or an exception message string on errors
	 */
	public function getDataCounters() {
		// Store period nelle class properties
		$this->intervalFrom = $this->getState('fromPeriod');
		$this->intervalTo = $this->getState('toPeriod');
		$this->whereQuery = "\n WHERE " .  $this->_db->quoteName('visitdate') . " >= " . $this->_db->quote($this->intervalFrom) .
							"\n AND " .  $this->_db->quoteName('visitdate') . " <= " . $this->_db->quote( $this->intervalTo );

		// Calculation dei dati da presentare all'utente
		try {
			$this->data['total_visited_pages'] = $this->totalVisitedPages();
			$this->data['total_visitors'] = $this->totalVisitors();
			$this->data['medium_visit_time'] = $this->mediumVisitTime();
			$this->data['medium_page_per_user'] = $this->mediumVisitedPagesPerSingleUser();

			// Check if even the data for the visual geo map are needed
			if($this->cParams->get('visualmap_stats', 0)) {
				$serverStatsGeoUsers = $this->numUsersGeoGrouped();
				$clientStatsGeoUsers = array();
				foreach ($serverStatsGeoUsers as $data) {
					$clientStatsGeoUsers[strtolower($data[1])] = $data[0];
				}
				$this->data['geomap_data'] = $clientStatsGeoUsers;
			}
			
			// Generic visitor counter without time limit
			if($this->cParams->get('visitors_counter', 0)) {
				$this->whereQuery = null;
				$this->data['visitors_counter'] = $this->totalVisitors();
			}
		} catch (JRealtimeException $e) {
			return $e->getMessage();
		} catch (Exception $e) {
			$jRealtimeException = new JRealtimeException($e->getMessage(), 'error');
			return $jRealtimeException->getMessage();
		}

		return $this->data;
	}
	
	/**
	 * Load details entity
	 *
	 * @access public
	 * @param string $identifier
	 * @param string $detailType
	 * @return Object[]
	 */
	public function loadStatsEntity($identifier, $detailType) {
		// Init query
		$query = null;
		
		// Switch load data in base al tipo di detail richiesto
		switch ($detailType){
			case 'user':
			case 'flow':
				$selectJoin = null;
				$leftJoin = null;
				// Where fixed query construction
				$this->whereQuery = "\n WHERE srvstats.visitdate >= " . $this->_db->quote($this->getState('fromPeriod')) .
									"\n AND srvstats.visitdate <= " . $this->_db->quote($this->getState('toPeriod'));
				if($this->cParams->get('xtd_singleuser_stats', 0)) {
					$selectJoin = ",\n srvstats.ip, srvstats.geolocation, srvstats.browser, srvstats.os, srvstats.device, usrs.email";
					$leftJoin = "\n LEFT JOIN #__users AS usrs ON srvstats.user_id_person = usrs.id";
				}
				$query = "SELECT srvstats.visitedpage," . 
						 "\n srvstats.session_id_person," .
						 "\n srvstats.customer_name," .
						 "\n srvstats.visit_timestamp," .
						 "\n srvstats.impulse".  
						 $selectJoin .
						 "\n FROM " . $this->_db->quoteName('#__realtimeanalytics_serverstats') . " AS srvstats" .
						 $leftJoin .
						 $this->whereQuery . 
						 "\n AND srvstats.session_id_person = " . $this->_db->quote($identifier) .
						 "\n ORDER BY srvstats.visit_timestamp DESC";
				break;
				
			case 'page':
				$selectJoin = null;
				// Where fixed query construction
				$this->whereQuery = "\n WHERE " .  $this->_db->quoteName('visitdate') . " >= " . $this->_db->quote($this->getState('fromPeriod')) .
									"\n AND " .  $this->_db->quoteName('visitdate') . " <= " . $this->_db->quote($this->getState('toPeriod'));
				if($this->cParams->get('xtd_singleuser_stats', 0)) {
					$selectJoin = ",\n srvstats.ip, srvstats.geolocation, srvstats.browser, srvstats.os, srvstats.device";
				}
				$query = "SELECT " . $this->_db->quoteName('customer_name') . "," . 
						 "\n ". $this->_db->quoteName('visitedpage') . "," .
						 "\n ". $this->_db->quoteName('visit_timestamp') . "," .
						 "\n " . $this->_db->quote($identifier) . " AS " . $this->_db->quoteName('pageurl') . "," .
						 "\n ". $this->_db->quoteName('impulse') .
						 $selectJoin .
						 "\n FROM " . $this->_db->quoteName('#__realtimeanalytics_serverstats') . " AS srvstats" .
						 $this->whereQuery . 
						 "\n AND " . $this->_db->quoteName('visitedpage') . " = " . $this->_db->quote($identifier) .
						 "\n ORDER BY " . 
						 $this->_db->quoteName('visit_timestamp') . " DESC";
				break;
				
			case 'ip':
				$selectJoin = null;
				// Where fixed query construction
				$this->whereQuery = "\n WHERE srvstats.visitdate >= " . $this->_db->quote($this->getState('fromPeriod')) .
									"\n AND srvstats.visitdate <= " . $this->_db->quote($this->getState('toPeriod'));
				$selectJoin = ",\n srvstats.customer_name, srvstats.geolocation, srvstats.browser, srvstats.os, srvstats.device";
				$query = "SELECT srvstats.visitedpage," .
						 "\n srvstats.session_id_person," .
						 "\n srvstats.customer_name," .
						 "\n srvstats.visit_timestamp," .
						 "\n srvstats.impulse".
						 $selectJoin .
						 "\n FROM " . $this->_db->quoteName('#__realtimeanalytics_serverstats') . " AS srvstats" .
						 $this->whereQuery .
						 "\n AND srvstats.ip = " . $this->_db->quote($identifier) .
						 "\n ORDER BY srvstats.visit_timestamp DESC";
				break;

			case 'referral':
				$selectJoin = null;
				// Where fixed query construction
				$this->whereQuery = "\n WHERE refer.record_date >= " . $this->_db->quote($this->getState('fromPeriod')) .
									"\n AND refer.record_date <= " . $this->_db->quote($this->getState('toPeriod'));
				$query = "SELECT refer.record_date," .
						 "\n refer.ip," .
						 "\n svrstats.geolocation" .
						 "\n FROM " . $this->_db->quoteName('#__realtimeanalytics_referral') . " AS refer" .
						 "\n LEFT JOIN " . $this->_db->quoteName('#__realtimeanalytics_serverstats') . " AS svrstats" .
						 "\n ON refer.ip = svrstats.ip" .
						 $this->whereQuery .
						 "\n AND refer.referral = " . $this->_db->quote($identifier) .
						 "\n GROUP BY refer.ip" .
						 "\n ORDER BY refer.record_date DESC";
				break;
		}
		
		try {
			$this->_db->setQuery($query);
			$results = $this->_db->loadObjectList();
			
			if ($this->_db->getErrorNum()) {
				throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
			}

			// Set timezone if required
			if($this->cParams->get('offset_type', 'joomla') == 'joomla') {
				$jConfig = JFactory::getConfig();
				date_default_timezone_set($jConfig->get('offset', 'UTC'));
			}
		} catch (JRealtimeException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$JRealtimeException = new JRealtimeException($e->getMessage(), 'error');
			$this->setError($JRealtimeException);
			return false;
		}
		
		return $results;
	}
	
	/**
	 * Delete DB cache for all stats tables
	 *
	 * @access public
	 * @return boolean
	 */
	public function deleteEntity($ids) {
		// Check if it's a partial or full delete
		$whereServerStats = null;
		$whereStats = null;
		if($this->getState('task') == 'deletePeriodEntity') {
			$startPeriod = $this->getState('fromPeriod', date('Y-m-d'));
			$toPeriod = $this->getState('toPeriod', date('Y-m-d'));
			$whereServerStats = "\n WHERE " . $this->_db->quoteName('visitdate') . " >= " . $this->_db->quote($startPeriod) .
								"\n AND " . $this->_db->quoteName('visitdate') . " <= " . $this->_db->quote($toPeriod);
			
			$whereStats = "\n WHERE " . $this->_db->quoteName('record_date') . " >= " . $this->_db->quote($startPeriod) .
						  "\n AND " . $this->_db->quoteName('record_date') . " <= " . $this->_db->quote($toPeriod);
		}
		
		try {
			$query = "DELETE FROM #__realtimeanalytics_serverstats" . $whereServerStats;
			$this->_db->setQuery($query);
			if(!$this->_db->execute()) {
				throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
			}
			
			$query = "DELETE FROM #__realtimeanalytics_referral" . $whereStats;
			$this->_db->setQuery($query);
			if(!$this->_db->execute()) {
				throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
			}
			
			$query = "DELETE FROM #__realtimeanalytics_searches" . $whereStats;
			$this->_db->setQuery($query);
			if(!$this->_db->execute()) {
				throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
			}
		} catch (JRealtimeException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jRealtimeException = new JRealtimeException($e->getMessage(), 'error');
			$this->setError($jRealtimeException);
			return false;
		}
		return true;
	}
	
	/**
	 * Main fetch data method already populated
	 *
	 * @access public
	 * @return Object[]
	 */
	public function fetchData() {
		// Force data get if not already done
		if(!count($this->data)) {
			$this->getData();
		}

		return $this->data;
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct() {
		parent::__construct ();
		
		$this->data = array ();
		$this->cParams = $this->getComponentParams ();
		
		// Map the reports to show based on task requested
		$this->mappingReports = array (
				'full' => array (),
				'landingleave' => array (
						'visitsbyip_stats',
						'visitsbypage_stats',
						'visitsbyuser_stats',
						'referral_stats',
						'searchkeys_stats'
				),
				'visitsbypage' => array (
						'visitsbyip_stats',
						'visitsbyuser_stats',
						'leaveoff_stats',
						'landing_stats',
						'referral_stats',
						'searchkeys_stats'
				),
				'visitsbyuser' => array (
						'visitsbyip_stats',
						'visitsbypage_stats',
						'leaveoff_stats',
						'landing_stats',
						'referral_stats',
						'searchkeys_stats'
				),
				'visitsbyip' => array (
						'visitsbypage_stats',
						'visitsbyuser_stats',
						'leaveoff_stats',
						'landing_stats',
						'referral_stats',
						'searchkeys_stats'
				),
				'referralkeys' => array (
						'visitsbypage_stats',
						'visitsbyuser_stats',
						'visitsbyip_stats',
						'leaveoff_stats',
						'landing_stats'
				)
		);
	}
}