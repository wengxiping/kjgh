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

class PPRegistrationJFBConnect extends PPRegistrationAbstract
{	
	public $type = 'jfbconnect';
	public $url = null;

	protected $file = JPATH_ROOT . '/components/com_jfbconnect/jfbconnect.php';

	public function __construct()
	{
		parent::__construct();

		$this->url = 'index.php?option=com_users&view=registration&fromPayplans=1';
	}

	/**
	 * Determines if JFB Connect exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{ 
		$enabled = JComponentHelper::isEnabled('com_jfbconnect');
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
		if (!$this->exists()) {
			return;
		}

		$userId = $this->getNewUserId();
		$this->set('userId', $userId);

		$output = $this->display('default');

		return $output;
	}

	/**
	 * Determines if the current url is a registration page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRegistrationUrl()
	{
		return false;
	}

	public function isOnRegistrationCompletePage()
	{
		return true;
	}
}
