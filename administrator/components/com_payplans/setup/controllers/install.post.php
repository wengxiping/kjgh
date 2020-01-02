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

require_once(__DIR__ . '/controller.php');

class PayplansControllerInstallPost extends PayplansSetupController
{
	/**
	 * Post installation process
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function execute()
	{
		// Get the api key so that we can store it
		$key = $this->input->get('apikey', '', 'default');

		// Update api key
		$this->updateConfig('main_apikey', PP_KEY);

		// Create core adminpay payment method
		$this->createCoreApps();

		// Create dummy user
		$this->createDummyUser();

		// Create site menu item
		$this->createDefaultMenu();

		// Now we need to update the #__update_sites row to include the api key as well as the domain
		$this->updateJoomlaUpdater();

		// update manifest cache
		$this->updateManifestCache();

		// Clear #__updates as the user might be using the internal updater
		$this->clearJoomlaUpdates();

		// uninstall unused apps / plugins
		$this->uninstallApps();

		// disable deprecated payment method
		$this->disableDeprecatedApps();

		// update group in apps table.
		$this->updateAppGroups();

		// Remove quick icon module
		$this->removeLegacyModules();

		// Fix log folder
		$this->fixLogs();

		$this->setInfo(JText::_('COM_PP_INSTALLATION_POST_EXECUTED_SUCCESS'), true);
		return $this->output();
	}

	/**
	 * Fix log files in the log folder to address issue with public users accessing log files
	 *
	 * @since	4.0.12
	 * @access	public
	 */
	public function fixLogs()
	{
		$log = PP::log();

		// check if there is already the .htaccess file created
		// in log folder.
		$log->addHTAccessFile();

		// now we fix the logs by converting the txt file into php file.
		$files = $log->getLegacyLFiles();
		if ($files) {
			foreach ($files as $file) {
				$log->fixLegacyFile($file);
			}
		}
	}
	
	/**
	 * Create necessary core apps during installation if it doesn't exist yet
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createCoreApps()
	{
		$this->engine();

		$db = PP::db();

		// Create admin pay if it doesn't exists yet
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_app') . ' WHERE `type`="adminpay"';
		$db->setQuery($query);
		$exists = $db->loadResult() > 0;

		if (!$exists) {
			$table = PP::table('App');
			$table->group = 'core';
			$table->title = 'Admin Pay';
			$table->type = 'adminpay';
			$table->description = 'This payment method is used for the core and cannot be deleted';
			$table->published = 1;
			$table->store();
		}


		// Create offline payment if it doesn't exists yet
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_app') . ' WHERE `type` = "offlinepay"';
		$db->setQuery($query);
		$exists = $db->loadResult() > 0;

		if (!$exists) {

			$coreParams = '{"applyAll":"1"}';
			$appParams = '{"bankname":"","account_name":"","account_number":"","allow_recurring_cancel":"","notify_users":""}';

			$table = PP::table('App');
			$table->group = 'payment';
			$table->title = 'Offline Payment';
			$table->type = 'offlinepay';
			$table->description = 'This payment method is used for the offline payment.';
			$table->published = 1;
			$table->core_params = $coreParams;
			$table->app_params = $appParams;
			$table->store();
		}
	}

	/**
	 * Creates a dummy user to process guest checkout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createDummyUser()
	{
		$this->engine();

		$model = PP::model('User');
		$dummy = $model->getDummyUser();

		if (!$dummy) {
			$model->createDummyUser();
		}
	}

	public function updateAppGroups()
	{
		$this->engine();

		// Skip this when we are on development mode
		if ($this->isDevelopment()) {
			return false;
		}

		$db = PP::db();

		// lets check if we need to run any maintenance or not.
		$query = "select count(1) from `#__payplans_app` where `group` = " . $db->Quote('');
		$db->setQuery($query);
		$result = $db->loadResult();

		if (! $result) {
			return false;
		}

		// available app's group
		// app, automation, payment, email, referral and core

		// payment
		$paymentGroups = array();
		$gatewayModel = PP::model('gateways');
		$gateways = $gatewayModel->getApps();
		foreach ($gateways as $gateway) {
			$paymentGroups[] = $gateway->element;
		}


		// app
		$appGroups = array();
		$appModel = PP::model('app');
		$apps = $appModel->getApps();
		foreach ($apps as $app) {
			$appGroups[] = $app->element;
		}

		// automation
		$automationGroups = array('httpquery', 'mysqlquery', 'assignplan');

		// email
		$emailGroup = array('email');

		// referral
		$referralGroup = array('referral');

		// core
		$coreGroups = array('adminpay', 'upgrade', 'planmodifier', 'limitsubscription', 'profilebasedplan', 'renewal');

		// do not change the index sequence unless you know what you are doing.
		$allApps = array(
			'payment' => $paymentGroups,
			'app' => $appGroups,
			'automation' => $automationGroups,
			'email' => $emailGroup,
			'referral' => $referralGroup,
			'core' => $coreGroups
		);


		foreach ($allApps as $key => $items) {

			$str = '';
			foreach ($items as $item) {
				$str .= ($str) ? ',' . $db->Quote($item) : $db->Quote($item);
			}

			$query = "update `#__payplans_app` set `group` = " . $db->Quote($key);
			$query .= " where `type` IN (" . $str . ")";
			$query .= " and `group` = " . $db->Quote('');

			$db->setQuery($query);
			$db->query();
		}

	}


	/**
	 * Create a new default payplans menu
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function createDefaultMenu()
	{
		$this->engine();

		// Skip this when we are on development mode
		if ($this->isDevelopment()) {
			return false;
		}

		// Get the extension id
		$extensionId = $this->getExtensionId();

		// Get the main menu that is used on the site.
		$menuType = $this->getMainMenuType();

		if (!$menuType) {
			return false;
		}

		$db = JFactory::getDBO();

		// Get any menu items that are already created with com_easyblog
		$query = array();
		$query[] = 'SELECT `id`, `link` FROM ' . $db->quoteName('#__menu');
		$query[] = 'WHERE ' . $db->quoteName('link') . ' LIKE(' . $db->Quote('%index.php?option=com_payplans%') . ')';
		$query[] = 'AND ' . $db->quoteName('type') . '=' . $db->Quote('component');
		$query[] = 'AND ' . $db->quoteName('client_id') . '=' . $db->Quote(0);

		$query = implode(' ', $query);
		$db->setQuery($query);

		$rows	= $db->loadObjectList();

		// If menu already exists, we need to ensure that all the existing menu's are now updated with the correct extension id
		if ($rows) {

			// check if there is any subscription view or not.
			foreach ($rows as $row) {
				$mid = $row->id;
				$link = $row->link;

				if (strpos($link, 'view=subscription') !== false) {
					// okay we need to update the liink.
					$newLink = 'index.php?option=com_payplans&view=dashboard';

					$query = "update `#__menu` set `link` = " . $db->Quote($newLink);
					$query .= " where `id` = " . $db->Quote($mid);
					$db->setQuery($query);
					$this->ppQuery($db);

				}
			}

			$query = array();
			$query[] = 'UPDATE ' . $db->quoteName('#__menu') . ' SET ' . $db->quoteName('component_id') . '=' . $db->Quote($extensionId);
			$query[] = 'WHERE ' . $db->quoteName('link') . ' LIKE (' . $db->Quote('%index.php?option=com_payplans%') . ')';
			$query[] = 'AND ' . $db->quoteName('type') . '=' . $db->Quote('component');
			$query[] = 'AND ' . $db->quoteName('client_id') . '=' . $db->Quote(0);

			$query = implode(' ', $query);
			$db->setQuery($query);
			$this->ppQuery($db);

			return true;
		}

		$menu = JTable::getInstance('Menu');
		$menu->menutype = $menuType;
		$menu->title = JText::_('COM_PP_INSTALLATION_DEFAULT_MENU_DASHBOARD');
		$menu->alias = 'my-dashboard';
		$menu->path = 'my-dashboard';
		$menu->link = 'index.php?option=com_payplans&view=dashboard';
		$menu->type = 'component';
		$menu->published = 1;
		$menu->parent_id = 1;
		$menu->component_id = $extensionId;
		$menu->client_id = 0;
		$menu->language = '*';
		$menu->img = '';
		$menu->params = '';

		$menu->setLocation('1', 'last-child');

		$state = $menu->store();

		return true;
	}

	/**
	 * Unpublish deprecated apps / plugins in PP 4.0
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function uninstallApps()
	{
		$this->engine();
		$db = PP::db();

		// Steps:
		// 1. uninstall plugins from joomla that responsible for the apps.
		// 2. disable apps instance from payplans.
		// 3. remove apps instance from payplans (if there is any).

		// 1. uninstall plugins
		$plugins = array(
					'payplans' => array('content', 'userdetail', 'userpreferences', 'corewidget',
										'upgrade', 'prodiscount', 'subscriptiondetail',
										'jdownloads', 'jnews', 'socialdiscount', 'multiloginrestriction', 
										'usedtimemonitor', 'xiprofiletype', 'eventlist', 'kissmetrics', 
										'juga', 'planmodifier', 'ninjaboard','export', 'limitplan', 
										'oneclickcheckout', 'subscriptionapproval','sobi2', 'relogin', 
										'subscriptionstartdate', 'parentchild','sample', 'renewal', 
										'limitsubscription', 'skipfreeinvoice', 'defaultplan', 
										'referral', 'discount', 'easydiscuss', 'k2', 'ats', 'rokfeaturetable', 
										'donation', 'paybywallet'),

					'payplansregistration' => array('auto', 'activateafterpayment', 'adagency',
													'comprofiler', 'easysocialregistration', 'jfbconnect',
													'jomsocial', 'joomla'),

					'authentication' => array('autologin'),

					'user' => array('loginredirector'),

					'system' => array('mtreepayplans')
				);

		$extensionType = 'plugin';

		foreach ($plugins as $folder => $elements) {

			$tempElements = '';
			foreach($elements as $element){
				$tempElements .= ($tempElements) ? ',' . $db->Quote($element) : $db->Quote($element);
			}

			// get package identifier (extension_id)
			$query = "SELECT `extension_id` FROM `#__extensions`";
			$query .= " WHERE `folder` = " . $db->Quote($folder);
			$query .= " AND `element` IN (" . $tempElements . ")";
			$query .= " AND `type` = " . $db->Quote($extensionType);

			$db->setQuery($query);
			$items = $db->loadColumn();

			if ($items) {
				foreach ($items as $eid){

					$installer = JInstaller::getInstance();
					$state = $installer->uninstall($extensionType, $eid);

					if (!$state) {
						// uninstallation failed. lets just unpublish this plugin.
						$query = "UPDATE `#__extensions` SET `enabled` = 0";
						$query .= " WHERE `extension_id` = " . $db->Quote($eid);

						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}

		// 2. disable app instances
		// $unpublishApps = array('');

		// $appTypes = '';
		// foreach ($unpublishApps as $type){
		// 	$appTypes .= ($appTypes) ? ',' . $db->Quote($type) : $db->Quote($type);
		// }

		// $query = "UPDATE `#__payplans_app` SET `published` = " . $db->Quote('0');
		// $query .= " WHERE `type` IN (" . $appTypes . ")";

		// $db->setQuery($query);
		// $db->query();


		// 3. remove app instance
		$removeApps = array('content', 'userpreferences', 'corewidget', 'usedtimemonitor', 'xiprofiletype',
						'eventlist', 'kissmetrics', 'juga', 'ninjaboard', 'subscriptionapproval', 'sobi2',
						'subscriptionstartdate', 'defaultplan', 'edcategory', 'k2');

		$appTypes = '';
		foreach ($removeApps as $type){
			$appTypes .= ($appTypes) ? ',' . $db->Quote($type) : $db->Quote($type);
		}

		$query = "DELETE FROM `#__payplans_app`";
		$query .= " WHERE `type` IN (" . $appTypes . ")";

		$db->setQuery($query);
		$db->query();


		return true;
	}

	/**
	 * disable deprecated payment plugins in PP 4.0
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function disableDeprecatedApps()
	{
		$db = PP::db();

		$plugins = array('moneybookers', 'gate2shop', 'ccavenue', 'sagepay',
						'eway', 'ccbill', 'payseal', 'deltapay', 'mes', 'ebs', 'payline',
						'paxum', 'payex', 'bankaudi', 'payu', 'robokassa', 'webmoney', 
						'paypaladaptive', 'epay', 'rede', 'alertpay', 'setcom', 
						'cashenvoy', 'authorizecimhosted', 'paynl');

		foreach ($plugins as $element) {

			$query	= 'UPDATE '. $db->quoteName( '#__extensions' )
					. ' SET   '. $db->quoteName('enabled').'='.$db->Quote(0)
					. ' WHERE '. $db->quoteName('element').'='.$db->Quote($element)
					. ' AND ' . $db->quoteName('folder').'='.$db->Quote('payplans') 
					. " AND `type`='plugin' ";
			
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}



	/**
	 * When the user installs Payplans, we'll need to ensure that #__updates is also removed
	 * so that Joomla will not alert them about the upgrade again
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function clearJoomlaUpdates()
	{
		$this->engine();

		$extensionId = $this->getExtensionId();

		$db = JFactory::getDBO();
		$query = array();
		$query[] = 'DELETE FROM ' . $db->quoteName('#__updates');
		$query[] = 'WHERE ' . $db->quoteName('extension_id') . '=' . $db->Quote($extensionId);

		$query = implode(' ', $query);
		$db->setQuery($query);

		return $this->ppQuery($db);
	}

	/**
	 * Removes legacy modules when user upgrades to PayPlans 4
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeLegacyModules()
	{
		$this->engine();

		$db = PP::db();

		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__modules');
		$query[] = 'WHERE ' . $db->qn('module') . '=' . $db->Quote('mod_payplans_quickicon');

		$db->setQuery($query);
		$state = $db->Query();

		if ($state) {
			$path = JPATH_ROOT . '/modules/mod_payplans_quickicon';

			if (JFolder::exists($path)) {
				JFolder::delete($path);
			}
		}

		return true;
	}

	/**
	 * Once the installation is completed, we need to update Joomla's update site table with the appropriate data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function updateJoomlaUpdater()
	{
		$this->engine();

		$extensionId = $this->getExtensionId();

		$db = JFactory::getDBO();
		$query = array();
		$query[] = 'SELECT ' . $db->quoteName('update_site_id') . ' FROM ' . $db->quoteName('#__update_sites_extensions');
		$query[] = 'WHERE ' . $db->quoteName('extension_id') . '=' . $db->Quote($extensionId);

		$query = implode(' ', $query);
		$db->setQuery($query);

		$updateSiteId = $db->loadResult();

		$defaultLocation = 'https://stackideas.com/jupdates/manifest/payplans';
		$location = $defaultLocation . '?apikey=' . PP_KEY;

		// For some Joomla versions, there is no tables/updatesite.php
		// Hence, the JTable::getInstance('UpdateSite') will return null
		$table = JTable::getInstance('UpdateSite');

		if ($table) {
			// Now we need to update the url
			$exists = $table->load($updateSiteId);

			if (!$exists) {
				return false;
			}

			$table->location = $location;
			$table->store();
		} else {
			$query	= 'UPDATE '. $db->quoteName('#__update_sites')
					. ' SET ' . $db->quoteName('location') . ' = ' . $db->Quote($location)
					. ' WHERE ' . $db->quoteName('update_site_id') . ' = ' . $db->Quote($updateSiteId);
			$db->setQuery($query);
			$this->ppQuery($db);
		}

		// Cleanup unwanted data from updates table
		// Since Joomla will always try to add a new record when it doesn't find the same match, we need to delete records created
		// for https://stackideas.com/jupdates/manifest/payplans
		$query = 'SELECT * FROM ' . $db->quoteName('#__update_sites') . ' WHERE ' . $db->quoteName('location') . '=' . $db->Quote($defaultLocation);
		$db->setQuery($query);

		$defaultSites = $db->loadObjectList();

		if (!$defaultSites) {
			return true;
		}

		foreach ($defaultSites as $site) {
			$query = 'DELETE FROM ' . $db->quoteName('#__update_sites') . ' WHERE ' . $db->quoteName('update_site_id') . '=' . $db->Quote($site->update_site_id);
			$db->setQuery($query);
			$this->ppQuery($db);

			$query = 'DELETE FROM ' . $db->quoteName('#__update_sites_extensions') . ' WHERE ' . $db->quoteName('update_site_id') . '=' . $db->Quote($site->update_site_id);
			$db->setQuery($query);
			$this->ppQuery($db);
		}
	}

	/**
	 * Update the manifest_cache column to ensure that Joomla knows this is the latest version
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function updateManifestCache()
	{
		$extensionId = $this->getExtensionId();
		$manifest_details = JInstaller::parseXMLInstallFile(JPATH_ROOT. '/administrator/components/com_payplans/payplans.xml');
		$manifest = json_encode($manifest_details);

		// For some Joomla versions, there is no tables/Extension.php
		// Hence, the JTable::getInstance('Extension') will return null
		$table = JTable::getInstance('Extension');

		if ($table) {
			$exists = $table->load($extensionId);

			if (!$exists) {
				return false;
			}

			$table->manifest_cache = $manifest;
			$table->store();
		} else {
			$query	= 'UPDATE '. $db->quoteName('#__extensions')
					. ' SET ' . $db->quoteName('manifest_cache') . ' = ' . $db->Quote($manifest)
					. ' WHERE ' . $db->quoteName('extension_id') . ' = ' . $db->Quote($extensionId);
			$db->setQuery($query);
			$this->ppQuery($db);
		}
	}

	/**
	 * Retrieves the extension id
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getExtensionId()
	{
		$this->engine();

		$db = JFactory::getDBO();

		$query = array();
		$query[] = 'SELECT ' . $db->quoteName('extension_id') . ' FROM ' . $db->quoteName('#__extensions');
		$query[] = 'WHERE ' . $db->quoteName('element') . '=' . $db->Quote('com_payplans');
		$query = implode(' ', $query);

		$db->setQuery($query);

		// Get the extension id
		$extensionId = $db->loadResult();

		return $extensionId;
	}

	/**
	 * Retrieves the main menu item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getMainMenuType()
	{
		$this->engine();

		$db = JFactory::getDBO();

		$query = array();
		$query[] = 'SELECT ' . $db->quoteName('menutype') . ' FROM ' . $db->quoteName('#__menu');
		$query[] = 'WHERE ' . $db->quoteName('home') . '=' . $db->Quote(1);
		$query = implode(' ', $query);

		$db->setQuery($query);
		$menuType = $db->loadResult();

		return $menuType;
	}

}
