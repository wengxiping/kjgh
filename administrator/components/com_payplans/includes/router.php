<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');


class PPR
{
	/**
	 * Method to inject PayPlans Itemid if needed.
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		// check if $url already has the Itemid or not. if not, we need to generate one.

		// Parse the url
		parse_str(parse_url($url, PHP_URL_QUERY), $query);

		$add = false;

		// Get the view portion from the query string
		$view = isset($query['view']) ? $query['view'] : 'plan';
		$layout = isset($query['layout']) ? $query['layout'] : null;
		$itemId = isset($query['Itemid']) ? $query['Itemid'] : '';
		$lang = isset($query['lang']) ? $query['lang'] : null;

		// we know the lang that we passed in is the short tag. we need to get the full tag. e.g. en-GB
		if ($lang) {
			$lang = self::getSiteLanguageTag($lang);
		}

		if ($itemId) {
			$current = JFactory::getApplication()->getMenu()->getItem($itemId);
			$menu = self::getMenu($view, $layout, $lang);

			// check if this active menu item is belong to PayPlans or not.
			if ((isset($current->query) && isset($current->query['option']) && $current->query['option'] != 'com_payplans'))  {
				// okay its not belong to payplans. lets manually get the correct menu items.
				if ($menu !== false) {
					$itemId = $menu->id;
					$add = true;
				} else {
					// reset itemid so that we will search manually.
					$itemId = '';
				}
			}
		}

		if (!$itemId) {

			$menu = self::getMenu($view, $layout, $lang);

			if ($menu === false) {
				// try getting plans menu item
				$menu = self::getMenu('plan', null, $lang);
			}

			if ($menu === false) {
				// try getting dashboard menu item
				$menu = self::getMenu('dashboard', null, $lang);
			}

			if ($menu !== false) {
				$itemId = $menu->id;
				$add = true;
			}

			// at this points if menu is still false,
			// we will give up and use whatever we have from the current active
			// menu item.
			if (!$itemId) {
				$active = JFactory::getApplication()->getMenu()->getActive();

				if ($active) {
					$itemId = $active->id;
				}
			}
		}

		if ($add) {

			//check if there is any anchor in the link or not.
			$pos = JString::strpos($url, '#');

			if ($pos === false) {
				$url .= '&Itemid='.$itemId;
			} else {
				$url = JString::str_ireplace('#', '&Itemid='.$itemId.'#', $url);
			}
		}

		$newUrl = JRoute::_($url, $xhtml, $ssl);
		return $newUrl;
	}

	/**
	 * Method to generata PayPlans url for external use.
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public static function external($url, $xhtml = true, $ssl = null) 
	{

		$url = self::_($url, $xhtml, $ssl);
		$url = str_replace('/administrator/', '/', $url);
		$url = ltrim($url, '/');

		// We need to use $uri->toString() because JURI::root() may contain a subfolder which will be duplicated
		// since $url already has the subfolder.
		$uri = JURI::getInstance();
		return $uri->toString(array('scheme', 'host', 'port')) . '/' . $url;

	}

	/**
	 * Method to get menu item id belong to Payplans
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public static function getMenu($view, $layout = null, $lang = null)
	{
		static $menus = null;
		static $selection = array();

		// Always ensure that layout is lowercased
		if (!is_null($layout)) {
			$layout = strtolower($layout);
		}

		$language = false;
		$languageTag = JFactory::getLanguage()->getTag();

		// If language filter is enabled, we need to get the language tag
		if (!JFactory::getApplication()->isAdmin()) {
			$language = JFactory::getApplication()->getLanguageFilter();
			$languageTag = JFactory::getLanguage()->getTag();
		}

		if ($lang) {
			$languageTag = $lang;
		}

		$key = $view . $layout . $languageTag;

		// var_dump($key);

		// Preload the list of menus first.
		if (is_null($menus)) {

			$model = PP::model('menu');
			$allmenu = $model->getMenuItems();

			$menus = array();

			foreach ($allmenu as $row) {

				// Remove the index.php?option=com_payplans from the link
				$tmp = str_ireplace('index.php?option=com_payplans', '', $row->link);

				// Parse the URL
				parse_str($tmp, $segments);

				// Convert the segments to std class
				$segments = (object) $segments;

				// if there is no view, most likely this menu item is a external link type. lets skip this item.
				if(!isset($segments->view)) {
					continue;
				}

				$menu = new stdClass();
				$menu->segments = $segments;
				$menu->link = $row->link;
				$menu->view = $segments->view;
				$menu->layout = isset($segments->layout) ? $segments->layout : 0;
				$menu->id = $row->id;

				// this is the safe step to ensure later we will have atlest one menu item to retrive.
				$menus[$menu->view][$menu->layout]['*'][] = $menu;
				$menus[$menu->view][$menu->layout][$row->language][] = $menu;
			}
		}

		// Get the current selection of menus from the cache
		if (!isset($selection[$key])) {

			$found = false;

			// Searches for $view and $layout only.
			if (isset($menus[$view]) && isset($menus[$view]) && !is_null($layout) && isset($menus[$view][$layout])) {
				$selection[$key] = isset($menus[$view][$layout][$languageTag]) ? $menus[$view][$layout][$languageTag] : $menus[$view][$layout]['*'];
				$found = true;
			}

			// Search for $view only. Does not care about layout
			if (isset($menus[$view]) && isset($menus[$view]) && (is_null($layout) || !$found)) {
				if (isset($menus[$view][0][$languageTag])) {
					$selection[$key] = $menus[$view][0][$languageTag];
				} else if (isset($menus[$view][0]['*'])) {
					$selection[$key] = $menus[$view][0]['*'];
				} else {
					$selection[$key] = false;
				}
			}

			// Flatten the array so that it would be easier for the caller.
			if (isset($selection[$key]) && is_array($selection[$key])) {
				$selection[$key] = $selection[$key][0];
			}

			// If we still can't find any menu, skip this altogether.
			if (!isset($selection[$key])) {
				$selection[$key] = false;
			}
		}

		return $selection[$key];
	}


	/**
	 * Get site langauge code
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public static function getSiteLanguageTag($langSEF)
	{
		static $cache = null;

		if (is_null($cache)) {
			$db = PP::db();

			$query = "select * from #__languages";
			$db->setQuery($query);

			$results = $db->loadObjectList();

			if ($results) {
				foreach($results as $item) {
					$cache[$item->sef] = $item->lang_code;
					$cache[$item->lang_code] = $item->sef;
				}
			}
		}

		if (isset($cache[$langSEF])) {
			return $cache[$langSEF];
		}

		return $langSEF;
	}


	/**
	 * Retrieve all menu's from the site associated with EasyBlog
	 *
	 * @since	5.1
	 * @access	public
	 */
	public static function getMenus($view, $layout = null, $id = null, $lang = null)
	{
		static $menus = null;
		static $selection = array();

		// Always ensure that layout is lowercased
		if (!is_null($layout)) {
			$layout = strtolower($layout);
		}

		// We want to cache the selection user made.
		// $key = $view . $layout . $id;
		$language = false;
		$languageTag = JFactory::getLanguage()->getTag();

		// If language filter is enabled, we need to get the language tag
		if (!JFactory::getApplication()->isAdmin()) {
			$language = JFactory::getApplication()->getLanguageFilter();
			$languageTag = JFactory::getLanguage()->getTag();
		}

		// var_dump($lang);
		if ($lang) {
			$languageTag = $lang;
		}

		$key = $view . $layout . $id . $languageTag;

		// Preload the list of menus first.
		if (is_null($menus)) {

			$model = EB::model('Menu');
			$result = $model->getAssociatedMenus();

			if (!$result) {
				return $result;
			}

			$menus = array();

			foreach ($result as $row) {

				// Remove the index.php?option=com_easyblog from the link
				$tmp = str_ireplace('index.php?option=com_easyblog', '', $row->link);

				// Parse the URL
				parse_str($tmp, $segments);

				// Convert the segments to std class
				$segments = (object) $segments;

				// if there is no view, most likely this menu item is a external link type. lets skip this item.
				if(!isset($segments->view)) {
					continue;
				}

				$menu = new stdClass();
				$menu->segments = $segments;
				$menu->link = $row->link;
				$menu->view = $segments->view;
				$menu->layout = isset($segments->layout) ? $segments->layout : 0;

				if (!$menu->layout && $menu->view == 'entry') {
					$menu->layout = 'entry';
				}

				$menu->id = $row->id;

				// var_dump($row->language);

				// this is the safe step to ensure later we will have atlest one menu item to retrive.
				$menus[$menu->view][$menu->layout]['*'][] = $menu;
				$menus[$menu->view][$menu->layout][$row->language][] = $menu;
			}

		}

		// Get the current selection of menus from the cache
		if (!isset($selection[$key])) {

			// Search for $view only. Does not care about layout nor the id
			if (isset($menus[$view]) && isset($menus[$view]) && is_null($layout)) {
				if (isset($menus[$view][0][$languageTag])) {
					$selection[$key] = $menus[$view][0][$languageTag];
				} else if (isset($menus[$view][0]['*'])) {
					$selection[$key] = $menus[$view][0]['*'];

				} else {
					$selection[$key] = false;
				}

			}


			// Searches for $view and $layout only.
			if (isset($menus[$view]) && isset($menus[$view]) && !is_null($layout) && isset($menus[$view][$layout]) && (is_null($id) || empty($id))) {
			$selection[$key] = isset($menus[$view][$layout][$languageTag]) ? $menus[$view][$layout][$languageTag] : $menus[$view][$layout]['*'];
			}

			// // view=entry is unique because it doesn't have a layout
			// if ($view == 'entry') {
			//     dump($layout, $selection[$key]);
			// }

			// Searches for $view $layout and $id
			if (isset($menus[$view]) && !is_null($layout) && isset($menus[$view][$layout]) && !is_null($id) && !empty($id)) {

				$found = false;
				if ($languageTag != '*' && isset($menus[$view][$layout][$languageTag])) {
					$tmp = $menus[$view][$layout][$languageTag];

					foreach ($tmp as $tmpMenu) {
						// Backward compatibility support. Try to get the ID from the new alias style, ID:ALIAS
						$parts = explode(':', $id);
						$legacyId = null;

						if (count($parts) > 1) {
							$legacyId = $parts[0];
						}

						if (isset($tmpMenu->segments->id) && ($tmpMenu->segments->id == $id || $tmpMenu->segments->id == $legacyId)) {
							$found = true;
							$selection[$key] = array($tmpMenu);
							break;
						}
					}
				}

				// in some situation where there are records in $menus[$view][$layout][$languageTag] but the correct item actually fall under
				// $menus[$view][$layout][*]. Due to this reason, we have no choice but to loop through all. #131
				if (! $found) {
					$tmp = $menus[$view][$layout]['*'];

					foreach ($tmp as $tmpMenu) {

						// Backward compatibility support. Try to get the ID from the new alias style, ID:ALIAS
						$parts = explode(':', $id);
						$legacyId = null;

						if (count($parts) > 1) {
							$legacyId = $parts[0];
						}

						if (isset($tmpMenu->segments->id) && ($tmpMenu->segments->id == $id || $tmpMenu->segments->id == $legacyId)) {
							$found = true;
							$selection[$key] = array($tmpMenu);
							break;
						}
					}

				}

			}

			// If we still can't find any menu, skip this altogether.
			if (!isset($selection[$key])) {
				$selection[$key] = false;
			}

			// Flatten the array so that it would be easier for the caller.
			if (is_array($selection[$key])) {
				$selection[$key] = $selection[$key][0];
			}
		}

		return $selection[$key];
	}

}
