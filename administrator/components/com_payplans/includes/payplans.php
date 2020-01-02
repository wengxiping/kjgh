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

require_once(__DIR__ . '/dependencies.php');
require_once(__DIR__ . '/api.php');

class PP
{
	/**
	 * Magic method to load static objects
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function __callStatic($name, $arguments)
	{
		static $staticLibraries = array();

		// Load the library first
		PP::load($name);

		$className = 'PP' . ucfirst($name);

		if (method_exists($className, 'factory')) {
			$object = call_user_func_array(array($className, 'factory'), $arguments);

			return $object;
		}

		// For classes with $static variables, we assume that it should only be rendered once
		if (isset($className::$static) && $className::$static) {

			if (!isset($staticLibraries[$className])) {
				$staticLibraries[$className] = new $className();
			}

			return $staticLibraries[$className];
		}

		$staticLibraries[$className] = new $className();

		return $staticLibraries[$className];
	}

	/**
	 * Ajax library needs to be a single instance
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function ajax()
	{
		static $ajax = null;

		if (is_null($ajax)) {
			PP::load('ajax');

			$ajax = new PPAjax();
		}

		return $ajax;
	}

	/**
	 * Creates an instance of the database library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function db()
	{
		PP::load('DB');

		$db = PPDb::getInstance();

		return $db;
	}

	/**
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public static function user($ids = null, $debug = false)
	{
		// Load the user library
		self::load('User');

		return PPUser::factory($ids, $debug);
	}

	/**
	 * Generic method to log data into a logging file (for debugging purposes only)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function debug($data, $file)
	{
		ob_start();
		print_r($data);
		$contents = ob_get_contents();
		ob_end_clean();

		return JFile::write($file, $contents);
	}

	/**
	 * Renders the encryptor library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function encryptor($reset = false)
	{
		static $instance = null;

		if ($instance !== null && $reset === false) {
			return $instance;
		}

		PP::load('encryptor');

		$config = PP::config();
		$key = JString::strtoupper($config->get('expert_encryption_key'));

		$instance = new PPEncryptor($key);

		return $instance;
	}

	/**
	 * Renders the event library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function event($debug = false)
	{
		static $event = null;

		if (is_null($event)) {
			PP::load('event');

			$event = new PPEvent();
		}

		return $event;
	}

	/**
	 * Includes a file given a particular namespace in POSIX format.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function import($namespace)
	{
		static $locations = array();

		if (!isset($locations[$namespace])) {
			$parts = explode(':', $namespace);

			// Non POSIX standard.
			if (count($parts) <= 1) {
				return false;
			}

			$base = $parts[0];

			// Default path
			$path = PP_SITE;

			if ($base == 'admin') {
				$path = PP_ADMIN;
			}

			// Replace / with proper directory structure.
			$path = $path . str_ireplace('/', DIRECTORY_SEPARATOR, $parts[1]) . '.php';

			include_once($path);

			$locations[$namespace] = true;
		}

		return true;
	}

	/**
	 * Initialize the scripts and stylesheets on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function initialize($location = 'site')
	{
		// Determines if we should compile the javascripts on the site
		$config = PP::config();

		if (PP::isSiteAdmin()) {
			$app = JFactory::getApplication();
			$input = $app->input;
			$compile = $input->get('compile', false, 'bool');

			if ($compile) {

				// Determines if we need to minify the js
				$minify = $input->get('minify', false, 'bool');

				// Get section if not default one
				$section = $input->get('section', $location, 'cmd');

				// Get the compiler
				$compiler = PP::compiler();
				$result = array();

				// Compile with jquery.easyblog.js
				$result['standard'] = $compiler->compile($section, $minify);

				// Compile with jquery.js
				$result['basic'] = $compiler->compile($section, $minify, false);

				if ($result !== false) {
					header('Content-type: text/x-json; UTF-8');
					echo json_encode($result);
					exit;
				}
			}
		}

		static $loaded = array();

		if (!isset($loaded[$location])) {

			$app = JFactory::getApplication();
			$location = $app->isAdmin() ? 'admin' : 'site';

			// @TODO: Replace this in the future
			$theme = 'wireframe';

			if ($location == 'admin') {
				$theme = 'default';
			}

			// Attach the scripts
			$scripts = PP::scripts();
			$scripts->attach($location);

			// Attach css files
			$stylesheet = PP::stylesheet($location, $theme);
			$stylesheet->attach();

			$loaded[$location] = true;
		}

		return $loaded[$location];
	}

	/**
	 * Determines if a given string is a namespace on the filesystem
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function isNamespace($str)
	{
		// Explode the namespace
		$parts = explode(':', $str);

		if (count($parts) <= 1) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is a super admin on the site.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function isSiteAdmin($id = null)
	{
		static $items = array();

		$user = JFactory::getUser($id);

		if (!isset($items[$user->id])) {
			$items[$user->id] = $user->authorise('core.admin');
		}

		return $items[$user->id] ? true : false;
	}

	/**
	 * Retrieves the current Joomla template being used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getJoomlaTemplate($client = 'site')
	{
		static $template = array();

		if (!array_key_exists($client, $template)) {

			$app = JFactory::getApplication();

			// Try to load the template from joomla cache since some 3rd party plugins can change the templates on the fly. #449
			if ($client == 'site' && $app->isSite()) {
				$template[$client] = $app->getTemplate();
			} else {

				$clientId = ($client == 'site') ? 0 : 1;

				$db = PP::db();

				$query	= 'SELECT template FROM `#__template_styles` AS s'
						. ' LEFT JOIN `#__extensions` AS e ON e.type = `template` AND e.element=s.template AND e.client_id=s.client_id'
						. ' WHERE s.client_id = ' . $db->quote($clientId) . ' AND home = 1';

				$db->setQuery( $query );

				$result 	= $db->loadResult();

				// Fallback template
				if( !$result )
				{
					$result = ($client == 'site') ? 'beez_20' : 'bluestork';
				}

				$template[$client] = $result;
			}
		}

		return $template[$client];
	}

	/**
	 * Retrieves the login link
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getLoginLink($route = true, $xhtml = false)
	{
		$currentUrl = JURI::getInstance()->toString();
		$return = base64_encode($currentUrl);

		$link = 'index.php?option=com_users&task=login&' . PP::token() . '=1&return=' . $return;

		if ($route) {
			return JRoute::_($link, $xhtml);
		}

		return $link;
	}

	/**
	 * Retrieves the object's context.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getObjectContext($object)
	{
		return JString::strtolower($object->getPrefix().'_'.$object->getName());
	}

	/**
	 * Retrieves the formatter object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getFormatter($class, $logClass)
	{
		$mappings = array(
						'PayplansFormatterLibApp' => 'PayplansAppFormatter',
						'PayplansFormatterLibConfig' => 'PayplansConfigFormatter',
						'PayplansFormatterLibGroup' => 'PayplansGroupFormatter',
						'PayplansFormatterLibInvoice' => 'PayplansInvoiceFormatter',
						'PayplansFormatterLibOrder' => 'PayplansOrderFormatter',
						'PayplansFormatterLibPayment' => 'PayplansPaymentFormatter',
						'PayplansFormatterLibPlan' => 'PayplansPlanFormatter',
						'PayplansFormatterLibSubscription'	=> 'PayplansSubscriptionFormatter',
						'PayplansFormatterLibTransaction' 	=> 'PayplansTransactionFormatter',
						'PayplansFormatterLibUser' => 'PayplansUserFormatter',
						'PayplansFormatterEmail' => 'PayplansFormatter'
		);

		if (isset($mappings[$class])) {
			$class = $mappings[$class];
		}

		// For cron logs and email logs as they use PayplansFormatter class
		if ($class == 'PayplansFormatter' || $class == 'XiFormatter') {
			return new PayplansFormatter();
		}

		// Find lib class
		$libClass = str_replace('Formatter', '', $class);

		// if log-class extends PayplansAppFormatter
		if ($libClass == 'PayplansApp') {

			$customAppFormatter = $logClass . 'Formatter';

			if (class_exists($customAppFormatter, true) && is_subclass_of($customAppFormatter, 'PayplansAppFormatter')) {
				$class = $customAppFormatter;
				return new $class();
			}

			return new PayplansAppFormatter();
		}

		// If an app renders it's own formatter, the class should already exist by now
		if (class_exists($class,true)) {
			return new $class();
		}

		// Try to get the formatter for this class
		$logFormatter = PP::logFormatter();
		$formatter = $logFormatter->getFormatter($libClass);

		if ($formatter !== false) {
			return new $class();
		}

		// If all else fails, just use the default formatter
		return new PayplansFormatter();
	}

	/**
	 * Get statuses available on the site given the entity of the item.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getStatuses($entity)
	{
		static $statuses = null;

		// Instead of using reflection class to get constants, we define them here once.
		if (is_null($statuses)) {

			$statuses['subscription'] = array(
				'none' => PP_NONE,
				'active' => PP_SUBSCRIPTION_ACTIVE,
				'hold' => PP_SUBSCRIPTION_HOLD,
				'expired' => PP_SUBSCRIPTION_EXPIRED
			);

			$statuses['invoice'] = array(
				'none' => PP_NONE,
				'confirmed' => PP_INVOICE_CONFIRMED,
				'paid' => PP_INVOICE_PAID,
				'refunded' => PP_INVOICE_REFUNDED
			);

			$statuses['order'] = array(
				'none' => PP_NONE,
				'confirmed' => PP_ORDER_CONFIRMED,
				'paid' => PP_ORDER_PAID,
				'refunded' => PP_ORDER_HOLD,
				'expired' => PP_ORDER_EXPIRED
			);
		}

		$entity = strtolower($entity);

		return $statuses[$entity];
	}

	/**
	 * Renders Joomla's Global Configuration library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function jconfig()
	{
		static $config = false;

		if (!$config) {
			$config = JFactory::getConfig();
		}

		return $config;
	}

	/**
	 * Creates a new modifier
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function createModifier(PPInvoice $invoice, $amount, $percentage = false, $type = '', $message = '', $frequency = PP_MODIFIER_FREQUENCY_ONE_TIME, $serial = PP_MODIFIER_PERCENT_DISCOUNT)
	{
		$modifier = PP::modifier();
		$modifier->amount = $amount;
		$modifier->percentage = $percentage;
		$modifier->invoice_id = $invoice->getId();
		$modifier->user_id = $invoice->getBuyer(true)->getId();
		$modifier->type = $type;
		$modifier->frequency = $frequency;
		$modifier->serial = $serial;
		$modifier->message = $message;

		return $modifier;
	}

	/**
	 * Creates a new transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function createTransaction($invoice = null, $payment = null, $transactionId = 0, $subscriptionId = 0, $parentId = 0, $params = null)
	{
		$transaction = PP::transaction();

		if ($payment && ($payment instanceof PPPayment)) {
			$transaction->user_id = $payment->getBuyer();
			$transaction->payment_id = $payment->getId();
		}

		if ($invoice && ($invoice instanceof PPInvoice)) {
			$transaction->invoice_id = $invoice->getId();
		}

		if ($params) {
			$params = new JRegistry($params);

			$transaction->params = $params->toString();
		}

		$transaction->gateway_txn_id = $transactionId;
		$transaction->gateway_subscr_id = $subscriptionId;
		$transaction->gateway_parent_txn = $parentId;

		return $transaction;
	}

	/**
	 * Renders Payplans Configuration
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function config($reload = false)
	{
		static $config = false;

		if ($config && !$reload) {
			return $config;
		}

		$model = PP::model('Config');
		$configData = $model->getConfig();

		// Merge the configurations
		$defaultConfigPath = PP_DEFAULTS . '/config.json';
		$defaultConfigContents = JFile::read($defaultConfigPath);
		$config = new JRegistry($defaultConfigContents);

		$siteConfig = new JRegistry($configData);

		// Merge the stored configuration with the default configuration
		$config->merge($siteConfig);

		// Merge joomla's configuration
		$jConfig = JFactory::getConfig();
		$config->merge($jConfig);

		// Let plugin modify config
		$args = array(&$config);

		// PP::event()->trigger('onPayplansConfigLoad', $args);

		return $config;
	}

	/**
	 * If the current user is a super admin, allow them to change the environment via the query string
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function checkEnvironment()
	{
		if (!PP::isSiteAdmin()) {
			return;
		}

		$app = JFactory::getApplication();
		$environment = $app->input->get('pp_env', '', 'word');
		$allowed = array('production', 'development');

		// Nothing has changed
		if (!$environment || !in_array($environment, $allowed)) {
			return;
		}

		// We also need to update the database value
		$config = PP::table('Config');
		$config->load(array('key' => 'environment'));
		$config->key = 'environment';
		$config->value = $environment;
		$config->store();

		PP::info()->set('Updated system environment to <b>' . $environment . '</b> mode', 'success');
		return $app->redirect('index.php?option=com_payplans');
	}

	/**
	 * Get the user id stored in the session for proxy purchases
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getUserIdFromSession()
	{
		$session = PP::session();
		$id = (int) $session->get('REGISTRATION_NEW_USER_ID');

		return $id;
	}

	/**
	 * Creates a new dummy user on the site if it doesn't exist yet.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getDummyUserId()
	{
		static $userId = null;

		if (is_null($userId)) {

			$model = PP::model('User');
			$dummy = $model->getDummyUser();

			// If it doesn't exist, create the dummy user
			if (!$dummy) {
				$dummy = $model->createDummyUser();
			}

			$userId = (int) $dummy->id;
		}

		return $userId;
	}

	/**
	 * Remove out comments from an SQL query
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function filterComments($sql)
	{
		return preg_replace("!/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/!s","",$sql);
	}

	/**
	 * Loads the Form instances
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function form($type)
	{
		PP::load('Form');

		$form = new PPForm($type);

		return $form;
	}

	/**
	 * Loads a library from the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function load($library)
	{
		// We do not need to use JString here because files are not utf-8 anyway.
		$library = strtolower($library);
		$obj = false;

		$path = PP_LIB . '/' . $library . '/' . $library . '.php';
		include_once($path);
	}

	/**
	 * Loads a library from the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function lock($name, $timeout = 0)
	{
		PP::load('lock');

		static $libraries = array();

		if (!isset($libraries[$name])) {
			$lock = new PPLock($name, $timeout);

			$libraries[$name] = $lock;
		}

		return $libraries[$name];
	}

	/**
	 * Retrieve JTable instance
	 *
	 * @since 	4.0.0
	 * @access	public
	 **/
	public static function table($name, $prefix = 'PayPlansTable')
	{
		PP::import('admin:/tables/table');

		$table = PayPlansTable::getInstance($name, $prefix);

		return $table;
	}

	/**
	 * Simple way to minify css codes
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function minifyCSS($css)
	{
		$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
		$css = str_replace(': ', ':', $css);
		$css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

		return $css;
	}

	/**
	 * Creates a new model instance
	 *
	 * @since 	4.0.0
	 * @access	public
	 **/
	public static function model($name, $config = array())
	{
		static $models = array();

		// Construct the cache id
		$cacheId = strtolower($name);
		$keys = array_keys($config);
		$values = array_values($config);

		$cacheId .= implode('.', $keys) . implode('.', $values);

		if (!isset($models[$cacheId])) {

			PP::import('admin:/includes/model');

			$className = 'PayPlansModel' . ucfirst($name);

			// Include the model file. This is much quicker than doing JLoader::import
			if (!class_exists($className)) {
				$path = PP_MODELS . '/' . strtolower($name) . '.php';
				require_once($path);
			}

			// If the class still doesn't exist, let's just throw an error here.
			if (!class_exists($className)) {
				return JError::raiseError(500, JText::sprintf('Invalid model %1$s', $className));
			}

			$model = new $className($config);

			$models[$cacheId]	= $model;
		}

		// Forcefully run initState here instead of construct in the model because the same model might be used more than once in different states
		if (!empty($config['initState'])) {
			$models[$cacheId]->initStates();
		}

		return $models[$cacheId];
	}

	/**
	 * Creates a new view instance if it doesn't exist yet
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function view($name, $backend = true)
	{
		static $views = array();

		$className = 'PayPlansView' . ucfirst($name);
		$index = md5($className);

		if (!isset($views[$index])) {

			if (!class_exists($className)) {
				$path = $backend ? PP_ADMIN : PP_SITE;

				$doc = JFactory::getDocument();
				$path .= '/views/' . strtolower( $name ) . '/view.' . $doc->getType() . '.php';

				if (!JFile::exists($path)) {
					return false;
				}

				// Include the view
				require_once($path);
			}

			if (!class_exists($className)) {
				JError::raiseError(500, JText::sprintf('View class not found: %1s', $className));
				return false;
			}

			$views[$index] = new $className(array());
		}

		return $views[$index];
	}

	/**
	 * Creates a new stylesheet instance
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function stylesheet($location = 'site')
	{
		PP::load('Stylesheet');

		$stylesheet = new PPStyleSheet($location);

		return $stylesheet;
	}

	/**
	 * Create a new statistics object
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function statistics()
	{
		PP::load('statistics');

		$statistics = new PPStatistics();

		return $statistics;
	}

	/**
	 * Generates the CSRF token from Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function token()
	{
		return JFactory::getSession()->getFormToken();
	}

	/**
	 * Single point of entry for static calls.
	 *
	 * @since	3.7
	 * @access	public
	 */
	public static function call($className, $method, $args = array())
	{
		$item = strtolower($className);
		$obj = false;

		$path = PP_LIB . '/' . $item . '/' . $item . '.php';

		require_once($path);

		$class = 'PP' . ucfirst($className);

		// Ensure that $args is an array.
		$args = PP::makeArray($args);

		return call_user_func_array(array($class, $method), $args);
	}

	/**
	 * Converts an argument into an array.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function makeArray($item, $delimeter = null)
	{
		// If this is already an array, we don't need to do anything here.
		if (is_array($item)) {
			return $item;
		}

		// Test if source is a SocialRegistry/JRegistry object
		if ($item instanceof PPRegistry || $item instanceof JRegistry) {
			return $item->toArray();
		}

		// Test if source is an object.
		if (is_object($item)) {
			return JArrayHelper::fromObject( $item );
		}

		if (is_integer($item)) {
			return array($item);
		}

		// Test if source is a string.
		if (is_string($item)) {
			if ($item == '') {
				return array();
			}

			// Test for comma separated values.
			if (!is_null($delimeter) && stristr($item, $delimeter) !== false) {
				$data = explode($delimeter, $item);

				return $data;
			}

			// Test for JSON array string
			$pattern = '#^\s*//.+$#m';
			$item = trim(preg_replace($pattern, '', $item));
			if ((substr($item, 0, 1) === '[' && substr($item, -1, 1) === ']')) {
				return FD::json()->decode($item);
			}

			// Test for JSON object string, but convert it into array
			if ((substr($item, 0, 1) === '{' && substr($item, -1, 1) === '}')) {
				$result = FD::json()->decode($item);

				return JArrayHelper::fromObject($result);
			}

			return array( $item );
		}

		return false;
	}

	/**
	 * Utility to mark exit
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function markExit($msg = 'NO_MESSAGE')
	{
		// if not already set
		 if (defined('PAYPLANS_EXIT')==false){
			define('PAYPLANS_EXIT',$msg);
			return true;
		}

		//already set
		return false;
	}

	/**
	 * Rearranges a list of modifiers
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function rearrageModifiers($modifiers)
	{
		$results = array();

		// arrage according to their serial
		$arrangeOrder = array();

		foreach ($modifiers as $modifier) {
			$arrangeOrder[$modifier->getSerial()][] = $modifier;
		}

		$arranged = array();

		foreach (self::$serials as $serial){
			if(!isset($arrangeOrder[$serial])){
				continue;
			}

			$arranged = array_merge($arranged, $arrangeOrder[$serial]);
		}

		return $arranged;
	}

	/**
	 * Redirects to a given link
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function redirect($link, $message = '')
	{
		if ($message) {
			$message = JText::_($message);
		}

		$app = JFactory::getApplication();
		$app->redirect($link, $message);

		return $app->close();
	}

	/**
	 * Renders the resolver library to resolve namespaes
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function resolver()
	{
		static $resolver = false;

		if (!$resolver) {
			PP::load('resolver');

			$resolver = new PPResolver();
		}

		return $resolver;
	}

	/**
	 * Renders the rewriter library. It needs to be a singleton instance
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function rewriter()
	{
		static $lib = null;

		if (is_null($lib)) {
			PP::load('rewriter');

			$lib = new PPRewriter();
		}

		return $lib;
	}

	/**
	 * Rewrites a given content with the rewriter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function rewriteContent($content, $obj, $newlineToBreak = false)
	{
		if (!$content) {
			return $content;
		}

		if ($newlineToBreak) {
			$content = nl2br($content);
		}

		$rewriter = PP::rewriter();
		$content = $rewriter->rewrite($content, $obj);

		return $content;
	}

	/**
	 * Resolve a given POSIX path.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function resolve($path)
	{
		if (strpos($path, ':/') === false) {
			return false;
		}

		$parts = explode(':/', $path);

		// Get the protocol.
		$protocol = $parts[0];

		// Get the real path.
		$path = $parts[1];

		if ($protocol == 'modules') {
			return PP::call('Modules', 'resolve', $path);
		}

		if ($protocol == 'themes') {
			return PP::call('Themes', 'resolve', $path);
		}

		if ($protocol == 'ajax') {
			return PP::call('Ajax', 'resolveNamespace', $path);
		}

		if ($protocol == 'site' || $protocol == 'admin') {
			$key = 'PP_' . strtoupper($protocol);
			$basePath = constant($key);

			return $basePath . '/' . $path;
		}

		return false;
	}

	/**
	 * Renders a login page if necessary. If this is called via an ajax method, it will trigger a dialog instead.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function requireLogin($redirect = '')
	{
		$my = JFactory::getUser();

		// User is logged in, allow them to proceed
		if (!$my->guest) {
			return true;
		}

		// Default redirection
		$redirect = $redirect ? $redirect : JRoute::_('index.php?option=com_users&view=login', false);

		$app = JFactory::getApplication();
		return $app->redirect($redirect);
	}

	/**
	 * Retrieves the current version of Payplans installed.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getLocalVersion()
	{
		static $version = false;

		if ($version === false) {
			$file = PP_ADMIN . '/payplans.xml';

			$contents = JFile::read($file);
			$parser = simplexml_load_string($contents);

			$version = $parser->xpath('version');
			$version = (string) $version[0];
		}

		return $version;
	}

	/**
	 * Retrieves the current installed Joomla version
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getJoomlaVersion($long = false)
	{
		if ($long) {
			return JVERSION;
		}

		$version = explode('.' , JVERSION);
		return $version[0] . '.' . $version[1];
	}

	/**
	 * Retrieves the current installed Joomla version
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getJoomlaCodename()
	{
		$versionName = 'joomla15';
		$version = self::getJoomlaVersion();

		if ($version >= '1.6') {
			$versionName = 'joomla30';
			return $versionName;
		}

		return $versionName;
	}

	/**
	 * Retrieves date library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function date($date = 'now', $offset = null)
	{
		if (is_object($date) && get_class($date) == 'PPDate') {
			return $date;
		}

		PP::load('Date');

		$date = new PPDate($date, $offset);

		return $date;
	}

	/**
	 * Retrieves the base URL of the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getBaseUrl()
	{
		$baseUrl = rtrim( JURI::root() , '/' ) . '/index.php?option=com_payplans';


		$app = JFactory::getApplication();
		$config = PP::config();
		$jConfig = PP::jconfig();
		$uri = JFactory::getURI();
		$language = $uri->getVar( 'lang' , 'none' );
		$router = $app->getRouter();
		$baseUrl = rtrim( JURI::base() , '/' ) . '/index.php?option=com_payplans&lang=' . $language;

		$itemId = JRequest::getVar( 'Itemid' ) ? '&Itemid=' . JRequest::getVar( 'Itemid' ) : '';

		if ($router->getMode() == JROUTER_MODE_SEF && JPluginHelper::isEnabled("system" , "languagefilter")) {

			$sefs = JLanguageHelper::getLanguages('sef');
			$lang_codes   = JLanguageHelper::getLanguages('lang_code');

			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			$params = new JRegistry();
			$params->loadString(empty($plugin) ? '' : $plugin->params);
			$removeLangCode = is_null($params) ? 'null' : $params->get('remove_default_prefix', 'null');

			$rewrite = $jConfig->get('sef_rewrite');

			$path = $uri->getPath();
			$parts = explode('/', $path);


			if ($removeLangCode) {

				$defaultLang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				$currentLang = $app->input->cookie->getString(JApplicationHelper::getHash('language'), $defaultLang);

				$defaultSefLang = $lang_codes[$defaultLang]->sef;
				$currentSefLang = $lang_codes[$currentLang]->sef;

				if ($defaultSefLang == $currentSefLang) {
					$language = '';
				} else {
					$language = $currentSefLang;
				}

			} else {

				$base = str_ireplace(JURI::root(true), '', $uri->getPath());
				$path = $rewrite ? $base : JString::substr($base , 10);
				$path = trim( $path , '/' );
				$parts = explode( '/' , $path );

				if ($parts) {
					// First segment will always be the language filter.
					$language = reset( $parts );
				} else {
					$language = '';
				}

			}

			if ($language) {
				$language .= '/';
			}

			if ($rewrite) {
				$baseUrl = rtrim(JURI::base(), '/') . '/' . $language . '?option=com_payplans';
			} else {
				$baseUrl = rtrim(JURI::base(), '/') . '/index.php/' . $language . '?option=com_payplans';
			}
		}

		return $baseUrl . $itemId;
	}

	/**
	 * Converts an argument into an object
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function makeObject($item, $debug = false)
	{
		// If this is already an object, skip this
		if (is_object($item)) {
			return $item;
		}

		if (is_array($item)) {
			return (object) $item;
		}

		if (strlen($item) < 1024 && is_file($item)) {
			jimport('joomla.filesystem.file');
			$item = JFile::read($item);
		}

		$json = PP::json();

		// Test if source is a string.
		if ($json->isJsonString($item)) {

			if ($debug) {
				$obj = $json->decode($item);
				var_dump($item, $obj);
				exit;
			}

			// Trim the string first
			$item = trim($item);

			$obj = $json->decode($item);

			if (!is_null($obj)) {
				return $obj;
			}

			$obj = new stdClass();
			return $obj;
		}

		return false;
	}

	/**
	 * Converts an array to string
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function makeString($val, $join = '')
	{
		if (is_string($val)) {
			return $val;
		}

		return implode($join, $val);
	}

	/**
	 * Converts an argument into a json string. If argument is a string, it wouldn't be processed.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function makeJSON($item)
	{
		if (is_string($item)) {
			return $item;
		}

		return json_encode($item);
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function normalize($data, $key, $default = null)
	{
		if (!$data) {
			return $default;
		}

		// $key cannot be an array
		if (is_array($key)) {
			$key = $key[0];
		}

		// Object datatype
		if (is_object($data) && isset($data->$key)) {
			return $data->$key;
		}

		// Array datatype
		if (is_array($data) && isset($data[$key])) {
			return $data[$key];
		}

		return $default;
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function normalizeCardNumber($cardNumber)
	{
		$number = trim(str_ireplace(' ', '', $cardNumber));

		return $number;
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function normalizeCardExpiry($month, $year)
	{
		$month = substr($month, 0, 2);
		$month = str_pad($month, 2, '0', STR_PAD_LEFT);
		
		$year = substr($year, -2);

		$expiryDate = $month . $year;

		return $expiryDate;
	}

	/**
	 * Retrieves the current currency used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getCurrency($isocode = null)
	{
		static $currencies = null;

		if (is_null($currencies)) {
			$model = PP::model('Currency');
			$currencies = $model->getAllCurrency();

		}

		if (is_null($isocode)) {
			return $currencies;
		}

		return $currencies[$isocode];
	}

	/**
	 * Retrieve company logo
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getCompanyLogo()
	{
		static $logo = null;

		if (is_null($logo)) {
			$config = PP::config();
			$logoPath = $config->get('companyLogo', '');

			if (!$logoPath) {
				$logoPath = '/media/com_payplans/images/logo-payplans.png';
			}

			$logo = rtrim(JURI::root(), '/') . $logoPath;
		}

		return $logo;
	}

	/**
	 * Given a list of objects, get the id of the objects. Must be PPAbstract object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getIds($objects)
	{
		if (!$objects) {
			return false;
		}

		$ids = array();
		
		foreach ($objects as $object) {
			if (is_object($object) && method_exists($object, 'getId')) {
				$ids[] = (int) $object->getId();
			}
		}

		return $ids;
	}

	/**
	 * Render editor
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getEditor($type = 'tinymce')
	{
		// Fall back to 'none' editor if the specified plugin is not enabled
		jimport('joomla.plugin.helper');
		$editorType = JPluginHelper::isEnabled('editors', $type) ? $type : 'none';

		JHtml::_('behavior.core');

		$editor = JFactory::getEditor($type);

		return $editor;
	}

	/**
	 * Given the key for the request string, convert the key into an id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getIdFromInput($key)
	{
		$input = JFactory::getApplication()->input;
		$key = $input->get($key, '', 'default');

		if (!$key) {
			return $key;
		}

		$id = (int) PP::encryptor()->decrypt($key);

		return $id;
	}

	/**
	 * Return apps belong to specify type. E.g. Payment apps.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getApps($type)
	{
		static $_apps = array();

		if (! isset($_apps[$type])) {

			// TODO: retrieve apps based on the type.
			$_apps[$type] = array();
		}

		return $_apps[$type];
	}

	/**
	 * Given the id, return the encrypted key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getKeyFromId($id)
	{
		$encryptor = PP::encryptor();
		return $encryptor->encrypt($id);
	}

	/**
	 * Given the encrypted key, return the id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getIdFromKey($key)
	{
		$encryptor = PP::encryptor();
		$id = (int) $encryptor->decrypt($key);

		return $id;
	}

	/**
	 * Reads a XML file.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public static function getXml($data, $isFile = true)
	{
		$class = 'SimpleXMLElement';

		if (class_exists('JXMLElement')) {
			$class = 'JXMLElement';
		}

		if ($isFile) {
			// Try to load the XML file
			$xml = simplexml_load_file($data, $class);

		} else {
			// Try to load the XML string
			$xml = simplexml_load_string($data, $class);
		}

		if ($xml === false) {
			foreach (libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
		}

		return $xml;
	}

	public function setMessaage($message, $type = PP_MSG_INFO)
	{
		PP::view()->setMessage($message, $type);
	}
}
