<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.modellist');

/**
 * InviteX unsubscribe list model
 *
 * @since  1.6.1
 */
class InvitexModelunsubscribe_List extends JModelList
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
				'invitee_email', 'invitee_email',
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
	 * @since   1.6.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState('invitee_email', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6.1
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('distinct(invitee_email),id');
		$query->from('`#__invitex_imports_emails`');
		$query->where('unsubscribe=1');

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('( LOWER(invitee_email) LIKE ' . $search . ' )');
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
	 * Method to get a unsubscribe list.
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
	 * function to add to users to unsubscribe list
	 *
	 * @param   ARRAY   $post    post data
	 * @param   STRING  $action  action to be performed
	 *
	 * @return   Files array
	 *
	 * @since  1.6.1
	 */
	public function manage_UnsubList($post, $action)
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDbo();

		$query = "SELECT distinct(invitee_email) FROM `#__invitex_imports_emails`";
		$db->setQuery($query);

		if (JVERSION < 3.0)
		{
			$results = $db->loadResultArray();
		}
		else
		{
			$results = $db->loadColumn();
		}

		if ($action == 'add')
		{
			$msg = JText::_("UNSUB_LIST_UPDATE_SUCCESS");
			$emils_str = $post->get('unsub_emails_add', '', 'STRING');
			$email_array = explode(',', $emils_str);
			$email_array = array_map('trim', $email_array);

			foreach ($email_array as $email)
			{
				// Plugin trigger on before adding email id to unsubscribers list
				JPluginHelper::importPlugin('actionlog');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('invitexOnBeforeUnsubscribe', array($email));

				if (in_array($email, $results))
				{
					$update_data = new stdClass;
					$update_data->invitee_email = $email;
					$update_data->unsubscribe = '1';
					$db->updateObject('#__invitex_imports_emails', $update_data, 'invitee_email');
				}
				else
				{
					$insert_data = new stdClass;
					$insert_data->inviter_id = JFactory::getUser()->id;
					$insert_data->invitee_email = $email;
					$insert_data->unsubscribe = '1';
					$db->insertObject('#__invitex_imports_emails', $insert_data, 'id');
				}

				// Plugin trigger on after adding email id to unsubscribers list
				JPluginHelper::importPlugin('actionlog');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('invitexOnAfterUnsubscribe', array($email));
			}
		}
		elseif ($action == 'remove')
		{
			$emails = $post->get('cid', '', 'ARRAY');

			foreach ($emails as $email)
			{
				// Plugin trigger on before user re-subscribe
				JPluginHelper::importPlugin('actionlog');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('invitexOnBeforeUserResubscribe', array($email));

				if (in_array($email, $results))
				{
					$update_data = new stdClass;
					$update_data->invitee_email = $email;
					$update_data->unsubscribe = '0';
					$db->updateObject('#__invitex_imports_emails', $update_data, 'invitee_email');
				}

				// Plugin trigger on after user re-subscribe
				JPluginHelper::importPlugin('actionlog');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('invitexOnAfterUserResubscribe', array($email));
			}
		}

		$link = 'index.php?option=com_invitex&view=unsubscribe_list';
		$mainframe->redirect($link, $msg);
	}
}
