<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/tables/table');

class PayplansTablePlan extends PayplansTable
{
	public $plan_id = null;
	public $title = null;
	public $published = null;
	public $visible = null;
	public $ordering = null;
	public $checked_out = null;
	public $checked_out_time = null;
	public $modified_date = null;
	public $description = null;
	public $details = null;
	public $params = null;

	/**
	 * Class Constructor
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function __construct($db)
	{
		parent::__construct('#__payplans_plan', 'plan_id', $db);
	}

	public function saveOrder()
	{
		$db = PP::db();

		$query = "select max(ordering) from `#__payplans_plan`";
		$db->setQuery($query);

		$max = $db->loadResult();
		$max = (int) $max;

		$this->ordering = $max + 1;
		$this->store();

		return true;
	}

	/**
	 * Allow caller to publish the group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function publish($items = array(), $state = 1, $userId = 0)
	{
		$this->published = 1;
		$this->store();

		return true;
	}

	/**
	 * Allow caller to unpublish the group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function unpublish($items = array())
	{
		$this->published = 0;
		$this->store();

		return true;
	}

	/**
	 * Allow caller to set group visible
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function visible($state = 1)
	{
		$this->visible = $state;
		$this->store();

		return true;
	}
}

