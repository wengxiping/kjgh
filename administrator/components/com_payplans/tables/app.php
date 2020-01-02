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

PP::import('admin:/tables/table');

class PayplansTableApp extends PayplansTable
{
	public $app_id = null;
	public $group = null;
	public $title  = null;
	public $type = null;
	public $description	= null;
	public $core_params = null;
	public $app_params = null;
	public $ordering = null;
	public $published = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_app', 'app_id', $db);
	}

	/**
	 * Allow caller to publish the plan modifier
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function publish($items = array(), $state = 1, $userId = 0)
	{
		$this->published = 1;
		$this->store();
	}

	/**
	 * Allow caller to unpublish the plan modifier
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function unpublish($items = array())
	{
		$this->published = 0;
		$this->store();
	}
}
