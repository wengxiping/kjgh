<?php
/**
 * @package    Invitex
 *
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Invittaion limits
 *
 * @since  2.2
 */
class InvitexModelInvitation_Limit extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'il.userid' => 'il.userid',
			'u.username' => 'u.username',
			'il.limit' => 'il.limit'
			);
		}

		$this->invhelperObj = new cominvitexHelper;

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_invitex');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('il.userid', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('distinct(il.userid),il.limit,u.username');
		$query->from('#__invitex_invitation_limit AS il ');
		$query->leftjoin('#__users AS u ON il.userid = u.id');

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('( LOWER(u.username) LIKE ' . $search . ' ) or
			 ( il.userid LIKE ' . $search . ' )');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get a list of products.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $k => $v)
		{
				$items[$k]->invitations_sent = $this->invhelperObj->getInvitesSent($v->userid);
		}

		return $items;
	}

	/**
	 * Method to check if each site user is listed in the invitation limit table
	 *
	 * @param   Boolean  $batch  to check if batch is processed
	 *
	 * @return  boolean
	 *
	 * @since   1.6.1
	 */
	public function update_invitation_limit($batch)
	{
		$mainframe = JFactory::getApplication();
		$db =	JFactory::getDbo();
		$input = JFactory::getApplication()->input;

		$batch_inv_limit = $input->get('batch_inv_limit', '', 'INT');
		$inv_limit = $input->get('inv_limit', '', 'ARRAY');
		$cid = $input->get('cid', '', 'ARRAY');

		if ($batch)
		{
			$limit = $batch_inv_limit;

			foreach ($cid as $ind => $u_id)
			{
				$update_data = new stdClass;
				$update_data->userid = $u_id;
				$update_data->limit = $limit;
				$db->updateObject('#__invitex_invitation_limit', $update_data, 'userid');
			}
		}
		else
		{
			foreach ($inv_limit as $u_id => $limit)
			{
					$update_data = new stdClass;
					$update_data->userid = $u_id;
					$update_data->limit = $limit;
					$db->updateObject('#__invitex_invitation_limit', $update_data, 'userid');
			}
		}

		return 1;
	}

	/**
	 * Method to check if each site user is listed in the invitation limit table
	 *
	 * @return  boolean
	 *
	 * @since   1.6.1
	 */
	public function getLimit_installed()
	{
		$db		=	JFactory::getDbo();
		$query = "SELECT distinct(userid) FROM `#__invitex_invitation_limit`";
		$db->setQuery($query);

		if (JVERSION >= 3.0)
		{
				$users_arr = $db->loadColumn();
		}
		else
		{
			$users_arr = $db->loadResultArray();
		}

		if (empty($users_arr))
		{
			return 0;
		}
		else
		{
			return 1;
		}
	}

	/**
	 * Method triggered from installtion screen
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function populateUsers()
	{
		$db		=	JFactory::getDbo();
		$query = "SELECT id FROM `#__users` where block=0";
		$db->setQuery($query);

		if (JVERSION >= 3.0)
		{
			$existing_users = array_map('trim', $db->loadColumn());
		}
		else
		{
			$existing_users = array_map('trim', $db->loadResultArray());
		}

		$query = "SELECT distinct(userid) FROM `#__invitex_invitation_limit`";
		$db->setQuery($query);

		if (JVERSION >= 3.0)
		{
			$users_arr = array_map('trim', $db->loadColumn());
		}
		else
		{
			$users_arr = array_map('trim', $db->loadResultArray());
		}

		$per_user_invitation_limit = $this->invhelperObj->invitex_params->get('per_user_invitation_limit');

		foreach ($existing_users as $u)
		{
				if (!in_array($u, $users_arr))
				{
					$data = new stdClass;
					$data->userid 	=	$u;
					$data->limit = $per_user_invitation_limit;

					if (!$db->insertObject('#__invitex_invitation_limit', $data, 'id'))
					{
						return false;
					}
				}
		}

		return true;
	}
}
