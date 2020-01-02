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

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansMenuAccess extends PPPlugins
{
	/**
	 * Triggered when PayPlans is rendered
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSystemStart()
	{
		$user = PP::user();

		if ($this->app->isAdmin() || $user->isAdmin()) {
			return true;
		}

		$path = __DIR__ . '/app/joomla';

		// JLoader::register('JMenu', $path . '/menu.php');
		// JLoader::register('JAbstractJ35Menu', $path . '/abstract/j35/menu.php');
		// JLoader::register('JAbstractJ35MenuSite', $path . '/abstract/j35/menu/site.php');
		JLoader::register('JMenuSite', $path . '/menu/site.php');
		
		// JLoader::registerAlias('JMenuSite', $path . '/menu/site.php', '5.0');

		// class_exists("JMenu", true);

		return true;
	}

	/**
	 * Triggered by Joomla system events
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		$option = $this->input->get('option', '');
		$task = $this->input->get('task', '');

		// No restrictions on logins
		if ($option == 'com_users' && !empty($task) && ($task == 'user.logout' || $task == 'user.login')) {
			return true;
		}

		$user = PP::user();

		//Nothing to do on Admin-end or when user is super user.
		if ($this->app->isAdmin() || $user->isAdmin()) {
			return true;
		}
		
		$redirected = $this->input->get('redirected', '');

		//Already Redirected then bypass.
		if ($redirected == 1) {
			$this->input->set('redirected', false);
			return true;
		}
		
		// Get a list of apps
		$apps = $this->getAvailableApps();

		if (!$apps) {
			return true;
		}

		$helper = $this->getAppHelper();

		//parse the request url
		$uri = clone(JUri::getInstance());
		$router = $this->app->getRouter();
		$currentUrl = $router->parse($uri);

		// Fix zoo menu items if needed
		$currentUrl = $helper->zoo($currentUrl);

		if (array_key_exists('Itemid', $currentUrl)) {
			unset($currentUrl['Itemid']);
		}

		if (isset($currentUrl['format'])) {
			unset($currentUrl['format']);
		} 
		
		$menu = $this->app->getMenu();
		
		// Get All Restricted Menus
		$appMenu = array();
		$menuPlans = array();
		$allowedMenus = array();
		$userPlans = $user->getPlans(PP_SUBSCRIPTION_ACTIVE);

		$userPlanIds = array();
		if ($userPlans) {
			foreach ($userPlans as $userPlan) {
				$userPlanIds[] = $userPlan->getId();
			}
		}

		foreach ($apps as $appId => $app) {

			$allowedMenus = $app->getAppParam('allowedMenus', array());
			$allowedMenus = is_array($allowedMenus) ? $allowedMenus : array($allowedMenus);

			//Load Menu Item of Joomla
			foreach ($allowedMenus as $menuId) {

				$menuItem = $menu->getItem($menuId);

				// Menu might have been deleted
				if (!$menuItem) {
					continue;
				}
				
				// If there is no menus in the link, we need to generate it
				if (!strpos($menuItem->link, '&id')) {
					$params = $menuItem->params;

					if ($params->get('item_id')) {
						$menuItem->link = $menuItem->link . "&id=" . $params->get('item_id');
					}
				}

				$language = '';

				if ($menuItem->language != '*') {
					$language = explode('-', $menuItem->language);
					$language = '&lang=' . $language[0];
				} else {
					
					// If in the menu it is set to all,language filter plugin is enabled
					// and user's site is multilangual. Then remove the language parameter.
					if (isset($currentUrl['lang'])) {
						unset($currentUrl['lang']);
					}
				}

				// Separate Out All Elements form Query String
				$menuItemUrl = JUri::getInstance($menuItem->link . $language)->getQuery(true);

				// If id is set then remove it
				if (isset($menuItemUrl['id']) && !isset($currentUrl['id'])){
					unset($menuItemUrl['id']);
				}

				// If id set in current url but request view restricted then check for extra parameters for easysocial
				if ($option == 'com_easysocial' && isset($currentUrl['id']) && !isset($menuItemUrl['id'])) {
					if (($currentUrl['option'] == $menuItemUrl['option']) && ($currentUrl['view'] == $menuItemUrl['view'])) {
						unset($currentUrl['id']);
					}
				}

				// count how many URL statement for the current URL and the menu URL
				$totalOfMenuItemUrl = count($menuItemUrl);
				$totalOfCurrentUrl = count($currentUrl);

				// only do something if both url statement count is the same			
				if ($totalOfMenuItemUrl == $totalOfCurrentUrl) {

					// it will return those different value if detected
					$hasDifferentURLStatement = array_diff_assoc($menuItemUrl, $currentUrl);

					// Process this if both URL statement are match
					if (!$hasDifferentURLStatement) {
						$applyAll = $app->getParam('applyAll', 0);
						$appPlans = $app->getPlans();

						if ($applyAll) {
							$appPlans = PPHelperPlan::getPlans(array('published' => 1), false);
						}

						$menuPlans = array_merge($menuPlans, $appPlans); 
					}
				}
			}
		}

		$config = PP::config();

		// Check for the all the plans which are added with applicable menu(restricted menu)
		// and allow user only if he has one of those plan
		if ($menuPlans && !array_intersect($userPlanIds, $menuPlans)) {

			$renderAs404 = $config->get('show404error');

			if (!$this->app->isAdmin() && !$renderAs404) {
				PP::info()->set('COM_PAYPLANS_APP_MENUACCESS_SUBSCRIPTION_EXPIRATION_MESSAGE', 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=plan');

				return PP::redirect($redirect);
			}

			// Otherwise, throw a 404 error
			throw new Exception(JText::_('COM_PAYPLANS_APP_MENUACCESS_PAGE_NOT_FOUND_MESSAGE'), '404');
			return true;
		}
		
		return true;				
	}

	/**
	 * Restrict menu items from being accessed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansMenusLoad(&$menus)
	{
		// Don't do anything at the back end
		if ($this->app->isAdmin()) {
			return true;
		}

		$apps = $this->getAvailableApps();

		if (!$apps) {
			return;
		}

		$user = PP::user();

		if ($user->isAdmin()) {
			return true;
		}

		// Get user plans
		$userPlans = $user->getPlans(PP_SUBSCRIPTION_ACTIVE);

		$userPlanIds = array();
		if ($userPlans) {
			foreach ($userPlans as $userPlan) {
				$userPlanIds[] = $userPlan->getId();
			}
		}

		$config = PP::config();

		//step 2:- select those menus which we want to display or hide according to app
		$display = array();
		$hidden = array();

		foreach ($apps as $appId => $app) {
			$applyAll = $app->getParam('applyAll', 0);

			$allowedMenus = $app->getAppParam('allowedMenus', array());
			$allowedMenus = is_array($allowedMenus) ? $allowedMenus : array($allowedMenus);
			$appPlans = $app->getPlans();

			if ($applyAll) {
				$appPlans = PPHelperPlan::getPlans(array('published' => 1), false);
			}

			if (array_intersect($userPlanIds, $appPlans)) {
				$display = array_merge($display, $allowedMenus);
			} else {
				$hidden = array_merge($hidden, $allowedMenus);
			}
			
		}

		// Step 3:- remove allowed menus from hide list
		$hidden = array_diff($hidden, $display);

		//step 4:- remove menus which are in hide list 
		// check show menus to user or not
		$showMenu = $config->get('showOrhide');

		if (!$showMenu) {
			foreach ($hidden as $hiddenMenu) {
				if (isset($menus[$hiddenMenu])) {
					$menus[$hiddenMenu]->access = 0;
				}
			}
		}
	}
}
