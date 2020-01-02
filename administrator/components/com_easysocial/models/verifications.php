<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

class EasySocialModelVerifications extends EasySocialModel
{
	private $data = null;

	public function __construct($config = array())
	{
		$this->displayOptions = array();
		parent::__construct('verifications', $config);
	}

	/**
	 * Populates the state
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();
	}

	/**
	 * Retrieves a list of items that has submitted for verifications
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getVerificationList($type = SOCIAL_TYPE_USER)
	{
		$db = $this->db;
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT a.* FROM ' . $db->qn('#__social_verification_requests') . ' AS a';

		$query[] = 'INNER JOIN ' . $db->qn('#__users') . ' AS b';
		$query[] = 'ON a.' . $db->qn('uid') . ' = b.' . $db->qn('id');

		$query[] = 'WHERE a.' . $db->qn('type') . '=' . $db->Quote($type);
		$query[] = 'AND a.' . $db->qn('state') . '=' . $db->Quote(ES_VERIFICATION_REQUEST);

		$search = $this->getState('search');

		if ($search) {
			$query[] = 'AND (';

			$query[] = 'b.' . $db->qn('name') . ' LIKE ' . $db->Quote('%' . $search . '%');
			$query[] = 'OR';
			$query[] = 'b.' . $db->qn('username') . ' LIKE ' . $db->Quote('%' . $search . '%');
			$query[] = 'OR';
			$query[] = 'b.' . $db->qn('email') . ' LIKE ' . $db->Quote('%' . $search . '%');


			$query[] = ')';
		}

		$query = implode(' ', $query);

		$this->setTotal($query, true);

		// Get the list of users
		$items = $this->getData($query, true);

		return $items;
	}
}
