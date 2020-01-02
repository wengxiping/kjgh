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

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';
$exists = JFile::exists($file);

if (!$exists) {
	return;
}

require_once($file);

class plgPayplansRedirectnonsubscriber extends PPPlugins
{
	/**
	 * Triggered by Joomla system events
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		$user = PP::user();

		if ($this->app->isAdmin() || $user->isAdmin()) {
			return true;
		}
		
		// If Default Page of site then nothing to do.
		$homeMenu = $this->isHomeMenu();
		if ($homeMenu) {
			return true;
		}

		//Parameters From the Current URL Requested by user 
		$option = $this->input->get('option', '');
		$view = $this->input->get('view', '');
		$task = $this->input->get('task', '');
		
		if ($option == 'com_users') {
			return true;
		}
		
		//prefix For Fetching Visitor's setting from plugin
		$prefix = 'visitor_';
		$userId = $user->user_id;
		if ($userId) {
			$userPlans = $user->getPlans(PP_SUBSCRIPTION_ACTIVE);
			
			//If User have an Active Subscription
			if (count($userPlans) > 0) {
				return true;
			}
		
			$prefix = 'non_subscriber_';
		}
		
		//URL Parameters From the Plugin For Redirecting
		$redirectOption = $this->params->get($prefix . 'option');
		$redirectView = $this->params->get($prefix . 'view');
		$redirectTask = $this->params->get($prefix . 'task');

		//If Parameters of Plugin are not set then nothing to do.
		if (!isset($redirectOption)) {
			return true;			
		}
					
		if (($option == $redirectOption) && ($view == $redirectView)) {
			return true;
		}
			
		// Create url for redirection
		$url = 'index.php?option='. "$redirectOption" . '&view=' . "$redirectView";

		if ($redirectTask) {
			$url = $url . '&task=' . $redirectTask;
		}

		$url = PP::redirect(JRoute::_($url, false));

		return $url;
	}

	/**
	 * Get Home Menu
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isHomeMenu()
	{
		$menu = $this->app->getMenu();
		$active	= $menu->getActive();
		$homeMenu = $menu->getDefault();
		if (isset($active)) {
			if ($homeMenu->id == $active->id) {
				return true;				
			}
		}

		return false;
	}
}

