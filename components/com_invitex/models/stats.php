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
 * View class for a list of contacts.
 *
 * @package     Invitex
 * @subpackage  com_invitex
 * @since       2.2
 */
class InvitexModelStats extends JModelList
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
			'iie.invitee_email' => 'iie.invitee_email'  ,
			'iie.invitee_name' => 'iie.invitee_name'
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
		$app = JFactory::getApplication('site');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Filter inviter.
		$invite_status = $app->getUserStateFromRequest($this->context . '.filter.invite_status', 'filter_invite_status', '', 'string');
		$this->setState('filter.invite_status', $invite_status);

		// List state information.
		parent::populateState('invitee_email', 'asc');
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
		$uid 		= $this->invhelperObj->getUserID();
		$user     = JFactory::getUser($uid);

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(" iie.invitee_email,iie.invitee_name,iie.click_count,
		 iie.expires,ii.date,if(iie.invitee_id<>0,'Yes','No') AS accepted, if(unsubscribe<>0,'Yes','No') AS unsubscribe ");
		$query->from('#__invitex_imports_emails AS iie');
		$query->leftJoin(' #__invitex_imports AS ii ON ii.id=iie.import_id ');
		$query->where('iie.inviter_id=' . $user->id);
		$query->where("ii.provider_email NOT IN('plug_techjoomlaAPI_orkut')");

		$search = $this->getState('filter.search');
		$status = $this->getState('filter.invite_status');

		if ($status == "1")
		{
			$query->where("(iie.invitee_id<>0)");
		}

		if ($status == "2")
		{
			$query->where("(iie.invitee_id=0)");
		}

		// Filter by search in title.
		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('((iie.invitee_email LIKE ' . $search . ' ) OR (iie.invitee_name LIKE ' . $search . ' ))');
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
	 * Method to get a state of products.
	 *
	 * @return  dropdown list
	 *
	 * @since   1.6.1
	 */
	public function getStatus()
	{
		$default = $this->getState('filter.invite_status');
		$options[] = JHtml::_('select.option', '0', JText::_('FILTER_BY'));
		$options[] = JHtml::_('select.option', '1', JText::_('REGISTERED'));
		$options[] = JHtml::_('select.option', 2, JText::_('NOT_REGISTERED'));
		$this->dropdown = JHtml::_(
			'select.genericlist',
			$options,
			'filter_invite_status',
			'class="input-medium" size="1" onchange="document.adminForm.submit();" ',
			'value',
			'text',
			$default
		);

		return $this->dropdown;
	}
}
