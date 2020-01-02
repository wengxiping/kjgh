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

$app = JFactory::getApplication();
$input = $app->input;
$option = $input->get('option', '', 'default');
$install = $input->get('setup', false, 'bool');

// check if currently is payplans installation.
$installationFile = JPATH_ROOT . '/tmp/payplans.installation';

// Do not load payplans when component is com_installer
if ($option == 'com_installer' || JFile::exists($installationFile) || $install) {
	return true;
}

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';
$exists = JFile::exists($file);

if (!$exists) {
	return;
}

require_once($file);

class plgSystemPayplans extends PPPlugins
{
	public function __construct($event, $options = array())
	{
		parent::__construct($event, $options);

		$this->app = JFactory::getApplication();
		$this->my = PP::user();
		$this->input = $this->app->input;
	}

	/**
	 * Triggered during Joomla's onAfterRoute trigger
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		$uId = PP::session()->get('PP_ACTIVATION_REDIRECTION');

		if ($uId) {

			// clear the session first
			PP::session()->clear('PP_ACTIVATION_REDIRECTION');

			// Get the proper url from config
			$config = PP::config();
			$redirectUrl = $config->get('activation_redirect_url', '');
			
			if ($redirectUrl) {

				// check if the url belong to payplans
				if (stristr($redirectUrl, 'com_payplans') !== false) {
					$redirectUrl = PPR::_($redirectUrl, false);
				} else {
					$redirectUrl = JRoute::_($redirectUrl, false);
				}

				$this->app->redirect($redirectUrl);
				return;
			}
		}

		// Let us do access check
		$this->checkAccess();

		// Process registrations if needed
		if (!$this->app->isAdmin() && !$this->my->id) {
			$registration = PP::registration();
			$registration->onAfterRoute();
		}

		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'cmd');
		$task = $this->input->get('task', '', 'cmd');

		$doc = JFactory::getDocument();

		if ($doc->getType() != 'html') {
			return;
		}

		if ($option != 'com_payplans') {
			return;
		}

		// from Payplans 2.0 payment notification will be
		// processed on payment=>notify rather then order=>notify
		if (($view == 'order') && ($task=='notify')) {
			$this->input->set('view', 'payment');
		}

		return true;
	}

	/**
	 * Triggered by Joomla after dispatching
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterDispatch()
	{
		$option = $this->input->get('option', '', 'default');

		if ($option != 'com_payplans' || $this->my->id == 0) {
			return;
		}

		if (!$this->app->isAdmin()) {
			return;
		}

		$cron = PP::cron();

		// Only show this error on the dashboard view
		$view = $this->input->get('view', '', 'string');
		
		if (!$cron->hasBeenRunning() && $view == 'dashboard') {
			PP::info()->set('COM_PAYPLANS_CRON_IS_NOT_RUNNING_PROPERLY', 'error');
		}

		return true;
	}

	/**
	 * @TODO: Need to remove this. It is a bad idea.
	 *
	 *
	 * Add a image just before </body> tag
	 * which will href to cron trigger.
	 */
	public function onAfterRender()
	{
		//V. IMP. : During uninstallation of Payplans
		// after uninstall this function get executed
		// so prevent it
		$option = JRequest::getVar('option');

		if ($option == 'com_installer') {
			return true;
		}

		// PayPlans was not included and loaded
		if (defined('PAYPLANS_DEFINE_ONSYSTEMSTART')==false){
			return;
		}

		// Only do if configuration say so : expert_run_automatic_cron is set to 1
		$config = PP::config();
		if ($config->get('expert_run_automatic_cron') != 1) {
			return;
		}

		// Only render for HTML output
		if (JFactory::getDocument()->getType() !== 'html' ) { return; }

		//only add if required, then add call back
		$cron = PP::cron();

		if ($cron->shouldRun()) {
			$image = '<img alt="' . JText::_('COM_PAYPLANS_LOGGER_CRON_START', true) . '" src="' . $cron->getImageUrl() . '" style="display: none;" />';

			$body = JResponse::getBody();
			$body = str_replace('</body>', $image . '</body>', $body);

			JResponse::setBody($body);
		}
	}

	/**
	 * Content plugin triggers which needs to be binded to internal plugins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		$args = array($context, &$row, &$params, $page);
		$results = PPEvent::trigger('onContentPrepare', $args);

		return true;
	}

	/**
	 * Content plugin triggers which needs to be binded to internal plugins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		$args = array($context, $article, $isNew);
		$results = PPEvent::trigger('onContentAfterSave', $args);

		return true;
	}

	/**
	 * Triggered when a new user is created. This is to allow us to facilitate user registrations
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterStoreUser($user, $isnew, $success, $msg)
	{
		// Process registration systems
		$registration = PP::registration();
		$registration->onAfterStoreUser($user, $isnew, $success, $message);
	}

	/**
	 * We need to trigger events from PayPlans plugins since they are not exposed to events from Joomla.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterInitialise()
	{
		//trigger system start event after loading of joomla framework
		if (defined('PAYPLANS_DEFINE_ONSYSTEMSTART')==false) {
			
			PP::event()->trigger('onPayplansSystemStart');
			
			define('PAYPLANS_DEFINE_ONSYSTEMSTART', true);
		}
	}

	/**
	 * Triggered before deleting a user. Seems like we need to remove all their orders.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onUserBeforeDelete($user)
	{
		$userId = $user['id'];

		$options = array('buyer_id' => $userId);

		// Delete Orders
		$ordersModel = PP::model('Order');
		$orders = $ordersModel->loadRecords($options);

		if ($orders) {
			foreach ($orders as $order) {
				$order = PP::order($order);
				$order->delete();
			}
		}

		$options = array('user_id' => $userId);

		// Delete invoices
		$invoiceModel = PP::model('Invoice');
		$invoiceModel->deleteMany($options);

		// Delete transactions
		$transactionModel = PP::model('Transaction');
		$transactionModel->deleteMany($options);

		// Delete payments
		$paymentModel = PP::model('Payment');
		$paymentModel->deleteMany($options);

		// Delete Resources
		$resourceModel = PP::model('Resource');
		$resourceModel->deleteMany(array('user_id' => $userId));
	}

	/**
	 * Performs access check
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function checkAccess()
	{
		if ($this->app->isAdmin()) {
			return;
		}
		
		$user = PP::user($this->my->id);

		// Access should never be applied on system administrator
		if ($user->isSiteAdmin()) {
			return;
		}

		// Any App and plugin can handle this event
		$dispatcher = PP::event();
		$args = array($user, array());
		$result = $dispatcher->trigger('onPayplansAccessCheck', $args, '', null);

		// We only trigger this if registration plugin is enabled
		$isPluginEnabled = JPluginHelper::isEnabled('payplans', 'registration');
		
		if ($isPluginEnabled) {
			$registration = PP::registration();
			$registration->onPayplansAccessCheck();
		}

		// is access check failed
		if (in_array(false, $result,true)) {
			$result = $dispatcher->trigger('onPayplansAccessFailed', $args, '', null);
			return false;
		}

		return true;
	}
}
