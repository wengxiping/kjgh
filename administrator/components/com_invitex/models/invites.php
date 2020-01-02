<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * InviteX invites model.
 *
 * @since  1.6
 */
class InvitexModelinvites extends JModelList
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
				'imp_id', 'import_email.id',
				'message', 'ii.message',
				'sent', 'import_email.sent',
				'accepted', 'import_email.invitee_email',
				'invitee_email', 'import_email.invitee_email',
				'invitee_name', 'import_email.invitee_name',
				'inviter_name', 'u.name',
				'expires', 'expires',
				'provider_email','provider_email',
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

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'imp_id';
		}

		$this->setState('list.ordering', $orderCol);

		// Set ordering direction.
		$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Filter provider.
		$provider_email = $app->getUserStateFromRequest($this->context . '.filter.provider_email', 'filter_provider_email', '', 'string');
		$this->setState('filter.provider_email', $provider_email);

		// Filter inviter.
		$inviter = (INT) $app->getUserStateFromRequest($this->context . '.filter.inviter', 'filter_inviter', '', 'string');
		$this->setState('filter.inviter', $inviter);

		// Filter provider.
		$accepted_status = $app->getUserStateFromRequest($this->context . '.filter.accepted_status', 'filter_accepted_status', '', 'string');
		$this->setState('filter.accepted_status', $accepted_status);

		// Filter provider.
		$sent_status = $app->getUserStateFromRequest($this->context . '.filter.sent_status', 'filter_sent_status', '', 'string');
		$this->setState('filter.sent_status', $sent_status);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_invitex');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('imp_id', 'asc');
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
		$query->select('import_email.id as imp_id,ii.provider_email,import_email.import_id,ii.message, u.username as inviter_name,import_email.*,u.id');
		$query->from('`#__users` AS u');
		$query->rightJoin('`#__invitex_imports` AS ii on ii.inviter_id =u.id ');
		$query->innerJoin('#__invitex_imports_emails as import_email on import_email.import_id=ii.id  ');

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('( ii.message LIKE ' . $search . ' ) or ( u.username LIKE '
			. $search . ' ) or ( import_email.invitee_name LIKE ' . $search . ' ) or ( import_email.invitee_name LIKE ' . $search . ' )');
		}

		// Filter by Inviter Name
		$inviter = (INT) $this->getState('filter.inviter');

		if (!empty($inviter))
		{
			$query->where('( ii.inviter_id = ' . $inviter . ' ) ');
		}

		// Filter by Inviter Name
		$provider_email = $this->getState('filter.provider_email');

		if ($provider_email != "-1" and $provider_email != "" and $provider_email != "0")
		{
			$query->where('( ii.provider_email LIKE "' . $provider_email . '" ) ');
		}

		// Filter by Accepted Status
		$accepted_status = $this->getState('filter.accepted_status');

		if (!empty($accepted_status))
		{
			if ($accepted_status == 1)
			{
				$query->where('( import_email.invitee_id<>0 AND  friend_count<>0) ');
			}
			elseif ($accepted_status == 2)
			{
				$query->where('( import_email.invitee_id=0 AND  friend_count=0) ');
			}
		}

		// Filter by Accepted Status
		$sent_status = $this->getState('filter.sent_status');

		if (!empty($sent_status))
		{
			if ($sent_status == 1)
			{
				$query->where('( import_email.sent=1) ');
			}
			elseif ($sent_status == 2)
			{
				$query->where('(( import_email.sent=0 )) ');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			if ( $orderCol == 'inviter_name')
			{
				$orderCol = $orderCol . ", guest";
			}

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
	 * Method to delete Invites from Invitex
	 *
	 * @param   array  $rowid_arr  ids
	 * @param   array  $importsid  ids
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function remove($rowid_arr, $importsid)
	{
		$db = JFactory::getDbo();

		JLoader::import('administrator.components.com_invitex.tables.invites', JPATH_SITE);

		if (!empty($rowid_arr))
		{
			foreach ($rowid_arr as $import_emails_id)
			{
				$invitesTable = new TableInvitesEmails($db);
				$invitesTable->load(array('id' => $import_emails_id));
				$invitationData = $invitesTable->getProperties();

				// Plugin trigger on before delete invitation
				JPluginHelper::importPlugin('actionlog');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('invitexOnBeforeDeleteInvitation', array($invitationData));

				// Import id for the specific email
				$import_id	=	$importsid[$import_emails_id];

				// Get the invites_cnt form imports table and update it
				$query = $db->getQuery(true);
				$query->select('invites_count');
				$query->from('`#__invitex_imports`');
				$query->where('id=' . $import_id);
				$db->setQuery($query);
				$invites_cnt = $db->loadResult();

				$update_data = new stdClass;
				$update_data->id	=	$import_id;
				$update_data->invites_count = $invites_cnt - 1;

				if (!$db->updateObject('#__invitex_imports', $update_data, 'id'))
				{
					echo $this->setError($db->getErrorMsg());

					return false;
				}

				// Delete the enties from #__invitex_imports_emails
				$query = $db->getQuery(true);
				$query = "DELETE FROM #__invitex_imports_emails WHERE id IN('" . $import_emails_id . "')";
				$db->setQuery($query);

				if (!$db->execute())
				{
					echo $this->setError($db->getErrorMsg());

					return false;
				}

				$query = $db->getQuery(true);

				// Delete all custom keys for user 1001.
				$conditions = array(
					$db->quoteName('id') . ' = ' . $import_emails_id
				);

				$query->delete($db->quoteName('#__invitex_imports_emails'));
				$query->where($conditions);

				$db->setQuery($query);

				if (!$db->query())
				{
					echo $this->setError($db->getErrorMsg());

					return false;
				}

				// Plugin trigger on after delete invitation
				JPluginHelper::importPlugin('actionlog');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('invitexOnAfterDeleteInvitation', array($invitationData));
			}

			// Delete the entries from the #_imports table inf there invites_cnt is updated to 0
			$query = $db->getQuery(true);
			$conditions = array(
				$db->quoteName('invites_count') . ' = 0'
			);

			$query->delete($db->quoteName('#__invitex_imports'));
			$query->where($conditions);

			$db->setQuery($query);

			if (!$db->query())
			{
				echo $this->setError($db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get providers
	 *
	 * @return array
	 */
	public function getInviters()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(' . $db->quoteName('u.id') . ')');
		$query->select($db->quoteName('u.name'));
		$query->from($db->quoteName('#__users', 'u'));
		$query->join('INNER', $db->quoteName('#__invitex_imports', 'ii')
		. ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('ii.inviter_id') . ')');
		$db->setQuery($query);

		return $db->loadAssocList('id');
	}

	/**
	 * Method to get providers
	 *
	 * @return array
	 */
	public function getProviders()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(' . $db->quoteName('provider_email') . ')');
		$query->from($db->quoteName('#__invitex_imports'));
		$query->where($db->quoteName('provider_email') . ' <> ' . $db->quote(''));
		$db->setQuery($query);

		return $db->loadAssocList('provider_email');
	}
}
