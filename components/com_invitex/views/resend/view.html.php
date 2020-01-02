<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
jimport('joomla.application.component.view');

/**
 * Namecard view
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class InvitexViewResend extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Namecard view
	 *
	 * @param   string  $tpl  Name of template
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$mainframe            = JFactory::getApplication();
		$input                = $mainframe->input;
		$this->invhelperObj   = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();
		$session              = JFactory::getSession();
		$tncAccepted          = $session->get('tj_send_invitations_consent');

		$user = JFactory::getUser();

		if (!$user->id)
		{
			$title = JText::_('LOGIN_TITLE_STAT');

			$mainframe = JFactory::getApplication();
			$msg = JText::_('LOGIN_TITLE_RESEND');
			$uri = $_SERVER["REQUEST_URI"];
			$url = base64_encode($uri);
			$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);

			return false;
		}

		// Take user consent for resending the invitations
		$invitationTermsAndConditions = $this->invitex_params->get('invitationTermsAndConditions', '0');
		$tNcArticleId = $this->invitex_params->get('tNcArticleId', '0');

		JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');
		$model = JModelLegacy::getInstance('Article', 'ContentModel');

		if ($invitationTermsAndConditions && $tNcArticleId)
		{
			$contentTable = $model->getTable('Content', 'JTable');
			$contentTable->load(array('id' => $tNcArticleId));

			$slug = $contentTable->id . ':' . $contentTable->alias;
			$this->privacyPolicyLink = JRoute::_(ContentHelperRoute::getArticleRoute($slug, $contentTable->catid, $contentTable->language));

			if (empty($tncAccepted))
			{
				$mainframe->enqueueMessage(JText::sprintf('COM_INVITEX_PRIVACY_CONSENT_MSG', $this->privacyPolicyLink), 'Info');
			}
		}

		$this->itemid        = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=resend');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->invite_status = $this->get('status');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		if ($this->invitex_params->get("reg_direct") == "JomSocial"  && $this->invitex_params->get("jstoolbar") == '1')
		{
			$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject('JomSocial');
			echo $sociallibraryobj->getToolbar();
		}

		if ($this->invitex_params->get("reg_direct") == "EasySocial"  && $this->invitex_params->get("estoolbar") == '1')
		{
			$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject('EasySocial');
			echo $sociallibraryobj->getToolbar();
		}

		parent::display($tpl);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
						'ie.invitee_email' => JText::_('EMAILS'),
						'ie.invitee_name' => JText::_('NAMES')
		);
	}
}
