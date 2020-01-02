<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

require_once(JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php');

class PPHelperApp
{
	// Deprecated
	static $apps = array();
	static $paths = array();
	//

	static $tags = null;
	static $registeredFiles = array();

	// For plugins that are installed under a different group than "payplans"
	static $customPaths = array();

	/**
	 * For apps that are not plugins under the "payplans" group, we allow them to register a different one
	 *
	 * @deprecated	4.0.0
	 */
	public static function addCustomPath($element, $path = null)
	{
		self::$customPaths[$element] = $path;
	}

	/**
	 * Retrieves the custom path
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getCustomPath($element)
	{
		$path = PP::normalize(self::$customPaths, $element, false);

		return $path;
	}

	/**
	 * Add a path from where we can load Apps
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function registerPath($path)
	{
		if (isset(self::$registeredFiles[$path])) {
			return self::$registeredFiles[$path];
		}

		$exists = JFile::exists($path);
		self::$registeredFiles[$path] = $exists;

		if ($exists) {
			require_once($path);
		}

		return self::$registeredFiles[$path];
	}

	/**
	 * Generates the app path
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getAppPath($app)
	{
		$path = JPATH_PLUGINS . '/payplans/' . strtolower($app->type);
		$path .= '/app/' . strtolower($app->type) . '.php';

		return $path;
	}

	/**
	 * Load a single app from the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function loadApp($app)
	{
		$className = 'PPApp' . ucfirst($app->type);

		if (!class_exists($className)) {
			$path = self::getAppPath($app);

			// Register the app's path
			self::registerPath($path);

			// If class still doesn't exist, it means there's no app
			if (!class_exists($className)) {
				return false;
			}
		}

		$instance = new $className($app);

		return $instance;
	}

	/**
	 * Load all apps from the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function loadApps()
	{
		static $instances = null;

		if (is_null($instances)) {

			$model = PP::model('App');
			$options = array('published' => 1);
			$apps = $model->loadRecords($options);

			$instances = array();

			foreach ($apps as $app) {

				$instance = self::loadApp($app);

				if ($instance === false) {
					continue;
				}

				$instances[$instance->getId()] = $instance;
			}
		}

		return $instances;
	}

	/**
	 * Trigger apps
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function trigger($eventName, $args = array(), $purpose ='',  $refObject = null)
	{
		// Load available apps on the site
		$apps = self::loadApps();
		
		$result = array();

		if (!$apps) {
			return $result;
		}

		// Trigger all apps if they serve the purpose
		foreach ($apps as $app) {
			if (method_exists($app, $eventName) && $app->hasPurpose($purpose) && $app->isApplicable($refObject, $eventName)) {
				$result[$app->getId()] = call_user_func_array(array($app,$eventName), $args);
			}
		}

		return $result;
	}

	/**
	 * @deprecated in 1.2, use getXml instead
	 * XITODO:1.4 This will not be available in 1.4 release. Remove it
	 */
	static function getXmlData($what = 'name')
	{
		$result = array();

		$xml = self::getXml();
		if ($xml !== null) {
			foreach($xml as $key => $array){
				$result[$key] = isset($array[$what])? $array[$what] : null;
			}
		}

		return $result;
	}

	static public function getTags($merged=false, $what = 'tags')
	{
		if(self::$tags === null){
			self::$tags = array();

			$xml = self::getXml();
			if ($xml !== null){
				foreach($xml as $key => $array){
					self::$tags[$key] = isset($array[$what])? $array[$what] : array();
				}
			}

			foreach (self::getPlugins() as $key => $array)
			{
				self::$tags[$key] = isset($array[$what])? $array[$what] : array();
			}
		}

		// return only tags
		if($merged){
			$mtags= array();
			foreach(self::$tags as $apptag){
				$mtags = array_merge($mtags, $apptag);
			}

			$mtags = array_unique(array_map('ucfirst', $mtags));
			// only unique and sorted
			return array_values($mtags);
		}
		return self::$tags;
	}


	static $xmlData = null;
	static public function getXml()
	{
		$apps = self::getApps();

		if(self::$xmlData === null){
			foreach($apps as $app){
				$appInstance = PayplansApp::getInstance( null, $app);
				if($appInstance == false){
					continue;
				}
				$xml = $appInstance->getLocation() . '/' . $appInstance->getName() . '.xml';

			if (file_exists($xml)) {
					$xmlContent = simplexml_load_file($xml);
				}
				else {
					$xmlContent = null;
				}

				// if no tag was defined at least all tag is added
				self::$xmlData[$appInstance->getName()]['tags'] = array('all');
				self::$xmlData[$appInstance->getName()]['location'] = $appInstance->getLocation();
				self::$xmlData[$appInstance->getName()]['icon'] = JPATH_ROOT.'/administrator/components/com_payplans/templates/default/_media/images/icons/48/app.png';

				foreach ($xmlContent as $element=> $value){
					$value = (string)$value;
					if($element == 'tags'){
						$value = array_merge(array('all'), explode(',',$value));
					}
					if($element == 'icon'){
						$value = $appInstance->getLocation().'/'.$value;
					}
					self::$xmlData[$appInstance->getName()][$element] = $value;
				}
			}
		}

		return self::$xmlData;
	}

	/**
	 * @deprecated Since 1.3 Use getApplicableApps
	 */
	static function getApplicationApps($purpose='', PPAppTriggerableInterface $refObject=null)
	{
		return self::getApplicableApps($purpose, $refObject);
	}

	/**
	 *
	 * Get all apps which are of this purpose and
	 * applicable on this refObject
	 *
	 * @param String $purpose
	 * @param PayplansIfaceApptriggerable $refObject
	 * @since 1.3
	 */
	static function getApplicableApps($purpose='', PPAppTriggerableInterface $refObject=null)
	{
		//get Plugins classes names
		$apps = self::loadApps();

		$results = array();

		//trigger all apps if they serve the purpose
		foreach($apps as $app)
		{
			if($app->hasPurpose($purpose) && $app->isApplicable($refObject)){
				$results[$app->getId()] = $app;
			}
		}

		return $results;
	}

	/**
	 * Retrieves a list of available apps
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getAvailableApps($purpose = '')
	{
		//get Plugins classes names
		$apps = self::loadApps();

		$results = array();

		//trigger all apps if they serve the purpose
		foreach ($apps as $app) {
			if ($app->hasPurpose($purpose)) {
				$results[$app->getId()] = $app;
			}
		}

		return $results;
	}

	/**
	 * Deprecated
	 *
	 * @deprecated	4.0.0
	 */
	public static function getResourceModel()
	{
		$model = PP::model('Resource');
		return $model;
	}

	public static function getResource($userid, $groupid, $resource)
	{
		$model = PP::model('resource');

		$record = $model->loadRecords(array(	'user_id'  => $userid,
												'title' => $resource,
												'value'	   => $groupid));

		$record = array_shift($record);
		if(empty($record) || !$record){
			return false;
		}

		// always trim the string by comma (,)
		$record->subscription_ids = JString::trim($record->subscription_ids, ',');

		return $record;
	}

	public static function addToResource($subId, $userid, $groupid, $resource, $count = 0)
	{
		$record 	= self::getResource($userid, $groupid, $resource);
		$id 		= 0;

		$data['subscription_ids'] 	= $subId;
		$data['value']				= $groupid;
		$data['title'] 				= $resource;
		$data['user_id']			= $userid;
		$data['count']				= $count;

		if($record){
			$id = $record->resource_id;
			$record->subscription_ids 	= empty($record->subscription_ids) ? array() : explode(',', $record->subscription_ids);
			$record->subscription_ids[] = $subId;
			$data['subscription_ids'] 	= implode(',', $record->subscription_ids);
			$data['count']				= $record->count + $count;
		}

		// each subscription id should be packed with comma (,)
		$data['subscription_ids'] = ','.$data['subscription_ids'].',';
		$rmodel = self::getResourceModel();
		return $rmodel->save($data, $id);
	}

	public static function removeFromResource($subId, $userid, $groupid, $resource, $count = 0)
	{
		$record 	= self::getResource($userid, $groupid, $resource);

		// should not remove from this group, if resource is not available
		if(!$record || empty($record)){
			return false;
		}

		$record->subscription_ids = explode(',', $record->subscription_ids);

		// do not remove from this group if user was not added by this subscription
		if(!in_array($subId, $record->subscription_ids)){
			return false;
		}

		$data['value']				= $groupid;
		$data['title'] 				= $resource;
		$data['user_id']			= $userid;
		$data['count']				= $record->count - $count;

		// if count becomes negative then set it 0
		if($data['count'] < 0){
			$data['count'] = 0;
		}

		// remove the currenct sub id from ids
		$record->subscription_ids = array_diff($record->subscription_ids, array($subId));
		$data['subscription_ids'] 	= implode(',', $record->subscription_ids);

		$rmodel = self::getResourceModel();
		$remove = false;

		// if ids are empty then return true, and remove from group
		// and delete the resource
		if(empty($data['subscription_ids'])){
			$rmodel->delete($record->resource_id);
			$remove = true;
		}
		// each subscription id should be packed with comma (,)
		$data['subscription_ids'] 	= ','.$data['subscription_ids'].',';
		// do not remove if any ids are there
		$rmodel->save($data, $record->resource_id);
		return $remove;
	}

	public static function getAppCount()
		{
			//SELECT type, count(`app_id`) FROM `j216_payplans_app` group by `type`
			$query = new XiQuery();
			$query->select('`type`,count(`app_id`) as count')
				  ->from('#__payplans_app')
				  ->group('`type`');
			$result  = $query->dbLoadQuery()->loadObjectList('type');
			return $result;

		}

	public static $pluginXmlData = null;
	public static function getPlugins()
	{

		if (self::$pluginXmlData !== null)
		{
			return self::$pluginXmlData;
		}

		//to load auto-login plugin to get listed in app section
		XiHelperPlugin::loadPlugins('authentication');

		$plugins  = PayplansHelperEvent::trigger('onPayplansGetPlugin');
		if(empty($plugins)){
			return array();
		}

		foreach($plugins as $plugin ){
			self::$pluginXmlData[$plugin['name']]['tags'] = array('all');
			self::$pluginXmlData[$plugin['name']]['icon'] = JUri::root().'administrator/components/com_payplans/templates/default/_media/images/icons/48/app.png';
			$attributes  = $plugin['value']->attributes();
			foreach ($plugin['value'] as $element=> $value){
				$value = (string)$value;

				self::$pluginXmlData[$plugin['name']][$element] = $value;
					 if($element == 'name')
						 $pluginName[] = $value;

					if($element == 'icon'){
						self::$pluginXmlData[$plugin['name']][$element]  = JUri::root().'plugins/'.(string)$attributes['group'].'/'.$plugin['name'].'/'.$value;
					}
				if($element == 'tags'){
					self::$pluginXmlData[$plugin['name']][$element] = array_merge(array('all'), explode(',',$value));
				}

			}

		 }

		 $pluginId =  PayplansFactory::getInstance('app','model')->getPluginId($pluginName);
		 foreach ($pluginId as $plg => $id){
			  self::$pluginXmlData[$plg]['extension_id'] = $id->id;
		 }

		 return self::$pluginXmlData;

	}


	/**
	 * Load apps from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getApps()
	{
		// Already loaded
		if (self::$apps) {
			return self::$apps;
		}

		$paths = self::getAppsPath();

		//load apps from file systems
		foreach ($paths as $path) {
			$newApps = JFolder::folders($path);

			if( !is_array($newApps)) {
				continue;
			}

			// add to new apps discovered into list (only if app file exist in folder)
			// also mark them autoload
			foreach ($newApps as $app) {
				$appPath = $path . '/' . $app . '/' . $app . '.php';
				$appPathExists = JFile::exists($appPath);

				if ($appPathExists){
					PayplansHelperLoader::addAutoLoadFile($appPath, 'PayplansApp'.$app);

					self::$apps[$app] = $app;
				}
			}
		}

		// Sort apps for consistent behavior
		sort(self::$apps);

		return self::$apps;
	}

	/**
	 * return Apps of given purpose e.g. payment
	 * In Default value return all apps
	 * @param string purpose
	 * @return Array of particular Purpose Apps
	 */
	static function getPurposeApps($purpose = '')
	{
		static $purposeApps = array();

		$allApps = self::getApps();
dump($allApps);
		// Return all apps
		if($purpose == ''){
			return $allApps;
		}

		//XITODO : implement cache clean
		// if already cached
		if(isset($purposeApps[$purpose]))
			return $purposeApps[$purpose];

		// not cached, add all classes
		$purposeApps[$purpose] = array();
		$purposeClass = 'PayplansApp'.JString::ucfirst($purpose);

		foreach ($allApps as $app) {
			$appClass = 'PayplansApp'.JString::ucfirst($app);

			// bug in php, subclass having issue with autoloading multiple chained classes
			// http://bugs.php.net/bug.php?id=51570
			class_exists($appClass, true);

			if (is_subclass_of($appClass, $purposeClass)) {
				$purposeApps[$purpose][] = $app;
			}
		}

		return $purposeApps[$purpose];
	}

	/**
	 * Deprecated. Use @customPath instead
	 *
	 * @deprecated	4.0.0
	 */
	public static function addAppsPath($path = null)
	{
		// DO NOT ADD ANYTHING HERE.
	}
}

