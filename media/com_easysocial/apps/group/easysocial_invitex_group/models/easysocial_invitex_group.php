<?php
/**
 * @package    InviteX
 * @copyright  Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license    GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link       http://www.techjoomla.com
 */

defined('_JEXEC') or die('Restricted access');

// Import the model file from the core
Foundry::import('admin:/includes/model');

/**
 * Model for invitex app
 *
 * @since  1.0
 * @return  Array	A list of textbook rowset.
 */
class Easysocial_Invitex_GroupModel extends EasySocialModel
{
	/**
	 * Model for invitex app
	 *
	 * @param   STRING  $grp_nm      User's id.
	 * @param   STRING  $group_link  User's id.
	 *
	 * @return  Array  A list of textbook rowset.
	 *
	 * @since   1.0
	 */
	public function getInvitexView($grp_nm, $group_link )
	{
		$this->invite_methods = $this->invite_apis = $invite_anywhere = $invite_url = $invite_type = '';

		$lang = JFactory::getLanguage();
		$lang->load('com_invitex', JPATH_SITE);

		$path = JPATH_ROOT . '/components/com_invitex/models/invites.php';

		if (!class_exists('InvitexModelInvites'))
		{
			JLoader::register('InvitexModelInvites', $path);
			JLoader::load('InvitexModelInvites');
		}

		$pathhelper = JPATH_ROOT . '/components/com_invitex/helper.php';

		if (!class_exists('cominvitexHelper'))
		{
			JLoader::register('cominvitexHelper', $pathhelper);
			JLoader::load('cominvitexHelper');
		}

		$model = new InvitexModelInvites;
		$invite_typeid = $model->getInvite_typeID('ESGroup');
		$session = JFactory::getSession();

		// SET Variables input values
		$invite_url = $group_link;

		JFactory::getApplication()->input->set('invite_url', urlencode($group_link));
		JFactory::getApplication()->input->set('invite_type', $invite_typeid);
		JFactory::getApplication()->input->set('invite_anywhere', 1);
		JFactory::getApplication()->input->set('tag', '[name=' . $grp_nm . ']');

		// $session->set('invite_url', urlencode($group_link));

		// $session->set('invite_type', $invite_typeid);

		// $session->set('invite_anywhere', 1);
		$session->set('tag', '[name=' . $grp_nm . ']');

		$mainframe = JFactory::getApplication();
		$input = $mainframe->input;
		$document = JFactory::getDocument();
		$this->invhelperObj = new cominvitexHelper;
		$this->invitex_params = $invitex_settings	= $this->invhelperObj->getconfigData();
		$rout	=	JFactory::getApplication()->input->get('rout');
		$this->itemid = $itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
		$this->isguest = $this->invitex_params->get('guest_invitation');

		$this->user_is_a_guest = 0;

		$layout = $input->get('layout');

		$uid 	= $this->invhelperObj->getUserID();
		$table = JUser::getTable();
		$this->oluser = '';

		if ($table->load($uid))
		{
			$this->oluser = JFactory::getUser($uid);
		}

		if ($this->oluser || $this->isguest == 1)
		{
			$invite_anywhere = 1;

			if ($invite_anywhere == 1)
			{
				$typedata = $this->invhelperObj->types_data($invite_typeid);
				$this->typedata = $typedata;
			}

			$jsfriend	= $model->getjsfriend();
			$this->jsfriend = $jsfriend;

			$jsinvitedfriend	=	$model->getjsinvitedfriend();
			$this->jsinvitedfriend = $jsinvitedfriend;

			$cbfriend	= $model->getcbfriend();
			$this->cbfriend = $cbfriend;

			$integration_with = $invitex_settings->get('reg_direct');

			if ($integration_with == 'EasySocial')
			{
				$esfriend	= $model->getesfriend();
				$this->esfriend = $esfriend;
				$esinvitedfriend	=	$model->getesinvitedfriend();
				$this->esinvitedfriend = $esinvitedfriend;
			}

			$validdomains	=	$model->getValiddomains();
			$this->validdomains = $validdomains;

			$limit_data	=	$this->invhelperObj->getInvitesLimitData();
			$this->limit_data = $limit_data;
			$this->rout = $rout;

			if (!$this->oluser && $this->isguest == 1)
			{
				$user_is_a_guest = 1;
			}

			if ($this->oluser)
			{
				$uid = $this->oluser->id;
			}
			else
			{
				$uid = 0;
			}

			if (JFactory::getApplication()->input->get('fb_redirect', '', 'get'))
			{
				if (JFactory::getApplication()->input->get('fb_redirect', '', 'get') == "success")
				{
					$mainframe->redirect('index.php?option=com_invitex&view=invites&Itemid=' . $this->itemid, "Invites Sent Succesfully");
				}
			}

			$onload_redirect = JRoute::_('index.php?option=com_invitex&view=invites&layout&layout=default_new&Itemid=' . $this->itemid, false);

			if (isset($_SERVER['HTTP_REFERER']))
			{
					$referer = $_SERVER['HTTP_REFERER'];
			}

			$_SESSION['oauth_token'] = '';
			$_SESSION['oauth_verifier'] = '';
			$friends = '';

			if ($invite_anywhere	== '1')
			{
				$this->invhelperObj->setSession();
				$session->set('invite_anywhere', $invite_anywhere);

				$referer = rawurldecode($invite_url);
				$session->set('invite_url', $referer);

				if ($grp_nm)
				{
					$session->set('invite_tag', '[name=' . $grp_nm . ']');
				}

				$session->set('invite_type', $invite_typeid);

				$typedata = $this->invhelperObj->types_data($invite_typeid);

				$this->invite_methods = explode(",", $typedata->invite_methods);

				$this->invite_apis = explode(",", $typedata->invite_apis);

				if (!empty($typedata->invite_apis))
				{
					$api_config = $this->invite_apis;
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('techjoomlaAPI');
					$result_old = $dispatcher->trigger('renderPluginHTML', array($api_config));

					foreach ($result_old as $key => $value)
					{
						if (!empty($value['message_type']))
						{
							$msg_type = $value['message_type'];

							if ($show_compact_view)
							{
								if ($msg_type == 'pm' and $invite_method == 'social_apis')
								{
									$result['social_apis'][] = $value;
								}
								else
								{
									$result[$msg_type . '_apis'][] = $value;
								}
							}
							else
							{
								if ($msg_type == 'email' or $msg_type == 'sms')
								{
									$result[$msg_type . '_apis'][] = $value;
								}
								elseif ($msg_type == 'pm')
								{
									$result['social_apis'][] = $value;
								}
							}
						}
					}

					$this->renderAPIicons = $result;
				}

				if ($this->invitex_params->get("reg_direct") == 'Jomsocial')
				{
					$jspath = JPATH_ROOT . '/components/com_community';

					if (JFolder::exists($jspath))
					{
						include_once $jspath . '/libraries/core.php';

						/* Include Messaging library
						// Add onclick action*/
						$friendsModel	= CFactory::getModel('Friends');
						$this->friends	= $friendsModel->getFriends($uid, 'name', false);
					}
				}
				elseif ($this->invitex_params->get('reg_direct') == 'EasySocial')
				{
					$esfriends_ids	= $model->getesfriend();
					$this->friends = $this->invhelperObj->sociallibraryobj->getFriends($this->oluser);
				}
			}
			else
			{
				$session->set('invite_anywhere', '');
				$session->set('invite_tag', '');
				$session->set('invite_type', '');
				$session->set('invite_url', '');
			}
		}

		if (!$this->oluser && $this->isguest == 1)
		{
			$this->user_is_a_guest = 1;
		}

		$oi_plugin_selection = array();

		$oi_path = JPATH_BASE . '/components/com_invitex/openinviter/openinviter.php';

		if (JFile::exists($oi_path))
		{
			require_once $oi_path;
			require JPATH_SITE . "/components/com_invitex/openinviter/config.php";
			$this->inviter = new openinviter;
			$this->oi_services = $oi_services = $this->inviter->getPlugins();

			if (($this->invitex_params->get('selections')))
			{
				$this->oi_plugin_selection = $this->invitex_params->get('selections');
			}
		}

		$temp_url = "index.php?option=com_invitex&view=invites&layout=preview&invite_anywhere=1&invite_type=" . $invite_typeid;
		$prev_url = $temp_url . "&tmpl=component&Itemid=" . $this->itemid;
		$this->preview_url = JURI::root() . substr(JRoute::_($prev_url, false), strlen(JURI::base(true)) + 1);

		$this->tool_tip_arr = array(
		'manual' => JText::_('INV_METHOD_MANUAL'),
		'inv_by_url' => JText::_('INV_METHOD_INV_BY_URL'),
		'other_tools' => JText::_('INV_METHOD_OTHER_TOOLS'),
		'social_apis' => '',
		'email_apis' => '',
		'sms_apis' => '',
		'oi_social' => JText::_('INV_METHOD_OI_SOCIAL'),
		'oi_email' => JText::_('INV_METHOD_OI_EMAIL'),
		'js_messaging' => JText::_('INV_METHOD_JSMESSAGING')
		);

		$this->show_compact_view = 1;
		$this->showonly_invite_methods = 1;
		$select_layout = $this->invitex_params->get('inv_look');

		if ($select_layout == 2)
		{
			$layout	=	JFactory::getApplication()->input->get('layout', 'black_white');
		}
		elseif ($select_layout == 0)
		{
			$layout	=	JFactory::getApplication()->input->get('layout', '');
		}
		elseif ($select_layout == 1)
		{
			$layout	=	JFactory::getApplication()->input->get('layout', 'fb');
		}

		// Set icon
		$this->icon_used = 'easysocial';
		include JPATH_SITE . '/components/com_invitex/js_helper.php';
		include JPATH_SITE . '/components/com_invitex/views/invites/tmpl/default_black_white.php';
	}
}
