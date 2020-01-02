<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
jimport('joomla.application.component.view');
JHTML::_('behavior.formvalidation');
JHtml::_('behavior.framework', true);
jimport('joomla.form.formvalidator');
jimport('joomla.filesystem.folder');

/**
 * Invites view
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class InvitexViewInvites extends JViewLegacy
{
	/**
	 * Invites view
	 *
	 * @param   string  $tpl  Name of template
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$mainframe             = JFactory::getApplication();
		$document              = JFactory::getDocument();
		$document->setTitle(JText::_("SEND_INV"));
		$this->invhelperObj    = new cominvitexHelper;
		$invitex_params        = $this->invitex_params = $this->invhelperObj->getconfigData();
		$session               = JFactory::getSession();
		$tncAccepted           = $session->get('tj_send_invitations_consent');

		$input                 = JFactory::getApplication()->input;
		$rout                  = $input->get('rout');
		$this->isguest         = $this->invitex_params->get("guest_invitation");
		$this->component_nm    = $input->get('option');
		$this->view_nm         = $input->get('view');
		$this->user_is_a_guest = 0;
		$user                  = JFactory::getUser();

		$invitationTermsAndConditions = $invitex_params->get('invitationTermsAndConditions', '0');
		$tNcArticleId = $invitex_params->get('tNcArticleId', '0');

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

		$this->invite_apis = $this->invite_methods = '';

		if ($this->invitex_params->get("invite_methods"))
		{
			$this->invite_methods = $this->invitex_params->get("invite_methods");
		}

		if ($this->invitex_params->get("invite_apis"))
		{
			$this->invite_apis = $this->invitex_params->get("invite_apis");
		}

		$this->itemid         = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
		$layout               = $input->get('layout', '');
		$uid          = $this->invhelperObj->getUserID();
		$table        = JUser::getTable();
		$this->oluser = '';

		if ($table->load($uid))
		{
			$this->oluser = JFactory::getUser($uid);
		}

		$this->isguest = $this->invitex_params->get("guest_invitation");

		if ($this->oluser || $this->isguest == 1 || $layout == 'unsubscribe')
		{
			$model = $this->getModel();
			$invite_anywhere_url = '';

			if ($input->get('invite_anywhere'))
			{
				$typedata       = $this->invhelperObj->types_data($input->get('invite_type', '', 'INT'));
				$this->typedata = $typedata;
				$invite_anywhere_url = '&invite_anywhere=1&invite_type=' . $input->get('invite_type', '', 'INT') . '&invite_url=' . $input->get('invite_url');

				// Default personal message for invite types

				if (!empty($this->typedata->personal_message))
				{
					$this->invitex_params->set('invitex_default_message', $this->typedata->personal_message);
				}
			}

			$preview_url = "index.php?option=com_invitex&view=invites&layout=preview&tmpl=component&Itemid=" . $this->itemid . $invite_anywhere_url;
			$this->preview_url = JURI::root() . substr(JRoute::_($preview_url, false), strlen(JURI::base(true)) + 1);

			if ($layout == 'send_invites')
			{



				if (is_array($session->get('invite_mails')))
				{
					$invmails = $session->get('invite_mails');
				}
				else
				{
					parse_str($session->get('invite_mails'), $invmails);
				}

				// APIS Like linkedin twitter
				if ($session->get('api_message_type') and $session->get('api_message_type') != 'email')
				{

					$contacts       = $this->get('Contacts');

					$this->contacts = $contacts;
				}
				else
				{

					$this->contacts = $invmails;

				}
				$session->get('provider_box');
				$indexkey    = 0;
				$newcontacts = array();

				if ($session->get('provider_box') == 'csv')
				{
                    $new_invite_mails = $session->get('new_invite_mails');
					foreach ($invmails AS $contactemail => $contactname)
					{
						$newcontacts[$indexkey]       = new StdClass;
						$newcontacts[$indexkey]->name = $contactname;
						$newcontacts[$indexkey]->id   = $contactemail;

						foreach ($new_invite_mails as $val){
						    if($val['email'] == $contactemail){
                                $newcontacts[$indexkey]->phone   = $val['phone'];
                            }
                        }
                        $indexkey++;
					}

					$this->contacts = $newcontacts;
				}

				$send_message_limit       = $model->get_send_message_limit();
				$this->send_message_limit = $send_message_limit;
			}

			$renderAPIicons       = $this->get('renderAPIicons');
			$this->renderAPIicons = $renderAPIicons;

			$integration_with = $this->invitex_params->get("reg_direct");

			if ($integration_with == 'JomSocial')
			{
				$jsfriend       = $this->get('jsfriend');
				$this->jsfriend = $jsfriend;
				$jsinvitedfriend       = $this->get('jsinvitedfriend');
				$this->jsinvitedfriend = $jsinvitedfriend;
			}

			if ($integration_with == 'Community Builder')
			{
				$cbfriend       = $this->get('cbfriend');
				$this->cbfriend = $cbfriend;
			}

			if ($integration_with == 'EasySocial')
			{
				$esfriend       = $this->get('esfriend');
				$this->esfriend = $esfriend;

				$esinvitedfriend       = $this->get('esinvitedfriend');
				$this->esinvitedfriend = $esinvitedfriend;
			}


			if ($rout == 'resend')
			{

				$data             = $this->get('data');
				$this->data       = $data;
				$this->items      = $this->get('Data');
				$total            = $this->get('Total');
				$this->pagination = $this->get('Pagination');
			}

			if ($layout == 'preview')
			{
				$preview_data       = $this->get('preview');
				$this->preview_data = $preview_data;
			}

			$validdomains       = $this->get('Validdomains');
			$this->validdomains = $validdomains;
			$limit_data         = $this->invhelperObj->getInvitesLimitData();
			$this->limit_data   = $limit_data;
			$this->rout         = $rout;

			$session              = JFactory::getSession();
			$document             = JFactory::getDocument();
			$this->invite_methods = $this->invite_apis = $invite_anywhere = $invite_url = $invite_type = '';

			if ($this->oluser)
			{
				$uid = $this->oluser->id;
			}
			else
			{
						$uid = 0;
			}

			$itemid = $this->itemid;

			if ($input->get('fb_redirect', '', 'get'))
			{
				if ($input->get('fb_redirect', '', 'get') == "success")
				{
					$mainframe->redirect('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, "Invites Sent Succesfully");
				}
			}

			if ($this->invitex_params->get("invite_methods"))
			{
				$this->invite_methods = $this->invitex_params->get("invite_methods");
			}

			if ($this->invitex_params->get("invite_apis"))
			{
				$this->invite_apis = $this->invitex_params->get("invite_apis");
			}

			$onload_redirect = JRoute::_('index.php?option=com_invitex&view=invites&layout=default_new&Itemid=' . $itemid, false);

			if (isset($_SERVER['HTTP_REFERER']))
			{
				$referer = $_SERVER['HTTP_REFERER'];
			}

			/* set session vaiable to blank */
			$_SESSION['oauth_token']    = '';
			$_SESSION['oauth_verifier'] = '';
			$friends                    = '';
			$invite_anywhere            = $input->get('invite_anywhere', '', 'get');

			if ($input->get('invite_anywhere'))
			{
				$session->set('invite_anywhere', 1);

				if ($input->get('invite_url', '', 'get'))
				{
					$referer = urldecode(base64_decode($input->get('invite_url', '', 'get')));

					if ($session->get('invite_url') != $referer)
					{
									$session->set('invite_url', $referer);
					}
				}

				if (isset($_SERVER['HTTP_REFERER']))
				{
								$referer = $_SERVER['HTTP_REFERER'];
				}

				if (!$session->get('invite_url'))
				{
					if (!empty($referer))
					{
						$session->set('invite_url', $referer);
					}
				}

				$this->invite_url = $session->get("invite_url");

				if ($input->get('tag', '', 'get'))
				{
					$session->set('invite_tag', $input->get('tag', '', 'get'));
				}

				$session->set('invite_type', $input->get('invite_type', '', 'INT'));
				$typedata = $this->invhelperObj->types_data($input->get('invite_type', '', 'INT'));
				$this->invite_methods = explode(",", $this->typedata->invite_methods);
				$this->invite_apis    = explode(",", $this->typedata->invite_apis);

				// Get Friends for Easysocial and jomsocial depending on integration
				if ($this->oluser)
				{
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
		else
		{
			$this->logged_userid = $user->id;

			if (!$this->logged_userid)
			{
				$msg = JText::_('NON_LOGIN_MSG');
				$uri = $_SERVER["REQUEST_URI"];
				$url = base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
			}
		}

		if (!$this->oluser && $this->isguest == 1)
		{
			$this->user_is_a_guest = 1;
		}

		$this->session       = JFactory::getSession();
		$oi_plugin_selection = array();
		$oi_path             = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';

		if (JFile::exists($oi_path))
		{
			require_once JPATH_BASE . '/components/com_invitex/openinviter/openinviter.php';
			require  JPATH_SITE . "/components/com_invitex/openinviter/config.php";
			$this->inviter     = new openinviter;
			$this->oi_services = $oi_services = $this->inviter->getPlugins();

			if ($this->invitex_params->get("selections"))
			{
				$this->oi_plugin_selection = $this->invitex_params->get("selections");
			}
		}

		$this->tool_tip_arr = array(
		'inv_by_url' => JText::_('INV_METHOD_INV_BY_URL'),
		'manual' => JText::_('INV_METHOD_MANUAL'),
		'advanced_manual' => JText::_('INV_METHOD_ADVANCED_MANUAL'),
		'inv_by_url' => JText::_('INV_METHOD_INV_BY_URL'),
		'other_tools' => JText::_('INV_METHOD_OTHER_TOOLS'),
		'social_apis' => '',
		'email_apis' => '',
		'sms_apis' => '',
		'oi_social' => JText::_('INV_METHOD_OI_SOCIAL'),
		'oi_email' => JText::_('INV_METHOD_OI_EMAIL'),
		'js_messaging' => JText::_('INV_METHOD_JSMESSAGING')
		);

		$this->img_path                = $invite_methods = $input->get('invite_method');
		$this->showonly_invite_methods = 1;
		$select_layout                 = $this->invitex_params->get("inv_look");

		if ($select_layout == 2)
		{
			$layout = $input->get('layout', 'black_white');
		}
		elseif ($select_layout == 0)
		{
			$layout = $input->get('layout', '');
		}
		elseif ($select_layout == 1)
		{
			$layout = $input->get('layout', 'fb');
		}

		if (empty($this->invite_url))
		{
			$this->invite_url = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $this->itemid, false);
		}

		// Add Easysocial/Jomsocial toolbar
		if ($layout != 'preview')
		{
			$get_toolbarof	= '';

			if ($invitex_params->get("reg_direct") == "JomSocial"  && $invitex_params->get("jstoolbar") == '1')
			{
				$get_toolbarof	= 'JomSocial';
				$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject($get_toolbarof);
				echo $sociallibraryobj->getToolbar();
			}

			if ($invitex_params->get("reg_direct") == "EasySocial"  && $invitex_params->get("estoolbar") == '1')
			{
				// To load language file for easy social toolbar
				$lang = JFactory::getLanguage();
				$extension = 'com_easysocial';
				$base_dir = JPATH_SITE;
				$language_tag = 'en-GB';
				$reload = true;
				$lang->load($extension, $base_dir, $language_tag, $reload);

				$get_toolbarof	= 'EasySocial';
				$sociallibraryobj = $this->invhelperObj->getSocialLibraryObject($get_toolbarof);
				echo $sociallibraryobj->getToolbar();
			}
		}



		if ($layout != 'default' and $layout)
		{
			parent::display($layout);
		}
		else
		{
			parent::display();
		}
	}
}
