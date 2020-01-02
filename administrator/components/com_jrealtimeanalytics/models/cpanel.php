<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
define ('SERVER_REMOTE_URI', 'http://storejextensions.org/dmdocuments/updates/');
define ('UPDATES_FORMAT', '.json');

/**
 * CPanel model responsibilities contract
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
interface ICPanelModel {
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return array
	 */
	public function getData();
	
	/**
	 * Get by remote server informations for new updates of this extension
	 *
	 * @access public
	 * @return mixed An object json decoded from server if update information retrieved correctly otherwise false
	 */
	public function getUpdates(IJRealtimeHttp $httpClient);
}
 
/**
 * CPanel model concrete implementation
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
class JRealtimeModelCpanel extends JRealtimeModel implements ICPanelModel {
	/**
	 * Hold the start period by config
	 * 
	 * @access private
	 * @var string
	 */
	private $intervalFrom;
	
	/**
	 * Hold the end period by config
	 *
	 * @access private
	 * @var string
	 */
	private $intervalTo;
	
	/**
	 * Common WHERE period filter
	 *
	 * @access private
	 * @var string
	 */
	private $whereQuery;
	
	/**
	 * Total users by period
	 *
	 * @access protected
	 * @param string $field
	 * @param string $value
	 * @return string
	 */
	protected function buildListQueryTotalUsers() {
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . "))" .  
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery;
		
		return $query;
	}
	
	/**
	 * Total pages by period
	 *
	 * @access protected
	 * @param string $field
	 * @param string $value
	 * @return string
	 */
	protected function buildListQueryTotalPages() {
		$query = "SELECT COUNT(*)" .  
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $this->whereQuery;
		return $query;
	}
	
	/**
	 * Total users registered to system
	 *
	 * @access protected
	 * @param string $field
	 * @param string $value
	 * @return string
	 */
	protected function buildListQuerySystemUsers() {
		$query = "SELECT COUNT(*) FROM #__users" .
	 	 		 "\n WHERE " . $this->_db->quoteName('block') . " = 0";
		return $query;
	}
	
	/**
	 * Total active events
	 *
	 * @access protected
	 * @param string $field
	 * @param string $value
	 * @return string
	 */
	protected function buildListQuerySystemEvents() {
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_eventstats" .
	 	 		 "\n WHERE " . $this->_db->quoteName('published') . " = 1";
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
		// Filter based on config settings period interval
		$fromPeriod = $this->getState('fromPeriod');
		$toPeriod = $this->getState('toPeriod');
		
		$whereQuery = "\n WHERE " .  $this->_db->quoteName('visitdate') . " >= " . $this->_db->quote($fromPeriod) .
					  "\n AND " .  $this->_db->quoteName('visitdate') . " <= " . $this->_db->quote($toPeriod);
		
		$query = "SELECT COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . ")) AS numusers," .  
				 $this->_db->quoteName('geolocation') .
				 "\n FROM  #__realtimeanalytics_serverstats" .
				 $whereQuery .
				 "\n GROUP BY " . $this->_db->quoteName('geolocation');
		
		return $query;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return array
	 */
	public function getData() {
		// Store period nelle class properties, fixed not from userstate but only da config
		$this->intervalFrom = $this->getState('fromPeriod');
		$this->intervalTo = $this->getState('toPeriod');
		
		$this->whereQuery = "\n WHERE " .  $this->_db->quoteName('visitdate') . " >= " . $this->_db->quote($this->intervalFrom) .
							"\n AND " .  $this->_db->quoteName('visitdate') . " <= " . $this->_db->quote( $this->intervalTo );
		
		$calculatedStats = array();
		// Build queries
		try {
			// Total users visitors
			$query = $this->buildListQueryTotalUsers ();
			$this->_db->setQuery ( $query );
			$calculatedStats ['chart_period_canvas'] ['totalusers'] = $this->_db->loadResult ();
			// Total global modules
			if ($this->_db->getErrorNum ()) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_DBERROR_STATS' ) . $this->_db->getErrorMsg (), 'error' );
			}
			
			// Total viewed pages
			$query = $this->buildListQueryTotalPages ();
			$this->_db->setQuery ( $query );
			$calculatedStats ['chart_period_canvas'] ['totalpages'] = $this->_db->loadResult ();
			// Total published modules
			if ($this->_db->getErrorNum ()) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_DBERROR_STATS' ) . $this->_db->getErrorMsg (), 'error' );
			}
			
			// Total system users
			$query = $this->buildListQuerySystemUsers ();
			$this->_db->setQuery ( $query );
			$calculatedStats ['chart_generic_canvas'] ['systemusers'] = $this->_db->loadResult ();
			// Total published modules
			if ($this->_db->getErrorNum ()) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_DBERROR_STATS' ) . $this->_db->getErrorMsg (), 'error' );
			}
			
			// Total system events
			$query = $this->buildListQuerySystemEvents ();
			$this->_db->setQuery ( $query );
			$calculatedStats ['chart_generic_canvas'] ['systemevents'] = $this->_db->loadResult ();
			// Total published modules
			if ($this->_db->getErrorNum ()) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_DBERROR_STATS' ) . $this->_db->getErrorMsg (), 'error' );
			}
			
			// Data for geo map
			$query = $this->buildListQueryStatsMap ();
			$this->_db->setQuery ( $query );
			$geolocationData = $this->_db->loadAssocList ( 'geolocation' );
			// Total published modules
			if ($this->_db->getErrorNum ()) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_DBERROR_STATS' ) . $this->_db->getErrorMsg (), 'error' );
			}
			// Normalize DB Data
			foreach ( $geolocationData as $country => $singleData ) {
				$calculatedStats ['geomap'] [strtolower ( $country )] = $singleData ['numusers'];
			}
		} catch ( JRealtimeException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			$calculatedStats = array ();
		} catch ( Exception $e ) {
			$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
			$calculatedStats = array ();
		}
	
		return $calculatedStats;
	}
	
	/**
	 * Get by remote server informations for new updates of this extension
	 *
	 * @access public
	 * @return mixed An object json decoded from server if update information retrieved correctly otherwise false
	 */
	public function getUpdates(IJRealtimeHttp $httpClient) {
		// Updates server remote URI
		$option = $this->getState ( 'option', 'com_jrealtimeanalytics' );
		if (! $option) {
			return false;
		}
		$url = SERVER_REMOTE_URI . $option . UPDATES_FORMAT;
		
		// Try to get informations
		try {
			$response = $httpClient->get ( $url )->body;
			if ($response) {
				$decodedUpdateInfos = json_decode ( $response );
			}
			return $decodedUpdateInfos;
		} catch ( JRealtimeException $e ) {
			return false;
		} catch ( Exception $e ) {
			return false;
		}
	}
}