<?php
/**
 * @version     SVN: <svn_id>
 * @package     Invitex
 * @subpackage  mod_inviters
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * model for top inviters
 *
 * @package     Invitex
 * @subpackage  mod_inviter
 * @since       3.1.4
 */
class InvitexModelTopinviters extends JModelList
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
				'username', 'u.username',
				'total_sent','SUM(ii.invites_count) as total_sent',
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

		// Load the parameters.
		$params = JComponentHelper::getParams('com_invitex');
		$this->setState('params', $params);
		$input = JFactory::getApplication()->input;
		$post = $input->post->getArray();

		if (isset($post['todate']))
		{
			$to_date = $post['todate'];
		}
		else
		{
			$to_date = date('Y-m-d');
		}

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$this->setState('filter.fromdate', $from_date);
		$this->setState('filter.todate', $to_date);

		// Filter inviter.
		$inviter = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $inviter);

		// List state information.
		parent::populateState('invites_count', 'desc');
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
		$fromdate = $this->getState('filter.fromdate');
		$todate = $this->getState('filter.todate');

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('u.username,u.id,SUM(ii.invites_count) as total_sent, COUNT(IF(iie.invitee_id > "1",1,NULL)) as registered');
		$query->from('`#__users` AS u');
		$query->innerjoin('#__invitex_imports AS ii on ii.inviter_id =u.id');
		$query->join('LEFT', '#__invitex_imports_emails AS iie on iie.import_id=ii.id');
		$query->where('ii.invites_count>0');

		if (strtotime($fromdate) == strtotime($todate) or (strtotime($todate) == strtotime(date('Y-m-d'))))
		{
			// Add 1 day to to_date
			$todate = date('Y-m-d', strtotime($todate . ' + 1 day'));
		}

		if ($fromdate)
		{
			$query->where(' ii.date >= ' . strtotime($fromdate));
		}

		if ($todate)
		{
			$query->where(' ii.date <= ' . strtotime($todate));
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('( ii.message LIKE ' . $search . ' ) or ( u.username LIKE ' . $search . ' ) ');
		}

		$query->group('u.id');

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
		$db    = $this->getDbo();
		$items = parent::getItems();

		foreach ($items as $k => $v)
		{
			$query = "";

			if ($v->id)
			{
				$items[$k]->total_sent = $this->invhelperObj->getInvitesSent($v->id);
			}

			$query = "SELECT COUNT(invitee_id) as count
			FROM #__invitex_imports_emails
			WHERE inviter_id=$v->id && invitee_id<>0 && friend_count<>0 ";
			$db->setQuery($query);

			$results = $db->loadResult();

			if (!empty($results))
			{
				$items[$k]->acc	= $results;
			}
			else
			{
				$items[$k]->acc	= '0';
			}

			$query1 = "SELECT sum(click_count)
			FROM #__invitex_imports_emails
			WHERE inviter_id=$v->id";
			$db->setQuery($query1);

			$results = $db->loadResult();

			if (!empty($results))
			{
				$items[$k]->click = $results;
			}
			else
			{
				$items[$k]->click	= '0';
			}
		}

		return $items;
	}
}
