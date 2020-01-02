<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @copyright  Copyright (C) 2005 - 2018. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * InviteX resend invites model
 *
 * @since  1.6
 */
class InvitexModelResend extends JModelList
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
			'iie.invitee_email' => 'ie.invitee_email'  ,
			'iie.invitee_name' => 'ie.invitee_name'
			);
		}

		$this->invhelperObj = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();

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
		$uid = $this->invhelperObj->getUserID();
		$user = JFactory::getUser($uid);

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$subQuery = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($db->quoteName(array("ie.invitee_email", "ie.invitee_name")));
		$query->from($db->quoteName('#__invitex_imports_emails', 'ie'));
		$query->join('INNER', $db->qn('#__invitex_imports', 'i') . ' ON (' . $db->qn('ie.import_id') . ' = ' . $db->qn('i.id') . ')');
		$query->where("(" . $db->qn('i.invite_type') . " <= 0  OR " . $db->qn('i.invite_type') . " = NULL" . ")");
		$query->where($db->qn("ie.inviter_id") . "=" . $uid);
		$query->where(
		$db->qn("ie.invitee_email") . " regexp '^[0-9a-z_\.-]+@([,]|([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[\,0-9a-z]\.)+[a-z]{2,4})$'"
		);

		$query->where($db->qn("ie.sent") . " = 1");
		$query->where($db->qn("ie.invitee_id") . " = 0");
		$query->where($db->qn("ie.unsubscribe") . " = 0");

		$subQuery->select($db->qn("email"));
		$subQuery->from($db->qn("#__users"));
		$subQuery->where($db->qn("email") . " is not null ");

		$query->where($db->qn("ie.invitee_email") . " NOT IN (" . $subQuery . ")");
		$query->group("invitee_email");
		$search = $this->getState('filter.search');
		$status = $this->getState('filter.invite_status');

		// Filter by search in title.
		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where("(" . $db->qn("ie.invitee_email") . " LIKE " . $search . " OR " . $db->qn("ie.invitee_name") . " LIKE " . $search . ")");
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
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
	 * Method to get status list dropdown
	 *
	 * @return  HTML
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

		$this->dropdown = JHtml::_(
		'select.genericlist', $options, 'filter_invite_status', $attributes, 'value', 'text', $default
		);

		return $this->dropdown;
	}

	/**
	 * Method to resend invitations
	 *
	 * @return  Boolean
	 *
	 * @since   1.6.1
	 */
	public function resend()
	{
		$session      = JFactory::getSession();
		$input        = JFactory::getApplication()->input;
		$post         = $input->getArray($_POST);
		$db           = JFactory::getDbo();
		$loggedinUser = JFactory::getUser();
		$mainframe    = JFactory::getApplication();

		// Check if user has given consent to store contact detail and send invitation
		$tncAccepted = $session->get('tj_send_invitations_consent');
		$invitationTermsAndConditions = $this->invitex_params->get('invitationTermsAndConditions', '0');
		$tNcArticleId = $this->invitex_params->get('tNcArticleId', '0');

		if (!empty($invitationTermsAndConditions) && !empty($tNcArticleId))
		{
			if (empty($tncAccepted))
			{
				$mainframe->enqueueMessage(JText::_("COM_INVITEX_PRIVACY_CONSENT_ERROR_MSG"), 'error');

				return false;
			}
		}

		foreach ($post['contacts'] as $name => $email)
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'resend_count')));
			$query->from($db->quoteName('#__invitex_imports_emails'));
			$query->where($db->quoteName('invitee_email') . "=" . $db->q($email));
			$query->where($db->quoteName('inviter_id') . "=" . $loggedinUser->id);
			$db->setQuery($query);

			$res = $db->loadObject();
			$resend = (int) $res->resend_count + 1;

			$datetime 		= time();
			$validity 		= $this->invitex_params->get('expiry');
			$expiry 		= $datetime + ($validity * 60 * 60 * 24);

			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('resend_count') . ' = ' . $resend,
				$db->quoteName('resend') . ' = 1',
				$db->quoteName('expires') . ' = ' . $db->quote($expiry),
				$db->quoteName('modified') . ' = ' . $db->quote($datetime),
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('invitee_email') . ' = ' . $db->quote($email),
				$db->quoteName('inviter_id') . ' = ' . $loggedinUser->id
			);

			if (!empty($invitationTermsAndConditions) && !empty($tNcArticleId) && !empty($tncAccepted))
			{
				$this->addResendingInviteConsent($res->id);
			}

			$query->update($db->quoteName('#__invitex_imports_emails'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * Method to add consent for re-sending email invitations.
	 *
	 * @param   INT  $clientId  client record id
	 *
	 * @return  void
	 *
	 * @since   3.0.8
	 */
	public function addResendingInviteConsent($clientId)
	{
		// Load privacy and model
		JLoader::import('tjprivacy', JPATH_SITE . '/components/com_tjprivacy/models');

		$user = JFactory::getUser();
		$session = JFactory::getSession();

		$userPrivacyData = array();
		$userPrivacyData['client'] = 'com_invitex.resendinvites';
		$userPrivacyData['client_id'] = $clientId;
		$userPrivacyData['user_id'] = $user->id;
		$userPrivacyData['purpose'] = JText::_('COM_INVITEX_USER_PRIVACY_TERMS_PURPOSE_FOR_RESENDING_INVITES');
		$userPrivacyData['accepted'] = 1;
		$userPrivacyData['date'] = JFactory::getDate('now')->toSQL();

		$tjprivacyModelObj = JModelLegacy::getInstance('tjprivacy', 'TjprivacyModel');
		$result = $tjprivacyModelObj->save($userPrivacyData);

		if (!empty($result))
		{
			$session->set('tj_send_invitations_consent', 0);
		}
	}
}
