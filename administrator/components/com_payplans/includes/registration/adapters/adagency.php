<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPRegistrationAdagency extends PPRegistrationAbstract
{
	public $type = 'adagency';
	public $url = null;

	protected $file = JPATH_ROOT . '/components/com_adagency/adagency.php';

	public function __construct()
	{
		$this->url = 'index.php?option=com_adagency&controller=adagencyAdvertisers&task=edit&fromPayplans=1';

		parent::__construct();
	}

	/**
	 * Set necessary parameters before redirecting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function beforeStartRedirection()
	{
		if (!$this->exists()) {
			PP::info()->set('Adagency is not installed on the site. Please update your settings to pick the correct registration integrations', 'error');

			$key = $this->input->get('invoice_key', 0 ,'');
			$id = PP::getIdFromKey($key);

			if ($id) {
				$invoice = PP::invoice($id);
				return $this->redirectToCheckout($invoice);
			}

			return $this->redirectToPlans();
		}
		
		$this->session->set('fromPayplans', 1);
	}

	/**
	 * Determines if adagency exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_adagency');
		$exists = JFile::exists($this->file);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the create account portion
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function html(PPInvoice $invoice)
	{
		$url = $this->getRegistrationUrl($invoice);
		$userId = $this->getNewUserId();

		$this->set('userId', $userId);
		$this->set('url', $url);

		$output = $this->display('default');

		return $output;
	}

	/**
	 * Determines if this is currently the registration url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRegistrationUrl()
	{
		$option = $this->input->get('option', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$task = $this->input->get('task', '', 'default');

		if ($option == 'com_adagency' && $controller == 'adagencyAdvertisers' && $task == 'edit') {
			return true;
		}

		return false;	 
	}
	
	public function isOnRegistrationCompletePage()
	{
		return true;
	}

	/**
	 * Check if user tries to bypass payplans plan page and register via adagency
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'default');
		$controller = $this->input->get('controller', '', 'default');

		$fromPayplans = $this->session->get('fromPayplans', 0);
		
		if ($option == 'com_adagency' && $task == 'edit' && $controller == 'adagencyAdvertisers' && !$fromPayplans) {
			return $this->redirectToPlans();
		}
	}
}