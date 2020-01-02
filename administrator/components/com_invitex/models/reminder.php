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
 * InviteX reminder model
 *
 * @since  1.6.1
 */
class InvitexModelReminder extends JModelList
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
				'e.invitee_email' => 'e.invitee_email',
				'e.modified' => 'e.modified'
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

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_invitex');
		$this->setState('params', $params);
		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

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

		// List state information.
		parent::populateState('e.modified', 'desc');
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
		$query->select('e.invitee_email,e.id,e.sent_at,e.modified ');
		$query->from(' #__invitex_imports_emails AS e');
		$query->leftjoin('#__invitex_imports AS i ON e.import_id = i.id');
		$query->leftjoin('#__users AS u ON i.inviter_id = u.id');
		$query->where("i.message_type='email'");
		$query->where('e.unsubscribe=0');
		$query->where('e.sent=1');
		$query->where('e.invitee_id=0');

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('( e.invitee_email LIKE ' . $search . ' )');
		}

		if (strtotime($fromdate) == strtotime($todate) or (strtotime($todate) == strtotime(date('Y-m-d'))))
		{
			// Add 1 day to to_date
			$todate = date('Y-m-d', strtotime($todate . ' + 1 day'));
		}

		if ($fromdate)
		{
			$query->where(' e.sent_at > ' . strtotime($fromdate));
		}

		if ($todate )
		{
			$query->where(' e.sent_at < ' . strtotime($todate));
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
		$db    = $this->getDbo();
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Function to send reminder
	 *
	 * @return  Boolean
	 *
	 * @since   1.6.1
	 */
	public function send_reminder()
	{
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		JLoader::import('administrator.components.com_invitex.tables.invites', JPATH_SITE);

		foreach ($post['remind_emails'] as $iie_id)
		{
			$invitesTable = new TableInvitesEmails($db);
			$invitesTable->load(array('id' => $iie_id));
			$invitationData = $invitesTable->getProperties();

			// Plugin trigger on before send reminder
			JPluginHelper::importPlugin('actionlog');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('invitexOnBeforeSendReminder', array($invitationData));

			$query = "select remind_count from #__invitex_imports_emails WHERE id='$iie_id'";
			$db->setQuery($query);
			$rem = $db->loadResult();
			$obj = new stdClass;
			$obj->id = $iie_id;
			$obj->remind = '1';
			$obj->modified = time();

			if (!$db->updateObject('#__invitex_imports_emails', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			// Plugin trigger on before send reminder
			JPluginHelper::importPlugin('actionlog');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('invitexOnAfterSendReminder', array($invitationData));
		}

		return true;
	}
}
