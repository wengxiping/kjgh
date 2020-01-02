<?php
// namespace components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::STREAM::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Stream model
 * The entity to perform CRUD operation on, here is the Stream
 * It supports special get/store/delete responsibilities to be a
 * more generic stream resource for every service
 *
 * @package JREALTIMEANALYTICS::STREAM::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.0
 */
class JRealtimeModelStream extends JRealtimeModelObservable {
	/**
	 * Session Object
	 *
	 * @access private
	 * @var Object &
	 */
	private $session;
	
	/**
	 * Me user Object
	 *
	 * @access private
	 * @var Object &
	 */
	private $myUser;
	
	/**
	 * Component config
	 *
	 * @access private
	 * @var Object &
	 */
	private $config;
	
	/**
	 * Max lifetime for inactivity time of Realtime display stats
	 *
	 * @access private
	 * @var int
	 */
	private $maxInactivityTime;
	
	/**
	 * Sess SQL query for the client id
	 * @access private
	 * @var string
	 */
	private $sessClientId;
	
	/**
	 * Array associativo della response HTTP
	 *
	 * @access private
	 * @var array
	 */
	private $response;
	
	/**
	 * Load realtime users on current page posted
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeUsersOnPage() {
		// Retrieve and ensure that a nowpage for current user is provided
		$nowPage = $this->getState('nowpage', null);
		if(!$nowPage) {
			return 0; // No counter active
		}

		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->_db->quoteName('nowpage') . " = " . $this->_db->quote($nowPage) .
				 "\n AND " . $this->sessClientId;
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_REALTIME_STATS_USERSONPAGE', $this->_db->getErrorMsg()), 'error', 'Realtime display stats');
		}

		return $result;
	}
	
	/**
	 * Load realtime users currently on site in some status
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeUsersTotal() {
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId;
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_REALTIME_STATS_USERSTOTAL', $this->_db->getErrorMsg()), 'error', 'Realtime display stats');
		}
	
		return $result;
	}
	
	/**
	 * Load realtime users logged on site
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeUsersLogged() {
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId .
				 "\n AND sess.guest = 0";
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_REALTIME_STATS_USERSLOGGED', $this->_db->getErrorMsg()), 'error', 'Realtime display stats');
		}
	
		return $result;
	}
	
	/**
	 * Load realtime visitors on site
	 *
	 * @access protected
	 * @return int
	 */
	protected function getRealtimeVisitors() {
		$query = "SELECT COUNT(*) FROM #__realtimeanalytics_realstats AS stats" .
				 "\n INNER JOIN #__session AS sess" .
				 "\n ON sess.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('lastupdate_time') . " > " . (int)(time() - $this->maxInactivityTime) .
				 "\n AND " . $this->sessClientId .
				 "\n AND sess.guest = 1";
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
	
		if ($this->_db->getErrorNum()) {
			throw new JRealtimeException(JText::sprintf('COM_JREALTIME_ERROR_REALTIME_STATS_VISITORS', $this->_db->getErrorMsg()), 'error', 'Realtime display stats');
		}
	
		return $result;
	}
	
	/**
	 * Execute della app logic da controller
	 *
	 * @access public
	 * @return Object The response object to be encoded for JS app
	 */
	public function getData() {
		$initialize = $this->getState ( 'initialize', false );
		// Store server stats con dependency injected object
		$userName = $this->myUser->name;
		if (! $userName) {
			$userName = JRealtimeHelpersUsers::generateRandomGuestNameSuffix ( $this->session->session_id, $this->cParams );
		}
		$this->setState('username', $userName);
		$this->setState('userid', $this->myUser->id);
		
		// Observers notify
		$observersResponses = $this->notify();
		
		// Manage observers exceptions for JS App debug
		foreach ($observersResponses as $observersResponse) {
			// Found an exception, set in JS app response for client side debug
			if ($observersResponse instanceof JRealtimeException) {
				$this->response['storing'][] = array('corefile'=>$observersResponse->getFile(), 'status'=>false, 'details'=>$observersResponse->getMessage());
			}
		}
		
		// Se è l'initialize = 1 ovvero la prima ajax call store server stats
		if ($initialize) {
			// Inject dei parametri che condizionano la restante parte nella JS APP
			if (! empty ( $this->cParams )) {
				$this->response ['configparams'] = $this->cParams->toObject ();
				unset($this->response['configparams']->rules);
			}
		}
		
		// If Realtime display stats are requested by module populating, go on to retrive realtime data and inject into response
		if($this->cParams->get('realtime_stats', false) && $this->getState('module_available', false)) {
			// Init data bind response
			$this->response['data-bind'] = array();

			// Retrieve realtime informations and manage exceptions for users
			try {
				$this->response['data-bind']['users_onpage'] = $this->getRealtimeUsersOnPage();
				$this->response['data-bind']['users_total'] = $this->getRealtimeUsersTotal();
				$this->response['data-bind']['users_logged'] = $this->getRealtimeUsersLogged();
				$this->response['data-bind']['visitors'] = $this->getRealtimeVisitors();
			} catch (JRealtimeException $e) {
				$this->response['loading'][] = array('corefile'=>$e->getFile(), 'details'=>$e->getMessage());
			} catch (Exception $e) {
				$jrealtimeException = new JRealtimeException ( JText::sprintf ( 'COM_JREALTIME_ERROR_REALTIME_STATS_DATABASE', $e->getMessage () ), 'error', 'Realtime display stats' );
				$this->response['loading'][] = array('corefile'=>$jrealtimeException->getFile(), 'details'=>$jrealtimeException->getMessage());
			}
		}
		
		return $this->response;
	}
	
	/**
	 * Store banning state for current session id user
	 *
	 * @access public
	 * @param string $resourceType for future REST usage
	 * @return array
	 */
	public function storeEntityResource($resourceType) {
		try {
			// Check if the unique key already exists
			$query = "SELECT " . $this->_db->quoteName('id') .
					 "\n FROM #__realtimeanalytics_heatmap" .
					 "\n WHERE" .
					 "\n " . $this->_db->quoteName('selector') . " = " . $this->_db->quote($this->getState('clicked_element')) .
					 "\n AND " . $this->_db->quoteName('pageurl') . " = " . $this->_db->quote($this->getState('nowpage'));
			$heatmapID = $this->_db->setQuery($query)->loadResult();
			if($this->_db->getErrorNum()) {
				throw new JRealtimeException(JText::_('COM_JREALTIME_ERROR_WRITING_ONSTREAM') . $this->_db->getErrorMsg(), 'error', 'Stream model storeEntityResource');
			}

			// If it's a new record and the heatmap ID is not existant place a new row and retrieve last ID
			if(!$heatmapID) {
				$query = "INSERT INTO #__realtimeanalytics_heatmap (" .
						 $this->_db->quoteName('selector') . ", " .
						 $this->_db->quoteName('pageurl') .
						 ") VALUES (" .
						 $this->_db->quote($this->getState('clicked_element')) . "," .
						 $this->_db->quote($this->getState('nowpage')) .
						 ")";
				$this->_db->setQuery($query)->execute ();
				if($this->_db->getErrorNum()) {
					throw new JRealtimeException(JText::_('COM_JREALTIME_ERROR_WRITING_ONSTREAM') . $this->_db->getErrorMsg(), 'error', 'Stream model storeEntityResource');
				}

				// Retrieve last generated ID
				$heatmapID = $this->_db->insertid();
			}

			// Always insert a new click tracking record
			$query = "INSERT INTO #__realtimeanalytics_heatmap_clicks (" .
					$this->_db->quoteName('record_date') . ", " .
					$this->_db->quoteName('heatmap_id') .
					") VALUES (" .
					$this->_db->quote(date('Y-m-d')) . "," .
					(int)$heatmapID .
					")";
			$this->_db->setQuery($query)->execute ();
			if($this->_db->getErrorNum()) {
				throw new JRealtimeException(JText::_('COM_JREALTIME_ERROR_WRITING_ONSTREAM') . $this->_db->getErrorMsg(), 'error', 'Stream model storeEntityResource');
			}
		} catch (JRealtimeException $e) {
			$this->response['storing'][] = array('corefile'=>$e->getFile(), 'status'=>false, 'details'=>$e->getMessage());
			return $this->response;
	
		} catch (Exception $e) {
			$jrealtimeException = new JRealtimeException($e->getMessage(), 'error', 'Stream model storeEntityResource');
			$this->response['storing'][] = array('corefile'=>$jrealtimeException->getFile(), 'status'=>false, 'details'=>$jrealtimeException->getMessage());
			return $this->response;
		}
			
		return $this->response;
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config        	
	 * @return Object&
	 */
	public function __construct($config = array()) {
		// Hold JS client app response
		$this->response = array ();
		
		// Session table
		$this->session = $config ['sessiontable'];
		
		// User object
		$this->myUser = JFactory::getUser ();
		
		// Component config with override management by model
		$this->cParams = $this->getComponentParams();
		
		// Set max life time for valid session on Realtime display stats
		$this->maxInactivityTime = $this->cParams->get('maxlifetime_session', 8);
		
		parent::__construct ( $config );
		
		// Evaluate the shared session option for SQL queries on Joomla 3.7+
		$sharedSession = (int)$this->app->get('shared_session', null);
		if($sharedSession == 1 && $this->cParams->get('shared_session_support', 1)) {
			$this->sessClientId = '(sess.client_id = 0 OR ISNULL(sess.client_id))';
		} else {
			$this->sessClientId = 'sess.client_id = 0';
		}
	}
}