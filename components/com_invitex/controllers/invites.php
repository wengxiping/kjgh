<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Invites
 *
 * @since  1.0
 */
class InvitexControllerInvites extends InvitexController
{
	/**
	 * Constructor
	 *
	 * @since  1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->invhelperObj   = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();
	}

	/**
	 * get_request_token
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get_request_token()
	{
		JSession::checkToken() or die('Invalid Token');
		$mainframe = JFactory::getApplication();
		$session   = JFactory::getSession();
		$model     = $this->getModel('invites');
		$input     = JFactory::getApplication()->input;
		$post      = $input->getArray($_POST);

		$session->set('provider_box', '');

		if (isset($post['guest']))
		{
			$session->set('guest_user', $post['guest']);
		}

		$api_used         = $post['api_used'];
		$api_message_type = $post['api_message_type'];
		$personal_message = isset($post['personal_message']) ? $post['personal_message'] : '';

		$session->set('api_used', $api_used);
		/*very important*/
		$session->set('api_message_type', $api_message_type);
		/*very important*/
		$session->set('personal_message', $personal_message);
		/*very important*/
		if ($api_used == 'plug_techjoomlaAPI_facebook')
		{
			if (!$session->get('invite_anywhere'))
			{
				$limit_data = $this->invhelperObj->getInvitesLimitData();
				$limit      = 0;

				if (!$limit_data->limit)
				{
					$limit = $this->invitex_params->get('per_user_invitation_limit');
				}
				else
				{
					$limit = $limit_data->limit;
				}

				if ($limit && $limit >= $limit_data->invitations_sent)
				{
					$invitestobesent = $limit - $limit_data->invitations_sent;

					if ($invitestobesent == 0)
					{
						$link = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false);
						$this->setRedirect($link, 'Your Invitation limit has reached..You can not send any more Invites!');
					}
				}
			}
		}
		elseif ($api_message_type == 'sms')
		{
			$post['contacts'] = $post['sms'];

			$sms_invite = $model->store_invites($post);
			$link = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false);

			if ($sms_invite)
			{
				$msg        = JText::_('INVITE_SUCESS');
				$this->setRedirect($link, $msg);
			}
			else
			{
				$this->setRedirect($link);
			}
		}

		/*this is how to send Invites via API Trigger  or  core model method*/
		$session->set('api_message_type', $post['api_message_type']);
		/*very important*/
		$grt_response = $model->getRequestToken($api_used);
	}

	/**
	 * get_access_token
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get_access_token()
	{
		$get = JRequest::get('get');

		if (isset($_GET['code']) && $_GET['code'] != '')
		{
			$get['code'] = $_GET['code'];
		}
		else
		{
			$get['code'] = $get;
		}

		$session   = JFactory::getSession();
		$mainframe = JFactory::getApplication();

		$model        = $this->getModel('invites');
		$gat_response = $model->getAccessToken($get);

		$itemId = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');

		if ($gat_response)
		{
			$mainframe->redirect(JRoute::_('index.php?option=com_invitex&controller=invites&task=get_contacts&Itemid=' . $itemId, false));
		}
		else
		{
			$mainframe->redirect(JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemId, false));
		}
	}

	/**
	 * FBRequestStore
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function FBRequestStore()
	{
		$model                = $this->getModel('invites');
		$db                   = JFactory::getDbo();
		$data                 = $_GET;
		$session              = JFactory::getSession();
		$data['message_type'] = 'social';
		$data['message_box']  = $link = '';
		$msgType = 'success';

		// $this->invitex_params	=	$this->invitex_params;

		if (isset($data['to']))
		{
			// TO GET NAMES OF THE INVITEE...
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('techjoomlaAPI', 'plug_techjoomlaAPI_facebook');

			foreach ($data['to'] as $id)
			{
				$name  = $dispatcher->trigger('plug_techjoomlaAPI_facebook_getUser_name', array($id));
				$required_user_name = $name[0];
				$data['contacts'][$required_user_name] = $data['request'] . '_' . $id;
			}

			$res = $model->queup_invites($data);

			if ($res)
			{
				foreach ($data['to'] as $id)
				{
					$update_data                = new stdClass;
					$update_data->invitee_email = $data['request'] . '_' . $id;
					$update_data->sent          = '1';
					$update_data->sent_at       = time();
					$update_data->modified      = time();
					$db->updateObject('#__invitex_imports_emails', $update_data, 'invitee_email');
				}

				$msg                    = JText::_('INVITE_SUCESS');
				$inviter_array          = array();
				$my                     = JFactory::getUser();
				$inviter_array[$my->id] = count($data['to']);
				$activity_stream        = $model->call_activity_stream($inviter_array);
			}
			else
			{
				$msgType = 'error';
				$msg = JText::_('SAVING_ERROR');
			}
		}
		else
		{
			$msgType = 'error';
			$msg = JText::_('SENDING_CANCELLED');
		}

		$itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');

		if ($session->get('invite_anywhere'))
		{
			$link         = 'index.php?option=com_invitex&view=invites';
			$link         = $link . '&invite_type=' . (INT) $session->get('invite_type') . '&catch_act=&invite_anywhere=1&Itemid=' . $itemid;
			$link         = JRoute::_($link, false);
		}
		else
		{
			$link = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false);
		}

		// If($this->invitex_params->get('allow_point_after_invite')==1)
		{
			if ($inviter_array)
			{
				foreach ($inviter_array as $inviter => $count_people)
				{
					if ($inviter != 0 || $inviter != '')
					{
						$po = $this->invitex_params->get('pt_option');
						$afterInvite = $this->invitex_params->get('inviter_point_after_invite');
						$dispatcher = JDispatcher::getInstance();
						JPluginHelper::importPlugin('system');
						$result = $dispatcher->trigger('onAfterinvitesent', array( $inviter, $po, $afterInvite, $count_people));
					}
				}
			}
		}

		$this->setRedirect($link, $msg, $msgType);
	}

	/**
	 * Get contacts
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get_contacts()
	{
		$get = array();

		if (isset($_GET['code']) && $_GET['code'] != '')
		{
			$get['code'] = $_GET['code'];
		}

		// In case of facebook limit=end start=offset
		$mainframe = JFactory::getApplication();
		$session   = JFactory::getSession();
		$api_used  = $session->get('api_used');
		$model     = $this->getModel('invites');
		$offset    = $limit = '';

		$input = JFactory::getApplication()->input;
		$post  = $input->getArray($_POST);

		if (isset($post['offset']))
		{
			$offset = $post['offset'];
		}

		if (isset($post['limit']))
		{
			$limit = $post['limit'];
		}

		if (isset($post['offset']))
		{
			header('Cache-Control: no-cache, must-revalidate');
			header('Content-type: application/json');
			$gc_response = array();
			$gc_response = $model->getContacts($offset, $limit);

			header('Content-type: application/json');

			echo json_encode($gc_response);
			jexit();
		}

		$itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
		$msg    = '';

		if ($session->get('invite_anywhere'))
		{
			$cl = 'index.php?option=com_invitex&view=invites&layout=send_invites';
			$iaCl = $cl . '&invite_type=' . (INT) $session->get('invite_type') . '&catch_act=&invite_anywhere=1&Itemid=' . $itemid;

			$contactslink = JRoute::_($iaCl, false);

			$link         = 'index.php?option=com_invitex&view=invites';
			$link         = $link . '&invite_type=' . (INT) $session->get('invite_type') . '&catch_act=&invite_anywhere=1&Itemid=' . $itemid;
			$link         = JRoute::_($link, false);
		}
		else
		{
			$contactslink = JRoute::_('index.php?option=com_invitex&view=invites&layout=send_invites&Itemid=' . $itemid, false);
			$link         = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false);
		}

		if ($session->get('api_message_type') == 'email')
		{
			$gc_response = $model->getContacts($offset, $limit, $get);

			if ($session->get('already_invited_mails'))
			{
				$i_mail = $session->get('already_invited_mails');
				$number = count($i_mail);

				$resend_link = "<a href='" . JRoute::_('index.php?option=com_invitex&view=resend&Itemid=' . $itemid, false) . "'>" . JText::_('RE_SEND') . "</a>";
				$msg .= "<br />  " . JText::sprintf('INV_ALREADY_INVITED_MSG', $number, $resend_link);
			}

			if ($session->get('invite_mails'))
			{
				$mainframe->redirect($contactslink, $msg);
			}
			else
			{
				$mainframe->redirect($link, $msg);
			}
		}

		$mainframe->redirect($contactslink);
	}

	/**
	 * Get captcha url
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getcaptchaURL()
	{
		$mainframe   = JFactory::getApplication();
		$model       = $this->getModel('invites');
		$gc_response = $model->getcaptchaURL();
	}

	/**
	 * Save contacts
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		(JSession::checkToken() or JSession::checkToken('get')) or die('Invalid Token');

		// Check for request forgeries
		$session = JFactory::getSession();
		$cache   = JFactory::getCache('com_invitex');
		$cache->clean();
		$model = $this->getModel('invites');
		$input = JFactory::getApplication()->input;
		$post  = $input->getArray($_POST);

		$rout          = $session->get('rout');
		$r_mail        = $session->get('registered_mails');
		$b_mail        = $session->get('unsubscribe_mails');
		$message       = '';
		$error_message = '';

		if ($rout == 'manual')
		{
			$post['contacts'] = $session->get('invite_mails');

			if (empty($post['personal_message']))
			{
				$post['personal_message'] = $session->get('personal_message');
			}

			$post['message_type'] = 'email';
		}
		elseif ($rout == 'inv_js_messaging')
		{
			$post['contacts'] = $session->get('invite_mails');

			if (empty($post['personal_message']))
			{
				$post['personal_message'] = $session->get('personal_message');
			}

			$post['message_type'] = 'social';
		}

		$check = 0;
		$check = $model->store_invites($post);

		if ($check == '1')
		{
			$msg = JText::_('INVITE_SUCESS');
		}
		elseif ($check == '-1')
		{
			$error_message = JText::_('SOCIAL_ERROR') . "<br>";
		}
		elseif (!$check)
		{
			$error_message = JText::_('SAVING_ERROR');
		}

		$mainframe = JFactory::getApplication();
		$itemid    = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');

		if ($session->get('invite_anywhere'))
		{
			if ($session->get('invite_url'))
			{
				$this->setRedirect($session->get('invite_url'), $msg);
			}
			else
			{
				$it = (INT) $session->get('invite_type');
				$temp_url = 'index.php?option=com_invitex&view=invites&invite_type=' . $it . '&catch_act=&invite_anywhere=1&Itemid=' . $itemid;
				$this->setRedirect(JRoute::_($temp_url, false), $msg);
			}
		}
		else
		{
			if ($error_message == '')
			{
				// START Invitex Sample development
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				$result = $dispatcher->trigger('onAfterinvitesqueued');

				if ($session->get('already_invited_mails'))
				{
					$i_mail      = $session->get('already_invited_mails');
					$number      = count($i_mail);

					$resend_url = 'index.php?option=com_invitex&view=resend&Itemid=' . $itemid;
					$resend_link = "<a href='" . JRoute::_($resend_url, false) . "'>" . JText::_('RE_SEND') . "</a>";

					$msg .= "<br />  " . JText::sprintf('INV_ALREADY_INVITED_MSG', $number, $resend_link);
				}

				$this->setRedirect(JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false), $msg);
			}
			else
			{
				$this->setRedirect(JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false), $error_message, 'error');
			}
		}
	}

	/**
	 * Validate an Email
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function validate_email()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		$input    = JFactory::getApplication()->input;
		$post     = $input->getArray($_POST);
		$model    = $this->getModel('invites');
		$document = JFactory::getDocument();

		// Set the MIME type for JSON output.
		$document->setMimeEncoding('application/json');

		header('Content-type: application/json');

		// Change the suggested filename.
		if ($model->validate_email($post['email']) == '1')
		{
			echo json_encode(1);
		}
		else
		{
			echo json_encode(-1);
		}

		jexit();
	}

	/**
	 * Add friends
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function add_friend()
	{
		$user_id = JFactory::getApplication()->input->get('fuid', '', 'INT');

		$model = $this->getModel('invites');
		$result = $model->add_friend($user_id);

		echo $result;

		jexit();
	}

	/**
	 * Called to validate captcha
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function isCaptchaCorrect()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		$input = JFactory::getApplication()->input;
		$post  = $input->post;

		JPluginHelper::importPlugin('captcha');
		$dispatcher = JDispatcher::getInstance();
		$input->set('recaptcha_response_field', $post->get('recaptcha_response_field', '', 'STRING'));
		$res     = $dispatcher->trigger('onCheckAnswer', 'asdasd');
		$content = '';

		if (empty($res[0]))
		{
			$content = 2;
		}
		else
		{
			$content = 1;
		}

		header('Content-type: application/json');

		echo json_encode($content);
		jexit();
	}

	/**
	 * Function to add user consent entry
	 *
	 * @return  void
	 *
	 * @since   3.0.8
	 */
	public function userConsent()
	{
		// Check CSRF token
		JSession::checkToken('get') or die('Invalid Token');

		$input = JFactory::getApplication()->input;
		$consent = $input->get('consent', 0, 'INT');
		$view = $input->get('view', '', 'STRING');

		$session = JFactory::getSession();
		$session->set('tj_send_invitations_consent', $consent);

		$returnData = array();
		$comInvitexHelper = new CominvitexHelper;

		if (empty($consent))
		{
			$returnData['success'] = 1;
			$returnData['redirectUrl'] = JUri::root();
		}
		else
		{
			$returnData['success'] = 1;
			$returnData['redirectUrl'] = "";

			if ($view == 'resend' || $view == 'invites')
			{
				$itemId = $comInvitexHelper->getitemid('index.php?option=com_invitex&view=' . $view);
				$invitexLink = 'index.php?option=com_invitex&view=' . $view . '&Itemid=' . $itemId;

				$returnData['redirectUrl'] = JUri::root() . substr(JRoute::_($invitexLink, false), strlen(JUri::base(true)) + 1);
			}
			else
			{
				$returnData['redirectUrl'] = $input->server->get('HTTP_REFERER', '', 'RAW');
			}
		}

		echo json_encode($returnData);

		jexit();
	}

	/**
	 * Function to revoke user consent entry
	 *
	 * @return  void
	 *
	 * @since   3.0.8
	 */
	public function revokeUserConsent()
	{
		// Check CSRF token
		JSession::checkToken('get') or die('Invalid Token');

		$input = JFactory::getApplication()->input;
		$view = $input->get('view', '', 'STRING');

		$comInvitexHelper = new CominvitexHelper;
		$session = JFactory::getSession();
		$session->set('tj_send_invitations_consent', 0);

		$itemId = $comInvitexHelper->getitemid('index.php?option=com_invitex&view=' . $view);
		$invitexLink = 'index.php?option=com_invitex&view=' . $view . '&Itemid=' . $itemId;

		$returnData = array();
		$returnData['success'] = 1;

		if ($view == 'invites' || $view == 'resend')
		{
			$returnData['redirectUrl'] = JUri::root() . substr(JRoute::_($invitexLink, false), strlen(JUri::base(true)) + 1);
		}
		else
		{
			$returnData['redirectUrl'] = $input->server->get('HTTP_REFERER', '', 'RAW');
		}

		echo json_encode($returnData);

		jexit();
	}
}
