<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JREALTIMEANALYTICS::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.5
 */
class JRealtimeModelGoogle extends JRealtimeModel {
	/**
	 * Current profile found for Google Analytics
	 * 
	 * @access private
	 * @var string
	 */
	private $currentProfile;
	
	/**
	 * Track the API connection mode, built in JSitemap Google App or user own
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
			$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
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
		try{
			$query = "DELETE FROM #__realtimeanalytics_google WHERE id = " . $clientID;
			$this->_db->setQuery ( $query )->execute();
			
			// Store logged in status in session
			$session = JFactory::getSession();
			$session->clear('jrealtime_ga_authenticate');
		} catch ( JRealtimeException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			return false;
		} catch ( Exception $e ) {
			$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get visits
	 *
	 * @access private
	 * @return array
	 */
	private function getVisitsByCountry($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:country';
		try {
			$serial = 'gadash_qr7' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		if (! $data ['rows']) {
			return 0;
		}
		
		$ga_dash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Traffic Sources
	 *
	 * @access private
	 * @return array
	 */
	private function getTrafficSources($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:medium';
		try {
			$serial = 'gadash_qr8' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		if (! $data ['rows']) {
			return 0;
		}
		
		$ga_dash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			$ga_dash_data .= "['" . addslashes ( str_replace ( "(none)", "direct", $data ['rows'] [$i] [0] ) ) . "'," . $data ['rows'] [$i] [1] . "],";
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get New vs. Returning
	 *
	 * @access private
	 * @return array
	 */
	private function getNewReturnVisitors($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:userType';
		try {
			$serial = 'gadash_qr9' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		
		if (! $data ['rows']) {
			return 0;
		}
		
		$ga_dash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Top Pages
	 *
	 * @access private
	 * @return array
	 */
	private function getTopPages($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:pageviews';
		$dimensions = 'ga:pageTitle';
		try {
			$serial = 'gadash_qr4' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions,
					'sort' => '-ga:pageviews',
					'max-results' => '24',
					'filters' => 'ga:pagePath!=/' 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		if (! $data ['rows']) {
			return 0;
		}
		
		$ga_dash_data = "";
		$i = 0;
		while ( isset ( $data ['rows'] [$i] [0] ) ) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
			$i ++;
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Top referrers
	 *
	 * @access private
	 * @return array
	 */
	private function getTopReferrers($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:source,ga:medium';
		try {
			$serial = 'gadash_qr5' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions,
					'sort' => '-ga:sessions',
					'max-results' => '24',
					'filters' => 'ga:medium==referral' 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		if (! $data ['rows']) {
			return 0;
		}
		
		$ga_dash_data = "";
		$i = 0;
		while ( isset ( $data ['rows'] [$i] [0] ) ) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [2] . "],";
			$i ++;
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Top searches
	 *
	 * @access private
	 * @return array
	 */
	private function getTopSearches($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:keyword';
		try {
			$serial = 'gadash_qr6' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions,
					'sort' => '-ga:sessions',
					'max-results' => '24',
					'filters' => 'ga:keyword!=(not provided);ga:keyword!=(not set)' 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		if (! $data ['rows']) {
			return 0;
		}
		
		$ga_dash_data = "";
		$i = 0;
		while ( isset ( $data ['rows'] [$i] [0] ) ) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
			$i ++;
		}
		
		return $ga_dash_data;
	}

	/**
	 * Return Google profile identifier object
	 *
	 * @access public
	 * @param string
	 * @return array
	 */
	private function getSitesProfiles($service, $client, $params) {
		try {
			$profile_switch = "";
			$serial = 'gadash_qr1';
			$profiles = $service->management_profiles->listManagementProfiles ( '~all', '~all' );
		} catch ( Exception $e ) {
			return $e;
		}
		
		$debugBuffer = null;
		$items = $profiles->getItems ();
		if (count ( $items ) != 0) {
			foreach ( $items as &$profile ) {
				$profileid = $profile->getId ();
				$this->currentProfile = $profile;
				$currentProfileUrl = $profile->getwebsiteUrl ();
				if($params->get('enable_debug', 0)) {
					$debugBuffer .= '<li>' . $currentProfileUrl . '</li>';
				}
				if ($this->purifyDomain ( $currentProfileUrl ) == $this->purifyDomain ( $params->get ( 'ga_domain', JUri::root () ) )) {
					return $profileid;
				}
			}
			// Fallback on the latest added domain to Google Analytics if no match found, with domain dumping if debug is enabled
			if($params->get('enable_debug', 0)) {
				echo JText::sprintf('COM_JREALTIME_GOOGLE_ANALYTICS_DEBUGINFO', $debugBuffer);
			}
			return $profileid;
		}
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getData() {
		$params = $this->getComponentParams ();
		
		$client = new Google_Client ();
		$client->setAccessType ( 'offline' );
		$client->setScopes ( array('https://www.googleapis.com/auth/analytics.readonly', 'https://www.googleapis.com/auth/webmasters.readonly') );
		$client->setApplicationName ( 'JRealtime Analytics' );
		$client->setRedirectUri ( 'urn:ietf:wg:oauth:2.0:oob' );
		
		$this->hasOwnCredentials = false;
		if ($params->get ( 'ga_api_key' ) and $params->get ( 'ga_client_id' ) and $params->get ( 'ga_client_secret' )) {
			$client->setClientId ( $params->get ( 'ga_client_id' ) );
			$client->setClientSecret ( $params->get ( 'ga_client_secret' ) );
			$client->setDeveloperKey ( $params->get ( 'ga_api_key' ) ); // API key
			$this->hasOwnCredentials = true;
		} else {
			$client->setClientId ( '872567856644-g1j8hbip0u8vm45ot7sqls53av49b0bg.apps.googleusercontent.com' );
			$client->setClientSecret ( '58fP1JwLkNfRSxhuQmvTSkik' );
			$client->setDeveloperKey ( 'AIzaSyC-4AcgDEPvXZOJ1KMZky7VgOfz4QrQKyc' );
		}
		
		$service = new Google_Service_Analytics ( $client );
		
		if ($this->getToken ()) { // extract token from session and configure client
			$token = $this->getToken ();
			$client->setAccessToken ( $token );
		}
		
		if (! $result = $client->getAccessToken ()) { // auth call to google
			$authUrl = $client->createAuthUrl ();
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
					$client->authenticate ( $this->app->input->getString('ga_dash_code'));
				} catch ( JRealtimeException $e ) {
					$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
					return '<a class="btn btn-primary" href="index.php?option=com_jrealtimeanalytics&amp;task=google.display">' . JText::_ ( 'COM_JREALTIME_GOBACK' ) . '</a>';
				} catch ( Exception $e ) {
					$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
					$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
					return '<a class="btn btn-primary" href="index.php?option=com_jrealtimeanalytics&amp;task=google.display">' . JText::_ ( 'COM_JREALTIME_GOBACK' ) . '</a>';
				}
				
				// Store the Google token in the DB for further login and authentication
				$this->storeToken ( $client->getAccessToken () );
			}
		}
		
		$projectId = $this->getSitesProfiles ( $service, $client, $params );
		
		if ( $projectId instanceof Exception ) {
			$this->deleteToken();
			$this->app->enqueueMessage ( $projectId->getMessage (), 'warning' );
			return '<a class="btn btn-primary" href="index.php?option=com_jrealtimeanalytics&amp;task=google.display">' . JText::_ ( 'COM_JREALTIME_GOBACK' ) . '</a>';
		}
		
		if ($this->app->input->get('gaquery')) {
			$gaquery = $this->app->input->get('gaquery');
		} else {
			$gaquery = "sessions";
		}
		
		if ($this->app->input->get('gaperiod')) {
			$gaperiod = $this->app->input->get('gaperiod');
		} else {
			$gaperiod = "last30days";
		}
		
		switch ($gaperiod) {
			
			case 'today' :
				$from = date ( 'Y-m-d' );
				$to = date ( 'Y-m-d' );
				$showevery = 5;
				break;
			
			case 'yesterday' :
				$from = date ( 'Y-m-d', time () - 24 * 60 * 60 );
				$to = date ( 'Y-m-d', time () - 24 * 60 * 60 );
				$showevery = 5;
				break;
			
			case 'last7days' :
				$from = date ( 'Y-m-d', time () - 7 * 24 * 60 * 60 );
				$to = date ( 'Y-m-d' );
				$showevery = 3;
				break;
			
			case 'last14days' :
				$from = date ( 'Y-m-d', time () - 14 * 24 * 60 * 60 );
				$to = date ( 'Y-m-d' );
				$showevery = 4;
				break;
				
			case 'last3months' :
				$from = date ( 'Y-m-d', time () - 90 * 24 * 60 * 60 );
				$to = date ( 'Y-m-d' );
				$showevery = 4;
				break;
			
			case 'last6months' :
				$from = date ( 'Y-m-d', time () - 180 * 24 * 60 * 60 );
				$to = date ( 'Y-m-d' );
				$showevery = 4;
				break;
				
			case 'last12months' :
				$from = date ( 'Y-m-d', time () - 365 * 24 * 60 * 60 );
				$to = date ( 'Y-m-d' );
				$showevery = 4;
				break;
			
			default :
				$from = date ( 'Y-m-d', time () - 30 * 24 * 60 * 60 );
				$to = date ( 'Y-m-d' );
				$showevery = 6;
				break;
		}
		
		switch ($gaquery) {
			
			case 'users' :
				$title = JText::_ ( 'COM_JREALTIME_GOOGLE_VISITORS' );
				break;
			
			case 'pageviews' :
				$title = JText::_ ( 'COM_JREALTIME_GOOGLE_PAGE_VIEWS' );
				break;
			
			case 'bounceRate' :
				$title = JText::_ ( 'COM_JREALTIME_GOOGLE_BOUNCE_RATE' );
				break;
			
			case 'organicSearches' :
				$title = JText::_ ( 'COM_JREALTIME_GOOGLE_ORGANIC_SEARCHES' );
				break;
			
			default :
				$title = JText::_ ( 'COM_JREALTIME_GOOGLE_VISITS' );
		}
		
		$metrics = 'ga:' . $gaquery;
		$dimensions = 'ga:year,ga:month,ga:day';
		
		if ($gaperiod == "today" or $gaperiod == "yesterday") {
			$dimensions = 'ga:hour';
		} else {
			$dimensions = 'ga:year,ga:month,ga:day';
		}
		
		try {
			$serial = 'gadash_qr2' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to . $metrics );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		$gadash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			if ($gaperiod == "today" or $gaperiod == "yesterday") {
				$gadash_data .= "['" . $data ['rows'] [$i] [0] . ":00'," . round ( $data ['rows'] [$i] [1], 2 ) . "],";
			} else {
				$gadash_data .= "['" . $data ['rows'] [$i] [0] . "-" . $data ['rows'] [$i] [1] . "-" . $data ['rows'] [$i] [2] . "'," . round ( $data ['rows'] [$i] [3], 2 ) . "],";
			}
		}
		// Avoid errors in the drawing phase of the visits map
		if(!$gadash_data) {
			$gadash_data = "['" . date('Y-m-d') . "',0]";
		}
		
		$metrics = 'ga:sessions,ga:users,ga:pageviews,ga:bounceRate,ga:organicSearches,ga:sessionDuration';
		$dimensions = 'ga:year';
		try {
			$serial = 'gadash_qr3' . str_replace ( array (
					'ga:',
					',',
					'-',
					date ( 'Y' ) 
			), "", $projectId . $from . $to );
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions 
			) );
		} catch ( Exception $e ) {
			return "<br />&nbsp;&nbsp;Error: " . $e->getMessage ();
		}
		
		$code = '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
	  google.load("maps", "3", { other_params : "key=AIzaSyBEgj4pfKxmBjw3wBFa8qP9bysTUOn7uA0"});
      google.setOnLoadCallback(ga_dash_callback);
	
	  function ga_dash_callback(){
			ga_dash_drawstats();
			if(typeof ga_dash_drawmap == "function"){
				ga_dash_drawmap();
			}
			if(typeof ga_dash_drawpgd == "function"){
				ga_dash_drawpgd();
			}
			if(typeof ga_dash_drawrd == "function"){
				ga_dash_drawrd();
			}
			if(typeof ga_dash_drawsd == "function"){
				ga_dash_drawsd();
			}
			if(typeof ga_dash_drawtraffic == "function"){
				ga_dash_drawtraffic();
			}
	  }
	
      function ga_dash_drawstats() {
        var data = google.visualization.arrayToDataTable([' . "
          ['" . JText::_ ( 'COM_JREALTIME_GOOGLE_DATE' ) . "', '" . $title . "']," . $gadash_data . "
        ]);
	
        var options = {
		  legend: {position: 'none'},
		  " . "colors:['#3366CC','#2B56AD']," . "
		  pointSize: 3,
          title: '" . $title . "',
		  chartArea: {width: '95%'},
          hAxis: { title: '" . JText::_ ( 'COM_JREALTIME_GOOGLE_DATE' ) . "',  titleTextStyle: {color: 'black'}, showTextEvery: " . $showevery . "},
		  vAxis: { textPosition: 'none', minValue: 0}
		};
	
        var chart = new google.visualization.AreaChart(document.getElementById('gadash_div'));
		chart.draw(data, options);
	
      }";
		
		$getVisitsByCountry = $this->getVisitsByCountry ( $service, $projectId, $from, $to, $params );
		if ($getVisitsByCountry) {
			$code .= '
		google.load("visualization", "1", {packages:["geochart"]})
		function ga_dash_drawmap() {
		var data = google.visualization.arrayToDataTable([' . "
		  ['Country', 'Visits']," . $getVisitsByCountry . "
		]);
	
		var options = {
			colors: ['white', '" . "blue" . "']
		};
	
		var chart = new google.visualization.GeoChart(document.getElementById('ga_dash_mapdata'));
		chart.draw(data, options);
	
	  }";
		}
		
		$getTrafficSources = $this->getTrafficSources ( $service, $projectId, $from, $to, $params );
		$getNewReturnVisitors = $this->getNewReturnVisitors ( $service, $projectId, $from, $to, $params );
		if ($getTrafficSources && $getNewReturnVisitors) {
			$code .= '
		google.load("visualization", "1", {packages:["corechart"]})
		function ga_dash_drawtraffic() {
		var data = google.visualization.arrayToDataTable([' . "
		  ['Source', 'Visits']," . $getTrafficSources . '
		]);

		var datanvr = google.visualization.arrayToDataTable([' . "
		  ['Type', 'Visits']," . $getNewReturnVisitors . "
		]);
	
		var chart = new google.visualization.PieChart(document.getElementById('ga_dash_trafficdata'));
		chart.draw(data, {
			is3D: false,
			tooltipText: 'percentage',
			legend: 'none',
			title: 'Traffic Sources',
			colors: ['" . "#001BB5" . "', '" . "#2D41AF" . "', '" . "#00137F" . "', '" . "blue" . "', '" . "#425AE5" . "']
		});
	
		var gadash = new google.visualization.PieChart(document.getElementById('ga_dash_nvrdata'));
		gadash.draw(datanvr,  {
			is3D: false,
			tooltipText: 'percentage',
			legend: 'none',
			title: 'New vs. Returning',
			colors: ['" . "#001BB5" . "', '" . "#2D41AF" . "', '" . "#00137F" . "', '" . "blue" . "', '" . "#425AE5" . "']
		});
	
	  }";
		}
		
		$getTopPages = $this->getTopPages ( $service, $projectId, $from, $to, $params );
		if ($getTopPages) {
			$code .= '
		google.load("visualization", "1", {packages:["table"]})
		function ga_dash_drawpgd() {
		var data = google.visualization.arrayToDataTable([' . "
		  ['Top Pages', 'Visits']," . $getTopPages . "
		]);
	
		var options = {
			page: 'enable',
			pageSize: 6,
			width: '100%'
		};
	
		var chart = new google.visualization.Table(document.getElementById('ga_dash_pgddata'));
		chart.draw(data, options);
	
	  }";
		}
		
		$getTopReferrers = $this->getTopReferrers ( $service, $projectId, $from, $to, $params );
		if ($getTopReferrers) {
			$code .= '
		google.load("visualization", "1", {packages:["table"]})
		function ga_dash_drawrd() {
		var datar = google.visualization.arrayToDataTable([' . "
		  ['Top Referrers', 'Visits']," . $getTopReferrers . "
		]);
	
		var options = {
			page: 'enable',
			pageSize: 6,
			width: '100%'
		};
	
		var chart = new google.visualization.Table(document.getElementById('ga_dash_rdata'));
		chart.draw(datar, options);
	
	  }";
		}
		
		$getTopSearches = $this->getTopSearches ( $service, $projectId, $from, $to, $params );
		if ($getTopSearches) {
			$code .= '
		google.load("visualization", "1", {packages:["table"]})
		function ga_dash_drawsd() {
	
		var datas = google.visualization.arrayToDataTable([' . "
		  ['Top Searches', 'Visits']," . $getTopSearches . "
		]);
	
		var options = {
			page: 'enable',
			pageSize: 6,
			width: '100%'
		};
	
		var chart = new google.visualization.Table(document.getElementById('ga_dash_sdata'));
		chart.draw(datas, options);
	
	  }";
		}
		
		$code .= "
	
	jQuery(window).resize(function(){
		if(typeof ga_dash_drawstats == 'function'){
			ga_dash_drawstats();
		}
		if(typeof ga_dash_drawmap == 'function'){
			ga_dash_drawmap();
		}
		if(typeof ga_dash_drawpgd == 'function'){
			ga_dash_drawpgd();
		}
		if(typeof ga_dash_drawrd == 'function'){
			ga_dash_drawrd();
		}
		if(typeof ga_dash_drawsd == 'function'){
			ga_dash_drawsd();
		}
		if(typeof ga_dash_drawtraffic == 'function'){
			ga_dash_drawtraffic();
		}
	});
	
	</script>" . 
	($this->currentProfile->getWebsiteUrl() ? "<span class='label label-info label-large'>" . $this->currentProfile->getWebsiteUrl() . "</span>" : null) .
	($this->hasOwnCredentials ? null : "<span data-content='" . JText::_('COM_JREALTIME_GOOGLE_APP_NOTSET_DESC') . "' class='label label-warning hasPopover google pull-right'>" . JText::_('COM_JREALTIME_GOOGLE_APP_NOTSET') . "</span>") .
	'<div id="ga-dash">
		<div class="btn-toolbar">
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "today" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'today\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_TODAY' ) . '</button></div>
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "yesterday" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'yesterday\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_YESTERDAY' ) . '</button></div>
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "last7days" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'last7days\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_LAST7DAYS' ) . '</button></div>
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "last14days" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'last14days\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_LAST14DAYS' ) . '</button></div>
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "last30days" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'last30days\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_LAST30DAYS' ) . '</button></div>
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "last3months" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'last3months\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_LAST3MONTHS' ) . '</button></div>
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "last6months" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'last6months\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_LAST6MONTHS' ) . '</button></div>
			<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaperiod == "last12months" ? ' active' : '') . '" onclick="document.getElementById(\'gaperiod\').value=\'last12months\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_LAST12MONTHS' ) . '</button></div>
		</div>
	
		<div class="accordion" id="jrealtime_googlegraph_accordion">
			<div class="row tablestats no-margin">
				<div class="accordion-group span12">
					<div class="accordion-heading">
						<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googlegraph_accordion" href="#jrealtime_googlestats_graph">
							<h3><span class="icon-copy"></span>' . JText::_ ('COM_JREALTIME_GOOGLE_STATS' ) . '</h3>
						</div>
					</div>
					<div id="jrealtime_googlestats_graph" class="accordion-body accordion-inner collapse" >
						<div class="btn-toolbar">
							<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaquery == "users" ? ' active' : '') . '" onclick="document.getElementById(\'gaquery\').value=\'users\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_METRIC_VISITORS' ) . '</button></div>
							<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaquery == "pageviews" ? ' active' : '') . '" onclick="document.getElementById(\'gaquery\').value=\'pageviews\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_METRIC_PAGEVIEWS' ) . '</button></div>
							<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaquery == "bounceRate" ? ' active' : '') . '" onclick="document.getElementById(\'gaquery\').value=\'bounceRate\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_METRIC_BOUNCERATE' ) . '</button></div>
							<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaquery == "organicSearches" ? ' active' : '') . '" onclick="document.getElementById(\'gaquery\').value=\'organicSearches\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_METRIC_ORGANICSEARCHES' ) . '</button></div>
							<div class="btn-wrapper"><button class="btn btn-small btn-xs' . ($gaquery == "sessions" ? ' active' : '') . '" onclick="document.getElementById(\'gaquery\').value=\'sessions\'">' . JText::_ ( 'COM_JREALTIME_GOOGLE_METRIC_VISITS' ) . '</button></div>
						</div>
						<div id="gadash_div" style="height:350px;"></div>
						<table class="gatable" cellpadding="4" width="100%" align="center">
							<tr>
								<td width="24%">' . JText::_ ( 'COM_JREALTIME_GOOGLE_VISITS' ) . ':</td>
								<td width="12%" class="gavalue"><a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [1] . '</td>
								<td width="24%">' . JText::_ ( 'COM_JREALTIME_GOOGLE_VISITORS' ) . ':</td>
								<td width="12%" class="gavalue"><a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [2] . '</a></td>
								<td width="24%">' . JText::_ ( 'COM_JREALTIME_GOOGLE_PAGE_VIEWS' ) . ':</td>
								<td width="12%" class="gavalue"><a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [3] . '</a></td>
							</tr>
							<tr>
								<td>' . JText::_ ( 'COM_JREALTIME_GOOGLE_BOUNCE_RATE' ) . ':</td>
								<td class="gavalue"><a href="javascript:void(0);" class="gatable">' . round ( $data ['rows'] [0] [4], 2 ) . '%</a></td>
								<td>' . JText::_ ( 'COM_JREALTIME_GOOGLE_ORGANIC_SEARCHES' ) . ':</td>
								<td class="gavalue"><a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [5] . '</a></td>
								<td>' . JText::_ ( 'COM_JREALTIME_GOOGLE_PAGES_VISIT' ) . ':</td>
								<td class="gavalue"><a href="javascript:void(0);" class="gatable">' . (($data ['rows'] [0] [1]) ? round ( $data ['rows'] [0] [3] / $data ['rows'] [0] [1], 2 ) : '0') . '</a></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>';
		
		$JText = 'JText';
		$multiReports = <<<MULTIREPORTS
						<div class="accordion" id="jrealtime_googlestats_accordion">
							<div class="row tablestats no-margin">
								<div class="accordion-group span12">
									<div class="accordion-heading">
										<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googlestats_accordion" href="#jrealtime_googlestats_map">
											<h3><span class="icon-copy"></span>{$JText::_ ('COM_JREALTIME_GOOGLE_MAP' )}</h3>
										</div>
									</div>
									<div id="jrealtime_googlestats_map" class="accordion-body accordion-inner collapse" >
										<div id="ga_dash_mapdata"></div>
									</div>
								</div>
							</div>
							<div class="row tablestats no-margin">
								<div class="accordion-group span12">
									<div class="accordion-heading">
										<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googlestats_accordion" href="#jrealtime_googlestats_traffic">
											<h3><span class="icon-copy"></span>{$JText::_ ('COM_JREALTIME_GOOGLE_TRAFFIC' )}</h3>
										</div>
									</div>
									<div id="jrealtime_googlestats_traffic" class="accordion-body accordion-inner collapse">
										<div id="ga_dash_trafficdata"></div><div id="ga_dash_nvrdata"></div>
									</div>
								</div>
							</div>
							<div class="row tablestats no-margin">
								<div class="accordion-group span12">
									<div class="accordion-heading">
										<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googlestats_accordion" href="#jrealtime_googlestats_referrers">
											<h3><span class="icon-copy"></span>{$JText::_ ('COM_JREALTIME_GOOGLE_REFERRERS' )}</h3>
										</div>
									</div>
									<div id="jrealtime_googlestats_referrers" class="accordion-body accordion-inner collapse">
										<div id="ga_dash_rdata"></div>
									</div>
								</div>
							</div>
							<div class="row tablestats no-margin">
								<div class="accordion-group span12">
									<div class="accordion-heading">
										<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googlestats_accordion" href="#jrealtime_googlestats_searches">
											<h3><span class="icon-copy"></span>{$JText::_ ('COM_JREALTIME_GOOGLE_SEARCHES' )}</h3>
										</div>
									</div>
									<div id="jrealtime_googlestats_searches" class="accordion-body accordion-inner collapse">
										<div id="ga_dash_sdata"></div>
									</div>
								</div>
							</div>
							<div class="row tablestats no-margin">
								<div class="accordion-group span12">
									<div class="accordion-heading">
										<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_googlestats_accordion" href="#jrealtime_googlestats_pages">
											<h3><span class="icon-copy"></span>{$JText::_ ('COM_JREALTIME_GOOGLE_PAGES' )}</h3>
										</div>
									</div>
									<div id="jrealtime_googlestats_pages" class="accordion-body accordion-inner collapse">
										<div id="ga_dash_pgddata"></div>
									</div>
								</div>
							</div>
						</div>
MULTIREPORTS;
		
		$code .= $multiReports;
		$code .= '</div>';
		
		return $code;
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
			$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
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