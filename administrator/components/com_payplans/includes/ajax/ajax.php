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

class PPAjax extends PayPlans
{
	private $commands = array();
	static $instance = null;

	public function addCommand($type, &$data)
	{
		$this->commands[] = array(
			'type' => $type,
			'data' =>& $data
		);

		return $this;
	}

	/**
	 * Format our own ajax url
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function getUrl()
	{
		static $url;

		if (isset($url)) {
			return $url;
		}

		$uri = JFactory::getURI();
		$language = $uri->getVar('lang', 'none');

		// Remove any ' or " from the language because language should only have -
		$app = JFactory::getApplication();
		$input = $app->input;

		$language = $input->get('lang', '', 'cmd');

		$jConfig = PP::jconfig();

		// Get the router
		$router = $app->getRouter();

		// It could be admin url or front end url
		$url = rtrim(JURI::base(), '/') . '/';

		// Determines if we should use index.php for the url
		$config = PP::config();

		if ($config->get('general.ajaxindex')) {
			$url .= 'index.php';
		}

		// Append the url with the extension
		$url = $url . '?option=com_payplans&lang=' . $language;

		// During SEF mode, we need to ensure that the URL is correct.
		$languageFilterEnabled = JPluginHelper::isEnabled("system","languagefilter");

		if ($app->isSite() && $router->getMode() == JROUTER_MODE_SEF && $languageFilterEnabled) {

			$sefs = JLanguageHelper::getLanguages('sef');
			$lang_codes   = JLanguageHelper::getLanguages('lang_code');

			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			$params = new JRegistry();
			$params->loadString(empty($plugin) ? '' : $plugin->params);
			$removeLangCode = is_null($params) ? 'null' : $params->get('remove_default_prefix', 'null');


			// Determines if the mod_rewrite is enabled on Joomla
			$rewrite = $jConfig->get('sef_rewrite');

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
				// Replace the path if it's on subfolders
				$base = str_ireplace(JURI::root(true), '', $uri->getPath());

				if ($rewrite) {
					$path = $base;
				} else {
					$path = JString::substr($base, 10);
				}

				// Remove trailing / from the url
				$path = JString::trim($path, '/');
				$parts = explode('/', $path);

				if ($parts) {
					// First segment will always be the language filter.
					$language = reset($parts);
				} else {
					$language = 'none';
				}
			}

			if ($language) {
				$language .= '/';
			}

			if ($rewrite) {
				$url = rtrim(JURI::base(), '/') . '/' . $language . '?option=com_payplans';
			} else {
				$url = rtrim(JURI::base(), '/') . '/index.php/' . $language . '?option=com_payplans';
			}
		}

		$menu = JFactory::getApplication()->getmenu();

		if (!empty($menu)) {
			$item = $menu->getActive();

			if (isset($item->id)) {
				$url .= '&Itemid=' . $item->id;
			}
		}

		// Some SEF components tries to do a 301 redirect from non-www prefix to www prefix. Need to sort them out here.
		$currentURL = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

		if (!empty($currentURL)) {

			// When the url contains www and the current accessed url does not contain www, fix it.
			if (stristr($currentURL, 'www') === false && stristr($url, 'www') !== false) {
				$url = str_ireplace('www.', '', $url);
			}

			// When the url does not contain www and the current accessed url contains www.
			if (stristr($currentURL, 'www') !== false && stristr($url, 'www') === false) {
				$url = str_ireplace('://', '://www.', $url);
			}
		}

		return $url;
	}

	/**
	 * Resolve a given POSIX path.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function resolveNamespace($namespace)
	{
		// Get the request
		$input = JFactory::getApplication()->input;

		$parts = explode('/', $namespace);

		// Determine the location of the namespace
		$location = $parts[0];

		// Remove the location from parts.
		array_shift($parts);

		// Remove the method from the namespace
		$method = array_pop($parts);

		// Get the absolute path of the initial location
		$path = $location == 'admin' ? PP_ADMIN : PP_SITE;

		// Determine if this is a view or controller.
		if ($location == 'site' || $location == 'admin') {

			$glued = implode('/', $parts);

			if ($parts[0] == 'controllers') {
				$path = $path . '/' . $glued . '.php';
			} else {
				$path = $path . '/' . $glued . '/view.ajax.php';

				$place = $location == 'site' ? 'site' : 'admin';

				// Import the base view
				PP::import($place . ':/views/views');
			}
		}

		// Get the arguments from the query string if there is any.
		$args = $input->get('args', '', 'default');

		// Check that the file exists.
		jimport('joomla.filesystem.file');

		$ajax = PP::ajax();

		if (!JFile::exists($path)) {
			$ajax->reject(JText::sprintf('The file %1s does not exist.', $namespace));
			return $ajax->send();
		}

		// Include the path.
		include_once($path);

		// Get the adapter to process.
		$adapter = self::getAdapter($location);
		$adapter->execute($namespace, $parts, $args, $method);

		// Terminate the output.
		$ajax->send();

		return $path;
	}

	/**
	 * Retrieves an ajax adapter so that it knows how to resolve the calls
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getAdapter($location)
	{
		$file = __DIR__ . '/adapters/' . strtolower($location) . '.php';

		require_once($file);

		$className = 'PPAjaxAdapter' . ucfirst($location);
		$adapter = new $className();

		return $adapter;
	}

	/**
	 * Magic method to process dynamic function calls
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$this->addCommand($method, $args);

		return $this;
	}

	/**
	 * Terminates the output
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function send()
	{
		header('Content-type: application/json; UTF-8');

		$callback = $this->input->get('callback', '', 'default');

		// Isolate PHP errors and send it as a notify command.
		$error_reporting = ob_get_contents();
		if (strlen(trim($error_reporting))) {
			$this->notify($error_reporting, 'debug');
		}

		ob_clean();

		// Process jsonp requests if necessary.
		if ($callback) {
			header('Content-type: application/javascript; UTF-8');
			echo $callback . '(' . json_encode($this->commands) . ');';
			exit;
		}

		$transport = $this->input->get('transport', '', 'default');

		if ($transport == "iframe") {
			header('Content-type: text/html; UTF-8');
			echo '<textarea data-type="application/json" data-status="200" data-statusText="OK">' . json_encode($this->commands) . '</textarea>';
			exit;
		}

		echo json_encode($this->commands);
		exit;
	}

	/**
	 * Processes an ajax call that is passed to the server. It is smart enough to decide which
	 * file would be responsible to keep these codes.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function listen()
	{
		// Do not proceed if the request is not in ajax format.
		if ($this->doc->getType() != 'ajax') {
			return;
		}

		// Namespace format should be POSIX format.
		$namespace = $this->input->get('namespace', '', 'default');

		// Split the namespace
		$parts = explode(':/', $namespace);

		// Detect if the user passed in a protocol.
		$hasProtocol = count($parts) > 1;

		if (!$hasProtocol) {
			$namespace = 'ajax:/' . $namespace;
		}

		// Remove the method from the namespace
		$parts = explode('/', $namespace);
		$location = $parts[1];
		$method = array_pop($parts);

		$namespace = implode('/', $parts);

		$resolver = PP::resolver();
		$path = $resolver->resolve($namespace);

		// Get the arguments from the query string if there is any.
		$args = $this->input->get('args', '', 'default');

		// Check that the file exists.
		jimport('joomla.filesystem.file');

		$ajax = PP::ajax();

		if (!JFile::exists($path)) {
			throw new Exception(JText::sprintf('The namespace %1$s does not exist.', $namespace));
		}

		// Include the path.
		include_once($path);

		// Get the adapter to process.
		$adapter = self::getAdapter($location);
		$adapter->execute($namespace, $parts, $args, $method);

		// Terminate the output.
		return $ajax->send();
	}
}