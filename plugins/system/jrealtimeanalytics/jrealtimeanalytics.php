<?php
/** 
 * JS APP renderer system plugin
 * @package JREALTIMEANALYTICS::plugins::system
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.plugin.plugin' );
class plgSystemJRealtimeAnalytics extends JPlugin {
	/**
	 * App reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $app;
	
	/**
	 * Component configuration
	 *
	 * @access protected
	 * @var Object
	 */
	protected $componentConfig;
	
	/**
	 * Manage obsservers notify for attached observers
	 *
	 * @access private
	 * @param array $states
	 *        	Additional states for observable object that drive methods execution inside observer
	 * @return array
	 */
	private function notifyObservers($states = array()) {
		// Require language helper for JS App inject translations
		require_once JPATH_BASE . '/administrator/components/com_jrealtimeanalytics/framework/helpers/users.php';
		require_once JPATH_BASE . '/administrator/components/com_jrealtimeanalytics/framework/helpers/device.php';
		require_once JPATH_BASE . '/administrator/components/com_jrealtimeanalytics/framework/exception/exception.php';
		JLoader::import ( 'model.model', JPATH_BASE . '/administrator/components/com_jrealtimeanalytics/framework' );
		JLoader::import ( 'model.observer', JPATH_BASE . '/administrator/components/com_jrealtimeanalytics/framework' );
		JLoader::import ( 'model.observable', JPATH_BASE . '/administrator/components/com_jrealtimeanalytics/framework' );
		JModelLegacy::addIncludePath ( JPATH_BASE . '/components/com_jrealtimeanalytics/models', 'JRealtimeModel' );
		
		// Instantiate session object for Dependency Injection into main model
		$userSessionTable = JRealtimeHelpersUsers::getSessiontable ();
		
		// Instantiate Observer Model instance
		$observerServerstatsModelInstance = JRealtimeModelObserver::getInstance ( 'ServerstatsObsrv', 'JRealtimeModel', array (
				'sessiontable' => $userSessionTable 
		) );
		
		// Instantiate Observer Model instance
		$observerEventstatsModelInstance = JRealtimeModelObserver::getInstance ( 'EventstatsObsrv', 'JRealtimeModel', array (
				'sessiontable' => $userSessionTable 
		) );
		
		// Instantiate Observable Model instance
		$observableModelInstance = JRealtimeModelObservable::getInstance ( 'Stream', 'JRealtimeModel', array (
				'sessiontable' => $userSessionTable 
		) );
		
		// Now attach observers to abservable model, plugin acts like a display controller main execution for front component
		$observableModelInstance->attach ( $observerServerstatsModelInstance );
		$observableModelInstance->attach ( $observerEventstatsModelInstance );
		$observableModelInstance->setState ( 'initialize', 1 );
		$observableModelInstance->setState ( 'appdispatch', true ); // Always indicates to track Referral is any valid
		                                                            
		// Manage additional state injected for observers methods exec
		if (count ( $states )) {
			foreach ( $states as $state ) {
				$observableModelInstance->setState ( $state [0], $state [1] );
			}
		}
		
		return $observableModelInstance->notify ();
	}
	
	/**
	 * Effettua l'app js output
	 *
	 * @param Object& $cParams        	
	 * @param Object& $doc        	
	 * @return boolean
	 */
	private function injectApp($cParams, $doc) {
		$option = $this->app->input->get ( 'option' );
		$base = JUri::base ();
		
		// Output JS APP nel Document esclusione
		if ($doc->getType () !== 'html' || $this->app->input->getCmd ( 'tmpl' ) === 'component') {
			return false;
		}
		
		// Require language helper for JS App inject translations
		JLoader::import ( 'helpers.language', JPATH_BASE . '/administrator/components/com_jrealtimeanalytics/framework' );
		
		// Create language object
		$language = JFactory::getLanguage ();
		$language->load ( 'com_jrealtimeanalytics', JPATH_SITE . '/components/com_jrealtimeanalytics' );
		$language->load ( 'com_jrealtimeanalytics', JPATH_SITE, null, true );
		$jrealtimeLanguage = JRealtimeHelpersLanguage::getInstance ();
		
		$translations = array (
				'COM_JREALTIME_NETWORK_ERROR' 
		);
		$jrealtimeLanguage->injectJsTranslations ( $translations, $doc );
		
		// Output JS APP nel Document
		$doc->addStyleSheet ( JUri::root ( true ) . '/components/com_jrealtimeanalytics/css/mainstyle.css' );
		
		// Scripts loading
		$defer = $cParams->get('scripts_loading', null) == 'defer' ? true : false;
		$async = $cParams->get('scripts_loading', null) == 'async' ? true : false;
		
		if ($cParams->get ( 'includejquery', 1 )) {
			JHtml::_ ( 'jquery.framework' );
		}
		if ($cParams->get ( 'noconflict', 1 )) {
			$doc->addScript ( JUri::root ( true ) . '/components/com_jrealtimeanalytics/js/jquery.noconflict.js' );
		}
		$doc->addScriptDeclaration ( "var jrealtimeBaseURI='$base';" );
		$doc->addScript ( JUri::root ( true ) . '/components/com_jrealtimeanalytics/js/stream.js', 'text/javascript', $defer, $async );
		
		if ($cParams->get ( 'heatmap_status', 1 ) && $this->app->input->get('option') != 'com_jrealtimeanalytics') {
			$doc->addScript ( JUri::root ( true ) . '/components/com_jrealtimeanalytics/js/heatmap.js', 'text/javascript', $defer, $async );
		}
		
		// Restore se overwrite jQuery prototype da parte di CB
		if ($option === 'com_comprofiler' && $cParams->get ( 'cbnoconflict' )) {
			// Evitiamo fatal error nei controlli ajax del CB con JDocumentRaw che non ha addCustomTag
			if ($doc instanceof JDocumentHTML) {
				$doc->addCustomTag ( '<script type="text/javascript">jQuery.noConflict(true);</script>' );
			}
		}
		
		// Check if the request is directed to com_finder, if so track the searched keywords, mimic the onContentSearch observer method
		$notifications = array ();
		$option = $this->app->input->get ( 'option' );
		$view = $this->app->input->get ( 'view' );
		if ($option === 'com_finder' && $view === 'search') {
			// Found a request for com_finder Smart Search, so record the searched keywords
			$keywords = $this->app->input->getString ( 'q', null );
			if (trim ( $keywords )) {
				$notifications = $this->onContentSearch ( $keywords, 'jrealtime_plugin' );
			}
		}
		
		// If valid plugin execution on main app dispatch
		$notifications = array_merge ( $notifications, $this->notifyObservers () );
		
		// Manage observers exceptions, cycle on $notifications responses to enqueue messages into app object for debug purpouse
		if ($this->componentConfig->get ( 'enable_debug', false )) {
			foreach ( $notifications as $exception ) {
				// Found an exception, set into app response message queue for client side debug
				if ($exception instanceof JRealtimeException) {
					$this->app->enqueueMessage ( $exception->getMessage (), $exception->getErrorLevel () );
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Renders custom visual styles in the page body output
	 *
	 * @access private
	 * @return void
	 */
	private function injectCustomStyles($doc) {
		// Retrieve and init parameters for the query
		$pageurl = $this->app->input->getString('jes_pageurl');
		$dateFrom = $this->app->input->get('jes_from');
		$dateTo = $this->app->input->get('jes_to');

		// Get new query object and retrieve all styles
		try {
			$db = JFactory::getDbo();
			$query = "SELECT count(c.id) AS numclicks, s.selector" .
					 "\n FROM #__realtimeanalytics_heatmap AS s" .
					 "\n INNER JOIN #__realtimeanalytics_heatmap_clicks AS c" .
					 "\n ON s.id = c.heatmap_id" .
					 "\n WHERE s.pageurl = " . $db->quote($pageurl) .
					 "\n AND c.record_date >= " . $db->quote($dateFrom) .
					 "\n AND c.record_date <= " . $db->quote($dateTo) .
					 "\n GROUP BY s.selector" .
					 "\n ORDER BY numclicks DESC";

			$heatmapSelectors = $db->setQuery ( $query )->loadObjectList ();
		} catch ( Exception $e ) {
			// No error reporting in this case
		}
	
		// Get importantify mode
		$importantify = ' !important';
	
		// Check if styles are found
		if (is_array ( $heatmapSelectors ) && count ( $heatmapSelectors )) {
			// run max value function and store in variable
			$max = $heatmapSelectors[0]->numclicks;
			$n = 100; // Declare the number of groups

			// Define the ending colour, which is white
			$xr = 255; // Red value
			$xg = 255; // Green value
			$xb = 255; // Blue value

			// Define the starting colour #f32075
			$yr = 243;
			$yg = 32;
			$yb = 117;

			$stylesheet = null;
			$additionalInjects = array();
			// Build the dynamic stylesheet based on custom visual styles
			foreach ( $heatmapSelectors as $selector ) {
				$val = $selector->numclicks;
				$pos = intval(($val / $max) * 100);
				$alpha = round($pos / 100, 1, PHP_ROUND_HALF_DOWN);
				$alpha = $alpha <= 0.2 ? 0.2 : $alpha;
				$alpha = $alpha >= 0.9 ? 0.9 : $alpha;

				// Calculate heat colors
				$red = intval($xr + (( $pos * ($yr - $xr)) / ($n-1)));
				$green = intval($xg + (( $pos * ($yg - $xg)) / ($n-1)));
				$blue = intval($xb + (( $pos * ($yb - $xb)) / ($n-1)));

				$stylesheet .= $selector->selector . '{background: radial-gradient(at 50% 50%, rgba(41, 154, 11, ' . $alpha . ') 0%, rgba(11, 109, 145, ' . $alpha . ') 32%, ' .
														'rgba(155, 168, 13, ' . $alpha . ') 48%, rgba(' . $red . ',' . $green . ',' . $blue . ', ' . $alpha . ') 74%) !important;' .
														'border: none;border-radius:50px;box-shadow:1px 1px 55px 25px rgba(' . $red . ',' . $green . ',' . $blue . ', ' . ($alpha+0.1) . ') !important;}';
				$stylesheet .= $selector->selector . ':after{content: "' . $selector->numclicks . ' clicks";display:inline-block;margin: 5px;padding:2px 4px;font-size:12px;' .
															'background-color:#FF0000;border-radius:3px;font-weight:bold;line-height:14px;color:#fff;}';

				// Match final input element in the selector and manage after append by JS
				if(preg_match('/((>input|>select|>textarea|>img)[\.a-zA-Z0-9-_]*)$/i', $selector->selector)) {
					$additionalInjects[] = ('$("' . $selector->selector . '").after("<span style=\'display:inline-block;margin: 5px;padding:2px 4px;font-size:12px;' .
																					'background-color:#FF0000;border-radius:3px;font-weight:bold;line-height:14px;color:#fff;\'>' .
																			$selector->numclicks . ' clicks</span>");');
				}
			}
	
			// Now inject
			$stylesheet = str_ireplace('@', '\@', $stylesheet);
			$doc->addStyleDeclaration ( $stylesheet );
			if(count($additionalInjects)) {
				if ($this->componentConfig->get ( 'includejquery', 1 )) {
					JHtml::_ ( 'jquery.framework' );
				}
				if ($this->componentConfig->get ( 'noconflict', 1 )) {
					$doc->addScript ( JUri::root ( true ) . '/components/com_jrealtimeanalytics/js/jquery.noconflict.js' );
				}
				$doc->addScriptDeclaration('jQuery(function($){' . implode(' ', $additionalInjects) . '});');
			}
		}
	}
	
	/**
	 * Manage notrack cases exclusions, posted internal pages, by IP, IP range, Pages, User groups
	 *
	 * @access private
	 * @return boolean
	 */
	private function validateTracking() {
		// Manage notrack case by posted internal pages
		if ($this->app->input->get ( 'notrack', false )) {
			return false;
		}
		
		// Exclude always for backend
		if ($this->app->getClientId ()) {
			return false;
		}
		
		$doc = JFactory::getDocument();
		// Output JS APP nel Document esclusione
		if ($doc->getType () !== 'html' || $this->app->input->getCmd ( 'tmpl' ) === 'component') {
			return false;
		}
		
		// Check for IP exclusion
		$ipAddressRegex = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/i';
		$clientIP = $this->componentConfig->get('cloudflare_ip_masking', 0) == 0 ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['HTTP_CF_CONNECTING_IP'];
		$clientIpDec = ( float ) sprintf ( "%u", ip2long ( $clientIP ) );
		$ipSingleAddress = trim($this->componentConfig->get ( 'ipaddress', null ));
		$validIpSingleAddress = preg_match ( $ipAddressRegex, $ipSingleAddress );
		if ($validIpSingleAddress) {
			$singleIpDec = ( float ) sprintf ( "%u", ip2long ( $ipSingleAddress ) );
			if ($singleIpDec == $clientIpDec) {
				return false;
			}
		}
		
		// Check for IP range exclusions
		$ipStart = trim($this->componentConfig->get ( 'iprange_start', null ));
		$ipEnd = trim($this->componentConfig->get ( 'iprange_end', null ));
		$validIpRangeStart = preg_match ( $ipAddressRegex, $ipStart );
		$validIpRangeEnd = preg_match ( $ipAddressRegex, $ipEnd );
		if ($validIpRangeStart && $validIpRangeEnd) {
			$lowerIpDec = ( float ) sprintf ( "%u", ip2long ( $ipStart ) );
			$upperIpDec = ( float ) sprintf ( "%u", ip2long ( $ipEnd ) );
			if (($clientIpDec >= $lowerIpDec) && ($clientIpDec <= $upperIpDec)) {
				return false;
			}
		}
		
		// Check for multiple IP exclusions
		$ipMultipleAddresses = $this->componentConfig->get ( 'ip_multiple', null);
		// Check if data are not null
		if($ipMultipleAddresses) {
			// Try to load every range, one per row
			$explodeIpsRows = explode(PHP_EOL, $ipMultipleAddresses);
			if(!empty($explodeIpsRows)) {
				foreach ($explodeIpsRows as $singleCycledIpAddress) {
					$singleCycledIpAddress = trim($singleCycledIpAddress);
					$validSingleCycledIpAddress = preg_match ( $ipAddressRegex, $singleCycledIpAddress );
					if($validSingleCycledIpAddress) {
						$validSingleCycledIpAddressDec = ( float ) sprintf ( "%u", ip2long ( $singleCycledIpAddress ) );
						if ($validSingleCycledIpAddressDec == $clientIpDec) {
							return false;
						}
					}
				}
			}
		}
		
		// Check for IP multiple ranges exclusions
		$ipRanges = $this->componentConfig->get ( 'iprange_multiple', null);
		// Check if data are not null
		if($ipRanges) {
			// Try to load every range, one per row
			$explodeRows = explode(PHP_EOL, $ipRanges);
			if(!empty($explodeRows)) {
				foreach ($explodeRows as $singleRange) {
					// Try to detect single range
					$explodeRange = explode('-', $singleRange);
					if(!empty($explodeRange) && count($explodeRange) == 2) {
						$ipStart = trim($explodeRange[0]);
						$ipEnd = trim($explodeRange[1]);
						$validIpRangeStart = preg_match ( $ipAddressRegex, $ipStart );
						$validIpRangeEnd = preg_match ( $ipAddressRegex, $ipEnd );
						if ($validIpRangeStart && $validIpRangeEnd) {
							$lowerIpDec = ( float ) sprintf ( "%u", ip2long ( $ipStart ) );
							$upperIpDec = ( float ) sprintf ( "%u", ip2long ( $ipEnd ) );
							if (($clientIpDec >= $lowerIpDec) && ($clientIpDec <= $upperIpDec)) {
								return false;
							}
						}
					}
				}
			}
		}
		
		// Check for pages exclusions
		$menu = $this->app->getMenu ()->getActive ();
		if (is_object ( $menu )) {
			$menuItemid = $menu->id;
			$menuExcluded = $this->componentConfig->get ( 'daemon_exclusions' );
			if (is_array ( $menuExcluded ) && ! in_array ( 0, $menuExcluded, false ) && in_array ( $menuItemid, $menuExcluded )) {
				return false;
			}
		}
		
		// Check for users groups exclusions
		$groupsExclusions = $this->componentConfig->get ( 'groups_exclusions', array ('0') );
		if (is_array ( $groupsExclusions ) && ! in_array ( 0, $groupsExclusions, false )) {
			// Check for user groups current user belong to
			$userGroups = $this->myUser->getAuthorisedGroups ();
			// Intersect to recognize groups
			$intersectResult = array_intersect ( $userGroups, $groupsExclusions );
			$isInExcludedGroup = ( bool ) (count ( $intersectResult ));
			
			// Eventually limit query to users that belong to groups
			if ($isInExcludedGroup) {
				return false;
			}
		}
		
		// Check for the country code exclusion
		$countryCodeExclusions = $this->componentConfig->get ( 'countrycode_exclusions', array ('0') );
		if (is_array ( $countryCodeExclusions ) && ! in_array ( '0', $countryCodeExclusions, true )) {
			// Check for the user country code
			$userCountryCode = $this->getUserCountry();
			$isInExcludedCountry = in_array($userCountryCode, $countryCodeExclusions, false);

			// Eventually limit query to users that belong to groups
			if ($isInExcludedCountry) {
				return false;
			}
		}
		
		// Check for user agent exclusion
		if(isset($_SERVER ['HTTP_USER_AGENT'])) {
			$useragent = $_SERVER ['HTTP_USER_AGENT'];
			$useragentsExcludedString = trim($this->componentConfig->get('useragents_exclusions', 'googlebot,bingbot'));
			$useragentsExcluded = explode(',', $useragentsExcludedString);
			if(!empty($useragentsExcluded) && $useragentsExcludedString) {
				foreach ($useragentsExcluded as $useragentExcluded) {
					$cleanedUseragentExcluded = strtolower(trim($useragentExcluded));
					if(stripos($useragent, $cleanedUseragentExcluded) !== false) {
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Validate cronjob report by email
	 *
	 * @access private
	 * @param Object $doc
	 * @return boolean
	 */
	private function validateReporting($doc) {
		// Session object
		$session = JFactory::getSession();

		if($session->get('cronjob_user', false)) {
			return false;
		} else {
			$session->set('cronjob_user', true);
		}

		if(!$this->componentConfig->get('report_byemail', 0)) {
			return false;
		}

		if($this->app->getClientId ()) {
			return false;
		}

		if($doc->getType () !== 'html') {
			return false;
		}

		if($this->app->input->getCmd ( 'tmpl' ) == 'component') {
			return false;
		}

		$cronjobFilename = JPATH_ROOT . '/components/com_jrealtimeanalytics/cronjob.txt';
		if(!file_exists($cronjobFilename)) {
			file_put_contents($cronjobFilename, date('Y-m-d'));
			return true;
		} else {
			$lastSend = file_get_contents($cronjobFilename);
			$frequency = $this->componentConfig->get('report_frequency', 7);
			// Validate sending for the current frequency
			$now = time();
			$expireTime = strtotime ( "+ " . $frequency . " days", strtotime ( $lastSend ));
			// Cronjob expired, new send email and update date on file for last send
			if($expireTime < $now) {
				file_put_contents($cronjobFilename, date('Y-m-d'));
				return true;
			}
		}

		return false;
	}
	
	/**
	 * CURL based file_get_contents
	 *
	 * @var string
	 * @access private
	 */
	private function curl_get_contents ($url) {
		if (!function_exists('curl_init')){
			return false;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	
	/**
	 * Get the user country code for filtering
	 *
	 * @access private
	 * @return string
	 */
	private function getUserCountry() {
		$code = null;
		$isBot = false;
		
		// Prevent IP based geolocation if a bot is detected avoiding to be blacklisted by geoplugin.net > 120/min
		if (isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
			$user_agent = $_SERVER ['HTTP_USER_AGENT'];
			$botRegexPattern = "(Googlebot\/|Googlebot\-Mobile|Googlebot\-Image|Google favicon|Mediapartners\-Google|bingbot|slurp|java|wget|curl|Commons\-HttpClient|Python\-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|FAST\-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub\.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum\.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips\-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail\.RU_Bot|discobot|heritrix|findthatfile|europarchive\.org|NerdByNature\.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb\-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web\-archive\-net\.com\.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks\-robot|it2media\-domain\-crawler|ip\-web\-crawler\.com|siteexplorer\.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki\-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e\.net|GrapeshotCrawler|urlappendbot|brainobot|fr\-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf\.fr_bot|A6\-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive\.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j\-asr|Domain Re\-Animator Bot|AddThis)";
			$isBot = preg_match("/{$botRegexPattern}/i", $user_agent);
		}
		
		// IP based geolocation by default
		if(!$isBot) {
			$geolocationService = $this->componentConfig->get('stats_geolocation_service', 'geoplugin') == 'geoplugin' ? 'http://www.geoplugin.net/json.gp?ip=' : 'https://json.geoiplookup.io/';
			$IPAddress = $this->componentConfig->get('cloudflare_ip_masking', 0) == 0 ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['HTTP_CF_CONNECTING_IP'];
			if($this->componentConfig->get('geolocation_php_func', 'file') == 'curl') {
				$webServiceResponse = $this->curl_get_contents($geolocationService . $IPAddress);
			} else {
				$webServiceResponse = file_get_contents($geolocationService . $IPAddress);
			}
			if($webServiceResponse) {
				$decodedResponse = json_decode($webServiceResponse);
				$code = isset($decodedResponse->geoplugin_countryCode) ? $decodedResponse->geoplugin_countryCode : (isset($decodedResponse->country_code) ? $decodedResponse->country_code : null);
			}
		}
		
		// Browser header based geolocation, by param or fallback
		if(($this->componentConfig->get('geolocation_mode', 'ip') == 'browser') || !$code || ($code == 'XX') || ($code == 'ZZ')) {
			$chunkHttpAcceptHeader = $_SERVER ['HTTP_ACCEPT_LANGUAGE'];
			// Patch per header HTTP Internet Explorer
			if (strlen ( $chunkHttpAcceptHeader ) > 2) {
				$spliced = explode ( '-', $chunkHttpAcceptHeader );
				$code = substr ( $spliced [1], 0, 2 );
			} else {
				$code = $chunkHttpAcceptHeader;
			}
		}
		return strtoupper ( $code );
	}
	
	/**
	 * Dispatch handler
	 *
	 * @access private
	 * @return void
	 */
	private function startApp() {
		// Get document
		$doc = JFactory::getDocument();
		
		// Evaluate and add a js snippet to inject an async iframe to execute the cronjob report emailer
		if($this->validateReporting($doc)) {
			$livesite = rtrim(JUri::root(), '/');
			$reportFormat = $this->componentConfig->get('report_format', 'emailxls');

			$jsSnippet = "var iframeReport = document.createElement('iframe');
						  iframeReport.style.display = 'none';
						  iframeReport.src = '$livesite/index.php?option=com_jrealtimeanalytics&task=serverstats.$reportFormat&tmpl=component';
						  document.body.appendChild(iframeReport);

						  var iframeReportOverview = document.createElement('iframe');
						  iframeReportOverview.style.display = 'none';
						  iframeReportOverview.src = '$livesite/index.php?option=com_jrealtimeanalytics&task=overlook.emailpdf&tmpl=component';
						  document.body.appendChild(iframeReportOverview);

						  var iframeReportWebmasters = document.createElement('iframe');
						  iframeReportWebmasters.style.display = 'none';
						  iframeReportWebmasters.src = '$livesite/index.php?option=com_jrealtimeanalytics&task=webmasters.emailxls&tmpl=component';
						  document.body.appendChild(iframeReportWebmasters);";
			
			
			// Output JS snippet nel Document
			if($this->componentConfig->get('report_script_jquery_onload_event', 0)) {
				$doc->addScriptDeclaration("jQuery(window).on('load', function(){ $jsSnippet });");
			} else {
				$doc->addScriptDeclaration("window.onload = function() { $jsSnippet };");
			}
		}
		
		// Execute heatmap app, in this case skip entirely the tracking app
		if ($this->app->input->getInt('jes_heatmap') === 1 && 
			$this->app->input->getString('token') == md5(date('Y-m-d')) &&
			!$this->app->getClientId () && $doc->getType () === 'html') {
			$this->injectCustomStyles ( $doc );
			return true;
		}
		
		// Ensure tracking is valid
		$validTrack = $this->validateTracking ();
		
		// Execute solo nel frontend
		if ($validTrack) {
			return $this->injectApp ( $this->componentConfig, $doc );
		}
		
		return true;
	}
	
	/**
	 * onAfterInitialise handler
	 *
	 * @access	public
	 * @return null
	 */
	public function onAfterDispatch() {
		if($this->app->isSite() && $this->componentConfig->get('start_application_event', 'afterroute') == 'afterdispatch') {
			$this->startApp();
		}
	}
	
	
	/**
	 * onAfterInitialise handler
	 *
	 * @access	public
	 * @return null
	 */
	public function onAfterRoute() {
		if($this->app->isSite() && $this->componentConfig->get('start_application_event', 'afterroute') == 'afterroute') {
			$this->startApp();
		}
	}
	
	/**
	 * Pseudo onContentSearch, global for old search and smart search
	 *
	 * @access public
	 * @return array
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null) {
		// Exclude always for backend
		if ($this->app->getClientId ()) {
			return false;
		}
		
		$doc = JFactory::getDocument();
		// Output JS APP nel Document esclusione
		if ($doc->getType () !== 'html' || $this->app->input->getCmd ( 'tmpl' ) === 'component') {
			return false;
		}
		
		// Discard other params and take only text keywords
		if (trim ( $text )) {
			$notifications = $this->notifyObservers ( array (
					array (
							'searchdispatch',
							$text 
					) 
			) );
			
			// Return notifications/Exception if called from this plugin
			if ($phrase == 'jrealtime_plugin') {
				return $notifications;
			} else {
				// This method has been called from Joomla search return a dummy void and manage Exception here if any
				if ($this->componentConfig->get ( 'enable_debug', false )) {
					foreach ( $notifications as $exception ) {
						// Found an exception, set into app response message queue for client side debug
						if ($exception instanceof JRealtimeException) {
							$this->app->enqueueMessage ( 'Error JRealtime Search', $exception->getErrorLevel () );
						}
					}
				}
				return;
			}
		}
	}
	
	/* Manage the Joomla updater based on the user license
	 *
	* @access public
	* @return void
	*/
	public function onInstallerBeforePackageDownload(&$url, &$headers) {
		$uri 	= JUri::getInstance($url);
		$parts 	= explode('/', $uri->getPath());
		$app = JFactory::getApplication();
		if ($uri->getHost() == 'storejextensions.org' && in_array('com_jrealtimeanalytics.zip', $parts)) {
			// Init as false unless the license is valid
			$validUpdate = false;
				
			// Manage partial language translations
			$jLang = JFactory::getLanguage();
			$jLang->load('com_jrealtimeanalytics', JPATH_BASE . '/components/com_jrealtimeanalytics', 'en-GB', true, true);
			if($jLang->getTag() != 'en-GB') {
				$jLang->load('com_jrealtimeanalytics', JPATH_BASE, null, true, false);
				$jLang->load('com_jrealtimeanalytics', JPATH_BASE . '/components/com_jrealtimeanalytics', null, true, false);
			}
				
			// Email license validation API call and &$url building construction override
			$cParams = JComponentHelper::getParams('com_jrealtimeanalytics');
			$registrationEmail = $cParams->get('registration_email', null);
				
			// License
			if($registrationEmail) {
				$prodCode = 'jrealtime';
				$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
	
				// Retrieve license informations from the remote REST API
				$apiResponse = null;
				$apiEndpoint = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet') . "/option,com_easycommerce/action,licenseCode/email,$registrationEmail/productcode,$prodCode";
				if (function_exists('curl_init')){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$apiResponse = curl_exec($ch);
					curl_close($ch);
				}
				$objectApiResponse = json_decode($apiResponse);
	
				if(!is_object($objectApiResponse)) {
					// Message user about error retrieving license informations
					$app->enqueueMessage(JText::_('COM_JREALTIME_ERROR_RETRIEVING_LICENSE_INFO'));
				} else {
					if(!$objectApiResponse->success) {
						switch ($objectApiResponse->reason) {
							// Message user about the reason the license is not valid
							case 'nomatchingcode':
								$app->enqueueMessage(JText::_('COM_JREALTIME_LICENSE_NOMATCHING'));
								break;
	
							case 'expired':
								// Message user about license expired on $objectApiResponse->expireon
								$app->enqueueMessage(JText::sprintf('COM_JREALTIME_LICENSE_EXPIRED', $objectApiResponse->expireon));
								break;
						}
							
					}
						
					// Valid license found, builds the URL update link and message user about the license expiration validity
					if($objectApiResponse->success) {
						$url = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet' . '/XZY1306TQUOnifs3243564832kfuxnj35td1rtt1286f.ugzy');
	
						$validUpdate = true;
						$app->enqueueMessage(JText::sprintf('COM_JREALTIME_EXTENSION_UPDATED_SUCCESS', $objectApiResponse->expireon));
					}
				}
			} else {
				// Message user about missing email license code
				$app->enqueueMessage(JText::sprintf('COM_JREALTIME_MISSING_REGISTRATION_EMAIL_ADDRESS', JFilterOutput::ampReplace('index.php?option=com_jrealtimeanalytics&task=config.display#_licensepreferences')));
			}
				
			if(!$validUpdate) {
				$app->enqueueMessage(JText::_('COM_JREALTIME_UPDATER_STANDARD_ADVISE'), 'notice');
			}
		}
	}
	
	/**
	 * Constructor
	 *
	 * @param
	 *        	object &$subject The object to observe
	 * @param array $config
	 *        	An optional associative array of configuration settings.
	 *        	Recognized key values include 'name', 'group', 'params', 'language'
	 *        	(this list is not meant to be comprehensive).
	 *        	
	 * @since 1.5
	 */
	public function __construct(&$subject, $config = array()) {
		parent::__construct ( $subject, $config );
		
		$this->app = JFactory::getApplication ();
		$this->myUser = JFactory::getUser ();
		$this->componentConfig = JComponentHelper::getParams ( 'com_jrealtimeanalytics' );
	}
}