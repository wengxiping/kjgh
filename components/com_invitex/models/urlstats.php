<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * URL invitation stats model
 *
 * @since  1.0.0
 */
class InvitexModelUrlstats extends JModelList
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
			'u.email' => 'u.email',
			'u.name' => 'u.name'
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
	protected function populateState($ordering = 'u.email', $direction = 'asc')
	{
		// Initialise variables.
		$app = JFactory::getApplication('site');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Filter inviter.
		$invite_status = $app->getUserStateFromRequest($this->context . '.filter.invite_status', 'filter_invite_status', '', 'string');
		$this->setState('filter.invite_status', $invite_status);

		// List state information.
		parent::populateState($ordering, $direction);
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
		$uid = $this->invhelperObj->getUserID();
		$user = JFactory::getUser($uid);

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(" u.name,u.email");
		$query->from(' #__users as u,#__invitex_invite_success as iis');
		$query->where('iis.inviter_id=' . $user->id);
		$query->where("u.id=iis.invitee_id");

		$search = $this->getState('filter.search');

		// Filter by search in title.
		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('((u.email LIKE ' . $search . ' ) OR (u.name LIKE ' . $search . ' ))');
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

		return $items;
	}

	/**
	 * Function to get status dropbox html
	 *
	 * @return  HTML  state dropdown
	 *
	 * @since   1.6.1
	 */
	public function getStatus()
	{
		$default = $this->getState('filter.invite_status');
		$options[] = JHtml::_('select.option', '0', JText::_('FILTER_BY'));
		$options[] = JHtml::_('select.option', '1', JText::_('REGISTERED'));
		$options[] = JHtml::_('select.option', 2, JText::_('NOT_REGISTERED'));
		$attributes = 'class="input-medium" size="1" onchange="document.adminForm.submit();" ';

		$this->dropdown = JHtml::_('select.genericlist', $options, 'filter_invite_status', $attributes, 'value', 'text', $default);

		return $this->dropdown;
	}
}
