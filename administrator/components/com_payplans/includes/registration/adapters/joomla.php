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

class PPRegistrationJoomla extends PPRegistrationAbstract
{
	public $type = 'joomla';
	public $url = null;

	public function __construct()
	{
		$this->url = 'index.php?option=com_users&view=registration&fromPayplans=1';

		parent::__construct();
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
		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'default');

		if ($option == 'com_users' && $view == 'registration' && !$task) {
			return true;
		}

		return false;	 
	}
	
	public function isOnRegistrationCompletePage()
	{
		return true;
	}

	/**
	 * Check if user tries to bypass payplans plan page and register via com_users
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck()
	{
	}
}

