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

class PPLock extends PayPlans
{
	public $result = null;
	public $name = '';
	public $timeout = 0;
	
	public function __construct($name = '', $timeout = 0)
	{
		$this->name = $name;
		$this->timeout = $timeout;
	}

	/**
	 * 0 for timeout, 1 for success
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function create($timeout = 0)
	{
		if (!$this->isFree()) {
			return false;
		}

		$db = PP::db();
		$query = 'SELECT GET_LOCK(' . $db->Quote($this->name) . ', ' . $db->Quote($timeout) . ')';
		$db->setQuery($query);

		$state = $db->loadResult() ? true : false;

		return $state;
	}
	
	/**
	 * Releases a lock
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function release()
	{
		$db = PP::db();
		$query = 'SELECT RELEASE_LOCK(' . $db->Quote($this->name) . ')';
		$db->setQuery($query);

		$state = $db->loadResult() ? true : false;

		return $state;
	}
	
	/**
	 * Determines if the process is free
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isFree()
	{
		$db = PP::db();
		$query = 'SELECT IS_FREE_LOCK(' . $db->Quote($this->name) . ')';
		$db->setQuery($query);

		$result = $db->loadResult() ? true : false;

		return $result;
	}

	/**
	 * Determi
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUsed()
	{
		$db = PP::db();
		$query = 'SELECT IS_USED_LOCK(' . $this->name . ')';
		$db->setQuery($query);

		$result = $db->loadResult();

		return $result;
	}
}