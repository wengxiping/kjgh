<?php
//namespace administrator\components\com_jrealtimeanalytics;
/**  
 * Application install script
 * @package JREALTIMEANALYTICS::administrator::components::com_jrealtimeanalytics 
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html   
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
  
/** 
 * Application install script class
 * @package JREALTIMEANALYTICS::administrator::components::com_jrealtimeanalytics  
 */
class com_jrealtimeanalyticsInstallerScript {
	/*
	* Find mimimum required joomla version for this extension. It will be read from the version attribute (install tag) in the manifest file
	*/
	private $minimum_joomla_release = '3.0';
	
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	function preflight($type, $parent) {
		// Check for Joomla compatibility
		if(version_compare(JVERSION, '3', '<') || version_compare(JVERSION, '4', '>=')) {
			JFactory::getApplication()->enqueueMessage (JText::sprintf('COM_JREALTIME_INSTALLING_VERSION_NOTCOMPATIBLE', JVERSION), 'error');
			return false;
		}
	}
	
	/*
	 * $parent is the class calling this method.
	 * install runs after the database scripts are executed.
	 * If the extension is new, the install method is run.
	 * If install returns false, Joomla will abort the install and undo everything already done.
	 */
	function install($parent) {
		$database = JFactory::getDBO ();
		echo ('<style type="text/css">div.alert-success, span.step_details {display: none;font-size: 12px;} span.step_details div{margin-top:0 !important;} table.adminform h3{text-align:left;}.installcontainer{max-width: 800px;}</style>');
		echo ('<link rel="stylesheet" type="text/css" href="' . JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/css/bootstrap-install.css' . '" />');
		echo ('<script type="text/javascript" src="' . JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/installer.js' .'"></script>' );
		$lang = JFactory::getLanguage ();
		$lang->load ( 'com_jrealtimeanalytics' );
		
		$parentParent = $parent->getParent();
		
		// Component installer
		$componentInstaller = JInstaller::getInstance ();
		if(!$componentInstaller->getPath ( 'source' )) {
			$componentInstaller = $parent->getParent();
		}
		$pathToPlugin = $componentInstaller->getPath ( 'source' ) . '/plugin';
		$pathToModule = $componentInstaller->getPath ( 'source' ) . '/module';
		
		echo ('<div class="installcontainer">');
		// New plugin installer
		$pluginInstaller = new JInstaller ();
		if (! $pluginInstaller->install ( $pathToPlugin )) {
			echo '<p>' . JText::_( 'COM_JREALTIME_ERROR_INSTALLING_PLUGINS' ) . '</p>';
			// Install failed, rollback changes
			$parentParent->abort(JText::_('COM_JREALTIME_ERROR_INSTALLING_PLUGINS'));
			return false;
		} else {
			$query = "UPDATE #__extensions" . "\n SET enabled = 1" . 
					 "\n WHERE type = 'plugin' AND element = " . $database->Quote ( 'jrealtimeanalytics' ) . 
					 "\n AND folder = " . $database->Quote ( 'system' );
			$database->setQuery ( $query );
			if (! $database->execute ()) {
				echo '<p>' . JText::_( 'COM_JREALTIME_ERROR_PUBLISHING_PLUGIN' ) . '</p>';
			}?>
			<div class="progress">
				<div class="bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
					<span class="step_details"><?php echo JText::_('COM_JREALTIME_OK_INSTALLING_PLUGINS');?></span>
				</div>
			</div>
			<?php 
		}
		
		// New module installer
		$moduleInstaller = new JInstaller ();
		if (! $moduleInstaller->install ( $pathToModule )) {
			echo '<p>' . JText::_ ( 'COM_JREALTIME_ERROR_INSTALLING_MODULE' ) . '</p>';
			// Install failed, rollback changes
			$parentParent->abort(JText::_('COM_JREALTIME_ERROR_INSTALLING_MODULE'));
			return false;
		} else {
			?>
			<div class="progress">
				<div class="bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
					<span class="step_details"><?php echo JText::_('COM_JREALTIME_OK_INSTALLING_MODULE');?></span>
				</div>
			</div>
			<?php 
		}
		?>
		<div class="progress">
			<div class="bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
				<span class="step_details"><?php echo JText::_('COM_JREALTIME_OK_INSTALLING_COMPONENT');?></span>
		  	</div>
		</div>
		
		<div class="alert alert-success"><?php echo JText::_('COM_JREALTIME_ALL_COMPLETED');?></div>
		<?php 
		echo ('</div>');
		
		// Update tables to Joomla 3.5 Utf8mb4 utf8_unicode_ci collation if the Joomla database has been upgraded, use feature detection on the #__session core table
		try {
			$db = JFactory::getDbo();
			
			// Get Third Party table current collation
			$thirdpartyCollationQuery = "SHOW FULL COLUMNS FROM " . $db->quoteName(('#__realtimeanalytics_serverstats'));
			$thirdpartyResultTableInfo = $db->setQuery($thirdpartyCollationQuery)->loadObjectList();
			$thirdpartyResultTableFieldInfo = $thirdpartyResultTableInfo[0]; // session_id_person field
			
			// Get Joomla core table current collation
			$testCollationQuery = "SHOW FULL COLUMNS FROM " . $db->quoteName(('#__session'));
			$resultTableInfo = $db->setQuery($testCollationQuery)->loadObject();
			if(isset($resultTableInfo->Collation) && isset($thirdpartyResultTableFieldInfo->Collation) && $resultTableInfo->Collation != $thirdpartyResultTableFieldInfo->Collation) {
				// #__realtimeanalytics_serverstats table Utf8mb4 utf8_unicode_ci
				$charset = strpos($resultTableInfo->Collation, 'utf8mb4') !== false ? 'utf8mb4' : 'utf8';
				$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_serverstats') . " CHANGE " . $db->quoteName('session_id_person') . " " . $db->quoteName('session_id_person') ." VARCHAR( 191 ) CHARACTER SET " . $charset . " COLLATE " . $resultTableInfo->Collation . " NOT NULL ;";
				$db->setQuery($alterSessiontablesCollation)->execute();
					
				// #__realtimeanalytics_realstats table Utf8mb4 utf8_unicode_ci
				$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_realstats') . " CHANGE " . $db->quoteName('session_id_person') . " " . $db->quoteName('session_id_person') ." VARCHAR( 191 ) CHARACTER SET " . $charset . " COLLATE " . $resultTableInfo->Collation . " NOT NULL ;";
				$db->setQuery($alterSessiontablesCollation)->execute();
					
				// #__realtimeanalytics_eventstats_track table Utf8mb4 utf8_unicode_ci
				$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_eventstats_track') . " CHANGE " . $db->quoteName('session_id') . " " . $db->quoteName('session_id') ." VARCHAR( 191 ) CHARACTER SET " . $charset . " COLLATE " . $resultTableInfo->Collation . " NOT NULL ;";
				$db->setQuery($alterSessiontablesCollation)->execute();
				
				// #__realtimeanalytics_realstats table Utf8mb4 utf8_unicode_ci
				$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_referral') . " CHANGE " . $db->quoteName('session_id_person') . " " . $db->quoteName('session_id_person') ." VARCHAR( 191 ) CHARACTER SET " . $charset . " COLLATE " . $resultTableInfo->Collation . ";";
				$db->setQuery($alterSessiontablesCollation)->execute();
			} else {
				// Align tables to the newest varbinary
				if(isset($resultTableInfo->Type) && isset($thirdpartyResultTableFieldInfo->Type) && stripos($resultTableInfo->Type, 'varbinary') !== false && $resultTableInfo->Type != $thirdpartyResultTableFieldInfo->Type) {
					// #__realtimeanalytics_serverstats table varbinary
					$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_serverstats') . " CHANGE " . $db->quoteName('session_id_person') . " " . $db->quoteName('session_id_person') ." VARBINARY( 192 ) NOT NULL ;";
					$db->setQuery($alterSessiontablesCollation)->execute();
					
					// #__realtimeanalytics_realstats table varbinary
					$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_realstats') . " CHANGE " . $db->quoteName('session_id_person') . " " . $db->quoteName('session_id_person') ." VARBINARY( 192 ) NOT NULL ;";
					$db->setQuery($alterSessiontablesCollation)->execute();
					
					// #__realtimeanalytics_eventstats_track table varbinary
					$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_eventstats_track') . " CHANGE " . $db->quoteName('session_id') . " " . $db->quoteName('session_id') ." VARBINARY( 192 ) NOT NULL ;";
					$db->setQuery($alterSessiontablesCollation)->execute();
					
					// #__realtimeanalytics_realstats table varbinary
					$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__realtimeanalytics_referral') . " CHANGE " . $db->quoteName('session_id_person') . " " . $db->quoteName('session_id_person') ." VARBINARY( 192 );";
					$db->setQuery($alterSessiontablesCollation)->execute();
				}
			}
		} catch (Exception $e) {
			// Do nothing for user
		}
		
		return true;
	}
	
	/*
	 * $parent is the class calling this method.
	 * update runs after the database scripts are executed.
	 * If the extension exists, then the update method is run.
	 * If this returns false, Joomla will abort the update and undo everything already done.
	 */
	function update($parent) {
		// Execute always SQL install file to get added updates in that file, disregard DBMS messages and Joomla queue for user
		$parentParent = $parent->getParent();
		$parentManifest = $parentParent->getManifest();
		try {
			// Install/update always without error handlingm case legacy J Error
			JError::setErrorHandling(E_ALL, 'ignore');
			if (isset($parentManifest->install->sql)) {
				$updateResult = $parentParent->parseSQLFiles($parentManifest->install->sql);
				if(!$updateResult) {
					$app = JFactory::getApplication();
					$appReflection = new ReflectionClass(get_class($app));
					$_messageQueue = $appReflection->getProperty('_messageQueue');
					$_messageQueue->setAccessible(true);
					$_messageQueue->setValue($app, array());
				}
			}
		} catch (Exception $e) {
			// Do nothing for user for Joomla 3.x case, case Exception handling
		}

		// Indifferentemente gestiamo l'installazione del plugin
		$this->install($parent);
	}
	
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * postflight is run after the extension is registered in the database.
	 */
	function postflight($type, $parent) { 
		// Preferences general
		$params ['daemonrefresh'] = '6';
		$params ['realtimerefresh'] = '6';
		$params ['maxlifetime_session'] = '10';
		$params ['daemontimeout'] = '0';
		$params ['guestprefix'] = 'Visitor';
		$params ['cpanelstats_period_interval'] = 'day';
		$params ['geolocation_mode'] = 'ip';
		$params ['heatmap_status'] = '1';
		$params ['heatmap_max_valid_width'] = '350';
		$params ['heatmap_max_valid_height'] = '50';
		$params ['registration_email'] = '';

		// Preferences stats report 
		$params ['default_period_interval'] = 'week';
		$params ['details_stats'] = '1';
		$params ['geolocation_stats'] = '1';
		$params ['os_stats'] = '1';
		$params ['browser_stats'] = '1';
		$params ['device_stats'] = '1';
		$params ['landing_stats'] = '1';
		$params ['leaveoff_stats'] = '1';
		$params ['visitsbypage_stats'] = '1';
		$params ['visitsbyuser_stats'] = '1';
		$params ['visitsbyip_stats'] = '1';
		$params ['referral_stats'] = '1';
		$params ['searchkeys_stats'] = '1';
		$params ['xtd_singleuser_stats'] = '0';
		$params ['show_usergroup'] = '0';
		$params ['show_referral'] = '0';
		
		// Exclusions
		$params ['ipaddress'] = '';
		$params ['iprange_start'] = '';
		$params ['iprange_end'] = '';
		$params ['ip_multiple'] = '';
		$params ['iprange_multiple'] = '';
		$params ['daemon_exclusions'] = array('0');
		$params ['groups_exclusions'] = array('0');
		$params ['countrycode_exclusions'] = array('0');
		$params ['useragents_exclusions'] = 'googlebot,bingbot,acontbot,baiduspider,slurp,google,duckduckbot,yandexbot,Sogou,facebookexternalhit,ia_archiver';
		
		// Stats module
		$params ['daily_stats'] = '1';
		$params ['realtime_stats'] = '0';
		$params ['visualmap_stats'] = '0';
		$params ['visitors_counter'] = '0';
		$params ['visitors_counter_position'] = '0';
		$params ['module_default_period_interval'] = 'day';
		$params ['module_start_date'] = '';
		$params ['module_end_date'] = '';

		// Advanced
		$params ['gdpr_integration'] = '0';
		$params ['gcenabled'] = '1'; 
 		$params ['probability'] = '20';
 		$params ['gc_serverstats_enabled'] = '0';
 		$params ['gc_serverstats_period'] = '24';
 		$params ['caching'] = '0';
 		$params ['cache_lifetime'] = '60';
 		$params ['offset_type'] = 'joomla';
 		$params ['anonymize_ipaddress'] = '0';
 		$params ['gdpr_ip_pseudonymisation'] = '0';
 		$params ['direct_track_extensions'] = array('0');
 		$params ['shared_session_support'] = '1';
 		$params ['geolocation_php_func'] = 'file';
 		$params ['cloudflare_ip_masking'] = '0';
 		$params ['backend_host_info'] = '0';
 		$params ['backend_geolocation_service'] = 'geoiplookup';
 		$params ['stats_geolocation_service'] = 'geoplugin';
 		
 		// Report
 		$params ['report_mailfrom'] = '';
 		$params ['report_fromname'] = '';
 		$params ['report_byemail'] = '0';
 		$params ['report_addresses'] = '';
 		$params ['report_format'] = 'emailxls';
 		$params ['report_frequency'] = '7';
 		$params ['overview_report_type'] = '0';
 		$params ['report_script_jquery_onload_event'] = '0';

 		// JS management
		$params ['includejquery'] = '1';
		$params ['noconflict'] = '1';
		$params ['scripts_loading'] = 'defer';
		$params ['start_application_event'] = 'afterroute';
		$params ['cbnoconflict'] = '1';
		$params ['enable_debug'] = '0';

		// Analytics settings
		$params ['ga_domain'] = '';
		$params ['wm_domain'] = '';
		$params ['ga_api_key'] = '';
		$params ['ga_client_id'] = '';
		$params ['ga_client_secret'] = '';

		// Insert all params settings default first time, merge and insert only new one if any on update, keeping current settings
		if ($type == 'install') {  
			$this->setParams ( $params );  
		} elseif ($type == 'update') {
			// Load and merge existing params, this let add new params default and keep existing settings one
			$db = JFactory::getDbo ();
			$query = $db->getQuery(true);
			$query->select('params');
			$query->from('#__extensions');
			$query->where($db->quoteName('name') . '=' . $db->quote('jrealtimeanalytics'));
			$db->setQuery($query);
			$existingParamsString = $db->loadResult();
			// store the combined new and existing values back as a JSON string
			$existingParams = json_decode ( $existingParamsString, true );
			$updatedParams = array_merge($params, $existingParams);
			
			$this->setParams($updatedParams);
		} 
	}
	
	/*
	 * $parent is the class calling this method
	 * uninstall runs before any other action is taken (file removal or database processing).
	 */
	function uninstall($parent) {
		$database = JFactory::getDBO ();
		$lang = JFactory::getLanguage();
		$lang->load('com_jrealtimeanalytics');
		 
		// Controllo esistenza del plugin
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'plugin' AND element = " . $database->Quote('jrealtimeanalytics') .
				 "\n AND folder = " . $database->Quote('system');
		$database->setQuery($query);
		$pluginID = $database->loadResult();
		if(!$pluginID) {
			echo '<p>' . JText::_('COM_JREALTIME_PLUGIN_ALREADY_REMOVED') . '</p>';
		} else {
			// Si necessita una nuova istanza dell'installer per il plugin
			$pluginInstaller = new JInstaller ();
			if(!$pluginInstaller->uninstall('plugin', $pluginID)) {
				echo '<p>' . JText::_('COM_JREALTIME_ERROR_UNINSTALLING_PLUGINS') . '</p>';
			} 
		}
		
		// Check if module exists
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'module' AND element = " . $database->quote('mod_jrealtimeanalytics') .
				 "\n AND client_id = 0";
		$database->setQuery($query);
		$moduleID = $database->loadResult();
		if(!$moduleID) {
			echo '<p>' . JText::_('COM_JREALTIME_MODULE_ALREADY_REMOVED') . '</p>';
		} else {
			// New plugin installer
			$moduleInstaller = new JInstaller ();
			if(!$moduleInstaller->uninstall('module', $moduleID)) {
				echo '<p>' . JText::_('COM_JREALTIME_ERROR_UNINSTALLING_MODULE') . '</p>';
			}
		}
		
		// Processing completo
		return true;
	}
	
	/*
	 * get a variable from the manifest file (actually, from the manifest cache).
	 */
	function getParam($name) {
		$db = JFactory::getDbo ();
		$db->setQuery ( 'SELECT manifest_cache FROM #__extensions WHERE name = "jrealtimeanalytics"' );
		$manifest = json_decode ( $db->loadResult (), true );
		return $manifest [$name];
	}
	
	/*
	 * sets parameter values in the component's row of the extension table
	 */
	function setParams($param_array) {
		if (count ( $param_array ) > 0) { 
			$db = JFactory::getDbo (); 
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode ( $param_array );
			$db->setQuery ( 'UPDATE #__extensions SET params = ' . $db->quote ( $paramsString ) . ' WHERE name = "jrealtimeanalytics"' );
			$db->execute ();
		}
	}
}