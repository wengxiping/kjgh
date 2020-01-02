<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Invite types model
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class InvitexModeltypes extends JModelList
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
			'id' => 'id',
			'name' => 'name',
			'internal_name' => 'internal_name'
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

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_invitex');
		$this->setState('params', $params);
		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		// List state information.
		parent::populateState('id', 'desc');
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
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('*');
		$query->from('#__invitex_types');

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('( name LIKE  ' . $search . ' ) OR  ( internal_name LIKE  ' . $search . ' )');
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
	 * Method to get a list of types.
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
	 * Function to get invite type data
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function getTypedata()
	{
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;
		$typeId = $input->get('type_id');
		$query = "SELECT * FROM #__invitex_types where id=" . $typeId;
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Function to save invite type
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function store()
	{
		global $mainframe;
		$db = JFactory::getDbo();
		$data = new stdClass;
		$input = JFactory::getApplication()->input;
		$inv_config = $input->post->get('type', '', 'RAW');

		if ($inv_config)
		{
			foreach ($inv_config as $name => $value)
			{
				if (is_array($value))
				{
					$value = implode(',', $value);
				}

				$data->$name = $value;
			}
		}

		if (!$input->post->get('edit'))
		{
			$data->id = '';
			$db->insertObject('#__invitex_types', $data, 'id');

			$invhelperObj = new cominvitexHelper;
			$itemid = $invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
			$invite_type = $data->id;

			$link = "index.php?option=com_invitex&view=invites&Itemid=" . $itemid
			. "&invite_type=" . $invite_type . "&invite_url=&catch_action=&invite_anywhere=1&tmpl=component";
			$widget = "\$link='" . $link . "';";
			$widget .= "JHTML::_('behavior.modal', 'a.modal');";
			$widget .= htmlspecialchars("<a href='" . $link . "'rel='{handler: 'iframe', size: {x:600, y: 600}}' class='modal'>Invite Anywhere</a>");

			$query = "UPDATE `#__invitex_types` SET `widget` = \"$widget\" WHERE `id` = '$invite_type' LIMIT 1";

			$db->setQuery($query);
			$db->query();
		}
		else
		{
			$data->id = $input->post->get('edit');

			if (!$db->updateObject('#__invitex_types', $data, 'id'))
			{
					echo $db->stderr();

					return false;
			}
		}

		return true;
	}

	/**
	 * Function to get invite methods
	 *
	 * @return  HTML
	 *
	 * @since   1.0
	 */
	public function getmethods_multiselect()
	{
		$opt = $inv_methods = $config_methods = array();
		$inv_methods['manual'] = JText::_('INV_METHOD_MANUAL');
		$oi_path = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';

		if (JFile::exists($oi_path))
		{
			$inv_methods['oi_email'] = JText::_('INV_METHOD_OI_EMAIL');
			$inv_methods['oi_social'] = JText::_('INV_METHOD_OI_SOCIAL');
		}

		$inv_methods['other_tools'] = JText::_('INV_METHOD_OTHER_TOOLS');
		$inv_methods['inv_by_url'] = JText::_('INV_METHOD_BY_URL');
		$inv_methods['social_apis'] = JText::_('INV_METHOD_SOCIAL_APIS');
		$inv_methods['email_apis'] = JText::_('INV_METHOD_EMAIL_APIS');
		$inv_methods['js_messaging'] = JText::_('INV_METHOD_JS_MESSAGING');
		$inv_methods['sms_apis'] = JText::_('INV_METHOD_SMS_APIS');
		$input = JFactory::getApplication()->input;
		$typeId = $input->get('type_id');

		if ($typeId)
		{
			$type_data = $this->getTypedata();
			$config_methods = explode(',', $type_data->invite_methods);

			foreach ($config_methods as $m)
			{
				if (isset($inv_methods[$m]))
				{
					$opt[] = JHTML::_('select.option', $m, $inv_methods[$m]);
					unset($inv_methods[$m]);
				}
			}
		}

		if ($inv_methods)
		{
			foreach ($inv_methods as $v => $t)
			{
				$opt[] = JHTML::_('select.option', $v, $t);
			}
		}

		return JHTML::_('select.genericlist', $opt, 'type[invite_methods][]', 'class="inputbox" multiple="multiple"', 'value', 'text', $config_methods);
	}

	/**
	 * Function to return list of the 'techjoomlaAPI' plugins
	 *
	 * @return  Object list
	 *
	 * @since   1.0
	 */
	public function getAPIpluginData()
	{
		$query = "SELECT extension_id as id,name,element,enabled as published FROM #__extensions WHERE enabled=1 AND folder ='techjoomlaAPI'";

		$this->_db->setQuery($query);

		return $this->_db->loadobjectList();
	}

	/**
	 * Function to return list of the 'emailalert' plugins
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getPluginNames()
	{
		// FIRST GET THE EMAIL-ALERTS RELATED PLUGINS FRM THE `jos_plugins` TABLE
		$this->_db->setQuery('SELECT element FROM #__extensions WHERE folder = \'emailalerts\'  AND enabled = 1');

		$email_alert_plugins_array = $this->_db->loadColumn();

		return  $email_alert_plugins_array;
	}

	/**
	 * Function to return description of the 'emailalert' plugins from the XML file
	 *
	 * @param   ARRAY  $plugin_array  contains names of the 'emailalert' plugins
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getPluginDescriptionFromXML($plugin_array)
	{
		$plugin_description_array = array();

		$i = 0;

		if ($plugin_array)
		{
			foreach ($plugin_array as $emailalert_plugin)
			{
				$data = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . '/plugins/emailalerts/' . $emailalert_plugin . '/' . $emailalert_plugin . '.xml');

				// Store it in the array
				$plugin_description_array[$i++] = $data['description'];
			}
		}

		// Return the array
		return $plugin_description_array;
	}
}
