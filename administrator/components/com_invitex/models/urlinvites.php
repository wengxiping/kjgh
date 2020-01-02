<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * InviteX invites model.
 *
 * @since  3.0.7
 */
class InvitexModelUrlInvites extends JModelList
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
				'name', 'u.name',
				'email','u.email',
				'inviter_id','iis.inviter_id',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 *
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'u.id';
		}

		$this->setState('list.ordering', $orderCol);

		// Set ordering direction.
		$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}

		$this->setState('list.direction', $listOrder);

		// Load the search.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the inviter
		$inviter = $app->getUserStateFromRequest($this->context . '.filter.urlinviter', 'filter_urlinviter', '', 'string');
		$this->setState('filter.urlinviter', $inviter);

		// List state information.
		parent::populateState('u.id');
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
		$inviter = $this->getState('filter.urlinviter');
		$search = $this->getState('filter.search');
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		// Load results in descending order if der is no ordering
		if (empty($orderDirn))
		{
			$orderDirn = 'DESC';
		}

		$columnArray = array('u.name', 'u.email', 'iis.inviter_id');

		// Select the required fields from the table.
		$query->select($db->quoteName($columnArray));
		$query->from($db->quoteName('#__users', 'u') . ' , ' . $db->quoteName('#__invitex_invite_success', 'iis'));
		$query->where($db->quoteName('u.id') . ' = ' . $db->quoteName('iis.invitee_id'));

		// Filter by inviter.
		if (!empty($inviter))
		{
			$query->where($db->quoteName('iis.inviter_id') . ' = ' . $db->quote($inviter));
		}

		// Filter by search.
		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where($db->quoteName('u.email') . ' LIKE ' . $search);
			$query->where($db->quoteName('u.name') . ' LIKE ' . $search);
		}

		// Add the list ordering clause.
		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
