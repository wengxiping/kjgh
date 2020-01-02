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

class PayplansTableGroup extends PayplansTable
{
	public $group_id = null;
	public $title = null;
	public $parent = null;
	public $published = null;
	public $visible = null;
	public $ordering = null;
	public $description = null;
	public $params = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_group', 'group_id', $db);
	}

	/**
	 * Override parent's store implementation
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function store($updateNulls = false, $new = false)
	{
		if (empty($this->ordering)) {
			$this->ordering = $this->getNextOrder();
		}

		$state = parent::store($updateNulls);

		return $state;
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
	}
}
