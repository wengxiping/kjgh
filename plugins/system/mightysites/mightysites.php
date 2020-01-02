<?php
/**
 * @package        Mightysites
 * @copyright      Copyright (C) 2009-2013 AlterBrains.com. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * @since          1.0
 */

use Joomla\CMS\Access\Access;

defined('_JEXEC') or die('Restricted access');

// Disable plugins, and people who use protected vars as well.
if ($mighty_hideplugins = JFactory::getConfig()->get('mighty_hideplugins'))
{
	// First remove from loaded plugins list to preserve further import.
	$JPluginHelper_load = new ReflectionMethod('JPluginHelper', 'load');
	$JPluginHelper_load->setAccessible(true);
	$JPluginHelper_load->invoke(null);

	$JPluginHelper_plugins = new ReflectionProperty('JPluginHelper', 'plugins');
	$JPluginHelper_plugins->setAccessible(true);
	$plugins = $JPluginHelper_plugins->getValue();

	// foreach() breaks code for some reason: plgSystemMightysites are not called later. 
	/** @noinspection ForeachInvariantsInspection */
	for ($i = 0, $count = count($plugins); $i < $count; $i++)
	{
		if (in_array($plugins[$i]->type . ':' . $plugins[$i]->name, $mighty_hideplugins, true))
		{
			// Remember class for dispatcher-related removal.
			$mighty_hideplugins['plg' . $plugins[$i]->type . $plugins[$i]->name] = true;

			// Disable it.
			$plugins[$i]->type = '';
		}
	}

	$JPluginHelper_plugins->setValue($plugins);

	// Next remove from dispatcher.
	$dispatcher = JEventDispatcher::getInstance();

	$JEventDispatcher_observers = new ReflectionProperty(get_class($dispatcher), '_observers');
	$JEventDispatcher_observers->setAccessible(true);
	$_observers = $JEventDispatcher_observers->getValue($dispatcher);

	foreach ($_observers as $key => $_observer)
	{
		if ((is_object($_observer) && isset($mighty_hideplugins[strtolower(get_class($_observer))])) || (is_array($_observer) && isset($_observer['handler']) && is_array($_observer['handler']) && isset($_observer['handler'][0]) && isset($mighty_hideplugins[strtolower(get_class($_observer['handler'][0]))])))
		{
			unset($_observers[$key]);
		}
	}

	$JEventDispatcher_observers->setValue($dispatcher, array_values($_observers));
}

// Base class
class plgSystemMightysitesBase extends JPlugin
{
	protected function bDecode($string)
	{
		$func = 'base' . (100 - 36) . '_' . 'decode';

		return $func($string);
	}

	protected function bEncode($string)
	{
		$func = 'base' . (100 - 36) . '_' . 'encode';

		return $func($string);
	}
}

$app    = JFactory::getApplication();
$config = JFactory::getConfig();

// Frontend options
if ($app->isSite())
{
	class plgSystemMightysites extends plgSystemMightysitesBase
	{
		public function onAfterRoute()
		{
			$app    = JFactory::getApplication();
			$config = JFactory::getConfig();

			// Check Itemid of disabled menu item, Falang overrides menu :(
			if (class_exists('plgSystemFalangdriver'))
			{
				// Remove items now.
				static::removeMenuItems();
			}

			/** @noinspection NullPointerExceptionInspection */
			$active = $app->getMenu()->getActive();

			// Implicitely setup homepage template style.
			if ($config->get('mighty_home_template') && $active && $active->home)
			{
				$app->input->set('templateStyle', $mighty_template = $config->get('mighty_home_template'));
			}

			// Implicitely setup template style, note that if current menu item has own assigned style - it's kept, but not if 'Force Default Template Style' is enabled.
			elseif ($config->get('mighty_template') || $config->get('mighty_yootheme_style'))
			{
				// Strange but effective and fast.
				if (!$active || !$active->template_style_id || $config->get('mighty_force_template'))
				{
					if ($mighty_template = $config->get('mighty_template'))
					{
						$app->input->set('templateStyle', $mighty_template);
					}

					if ($mighty_yootheme_style = $config->get('mighty_yootheme_style'))
					{
						list($yootheme_state_var, $yootheme_template, $yootheme_style) = explode('.', $mighty_yootheme_style);

						$app->input->set('template', $yootheme_template);

						$app->setUserState($yootheme_state_var, $yootheme_style);
					}
				}
			}

			// Reset template initialized before our code.
			if (!empty($mighty_template) || !empty($mighty_yootheme_style))
			{
				$templateStyleProperty = new ReflectionProperty($app, 'template');
				$templateStyleProperty->setAccessible(true);
				$templateStyle = $templateStyleProperty->getValue($app);

				if (is_object($templateStyle) && ((isset($mighty_template) && $templateStyle->id != $config->get('mighty_template')) || (isset($yootheme_template) && $templateStyle->template != $yootheme_template)))
				{
					$templateStyleProperty->setValue($app, null);
				}
			}

			// Guard content categories.
			$hidecontentcats = $config->get('mighty_hidecontentcats');
			$onlycontentcats = $config->get('mighty_onlycontentcats');

			if (!empty($hidecontentcats) || !empty($onlycontentcats))
			{
				if ($app->input->get('option') === 'com_content')
				{
					switch ($app->input->get('view'))
					{
						case 'categories':
						case 'category':
							if ($catid = $app->input->getInt('id'))
							{
								if (($hidecontentcats && in_array($catid, $hidecontentcats)) || ($onlycontentcats && !in_array($catid, $onlycontentcats)))
								{
									throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
								}
							}
							break;

						case 'article':
							$app->registerEvent('onContentPrepare', function ($context, $item) use ($hidecontentcats, $onlycontentcats) {
								if ($context === 'com_content.article' && !empty($item->catid))
								{
									if (($hidecontentcats && in_array($item->catid, $hidecontentcats)) || ($onlycontentcats && !in_array($item->catid, $onlycontentcats)))
									{
										throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
									}
								}
							});
							break;
					}
				}

				// Limit Joomla content search plugin
				if (isset($_REQUEST['searchword']) && $app->input->get('option') === 'com_search')
				{
					JPluginHelper::importPlugin('search');

					if (class_exists('PlgSearchContent'))
					{
						$own_plugin = JFactory::getConfig()->get('cache_path', JPATH_CACHE) . '/mightysites_plg_search_content.' . JVERSION . '.php';

						// Create own plugin
						if (!file_exists($own_plugin) && is_writable(dirname($own_plugin)))
						{
							$where = '->where(\'1=1\' . 
								(($mighty_hidecontentcats = JFactory::getConfig()->get(\'mighty_hidecontentcats\')) ? \' AND c.id NOT IN(\'.implode(\',\', $mighty_hidecontentcats).\')\' : \'\')
								.
								(($mighty_onlycontentcats = JFactory::getConfig()->get(\'mighty_onlycontentcats\')) ? \' AND c.id IN(\'.implode(\',\', $mighty_onlycontentcats).\')\' : \'\')
							)';

							file_put_contents($own_plugin, strtr(file_get_contents(JPATH_SITE . '/plugins/search/content/content.php'), [
								'class PlgSearchContent extends JPlugin' => 'class PlgSearchMightysitesContent extends PlgSearchContent',
								'->from(\'#__content '                   => $where . "\n\t\t\t\t" . '->from(\'#__content ',
							]));
						}

						// Next replace in Dispatcher.
						if (file_exists($own_plugin))
						{
							/** @noinspection PhpIncludeInspection */
							require_once $own_plugin;

							$dispatcher = JEventDispatcher::getInstance();

							$JEventDispatcher_observers = new ReflectionProperty(get_class($dispatcher), '_observers');
							$JEventDispatcher_observers->setAccessible(true);
							$_observers = $JEventDispatcher_observers->getValue($dispatcher);

							foreach ($_observers as $key => $_observer)
							{
								if (is_object($_observer) && strtolower(get_class($_observer)) === 'plgsearchcontent')
								{
									//$content_params = $_observers[$key]->params;

									/** @noinspection PhpUndefinedClassInspection */
									$_observers[$key] = new PlgSearchMightysitesContent($dispatcher, [
										'type'   => 'search',
										'name'   => 'content',
										'params' => $_observers[$key]->params,
										'id'     => 'mightysites_plg_search_content',
									]);
								}
							}

							$JEventDispatcher_observers->setValue($dispatcher, array_values($_observers));
						}
					}
				}
			}

			// Guard K2 categories.
			if ($app->input->get('option') === 'com_k2')
			{
				$hidek2cats = $config->get('mighty_hidek2cats');
				$onlyk2cats = $config->get('mighty_onlyk2cats');

				if (!empty($hidek2cats) || !empty($onlyk2cats))
				{
					switch ($app->input->get('view'))
					{
						case 'itemlist':
							if ($catid = $app->input->getInt('id'))
							{
								if (($hidek2cats && in_array($catid, $hidek2cats)) || ($onlyk2cats && !in_array($catid, $onlyk2cats)))
								{
									throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
								}
							}
							break;

						case 'item':
							$app->registerEvent('onContentPrepare', function ($context, $item) use ($hidek2cats, $onlyk2cats) {
								if ($context === 'com_k2.item' && !empty($item->catid))
								{
									if (($hidek2cats && in_array($item->catid, $hidek2cats)) || ($onlyk2cats && !in_array($item->catid, $onlyk2cats)))
									{
										throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
									}
								}
							});
							break;
					}
				}
			}
		}

		// Start single login?
		public function onUserLogin($user, $options = [])
		{
			$app = JFactory::getApplication();

			// Not used if we login via our SSI plugin!
			if ($app->getCfg('mighty_slogin') && $app->getCfg('mighty_sdomains') && !isset($_REQUEST['mighty_login']))
			{
				$_SESSION['mightylogin'] = [
					'username' => $user['username'],
					'password' => $user['password'],
					'remember' => isset($options['remember']) ? $options['remember'] : 0,
				];
			}
		}

		// Start single logout?
		public function onUserLogout($user, $options = [])
		{
			$app = JFactory::getApplication();

			// Not used if we logout via our SSI plugin!
			if ($app->getCfg('mighty_slogout') && $app->getCfg('mighty_sdomains') && !isset($_REQUEST['mighty_logout']))
			{
				setcookie('mightylogout', 1, time() + 3600, '/');

				// No page cache! At all, othwerwise logout redirect will load cached page.
				$this->disableCache(false);
			}
		}

		// Start single login/logout to other sites!
		public function onBeforeCompileHead()
		{
			$config = JFactory::getConfig();

			/** @var JDocumentHtml $document */
			$document = JFactory::getDocument();

			if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && $document->getType() === 'html')
			{
				// Login there
				if (isset($_SESSION['mightylogin']))
				{
					$user = $_SESSION['mightylogin'];
					unset($_SESSION['mightylogin']);

					jimport('joomla.utilities.simplecrypt');
					jimport('joomla.utilities.utility');

					$domains = $config->get('mighty_sdomains');

					foreach ($domains as $domain => $secret)
					{
						$key   = md5($secret . @$_SERVER['HTTP_USER_AGENT']);
						$crypt = new JSimpleCrypt($key);
						$hash  = $this->bEncode($crypt->encrypt(serialize($user)));
						// possible 'suhosin.get.max_value_length' issue, let's use array
						$hash = implode('&mighty_login[]=', str_split($hash, 250));

						if (strpos($domain, 'http://') !== 0 && strpos($domain, 'https://') !== 0)
						{
							$domain = 'http://' . $domain;
						}

						// Special case if are on HTTPS and login to usual HTTP domain.
						if (JUri::getInstance()->getScheme() === 'https' && mb_strpos($domain, 'https://') !== 0)
						{
							$document->addCustomTag('<img src="' . $domain . '/index.php?' . md5($_SERVER['REQUEST_TIME']) . '=1&mighty_login[]=' . $hash . '" style="height:0px;width:0px;position:absolute;top:-1000px;"/>');
						}
						else
						{
							$document->addScript($domain . '/index.php?' . md5($_SERVER['REQUEST_TIME']) . '=1&amp;mighty_login[]=' . $hash, 'text/javascript', true);
						}
					}

					// No page cache!
					$this->disableCache();
				}

				// Logout there
				if (isset($_COOKIE['mightylogout']))
				{
					/** @noinspection SummerTimeUnsafeTimeManipulationInspection */
					setcookie('mightylogout', '', time() - 86400, '/');

					$domains = $config->get('mighty_sdomains');

					foreach ($domains as $domain => $secret)
					{
						if (strpos($domain, 'http://') !== 0 && strpos($domain, 'https://') !== 0)
						{
							$domain = 'http://' . $domain;
						}

						// Special case if are on HTTPS and logout to usual HTTP domain.
						if (JUri::getInstance()->getScheme() === 'https' && mb_strpos($domain, 'https://') !== 0)
						{
							$document->addCustomTag('<img src="' . $domain . '/index.php?' . md5($_SERVER['REQUEST_TIME']) . '=1&mighty_logout[]=1" style="height:0px;width:0px;position:absolute;top:-1000px;"/>');
						}
						else
						{
							/** @noinspection PhpDeprecationInspection */
							$document->addScript($domain . '/index.php?' . md5($_SERVER['REQUEST_TIME']) . '=1&amp;mighty_logout=1', 'text/javascript', true);
						}
					}

					// No page cache! But enable it if previously disabled
					$this->disableCache(true);
				}

				// Custom CSS files
				if ($config->get('mighty_css'))
				{
					foreach ((array) $config->get('mighty_css') as $css_file)
					{
						if ($css_file)
						{
							// Replace ?{filemtime} placeholder for local files.
							if (strpos($css_file, '//') === false && strpos($css_file, '?{filemtime}') !== false)
							{
								$filepath = JPath::clean(JPATH_ROOT . '/' . JUri::getInstance($css_file)->toString(['path']));

								if (is_file($filepath))
								{
									$css_file = str_replace('{filemtime}', filemtime($filepath), $css_file);
								}
							}

							/** @noinspection PhpDeprecationInspection */
							$document->addStylesheet($css_file);
						}
					}
				}

				// Custom JavaScript
				if ($config->get('mighty_js'))
				{
					$document->addScriptDeclaration($config->get('mighty_js'));
				}

				// Custom Favicon
				if ($config->get('mighty_favicon'))
				{
					foreach ($document->_links as $i => $link)
					{
						if ($link['relation'] === 'shortcut icon')
						{
							unset($document->_links[$i]);
						}
					}
					$document->addFavicon($config->get('mighty_favicon'));
				}
			}
		}

		public function onAfterDispatch()
		{
			// Lost database handler?
			/** @noinspection InstanceofCanBeUsedInspection */
			if (JFactory::getConfig()->get('mighty_enable') && get_class(JFactory::$database) !== 'JDatabaseMightysites')
			{
				JDatabaseMightysites::changeHandler();
			}
		}

		// Since Joomla 3.4.1
		public function onAfterCleanModuleList(&$modules)
		{
			$config = JFactory::getConfig();

			// Hide modules.
			$mighty_hidemodules = (array) $config->get('mighty_hidemodules');

			// Only modules.
			$mighty_onlymodules = (array) $config->get('mighty_onlymodules');

			if (!empty($mighty_hidemodules) || !empty($mighty_onlymodules))
			{
				foreach ($modules as $key => $module)
				{
					if (in_array($module->id, $mighty_hidemodules) || ($mighty_onlymodules && !in_array($module->id, $mighty_onlymodules)))
					{
						unset($modules[$key]);
					}
				}

				// Normalize keys,
				$modules = array_values($modules);
			}
		}

		protected function hideModules()
		{
			$config   = JFactory::getConfig();
			$document = JFactory::getDocument();

			if ($document->getType() !== 'html')
			{
				return;
			}

			// Remove modules
			$mighty_modules = (array) $config->get('mighty_hidemodules');

			if ($mighty_modules && $mighty_modules !== [''])
			{
				$mighty_modules = array_flip($mighty_modules);

				$rMethod = new ReflectionMethod('JModuleHelper', 'load');

				// We can get 'Method JModuleHelper::load() does not exist' for some reason on PHP 5.5, so sometimes only deprecated _load helps
				//$rMethod = new ReflectionMethod('JModuleHelper', '_load');

				$rMethod->setAccessible(true);
				$items = $rMethod->invoke(null);

				foreach ($items as $item)
				{
					if (isset($mighty_modules[$item->id]))
					{
						$mighty_modules[$item->id] = true;
						$item->module              = 'unknown'; // ;)
						$item->position            = 'unknown'; // ;)
					}
				}
			}

			// Only modules
			/** @noinspection SuspiciousAssignmentsInspection */
			$mighty_modules = (array) $config->get('mighty_onlymodules');

			if ($mighty_modules && $mighty_modules !== [''])
			{
				$mighty_modules = array_flip($mighty_modules);

				$rMethod = new ReflectionMethod('JModuleHelper', 'load');

				// We can get 'Method JModuleHelper::load() does not exist' for some reason on PHP 5.5, so sometimes onlt deprecated _load helps
				//$rMethod = new ReflectionMethod('JModuleHelper', '_load');

				$rMethod->setAccessible(true);
				$items = $rMethod->invoke(null);

				foreach ($items as $item)
				{
					if (!isset($mighty_modules[$item->id]))
					{
						$mighty_modules[$item->id] = true;
						$item->module              = 'unknown'; // ;)
						$item->position            = 'unknown'; // ;)
					}
				}
			}
		}

		protected function disableCache($plugin_status = null)
		{
			// Change plugin status
			if (isset($plugin_status))
			{
				$db = JFactory::getDbo();

				// Disable only if it's currently enabled
				if ($plugin_status === false && class_exists('PlgSystemCache'))
				{
					$db->setQuery('UPDATE #__extensions SET `enabled`=0 WHERE `type`="plugin" AND `element`="cache" AND `folder`="system"');
					$db->execute();
					setcookie('mightylogout_cache', 1, time() + 3600, '/');
				}

				// Enable only if it was previously disabled
				if ($plugin_status === true && isset($_COOKIE['mightylogout_cache']))
				{
					$db->setQuery('UPDATE #__extensions SET `enabled`=1 WHERE `type`="plugin" AND `element`="cache" AND `folder`="system"');
					$db->execute();

					/** @noinspection SummerTimeUnsafeTimeManipulationInspection */
					setcookie('mightylogout_cache', '', time() - 86400, '/');
				}

				// Clear system cache
				if (JFactory::getConfig()->get('caching') > 0)
				{
					$cache = JFactory::getCache();
					$cache->clean('com_plugins');
				}
			}

			// Just disable caching of current page.
			if (class_exists('PlgSystemCache'))
			{
				$dispatcher = JDispatcher::getInstance();
				$_methods   = $dispatcher->get('_methods');

				foreach ($dispatcher->get('_observers') as $oId => $observer)
				{
					if (is_object($observer) && strtolower(get_class($observer)) === 'plgsystemcache')
					{
						foreach ($_methods['onafterrender'] as $key => $value)
						{
							if ($value == $oId)
							{
								unset($_methods['onafterrender'][$key]);
								$dispatcher->set('_methods', $_methods);
								break;
							}
						}
					}
				}
			}
		}

		public static function removeMenuItems()
		{
			$config = JFactory::getConfig();

			$hidemenus = $config->get('mighty_hidemenus');
			$onlymenus = $config->get('mighty_onlymenus');

			$hidemenuitems = $config->get('mighty_hidemenuitems');
			$onlymenuitems = $config->get('mighty_onlymenuitems');

			if (empty($hidemenus) && empty($onlymenus) && empty($hidemenuitems) && empty($onlymenuitems))
			{
				return;
			}

			// Legacy before 3.3.1, todo - remove
			// Names were changed: old $hidemenus is actually new $hidemenuitems etc.s
			if (!$config->exists('mighty_hidemenuitems'))
			{
				$hidemenuitems = $hidemenus;
				$onlymenuitems = $onlymenus;

				$hidemenus = [];
				$onlymenus = [];
			}

			$menu = JFactory::getApplication()->getMenu();

			/** @noinspection NullPointerExceptionInspection */
			$active = $menu->getActive();

			// I hate Joomla sometimes... smbd is crazy on privates
			$rProperty = new ReflectionProperty($menu, '_items');
			$rProperty->setAccessible(true);
			$items = $rProperty->getValue($menu);

			// Hide menu items
			if (!empty($hidemenuitems))
			{
				foreach ($hidemenuitems as $hidemenuitem)
				{
					unset($items[$hidemenuitem]);
				}
			}

			$hidemenus     = $hidemenus ? array_flip($hidemenus) : [];
			$onlymenus     = $onlymenus ? array_flip($onlymenus) : [];
			$onlymenuitems = $onlymenuitems ? array_flip($onlymenuitems) : [];

			foreach ($items as $key => $item)
			{
				// Hide menus
				if ($hidemenus && isset($hidemenus[$item->menutype]))
				{
					unset($items[$key]);
				}

				// Only menus
				if ($onlymenus && !isset($onlymenus[$item->menutype]))
				{
					unset($items[$key]);
				}

				// Only menu items. Preserve homes!
				if ($onlymenuitems && !isset($onlymenuitems[$item->id]) && !$item->home)
				{
					unset($items[$key]);
				}

				// Hide menu items without parents (also possibly removed via $hidemenuitems)
				if ($item->parent_id != 1 && !isset($items[$item->parent_id]))
				{
					unset($items[$key]);
				}
			}

			$rProperty->setValue($menu, $items);

			// Check default items.
			$rProperty = new ReflectionProperty($menu, '_default');
			$rProperty->setAccessible(true);
			$_default = $rProperty->getValue($menu);

			foreach ($_default as $lang_code => $item_id)
			{
				if (!isset($items[$item_id]))
				{
					unset($_default[$lang_code]);
				}
			}

			$rProperty->setValue($menu, $_default);

			// Raise error on known removed menu item.
			if ($active)
			{
				if ((!empty($hidemenuitems) && in_array($active->id, $hidemenuitems)) || (!empty($hidemenus) && isset($hidemenus[$active->menutype])))
				{
					// Reset active item, setActive() won't work since item is removed from _items.
					$rProperty = new ReflectionProperty($menu, '_active');
					$rProperty->setAccessible(true);
					$rProperty->setValue($menu, 0);

					throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
				}
			}
		}
	}

	// Implicitly setup language, getLanguage() only next!
	// Not if we already have language cookie, once first time the user goes here. And not if lang is forced via request.
	// "System - Language Filter" plugin should be orderd before "System - MightySites"
	if ($lang_code = $config->get('mighty_language'))
	{
		// Override default lang
		if (class_exists('PlgSystemLanguageFilter'))
		{
			$lparams = JComponentHelper::getParams('com_languages');

			if ($lparams->get('site') != $lang_code)
			{
				$lparams->set('site', $lang_code);

				foreach (JEventDispatcher::getInstance()->get('_observers') as $observer)
				{
					if (is_object($observer) && strtolower(get_class($observer)) === 'plgsystemlanguagefilter')
					{
						$rProperty = new ReflectionProperty($observer, 'default_lang');
						$rProperty->setAccessible(true);
						$rProperty->setValue($observer, $lang_code);
						break;
					}
				}
			}

			// Set cookie.

			// Always use this one.
			/** @noinspection SummerTimeUnsafeTimeManipulationInspection */
			$cookie_expire = time() + 365 * 86400;

			// Create a cookie.
			$cookie_domain = $app->get('cookie_domain');
			$cookie_path   = $app->get('cookie_path', '/');
			$cookie_secure = $app->isSSLConnection();
			$app->input->cookie->set(JApplicationHelper::getHash('language'), $lang_code, $cookie_expire, $cookie_path, $cookie_domain, $cookie_secure);
		}

		// No PlgSystemLanguageFilter plugin - no cookies!
		if (!class_exists('PlgSystemLanguageFilter'))
		{
			unset($_COOKIE[JApplication::getHash('language')]);
		}

		// Smth.
		if ($lang_code != JFactory::getLanguage()->getTag() && /*empty($_COOKIE[JApplication::getHash('language')]) && */
			empty($_REQUEST['language']))
		{
			$config->set('language', $lang_code);

			// Raw override
			// Build our language object
			$lang = JLanguage::getInstance($config->get('language'), $config->get('debug_lang'));

			// Load the language to the API
			$app->loadLanguage($lang);

			// Register the language object with JFactory
			JFactory::$language = $app->getLanguage();
		}
	}

	// Now we can load language, but override it first!
	$lang = JFactory::getLanguage();

	// Implicitely setup home menu item
	if ($config->get('mighty_home'))
	{
		$menu = $app->getMenu();

		if ($menu->getDefault())
		{
			$menu->getDefault()->home = 0;
		}
		if ($menu->getDefault($lang->getTag()))
		{
			$menu->getDefault($lang->getTag())->home = 0;
		}

		$menu->setDefault($config->get('mighty_home'), $lang->getTag());
		$menu->setDefault($config->get('mighty_home'), '*');
		$menu->getDefault()->home = 1;
	}

	// Remove menu items.
	// Useless if we have Falang at this point because it will override them later.
	if (!class_exists('plgSystemFalangdriver'))
	{
		plgSystemMightysites::removeMenuItems();
	}

	// Load language overrides, 
	if ($config->get('mighty_langoverride'))
	{
		// Load custom file after parse because PlgSystemLanguageFilter can switch language during parse.
		$app::getRouter()->attachParseRule(function () {
			$lang = JFactory::getLanguage();

			$domain = $_SERVER['HTTP_HOST'] ?: $_SERVER['SERVER_NAME'];
			$domain = strpos($domain, 'www.') === 0 ? substr($domain, 4) : $domain;
			$domain = preg_replace('#([^A-Z0-9])#i', '_', $domain);

			$override_file = JPATH_SITE . '/language/overrides/' . $domain . '.' . $lang->getTag() . '.override.ini';

			if (file_exists($override_file))
			{
				$rProperty = new ReflectionProperty($lang, 'override');
				$rProperty->setAccessible(true);
				$override = $rProperty->getValue($lang);

				$rMethod = new ReflectionMethod($lang, 'parse');
				$rMethod->setAccessible(true);
				$contents = $rMethod->invoke($lang, $override_file);

				if (is_array($contents))
				{
					$override = array_merge($override, $contents);
				}

				$rProperty->setValue($lang, $override);
			}
		}, JRouter::PROCESS_AFTER);
	}

	// MijoShop ID
	if ($config->get('mighty_mijoshopid') !== '')
	{
		$app->input->set('mijoshop_store_id', $config->get('mighty_mijoshopid'));
	}

	// Frontend login usergroups.
	if (JFactory::getUser()->get('guest') && ($config->get('mighty_login_usergroups_allow') || $config->get('mighty_login_usergroups_deny')))
	{
		$app->registerEvent('onUserLogin', function ($user, $options) use ($app) {
			if ($options['action'] === 'core.login.site')
			{
				$instance = JUser::getInstance();
				$id       = (int) JUserHelper::getUserId($user['username']);

				if ($id && $instance->load($id))
				{
					$require_changes = false;

					// Warm permissions cache.
					if ($instance->authorise($options['action']))
					{
						if ($app->get('mighty_login_usergroups_deny'))
						{
							$require_changes = true;
						}
					}
					elseif ($app->get('mighty_login_usergroups_allow'))
					{
						$require_changes = true;
					}

					if ($require_changes)
					{
						// Reset cache.
						$property = new ReflectionProperty(Access::class, 'assetRulesIdentities');
						$property->setAccessible(true);
						$assetRulesIdentities = $property->getValue();

						/** @var \Joomla\CMS\Access\Rules $rules */
						foreach ($assetRulesIdentities as $rules)
						{
							if ($app->get('mighty_login_usergroups_allow'))
							{
								$rules->mergeAction('core.login.site', array_fill_keys((array) $app->get('mighty_login_usergroups_allow'), 1));
							}
							if ($app->get('mighty_login_usergroups_deny'))
							{
								$rules->mergeAction('core.login.site', array_fill_keys((array) $app->get('mighty_login_usergroups_deny'), false));
							}
						}

						$property->setValue($assetRulesIdentities);

						// Next login will be allowed/denied in core user 'joomla' plugin via onUserLogin.
					}
				}
			}
		});
	}

	// Custom JomSocial template
	if ($jomsocial_template = $config->get('mighty_jomsocial_template'))
	{
		$app->registerEvent('onSystemStart', function () use ($jomsocial_template) {
			/** @var CConfig $config */
			$config = CFactory::getConfig();
			$config->set('template', $jomsocial_template);
		});
	}
}

// Backend options
if (JFactory::getApplication()->isAdmin())
{
	class plgSystemMightysites extends plgSystemMightysitesBase
	{
		public function onAfterRoute()
		{
			$app    = JFactory::getApplication();
			$config = JFactory::getConfig();

			// Check config file, no own settings means possible override.
			if ($config->get('mighty_enable') === null)
			{
				/** @noinspection PhpIncludeInspection */
				require_once JPATH_ADMINISTRATOR . '/components/com_mightysites/helpers/helper.php';

				MightysitesHelper::patchConfiguration();
			}

			// Check logins.
			if (isset($_REQUEST['mighty_token']) && strlen($_REQUEST['mighty_token']))
			{
				$token  = $app->input->getString('mighty_token');
				$folder = $app->getCfg('tmp_path');

				jimport('joomla.filesystem.folder');
				jimport('joomla.filesystem.file');

				$files = JFolder::files($folder, '\.mighty$');

				if (count($files))
				{
					$data = false;

					foreach ($files as $file)
					{
						if ($file == md5($token . $app->getCfg('secret')) . '.mighty')
						{
							$data = file_get_contents($folder . '/' . $file);
						}

						JFile::delete($folder . '/' . $file);
					}

					if ($data)
					{
						/** @noinspection UnserializeExploitsInspection */
						$data = unserialize($data);

						if (!JFactory::getUser()->id)
						{
							$user = new JUser();
							$user->load($data['user_id']);

							// try load by username next
							if (!$user->id)
							{
								// remove "JUser: :_load: Unable to load user with id: 42" message
								$session = JFactory::getSession();
								$session->set('application.queue', null);

								// load other admin by username
								$db = JFactory::getDBO();
								$db->setQuery('SELECT id FROM #__users WHERE `username`=' . $db->quote($data['username']));
								$user->load($db->loadResult());
							}

							if ($user->id)
							{
								// Mark the user as logged in
								$user->set('guest', 0);

								// Register the needed session variables
								$session = JFactory::getSession();
								$session->set('user', $user);

								$db = JFactory::getDBO();

								// Check to see the the session already exists.
								$app->checkSession();

								// Update the user related fields for the Joomla sessions table.
								$db->setQuery('UPDATE `#__session`' . ' SET `guest` = ' . $db->quote($user->get('guest')) . ',' . '	`username` = ' . $db->quote($user->get('username')) . ',' . '	`userid` = ' . (int) $user->get('id') . ' WHERE `session_id` = ' . $db->quote($session->getId()));
								$db->query();

								// Hit the user last visit field
								$user->setLastVisit();
							}
						}

						if (strpos($data['return'], 'index.php') === 0)
						{
							$data['return'] = JUri::base(true) . '/' . ltrim($data['return'], '/');
						}

						$app->redirect($data['return']);
					}
				}
			}
		}

		// Let's run the install queries for the component which already exists.
		// Not used since 11 Nov 2018 because it creates issues with extension update via zip upload
		// if install SQL has DROP TABLE.
		/*public function onExtensionBeforeInstall($method, $type, $manifest, $extension)
		{
			if ($method == 'install' && $type == 'component')
			{
				$element = $this->getComponentName($manifest);
				
				if (file_exists(JPATH_SITE.'/components/'.$element) || file_exists(JPATH_ADMINISTRATOR.'/components/'.$element))
				{
					if (isset($manifest->install->sql))
					{
						$installer	= JInstaller::getInstance();
						
						// Pre-create path to use for getting SQL files
						$installer->setPath('extension_root', JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $element));
						
						// Don't use rewrite!
						$GLOBALS['no_mightysharing'] = true;
						
						$installer->parseSQLFiles($manifest->install->sql);
						
						unset($GLOBALS['no_mightysharing']);
					}
				}
			}
		}*/

		protected function getComponentName($manifest)
		{
			$name = strtolower(JFilterInput::getInstance()->clean((string) $manifest->name, 'cmd'));

			if (strpos($name, 'com_') === 0)
			{
				$element = $name;
			}
			else
			{
				$element = 'com_' . $name;
			}

			return $element;
		}
	}
}

// Shared options.

// Media Manager overrides.
if ($app->input->get('option') === 'com_media')
{
	if ($mighty_file_path = $config->get('mighty_file_path'))
	{
		JComponentHelper::getParams('com_media')->set('file_path', $mighty_file_path);
	}
	if ($mighty_image_path = $config->get('mighty_image_path'))
	{
		JComponentHelper::getParams('com_media')->set('image_path', $mighty_image_path);
	}
}

// Users Manager overrides.
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && in_array($app->input->get('option'), [
		'com_users',
		'com_comprofiler',
		'com_community',
		'com_easysocial',
		'com_k2',
		'com_easyprofile',
	]))
{
	if ($mighty_new_usertype = $config->get('mighty_new_usertype'))
	{
		JComponentHelper::getParams('com_users')->set('new_usertype', $mighty_new_usertype);
	}
}
