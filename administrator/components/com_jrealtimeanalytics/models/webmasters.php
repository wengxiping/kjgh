<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Google model responsibilities for access Google Analytics and Webmasters Tools API
 *
 * @package JREALTIME::SOURCES::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.6
 */
interface IJRealtimeModelWebmasters {
	/**
	 * Get data method for webmasters tools stats
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataWebmasters();

	/**
	 * Return the google token
	 *
	 * @access public
	 * @return string
	 */
	public function getToken();
}

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.6
 */
class JRealtimeModelWebmasters extends JRealtimeModel implements IJRealtimeModelWebmasters {
	/**
	 * Google_Client object
	 *
	 * @access private
	 * @var Google_Client
	 */
	private $client;
	
	/**
	 * Current profile found for Google Analytics
	 *
	 * @access private
	 * @var string
	 */
	private $currentProfile;
	
	/**
	 * Track the API connection mode, built in JRealtime Google App or user own
	 *
	 * @access private
	 * @var string
	 */
	private $hasOwnCredentials;
	
	/**
	 * Purify and normalize domain protocol
	 *
	 * @access private
	 * @return string
	 */
	private function purifyDomain($domain) {
		return str_replace ( array (
				"https://",
				"http://",
				" "
		), "", rtrim ( $domain, "/" ) );
	}
	
	/**
	 * Purify and normalize domain uri for webmasters tools stats
	 *
	 * @access private
	 * @return string
	 */
	private function purifyWebmastersDomain($domain) {
		return str_replace ( array (
				" "
		), "", rtrim ( $domain, "/" ) );
	}
	
	/**
	 * Manage the authentication form and action
	 *
	 * @param Object $params
	 * @access private
	 * @return mixed A string when auth is needed, null if performing an auth
	 */
	private function authentication($params) {
		$this->client = new Google_Client ();
		$this->client->setAccessType ( 'offline' );
		$this->client->setScopes ( array('https://www.googleapis.com/auth/analytics.readonly', 'https://www.googleapis.com/auth/webmasters.readonly'));
		$this->client->setApplicationName ( 'JRealtime Analytics' );
		$this->client->setRedirectUri ( 'urn:ietf:wg:oauth:2.0:oob' );
	
		$this->hasOwnCredentials = false;
		if ($params->get ( 'ga_api_key' ) && $params->get ( 'ga_client_id' ) && $params->get ( 'ga_client_secret' )) {
			$this->client->setClientId ( $params->get ( 'ga_client_id' ) );
			$this->client->setClientSecret ( $params->get ( 'ga_client_secret' ) );
			$this->client->setDeveloperKey ( $params->get ( 'ga_api_key' ) ); // API key
			$this->hasOwnCredentials = true;
		} else {
			$this->client->setClientId ( '872567856644-g1j8hbip0u8vm45ot7sqls53av49b0bg.apps.googleusercontent.com' );
			$this->client->setClientSecret ( '58fP1JwLkNfRSxhuQmvTSkik' );
			$this->client->setDeveloperKey ( 'AIzaSyC-4AcgDEPvXZOJ1KMZky7VgOfz4QrQKyc' );
		}
	
		if ($this->getToken ()) { // extract token from session and configure client
			$token = $this->getToken ();
			$this->client->setAccessToken ( $token );
		}

		if (! $result = $this->client->getAccessToken ()) { // auth call to google
			$authUrl = $this->client->createAuthUrl ();
			// Trying to authenticate?
			if (!$this->app->input->get('ga_dash_authorize')) {
				$JText = 'JText';
				$htmlSnippet = <<<HTML
					<div>
						<p class="well">
							<span class="label label-info">
								{$JText::_ ( 'COM_JREALTIME_GOOGLE_STEP1_CODE_DESC' )}
							</span>
	  						<a class="btn btn-primary btn-sm hasPopover google" data-content="{$JText::_ ( 'COM_JREALTIME_GOOGLE_CODE_INSTUCTIONS' )}" href="$authUrl" target="_blank">
	  							{$JText::_ ( 'COM_JREALTIME_GOOGLE_CODE' )}
	  						</a>
  						</p>

  						<p class="well">
  							<span class="label label-info">
  								{$JText::_ ( 'COM_JREALTIME_GOOGLE_STEP2_ACCESS_CODE_INSERT' )}
  							</span>
  							<input type="text" name="ga_dash_code" value="" size="61">
  						</p>

  						<p class="well">
  							<span class="label label-info">
  								{$JText::_ ( 'COM_JREALTIME_GOOGLE_STEP3_AUTHENTICATE' )}
  							</span>
							<input type="submit" class="btn btn-primary btn-sm waiter" name="ga_dash_authorize" value="{$JText::_ ( 'COM_JREALTIME_GOOGLE_AUTHENTICATE' )}"/>
						</p>
					</div>

HTML;
					return $htmlSnippet;
				} else {
				// Yes! This is an authentication attempt let's try it
				try {
					$this->client->authenticate ( $this->app->input->getString('ga_dash_code'));
  				} catch ( JRealtimeException $e ) {
					$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
					return '<a class="btn btn-primary" href="index.php?option=com_jrealtimeanalytics&amp;task=webmasters.display">' . JText::_ ( 'COM_JREALTIME_GOBACK' ) . '</a>';
				} catch ( Exception $e ) {
					$JRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
					$this->app->enqueueMessage ( $JRealtimeException->getMessage (), $JRealtimeException->getErrorLevel () );
					return '<a class="btn btn-primary" href="index.php?option=com_jrealtimeanalytics&amp;task=webmasters.display">' . JText::_ ( 'COM_JREALTIME_GOBACK' ) . '</a>';
				}

  				// Store the Google token in the DB for further login and authentication
				$this->storeToken ( $this->client->getAccessToken () );

				return null;
			}
		}
	}

	/**
	 * Store the Google token
	 *
	 * @access private
	 * @return boolean
	 */
	private function storeToken($token) {
		$clientID = (int)$this->app->getClientId();
		try {
			$query = "INSERT IGNORE INTO #__realtimeanalytics_google (id, token) VALUES ($clientID, '$token');";
			$this->_db->setQuery ( $query );
			$result = $this->_db->query ();
			
			// Store logged in status in session
			$session = JFactory::getSession();
			$session->set('jrealtime_ga_authenticate', true);
		} catch ( JRealtimeException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			$result = false;
		} catch ( Exception $e ) {
			$JRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $JRealtimeException->getMessage (), $JRealtimeException->getErrorLevel () );
			$result = false;
		}
		
		return $result;
	}
	
	/**
	 * Delete the Google token
	 *
	 * @access private
	 * @return boolean
	 */
	private function deleteToken() {
		$clientID = (int)$this->app->getClientId();
		try {
			$query = "DELETE FROM #__realtimeanalytics_google WHERE id = " . $clientID;
			$this->_db->setQuery ( $query )->execute();
			
			// Store logged in status in session
			$session = JFactory::getSession();
			$session->clear('jrealtime_ga_authenticate');
		} catch ( JRealtimeException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			return false;
		} catch ( Exception $e ) {
			$JRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $JRealtimeException->getMessage (), $JRealtimeException->getErrorLevel () );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get data method for webmasters tools stats
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataWebmasters() {
		$params = $this->getComponentParams ();
	
		// Perform the authentication management before going on
		$authenticationData = $this->authentication ( $params );
		if($authenticationData) {
			$this->state->set('loggedout', true);
			return $authenticationData;
		}

		// Set the analyzed domain in the model state
		$webmastersStatsDomain = $this->purifyWebmastersDomain( $params->get ( 'wm_domain', JUri::root() )) ;
		$this->state->set('stats_domain', $webmastersStatsDomain);
		$this->state->set('has_own_credentials', $this->hasOwnCredentials);

		$results = array();

		try {
			// New Service instance for the API, Google_Service_Webmasters
			$service = new Google_Service_Webmasters ( $this->client );
			$postBody = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
			
			$postBody->setStartDate($this->getState('fromPeriod'));
			$postBody->setEndDate($this->getState('toPeriod'));
			
			// Fetch data metric
			$postBody->setDimensions(array('query'));
			$results['results_query'] = $service->searchanalytics->query($webmastersStatsDomain, $postBody);
				
			// Fetch data metric
			$postBody->setDimensions(array('page'));
			$results['results_page'] = $service->searchanalytics->query($webmastersStatsDomain, $postBody);
		}  catch ( Google_Exception $e ) {
			$JRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $JRealtimeException->getMessage (), $JRealtimeException->getErrorLevel () );
			$result = array();
		} catch ( Exception $e ) {
			$JRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $JRealtimeException->getMessage (), $JRealtimeException->getErrorLevel () );
			$result = array();
		}

		return $results;
	}

	/**
	 * Return the google token
	 *
	 * @access public
	 * @return string
	 */
	public function getToken() {
		$clientID = (int)$this->app->getClientId();
		try {
			$query = "SELECT token FROM #__realtimeanalytics_google WHERE id = " . $clientID;
			$this->_db->setQuery ( $query );
			$result = $this->_db->loadResult ();
		} catch ( JRealtimeException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			$result = null;
		} catch ( Exception $e ) {
			$JRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $JRealtimeException->getMessage (), $JRealtimeException->getErrorLevel () );
			$result = null;
		}
		return $result;
	}

	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array ();
		return $lists;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters() {
		$filters = array ();
		return $filters;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids
	 * @access public
	 * @return boolean
	 */
	public function deleteEntity($ids) {
		return $this->deleteToken();
	}
	
}