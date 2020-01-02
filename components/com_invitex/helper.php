<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

if (!class_exists('cominvitexHelper'))
{
	/**
	 * Helper functions for Innvitex
	 *
	 * @since  1.0
	 */
	class CominvitexHelper
				{
		/**
		 * Helper functions for Innvitex
		 *
		 * @since  1.0
		 */
		public function __construct()
		{
			$this->invitex_params = cominvitexHelper::getconfigData();
			jimport('techjoomla.jsocial.jsocial');
			jimport('techjoomla.jsocial.joomla');

			jimport('techjoomla.jsocial.jomwall');
			jimport('techjoomla.jsocial.alphauserpoints');
			$integration_option = $this->invitex_params->get('reg_direct');

			if ($integration_option == 'Community Builder')
			{
				jimport('techjoomla.jsocial.cb');
			}
			elseif ($integration_option == 'JomSocial')
			{
				jimport('techjoomla.jsocial.jomsocial');
			}
			elseif ($integration_option == 'Jomwall')
			{
				jimport('techjoomla.jsocial.jomwall');
			}
			elseif ($integration_option == 'EasySocial')
			{
				jimport('techjoomla.jsocial.easysocial');
			}

			$this->sociallibraryobj = cominvitexHelper::getSocialLibraryObject();
			$app                    = JFactory::getApplication();

			if ($app->isSite())
			{
				cominvitexHelper::getLanguageConstantForJs();
			}
		}

		/**
		 * checks for view override
		 *
		 * @param   string  $viewname       Name of view
		 * @param   string  $layout         Layout name eg order
		 * @param   string  $searchTmpPath  It may be admin or site. it is side(admin/site) where to search override view
		 * @param   string  $useViewpath    It may be admin or site. it is side(admin/site) which VIEW shuld be use IF OVERRIDE IS NOT FOUND
		 *
		 * @return  if exit override view then return path
		 *
		 * @since  1.0
		 */
		public function getViewpath($viewname, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
		{
			$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
			$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
			$app           = JFactory::getApplication();

			if (!empty($layout))
			{
				$layoutname = $layout . '.php';
			}
			else
			{
				$layoutname = "default.php";
			}

			$override = $searchTmpPath . '/templates/' . $app->getTemplate() . '/html/com_invitex/' . $viewname . '/' . $layoutname;

			if (JFile::exists($override))
			{
				return $view = $override;
			}
			else
			{
				return $view = $useViewpath . '/components/com_invitex/views/' . $viewname . '/tmpl/' . $layoutname;
			}
		}

		/**
		 * Load Assets which are require for quick2cart.
		 *
		 * @return  null.
		 *
		 * @since   3.0
		 */
		public static function loadInvitexAssetFiles()
		{
			// Load css files
			$comparams = JComponentHelper::getParams('com_invitex');
			$currentBSViews = $comparams->get('currentBSViews', "bs3");
			$laod_boostrap = $comparams->get('loadBootstrap', 1);

			// Define wrapper class
			if (!defined('INVITEX_WRAPPER_CLASS'))
			{
				$wrapperClass = "invitex-wrapper";
				$currentBSViews = $comparams->get('currentBSViews', "bs3");

				if ($currentBSViews == "bs3")
				{
					$wrapperClass = " invitex-wrapper tjBs3 ";
				}
				else
				{
					$wrapperClass = " invitex-wrapper techjoomla-bootstrap ";
				}

				define('INVITEX_WRAPPER_CLASS', $wrapperClass);
			}
		}

		/**
		 * This function return array of js files which is loaded from tjassesloader plugin.
		 *
		 * @param   string  &$jsFilesArray                  Js file's array.
		 *
		 * @param   array   &$firstThingsScriptDeclaration  javascript to be declared first.
		 *
		 * @return   Files array
		 *
		 * @since  1.0
		 */
		public function getInvitexJsFiles(&$jsFilesArray, &$firstThingsScriptDeclaration)
		{
			$document  = JFactory::getDocument();
			$input = JFactory::getApplication()->input;
			$option = $input->get('option', '', 'STRING');
			$view = $input->get('view', '', 'STRING');
			$task = $input->get('task', '', 'STRING');
			$controller = $input->get('controller', '', 'STRING');
			$layout   = $input->get('layout', '');
			$app    = JFactory::getApplication();

			// Load css files
			$comparams = JComponentHelper::getParams('com_invitex');
			$currentBSViews = $comparams->get('currentBSViews', "bs3");
			$laod_boostrap = $comparams->get('loadBootstrap', 1);

			if ($app->isSite())
			{
				if ($currentBSViews == "bs3")
				{
					// Load Css
					if (!empty($laod_boostrap))
					{
						$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/bs3/css/bootstrap.min.css');
						$jsFilesArray[] = 'media/techjoomla_strapper/bs3/js/bootstrap.min.js';
					}
				}
				elseif ($currentBSViews == "bs2")
				{
					if (!empty($laod_boostrap))
					{
						$document->addStyleSheet(JUri::root(true) . '/media/jui/css/bootstrap.min.css');
					}
				}

				if (($option == "com_invitex" && $view == "invites"))
				{
					// Weird fix for load more contacts twitter- this needs to be sorted out later after v2.9.7
					if ($task !== 'get_contacts')
					{
						if ($layout != 'unsubscribe' && $task != 'unSubscribeConfirm'
							&& $task != 'isCaptchaCorrect' && $task !== 'add_friend'
							&& $task !== 'userConsent' && $task !== 'revokeUserConsent'
							&& ($controller != 'invites' && $task != 'save'))
						{
							require_once JPATH_SITE . '/components/com_invitex/views/invites/tmpl/js_defines.php';
						}
					}

					$jsFilesArray[] = 'media/com_invitex/js/bootstrap-tokenfield.min.js';
					$jsFilesArray[] = 'media/com_invitex/js/invite.js';
					$jsFilesArray[] = 'media/com_invitex/js/jquery.quicksearch.js';
				}
			}

			$firstThingsScriptDeclaration[] = " var invitex_root_url='" . JUri::root() . "';";

			return $jsFilesArray;
		}

		/**
		 * To load given template
		 *
		 * @param   string  $viewobj   Name of view
		 * @param   string  $viewname  Name of view
		 * @param   string  $layout    Layout name eg order
		 *
		 * @return  if exit override view then return path
		 *
		 * @since  1.0
		 */
		public function loadJoomlaTemplate($viewobj, $viewname, $layout)
		{
			$input = JFactory::getApplication()->input;

			$option             = $input->get('option', '');
			$this->invhelperObj = new cominvitexHelper;

			$app       = JFactory::getApplication();
			$core_file = JPATH_BASE . '/components/com_invitex/views/' . $viewname . '/tmpl/default_' . $layout . '.php';
			$override  = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/com_invitex/' . $viewname . '/default_' . $layout . '.php';

			if (JFile::exists($override))
			{
				return $override;
			}
			else
			{
				return $core_file;
			}
		}

		/**
		 * This function return array of js files which is loaded from tjassesloader plugin.
		 *
		 * @return  void
		 *
		 * @since  1.0
		 */
		public function getInvitexCSSFiles()
		{
		}

		/**
		 * This function returns the config data of Invitex
		 *
		 * @return  void
		 *
		 * @since  1.0
		 */
		public function getconfigData()
		{
			$db    = JFactory::getDbo();
			$query = "SELECT namekey,value FROM `#__invitex_config`  ";
			$db->setQuery($query);
			$res    = $db->loadObjectList();
			$config = array();
			$params = JComponentHelper::getParams('com_invitex');

			// For other params
			foreach ($res as $ind => $obj)
			{
				$params_inxml = $params->get($obj->namekey);

				if (!isset($params_inxml))
				{
					$params->set($obj->namekey, $obj->value);
				}
			}

			return $params;
		}

		/**
		 * To get number of Invites sent by a user
		 *
		 * @param   INT  $uid  id of a user
		 *
		 * @return  cnt of number of Invites sent by a user
		 *
		 * @since  1.0
		 */
		public function getInvitesSent($uid)
		{
			$cnt = 0;

			if ($uid)
			{
				$db    = JFactory::getDbo();
				$query = "SELECT SUM(invites_count) FROM `#__invitex_imports` WHERE inviter_id='" . $uid . "' ";
				$db->setQuery($query);
				$cnt = $db->loadResult();
			}

			return $cnt;
		}

		/**
		 * To get Object Invitation limit enforced and invitation sent
		 *
		 * @return  Object
		 *
		 * @since  1.0
		 */
		public function getInvitesLimitData()
		{
			$cnt = 0;
			$uid = cominvitexHelper::getUserID();
			$res = new stdClass;

			if ($uid)
			{
				$db    = JFactory::getDbo();
				$query = "SELECT il.limit FROM `#__invitex_invitation_limit` as il WHERE il.userid='" . $uid . "'";
				$db->setQuery($query);
				$res                   = $db->loadObject();

				if (!$res)
				{
					$res = new stdclass;
				}

				$res->invitations_sent = (cominvitexHelper::getInvitesSent($uid)) ? cominvitexHelper::getInvitesSent($uid) : '0';
			}

			return $res;
		}

		/**
		 * Get Referal URL of loggedin User
		 *
		 * @return  url
		 *
		 * @since  1.0
		 */
		public function getinviteURL()
		{
			$uid       = '';
			$uid       = cominvitexHelper::getUserID();
			$in_itemid = cominvitexHelper::getitemid('index.php?option=com_invitex&view=invites');
			$inv_url = "index.php?option=com_invitex&task=sign_up&Itemid=" . $in_itemid . "&inviter_id=" . md5($uid);
			$url       = JUri::root() . substr(JRoute::_($inv_url, false), strlen(JUri::base(true)) + 1);

			return $url;
		}

		/**
		 * Get Referal URL of Provided user id
		 *
		 * @param   INT  $namecard_id  id of a user
		 *
		 * @return  Object
		 *
		 * @since  1.0
		 */
		public function getnamecardinviteURL($namecard_id)
		{
			$in_itemid = cominvitexHelper::getitemid('index.php?option=com_invitex&view=invites');
			$inv_url = "index.php?option=com_invitex&task=sign_up&Itemid=" . $in_itemid . "&inviter_id=" . md5($namecard_id);
			$url       = JUri::root() . substr(JRoute::_($inv_url, false), strlen(JUri::base(true)) + 1);

			return $url;
		}

		/**
		 * Get invitation type data
		 *
		 * @param   INT  $invite_type  id of a invitation type
		 *
		 * @return  Object
		 *
		 * @since  1.0
		 */
		public function types_data($invite_type)
		{
			if (empty($invite_type))
			{
				$input = JFactory::getApplication()->input;
				$invite_type = $input->get('invite_type', '', 'INT');
			}

			$database = JFactory::getDbo();
			$sql      = "SELECT * FROM #__invitex_types WHERE id=" . $invite_type;
			$database->setQuery($sql);

			return ($database->loadObject());
		}

		/**
		 * Get Registration, Join and unsubscribe links
		 *
		 * @param   INT  $refid                        id of invitex_imports_email table
		 * @param   INT  $custom_landing_page_visited  optional to chek if triggered form userinfo plugin
		 *
		 * @return  Array of links
		 *
		 * @since  1.0
		 */
		public function get_links($refid, $custom_landing_page_visited = '')
		{
			$refid      = trim($refid);
			$encoded_id = MD5($refid);

			$in_itemid = cominvitexHelper::getitemid('index.php?option=com_invitex&view=invites');

			$inv_extra_variables = '';

			if ($this->invitex_params->get('ga_campaign_enable'))
			{
				$inv_extra_variables .= "&utm_campaign=" . $this->invitex_params->get('ga_campaign_enable');
				$inv_extra_variables .= "&utm_source=" . $this->invitex_params->get('ga_campaign_source');
				$inv_extra_variables .= "&utm_medium=" . $this->invitex_params->get('ga_campaign_medium');
			}

			if ($custom_landing_page_visited == 1)
			{
				$inv_extra_variables .= "&custom_landing_page_visited=1";
			}

			$url_reg   = "index.php?option=com_invitex&task=sign_up&Itemid=" . $in_itemid . "&id={$encoded_id}";
			$url_join  = "index.php?option=com_invitex&task=sign_up&invite_anywhere=1&Itemid=" . $in_itemid . "&id={$encoded_id}";
			$url_block = "index.php?option=com_invitex&task=unSubscribe&Itemid=" . $in_itemid . "&id={$encoded_id}";

			if ($inv_extra_variables)
			{
				$url_reg .= $inv_extra_variables;
				$url_join .= $inv_extra_variables;
				$url_block .= $inv_extra_variables;
			}

			$url_regi                  = JUri::root() . substr(JRoute::_($url_reg, false), strlen(JUri::base(true)) + 1);
			$links['message_register'] = $url_regi;

			$url_join              = JUri::root() . substr(JRoute::_($url_join, false), strlen(JUri::base(true)) + 1);
			$links['message_join'] = $url_join;

			$url_block                    = JUri::root() . substr(JRoute::_($url_block, false), strlen(JUri::base(true)) + 1);
			$links['message_unsubscribe'] = $url_block;

			return $links;
		}

		/**
		 * Get User id
		 *
		 * @return  id
		 *
		 * @since  1.0
		 */
		public function getUserID()
		{
			$uid = '';
			$my  = JFactory::getUser();

			if ($my->id)
			{
				return $uid = $my->id;
			}
			elseif (isset($_COOKIE['invitex_reg_user']) && $_COOKIE['invitex_reg_user'] != '')
			{
				$uid = $_COOKIE['invitex_reg_user'];
			}
			else
			{
				// IF GUEST
				$uid = 0;
			}

			return $uid;
		}

		/**
		 * Get common template to be used by PM's
		 *
		 * @param   STRING  $attached_msg  optional personal message
		 * @param   INT     $invitor_id    inviter id
		 * @param   STRING  $invite_tag    invite tag to be used in template
		 *
		 * @return  array with common tags
		 *
		 * @since  1.0
		 */
		public function buildCommonPM($attached_msg, $invitor_id, $invite_tag)
		{
			$session = JFactory::getSession();
			$mail    = array();

			$mail['message'] = stripslashes($attached_msg);

			if ($invite_tag)
			{
				$tag                    = str_replace('[', '', $invite_tag);
				$tag                    = str_replace(']', '', $tag);
				$tag_array              = explode('=', $tag);
				$mail['invitetypename'] = $tag_array[1];
			}

			$mail['inviter_id'] = $invitor_id;

			if ($invitor_id != 0)
			{
				$mail['inviter_name']       = JFactory::getUser($invitor_id)->name;
				$mail['inviter_uname']      = JFactory::getUser($invitor_id)->username;
				$mail['inviter_profileurl'] = $this->sociallibraryobj->getProfileUrl(JFactory::getUser($invitor_id));
				$mail['avatar']             = $this->sociallibraryobj->getAvatar(JFactory::getUser($invitor_id));
			}
			else
			{
				$mail['inviter_name']  = $session->get('guest_user');
				$mail['inviter_uname'] = $session->get('guest_user');
				$mail['avatar']        = "";
			}

			$mail['PWIU']    = '';
			$mail['message'] = stripslashes($attached_msg);

			$validity       = $this->invitex_params->get('expiry');
			$expiry         = time() + ($validity * 60 * 60 * 24);
			$mail['expiry'] = $expiry = JHtml::Date($expiry, JText::_('COM_INVIEX_DATE_FORMAT_TO_SHOW'));

			return $mail;
		}

		/**
		 * Get individual PM
		 *
		 * @param   ARRAY   $mail       Array of values
		 * @param   STRING  $name       Name of the invitee
		 * @param   STRING  $invite_id  refid
		 *
		 * @return  array with common tags
		 *
		 * @since  1.0
		 */
		public function buildPM($mail, $name, $invite_id)
		{
			$mail['name'] = $name;

			$links = cominvitexHelper::get_links($invite_id);

			$mail['message_register']    = $links['message_register'];
			$mail['message_join']        = $links['message_join'];
			$mail['message_unsubscribe'] = $links['message_unsubscribe'];

			return $mail;
		}

		/**
		 * Check if package is installed
		 *
		 * @param   STRING  $folder  Name of the folder
		 *
		 * @return  boolean
		 *
		 * @since  1.0
		 */
		public function Checkifinstalled($folder)
		{
			$path = JPATH_SITE . '/components/' . $folder;

			if (JFolder::exists($path))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * Check profile link of a user
		 *
		 * @param   STRING  $to_direct  Integration set with
		 * @param   INT     $user       User whose profile needs to get
		 *
		 * @return  string
		 *
		 * @since  1.0
		 */
		public function getprofilelink($to_direct, $user)
		{
			$link = $this->sociallibraryobj->getProfileUrl($user);

			return $link;
		}

		/**
		 * Check profile avatar of a user
		 *
		 * @param   STRING  $to_direct  Integration set with
		 * @param   INT     $user       User whose profile needs to get
		 *
		 * @return  string
		 *
		 * @since  1.0
		 */
		public function getprofileavatar($to_direct, $user)
		{
			$link = $this->sociallibraryobj->getAvatar($user);

			return $link;
		}

		/**
		 * Check profile avatar of a user
		 *
		 * @param   STRING  $to_direct  Integration set with
		 * @param   INT     $id         User whose profile needs to get
		 *
		 * @return  string
		 *
		 * @since  1.0
		 */
		public function getavatar($to_direct, $id)
		{
			$database = JFactory::getDbo();

			$avatar = '';

			if (strcmp($to_direct, "JomSocial") == 0)
			{
				$path = JPATH_SITE . '/components/com_community';

				if (JFolder::exists($path))
				{
					// Fetching the avatar fron amazon S3
					$user1  = CFactory::getUser($id);
					$uimage = $user1->getThumbAvatar();
					$avatar = str_replace('administrator/', '', $uimage);
				}
			}
			elseif (strcmp($to_direct, "Community Builder") == 0)
			{
				$path = JPATH_SITE . '/components/com_comprofiler';

				if (JFolder::exists($path))
				{
					$q = "SELECT avatar
				FROM #__comprofiler
				WHERE id=$id";
					$database->setQuery($q);
					$avatar = $database->loadResult();

					if ($avatar)
					{
						$avatar = JUri::base() . "images/comprofiler/" . $avatar;
					}
					else
					{
						$avatar = JUri::base() . "components/com_comprofiler/plugin/language/default_language/images/tnnophoto.jpg";
					}
				}
			}

			return $avatar;
		}

		/**
		 * Get template to be used
		 *
		 * @param   STRING  $invite_type  Invitation type
		 * @param   INT     $action       email,or reminder, fbrequest
		 *
		 * @return  string
		 *
		 * @since  1.0
		 */
		public function get_message_template($invite_type, $action)
		{
			$mail = array();

			if ($invite_type)
			{
				$it_res = cominvitexHelper::types_data($invite_type);

				if ($action == 'reminder')
				{
					$message_body = stripslashes($this->invitex_params->get('reminder_body'));
				}
				elseif ($action == 'fbrequest')
				{
					$message_body = stripslashes($it_res->template_fb_request);
				}
				else
				{
					$message_body = stripslashes($it_res->template_html);
				}
			}
			else
			{
				if ($action == 'reminder')
				{
					$message_body = stripslashes($this->invitex_params->get('reminder_body'));
				}
				elseif ($action == 'fbrequest')
				{
					$message_body = stripslashes($this->invitex_params->get('fb_request_body'));
				}
				else
				{
					$message_body = stripslashes($this->invitex_params->get('message_body'));
				}
			}

			return $message_body;
		}

		/**
		 * Get template message
		 *
		 * @param   STRING  $invite_type  Invitation type
		 * @param   INT     $action       email,or reminder, fbrequest
		 *
		 * @return  string
		 *
		 * @since  1.0
		 */
		public function get_message_subject($invite_type, $action)
		{
			$mail = array();

			if ($invite_type)
			{
				$it_res          = cominvitexHelper::types_data($invite_type);
				$message_subject = stripslashes($it_res->template_html_subject);
			}
			else
			{
				$message_subject = stripslashes($this->invitex_params->get('message_subject'));
			}

			return $message_subject;
		}

		/**
		 * Get invitetypename
		 *
		 * @param   STRING  $invite_type      Invitation type
		 * @param   INT     $invite_type_tag  Tag to be used
		 *
		 * @return  string
		 *
		 * @since  1.0
		 */
		public function getinvitetypename($invite_type, $invite_type_tag)
		{
			if ($invite_type_tag)
			{
				$tag            = $invite_type_tag;
				$tag            = str_replace('[', '', $tag);
				$tag            = str_replace(']', '', $tag);
				$tag_array      = explode('=', $tag);
				$invitetypename = $tag_array[1];
			}
			else
			{
				$invitetypename = '';
			}

			return $invitetypename;
		}

		/**
		 * Method to replace the tags in the message body
		 *
		 * @param   INT     $inviter_id       inviterid
		 * @param   INT     $refid            unique refid
		 * @param   String  $message          optional messgae
		 * @param   String  $invitee_mail     invitee_mail
		 * @param   String  $invitee_name     invitee_name
		 * @param   String  $expires          expires
		 * @param   String  $invite_type      invite_type
		 * @param   String  $invite_type_tag  invitee_name
		 * @param   String  $clp_visited      custom_landing_page_visited
		 *
		 * @return  array of the replacements
		 *
		 * @since  1.0
		 */
		public function getMailtagsinarray($inviter_id,$refid,$message,$invitee_mail,$invitee_name,$expires,$invite_type,$invite_type_tag,$clp_visited = 0)
		{
			$mail = array();

			$to_direct = $this->invitex_params->get('reg_direct');

			$mail['invitetypename'] = cominvitexHelper::getinvitetypename($invite_type, $invite_type_tag);

			$noname = 0;

			if ($invitee_name)
			{
				if (strpos($invitee_name, '@') != false)
				{
					$noname = 1;
				}
				else
				{
					$mail['name'] = $invitee_name;
				}
			}
			else
			{
				$noname = 1;
			}

			if ($noname == 1)
			{
				$invitee      = explode("@", $invitee_mail);
				$mail['name'] = $invitee[0];
			}

			$mail['inviter_id'] = $inviter_id;

			// IF REGISTERED USER
			if ($inviter_id != 0)
			{
				$mail['inviter_name']  = JFactory::getUser($inviter_id)->name;
				$mail['inviter_uname'] = JFactory::getUser($inviter_id)->username;
				$avatar                = $this->sociallibraryobj->getAvatar(JFactory::getUser($inviter_id));
				$mail['avatar']        = $avatar;
				$inviter_profileurl    = $this->sociallibraryobj->getProfileUrl(JFactory::getUser($inviter_id));
			}
			else
			{
				$mail['avatar'] = '';
			}

			$links                       = cominvitexHelper::get_links($refid, $clp_visited);
			$mail['message_register']    = "<a class='btn btn-success' href='" . $links['message_register'] . "' target=\"_blank\">";
			$mail['message_register']   .= JText::_('SIGN_UP') . "</a>";
			$mail['message_join']        = "<a href='" . $links['message_join'] . "' target=\"_blank\">" . JText::_('JOIN') . "</a>";
			$mail['message_unsubscribe'] = "<a href='" . $links['message_unsubscribe'] . "' target=\"_blank\">" . JText::_('UNSUBSCRIBE') . "</a>";

			if (isset($inviter_profileurl))
			{
				$mail['inviter_profileurl']  = "<a href='" . $inviter_profileurl . "' target=\"_blank\">" . JText::_('INVITER_PROFILE') . "</a>";
			}

			$mail['message']             = nl2br($message);

			$mail['expiry'] = JHtml::Date($expires, JText::_('COM_INVIEX_DATE_FORMAT_TO_SHOW'));

			if ($invitee_mail)
			{
				$modelpath = JPATH_SITE . '/components/com_invitex/models/invites.php';
				require_once $modelpath;
				$invModelObj  = new InvitexModelInvites;
				$mail['pwiu'] = $invModelObj->Getpplwhohaveinvitedbefore($invitee_mail, $to_direct, $inviter_id);
			}

			return $mail;
		}

		/**
		 * Method to replace the tags in the message body for preview
		 *
		 * @param   INT     $inviter_id       inviterid
		 * @param   INT     $refid            unique refid
		 * @param   String  $message          optional messgae
		 * @param   String  $invitee_mail     invitee_mail
		 * @param   String  $invitee_name     invitee_name
		 * @param   String  $expires          expires
		 * @param   String  $invite_type      invite_type
		 * @param   String  $invite_type_tag  invitee_name
		 *
		 * @return  array of the replacements
		 *
		 * @since  1.0
		 */
		public function getMailtagsinarrayPreview($inviter_id,$refid,$message,$invitee_mail,$invitee_name,$expires,$invite_type,$invite_type_tag)
		{
			$mail  = array();
			$input = JFactory::getApplication()->input;

			$to_direct = $this->invitex_params->get('reg_direct');

			$mail['invitetypename'] = cominvitexHelper::getinvitetypename($invite_type, $invite_type_tag);

			$noname = 0;

			if ($invitee_name)
			{
				if (strpos($invitee_name, '@') != false)
				{
					$noname = 1;
				}
				else
				{
					$mail['name'] = $invitee_name;
				}
			}
			else
			{
				$noname = 1;
			}

			if ($noname == 1)
			{
				$invitee      = explode("@", $invitee_mail);
				$mail['name'] = $invitee[0];
			}

			$mail['inviter_id'] = $inviter_id;

			if ($inviter_id)
			{
				$mail['inviter_name']       = JFactory::getUser($inviter_id)->name;
				$mail['inviter_uname']      = JFactory::getUser($inviter_id)->username;
				$mail['inviter_profileurl'] = $this->sociallibraryobj->getProfileUrl(JFactory::getUser($inviter_id));
			}
			else
			{
				$session = JFactory::getSession();

				if ($session->get('guest_user'))
				{
					$mail['inviter_name']  = $session->get('guest_user');
					$mail['inviter_uname'] = $session->get('guest_users');
				}

				if ($input->get('guest_user'))
				{
					$mail['inviter_name']  = $input->get('guest_user');
					$mail['inviter_uname'] = $input->get('guest_user');
				}
			}

			if ($inviter_id != 0)
			{
				$avatar         = $this->sociallibraryobj->getAvatar(JFactory::getUser($inviter_id));
				$mail['avatar'] = $avatar;
			}

			$mail['message_register']    = "<a href='#' target=\"_blank\">" . JText::_('SIGN_UP') . "</a>";
			$mail['message_join']        = "<a href='#' target=\"_blank\">" . JText::_('JOIN') . "</a>";
			$mail['message_unsubscribe'] = "<a href='#' target=\"_blank\">" . JText::_('UNSUBSCRIBE') . "</a>";

			$mail['message'] = nl2br($message);
			$mail['expiry']  = JHtml::Date($expires, JText::_('COM_INVIEX_DATE_FORMAT_TO_SHOW'));
			$mail['pwiu']    = "[PWIU] " . JText::_('REPLACE_LATER');

			return $mail;
		}

		/**
		 * Method to replace the tags in the message body for preview
		 *
		 * @param   ARRAY  $msg   message array
		 * @param   INT    $flag  optional boolean field
		 *
		 * @return  array of the replacements
		 *
		 * @since  1.0
		 */
		public function tagreplace($msg, $flag = ' ')
		{
			$session      = JFactory::getSession();
			$message_body = stripslashes($msg['msg_body']);

			if (isset($msg['name']))
			{
				$message_body = str_replace("[NAME]", $msg['name'], $message_body);
			}

			// If tags not found then replace with blank

			if (isset($msg['inviter_name']))
			{
				$message_body = str_replace("[INVITER_NAME]", $msg['inviter_name'], $message_body);
				$message_body = str_replace("[INVITER]", $msg['inviter_name'], $message_body);
			}
			else
			{
				$message_body = str_replace("[INVITER_NAME]", '', $message_body);
				$message_body = str_replace("[INVITER]", '', $message_body);
			}

			// If tags not found then replace with blank

			if (isset($msg['inviter_uname']))
			{
				$message_body = str_replace("[INVITER_UNAME]", $msg['inviter_uname'], $message_body);
			}
			else
			{
				$message_body = str_replace("[INVITER_UNAME]", '', $message_body);
			}

			if (isset($msg['message']))
			{
				$message_body = str_replace("[MESSAGE]", $msg['message'], $message_body);
			}

			if (isset($msg['message_register']))
			{
				$message_body = str_replace("[SUBSCRIBE]", trim($msg['message_register']), $message_body);
			}

			if (isset($msg['message_join']))
			{
				$message_body = str_replace("[JOIN]", ' ' . $msg['message_join'], $message_body);
			}

			if (isset($msg['message_unsubscribe']))
			{
				$message_body = str_replace("[UNSUBSCRIBE]", $msg['message_unsubscribe'], $message_body);
			}

			if (isset($msg['pwiu']))
			{
				$message_body = str_replace("[PWIU]", $msg['pwiu'], $message_body);
			}

			if (isset($msg['expiry']))
			{
				$message_body = str_replace("[EXPIRYDAYS]", $msg['expiry'], $message_body);
			}

			// Variable to store different tags

			if (!empty($msg['invitetypename']))
			{
				$urlinvitetypename = explode("|", $msg['invitetypename']);

				// Get only name of invite type
				$msg['invitetypename'] = $urlinvitetypename[0];

				$message_body = str_replace("[INVITETYPENAME]", $msg['invitetypename'], $message_body);
			}

			if ($this->invitex_params->get('reg_direct') !== "Joomla")
			{
				if (isset($msg['inviter_profileurl']))
				{
					$message_body = str_replace("[INVITER_PROFILEURL]", $msg['inviter_profileurl'], $message_body);
				}
				else
				{
					$message_body = str_replace("[INVITER_PROFILEURL]", '', $message_body);
				}
			}
			else
			{
				$message_body = str_replace("[INVITER_PROFILEURL]", '', $message_body);
			}

			if (isset($msg['nooffriends']))
			{
				$message_body = str_replace("[NUMBEROFFRIENDS]", $msg['nooffriends'], $message_body);
			}

			if (isset($msg['friendsonsite']))
			{
				$message_body = str_replace("[FRINEDSONSITE]", $msg['friendsonsite'], $message_body);
			}

			// Replace the long URL with short
			$message_body = cominvitexHelper::givShortURL($message_body, $flag);

			if (isset($msg['avatar']))
			{
				$avatar_src	=	$msg['avatar'];

				// If Avatar does not have http in it,its relative url.. add Juri to get the absolute
				if (!parse_url($msg['avatar'], PHP_URL_HOST))
				{
					$avatar_src	=	JUri::root() . $avatar_src;
				}

				$avtar_img	=	"<img src='" . $avatar_src . "' class='inv_avatar' style='border-radius: 50%'>";
				$message_body = str_replace("[AVATAR]", $avtar_img, $message_body);
			}

			$img_path = 'img src="' . JUri::root();
			$message_body = str_replace('img src="' . JUri::root(), 'img src="', $message_body);
			$message_body = str_replace('img src="', $img_path, $message_body);
			$message_body = str_replace("background: url('" . JUri::root(), "background: url('", $message_body);
			$message_body = str_replace("background: url('", "background: url('" . JUri::root(), $message_body);

			$app          = JFactory::getApplication();
			$sitename     = $app->getCfg('sitename');
			$message_body = str_replace("[SITENAME]", $sitename, $message_body);

			$message_body = str_replace("[SITEURL]", "<a class='inv_siteurl' href='" . JUri::base() . "' >" . $sitename . "</a>", $message_body);

			$message_body = str_replace("[SITELINK]", "<a class='inv_sitelink' href='" . JUri::base() . "' >" . $sitename . "</a>", $message_body);

			/* TO Replace Language constants used in the template*/
			if (isset($msg['inviter_id']) && $msg['inviter_id'] != '')
			{
				$inviter = JFactory::getUser($msg['inviter_id']);

				$tag = $inviter->getParam('language');

				if (strpos($message_body, '{') !== false)
				{
					preg_match_all("/{([^}]*)}/", $message_body, $matches);

					foreach ($matches[1] as $con)
					{
						/*if lang constant, it will be in uppercase*/
						if (strtoupper($con) != $con)
						{
							continue;
						}

						$extension = 'com_invitex';

						/* Get the Joomla core language object */
						$language  = JFactory::getLanguage();

						/* Set the base directory for the language */
						$base_dir  = JPATH_SITE;

						/* Load the language */
						$language->load($extension, $base_dir, $tag, true);
						$jsSafe               = false;
						$interpretBackSlashes = true;
						$replacer             = JText::_($con);
						$message_body         = str_replace("{" . $con . "}", $replacer, $message_body);
					}
				}

				/* TO Replace Profile fields of user used in the template*/
				preg_match_all("/\[.*?\]/", $message_body, $matches);

				if (!empty($matches[0]))
				{
					$jspath    = JPATH_ROOT . '/components/com_community';
					$cbpath    = JPATH_ROOT . '/components/com_comprofiler';
					$jsprofile = '';

					if (JFolder::exists($jspath))
					{
						$lang = JFactory::getLanguage();
						$lang->load('com_community.country', JPATH_ROOT);

						$lang_js = JFactory::getLanguage();
						$lang_js->load('com_community', JPATH_ADMINISTRATOR, 'en-GB', true);

						include_once $jspath . '/libraries/core.php';
						$jsprofile = CFactory::getUser($msg['inviter_id']);
					}

					$esuser = '';
					$espath = JPATH_ROOT . '/components/com_easysocial';

					if (JFolder::exists($espath))
					{
						$lang = JFactory::getLanguage();
						$lang->load('com_easysocial', JPATH_ADMINISTRATOR, 'en-GB', true);

						require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
						$esuser = Foundry::user($msg['inviter_id']);
					}

					foreach ($matches[0] as $field)
					{
						$field1    = str_replace("[", " ", $field);
						$field1    = str_replace("]", " ", $field1);
						$field_arr = explode(":", $field1);
						$db        = JFactory::getDbo();

						if (isset($field_arr[1]))
						{
							$column = $field_arr[1];
							$res    = '';

							if (trim($field_arr[0]) == "cbfield" && JFolder::exists($cbpath))
							{
								$query = "SELECT $column FROM #__comprofiler WHERE id = " . $msg['inviter_id'];
								$db->setQuery($query);
								$res = $db->loadResult();
							}

							if (trim($field_arr[0]) == "jsfield" && $jsprofile)
							{
								$res = $jsprofile->getInfo($column);
							}

							// Replace easysocial field tags with field values of inviter.
							if (trim($field_arr[0]) == "esfield" && $esuser != '')
							{
								$res = $esuser->getFieldValue($column);

								// Since we are printing user data, lets implode array and return string
								if (is_array($res))
								{
									$res = implode(",", $res);
								}
							}

							if (!empty($res))
							{
								$message_body = str_replace($field, JText::_($res), $message_body);
							}
							else
							{
								$message_body = str_replace($field, "", $message_body);
							}
						}
					}
				}
			}

			return $message_body;
		}

		/**
		 * strips the long urls to short url with Google shortening
		 *
		 * @param   String  $string  string to be shortened
		 * @param   INT     $flag    optional boolean parameter
		 *
		 * @return  string  with shortened urls
		 *
		 * @since  1.0
		 */
		public function givShortURL($string, $flag = '')
		{
			if (!$this->invitex_params->get('urlapi') || $flag == 1)
			{
				return $string;
			}

			/* Removed for now as bitly giving 503 errors on some site
			/*if ($flag == 'bitly')
			{
			$string = cominvitexHelper::givShortURLBitly($string);
			}
			else
			{
			  $string = cominvitexHelper::givShortURLGoogle($string);
			}*/

			$string = cominvitexHelper::givShortURLGoogle($string);

			return $string;
		}

		/**
		 * strips the long urls to short url with Google shortening
		 *
		 * @param   String  $string  string to be shortened
		 *
		 * @return  string  with shortened urls
		 *
		 * @since  1.0
		 */
		public function givShortURLGoogle($string)
		{
			require_once JPATH_SITE . '/components/com_invitex/controllers/googlshorturl.php';
			$api_key = '';
			$api_key = $this->invitex_params->get('url_apikey');

			// If Api keys are not provided then return url as it is
			if (empty($api_key))
			{
				return $string;
			}

			$goo = new Googl($api_key);

			// Replacement of url in title
			$regex = "/((https?\:\/\/|ftps?\:\/\/)|(www\.))(\S+)(\w{1,5})(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i";
			preg_match_all($regex, $string, $matches);

			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $match)
				{
					$shorturl = $goo->set_short($match);

					if (!empty($shorturl['id']))
					{
						$string   = str_replace($match, $shorturl['id'], $string);
					}
				}
			}

			return $string;
		}

		/**
		 * strips the long urls to short url with Bitly shortening
		 *
		 * @param   String  $string  string to be shortened
		 *
		 * @return  string  with shortened urls
		 *
		 * @since  1.0
		 */
		public function givShortURLBitly($string)
		{
			require_once JPATH_SITE . '/components/com_invitex/controllers/bitlyshorturl.php';
			$api_key = '';
			$api_key = $this->invitex_params->get('url_apikey_bitly');
			$api_key = trim($api_key);

			// If bitly api key absent Use google url shortening
			if (!$api_key)
			{
				return $string = givShortURLGoogle($string);
			}

			$bitly = new Bitly;

			// Replacement of url in title
			$regex = "/((https?\:\/\/|ftps?\:\/\/)|(www\.))(\S+)(\w{1,5})(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i";
			preg_match_all($regex, $string, $matches);

			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $match)
				{
					$shorturl = $bitly->set_short($match, $api_key);
					$string   = str_replace($match, $shorturl['url'], $string);
				}
			}

			return $string;
		}

		/**
		 * Get ItemId function
		 *
		 * @param   string   $link          URL to find itemid for
		 *
		 * @param   integer  $skipIfNoMenu  return 0 if no menu is found
		 *
		 * @return  integer  $itemid
		 */
		public function getitemid($link, $skipIfNoMenu = 0)
		{
			$itemid    = 0;
			$mainframe = JFactory::getApplication();

			if ($mainframe->issite())
			{
				$menu = $mainframe->getMenu();
				$items = $menu->getItems('link', $link);

				if (isset($items[0]))
				{
					$itemid = $items[0]->id;
				}
			}

			if (!$itemid)
			{
				$db = JFactory::getDbo();

				if (JVERSION >= 3.0)
				{
					$query = "SELECT id FROM #__menu
				WHERE link LIKE '%" . $link . "%'
				AND published =1
				LIMIT 1";
				}
				else
				{
					$query = "SELECT id FROM " . $db->quoteName('#__menu') . "
				WHERE link LIKE '%" . $link . "%'
				AND published =1
				ORDER BY ordering
				LIMIT 1";
				}

				$db->setQuery($query);
				$itemid = $db->loadResult();
			}

			if (!$itemid)
			{
				if ($skipIfNoMenu)
				{
					$itemid = 0;
				}
				else
				{
					$jinput = JFactory::getApplication()->input;
					$itemid = JFactory::getApplication()->input->getInt('Itemid', 0);
				}
			}

			return $itemid;
		}

		/**
		 * Set variables in session
		 *
		 * @return  void
		 */
		public function setSession()
		{
			$session = JFactory::getSession();
			$session->set('inv_orkut_contacts', '');
			$session->set('unsubscribe_mails', '');
			$session->set('registered_mails', '');
			$session->set('already_invited_mails', '');
			$session->set('email_box', '');
			$session->set('provider_box', '');
			$session->set('plugType', '');
			$session->set('rout', '');
			$session->set('import_type', '');
			$session->set('oi_session_id', '');
			$session->set('OI_plugType', '');
		}

		/**
		 * Push activity stream
		 *
		 * @param   MIXED  $contentdata  array with required values
		 *
		 * @return  void
		 */
		public function pushtoactivitystream($contentdata)
		{
			$actor_id           = $contentdata['user_id'];
			$integration_option = $contentdata['integration_option'];
			$act_access         = 0;
			$act_description    = $contentdata['act_description'];
			$act_type           = '';
			$act_subtype        = '';
			$act_link           = '';
			$act_title          = '';
			$act_access         = 0;

			$result = $this->sociallibraryobj->pushActivity($actor_id, $act_type, $act_subtype, $act_description, $act_link, $act_title, $act_access);

			if (!$result)
			{
				return false;
			}

			return true;
		}

		/**
		 * Send notification email
		 *
		 * @param   INT     $invitee_id  invitee id
		 * @param   String  $username    username
		 * @param   INT     $inviter_id  invitee id
		 * @param   String  $body        notification body
		 *
		 * @return  void
		 */
		public function send_notification_emails($invitee_id, $username, $inviter_id, $body)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT name,email FROM #__users WHERE id=" . $inviter_id;
			$db->setQuery($query);
			$inviter_info = $db->loadAssoclist();

			// Find and replace
			$mainframe = JFactory::getApplication();
			$find      = array(
				'{NAME}',
				'{FRIEND}',
				'{SITENAME}'
			);

			$replace   = array(
				$inviter_info[0]['name'],
				$username,
				$mainframe->getCfg('sitename')
			);

			// Mail content
			$body = str_replace($find, $replace, $body);
			$from = $mainframe->getCfg('mailfrom');

			$fromname = $mainframe->getCfg('fromname');

			$recipient = $inviter_info[0]['email'];
			$subject   = JText::_('INV_MAIL_SUBJECT');
			$body      = nl2br($body);

			$mode        = 1;
			$cc          = null;
			$bcc         = null;
			$bcc         = null;
			$attachment  = null;
			$replyto     = null;
			$replytoname = null;

			try
			{
				JFactory::getMailer()->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
			}
			catch (Exception $e)
			{
				echo $e->getMessage() . "\n";
			}
		}

		/**
		 * Get Social libarary object
		 *
		 * @param   String  $integration_option  integrtion set
		 *
		 * @return  Object
		 */
		public function getSocialLibraryObject($integration_option = '')
		{
			$SocialLibraryObject = new StdClass;

			if (!$integration_option)
			{
				$integration_option = $this->invitex_params->get('reg_direct');
			}

			if ($integration_option == 'Community Builder')
			{
				$SocialLibraryObject = new JSocialCB;
			}
			elseif ($integration_option == 'JomSocial')
			{
				$SocialLibraryObject = new JSocialJomSocial;
			}
			elseif ($integration_option == 'Jomwall')
			{
				$SocialLibraryObject = new JSocialJomwall;
			}
			elseif ($integration_option == 'EasySocial')
			{
				$SocialLibraryObject = new JSocialEasySocial;
			}
			elseif ($integration_option == 'Joomla')
			{
				$SocialLibraryObject = new JSocialJoomla;
			}

			return $SocialLibraryObject;
		}

		/**
		 * This define the lanugage contant which you have use in js file.
		 *
		 * @return void
		 */
		public static function getLanguageConstantForJs()
		{
			JText::script('ATLEAST_ONE');
			JText::script('ALL_WRONG_EMAILS');
			JText::script('INVITES_LEFT_MSG');
			JText::script('INCORRECT_EMAILS_REMOVED');
			JText::script('COM_INVITEX_NOT_VALID_EMAIL');
			JText::script('COM_INVITEX_SELF_INVITATION_ERROR');
			JText::script('COM_INVITEX_DOMAIN_NOT_ALLOWED');
			JText::script('COM_INVITEX_ERROR_LOADING_DOC');
			JText::script('COM_INVITEX_CAPTCHA_INVALID_NAME_ERROR');
			JText::script('COM_INVITEX_CAPTCHA_ERROR_MSG');
			JText::script('COM_INVITEX_PASSWORD_ERROR_MSG');
			JText::script('COM_INVITEX_EMPTY_PROVIDER_MSG');
			JText::script('COM_INVITEX_EMPTY_CSV_MSG');
			JText::script('COM_INVITEX_EMPTY_EMAIL_MSG');
			JText::script('COM_INVITEX_EMPTY_NAME_MSG');
			JText::script('COM_INVITEX_ENTER_NUMERICS');
			JText::script('NO_MORE_CONTACTS');
			JText::script('INVITES_LEFT_MSG');
			JText::script('COM_INVITEX_GUEST_NAME_ERROR_MSG');
			JText::script('SELECT_FRIEND');
			JText::script('CLICK_CONTINUE');
			JText::script('CONNECTED');
			JText::script('COM_INVITEX_SOMETHING_WENT_WRONG');
		}

		/**
		 * Get invitation type id from internal name
		 *
		 * @param   String  $internal_name  internal name
		 *
		 * @return  Invitation type id
		 */
		public function geTypeId_By_InernalName($internal_name)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select("id");
			$query->from("#__invitex_types");
			$query->where("internal_name = '" . $internal_name . "'");

			$db->setQuery($query);

			return $result = $db->loadResult();
		}

		/**
		 * Get sites/administrator default template
		 *
		 * @param   mixed  $client  0 for site and 1 for admin template
		 *
		 * @return  json
		 *
		 * @since   1.5
		 */
		public function getSiteDefaultTemplate($client = 0)
		{
			try
			{
				$db    = JFactory::getDbo();

				// Get current status for Unset previous template from being default
				// For front end => client_id=0
				$query = $db->getQuery(true)
							->select('template')
							->from($db->quoteName('#__template_styles'))
							->where('client_id=' . $client)
							->where('home=1');
				$db->setQuery($query);

				return $db->loadResult();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return '';
			}
		}
	}
}

if (!class_exists('techjoomlaHelperLogs'))
{
	/**
	 * this class is used to make log for f/l/t controllers
	 *
	 * @since  1.0
	 */
	class TechjoomlaHelperLogs
				{
		/**
		 * Method to log the comment in provided file
		 *
		 * @param   String  $comment   comment
		 * @param   INT     $userid    userid
		 * @param   String  $type      type
		 * @param   String  $filename  filename
		 * @param   String  $path      path
		 * @param   INT     $display   display
		 * @param   Array   $params    params
		 *
		 * @return  array of the replacements
		 *
		 * @since  1.0
		 */
		public function simpleLog($comment = '', $userid = '', $type = '', $filename= '', $path = "", $display = 1, $params = array())
		{
			if ($path == "" and $type = "plugin")
			{
				if (JVERSION >= '1.6.0')
				{
					$path = JPATH_SITE . '/plugins/' . $params['group'] . '/' . $params['name'] . '/' . $params['name'] . '/error_log';
				}
				else
				{
					$path = JPATH_SITE . '/plugins/' . $params['group'] . '/' . $params['name'] . '/error_log';
				}
			}

			if ($path == "" and $type = "component")
			{
				$path = JPATH_JPATH_COMPONENT . '/error_log';
			}

			if ($userid)
			{
				$my = JFactory::getUser($userid);
			}
			else
			{
				/*$uid 		= $this->getUserID();
				// $my	= JFactory::getUser($uid);*/
			}

			if (isset($params['http_code']))
			{
				$http_code = $params['http_code'];
			}
			else
			{
				$http_code = '';
			}

			if (isset($params['desc']))
			{
				$desc = $params['desc'];
			}
			else
			{
				$desc = '';
			}

			$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
			jimport('joomla.log.log');
			JLog::addLogger(
					array(
				'text_file' => $filename,
				'text_entry_format' => $options,
				'text_file_path' => $path
			), JLog::INFO, 'com_invitex');

			$logEntry            = new JLogEntry('Transaction added', JLog::INFO, 'com_invitex');
			$logEntry->desc      = json_encode($desc);
			$logEntry->http_code = $http_code;
			$logEntry->comment   = $comment;
			JLog::add($logEntry);
		}

		/**
		 * Method to convert xml to array
		 *
		 * @param   String  $contents        contents
		 * @param   INT     $get_attributes  userid
		 * @param   String  $priority        priority
		 *
		 * @return  array of the replacements
		 *
		 * @since  1.0
		 */
		public function xml2array($contents, $get_attributes = 1, $priority = 'tag')
		{
			if (!$contents)
			{
				return array();
			}

			if (!function_exists('xml_parser_create'))
			{
				return array();
			}

			// Get the XML parser of PHP - PHP must have this module for the parser to work
			$parser = xml_parser_create('');
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parse_into_struct($parser, trim($contents), $xml_values);
			xml_parser_free($parser);

			if (!$xml_values)
			{
				return;
			}

			// Initializations
			$xml_array   = array();
			$parents     = array();
			$opened_tags = array();
			$arr         = array();

			// Refference
			$current =& $xml_array;

			// Go through the tags. Multiple tags with same name will be turned into an array
			$repeated_tag_index = array();

			foreach ($xml_values as $data)
			{
				// Remove existing values, or there will be trouble
				unset($attributes, $value);

				/* This command will extract these variables into the foreach scope
					tag(string), type(string), level(int), attributes(array).
					We could use the array by itself, but this cooler*/

				extract($data);

				$result          = array();
				$attributes_data = array();

				if (isset($value))
				{
					if ($priority == 'tag')
					{
						$result = $value;
					}
					else
					{
						// Put the value in a assoc array if we are in the 'Attribute' mode
						$result['value'] = $value;
					}
				}

				// Set the attributes too.
				if (isset($attributes) and $get_attributes)
				{
					foreach ($attributes as $attr => $val)
					{
						if ($priority == 'tag')
						{
							$attributes_data[$attr] = $val;
						}
						else
						{
							// Set all the attributes in a array called 'attr'
							$result['attr'][$attr] = $val;
						}
					}
				}

				// See tag status and do the needed.The starting of the tag '<tag>'
				if ($type == "open")
				{
					$parent[$level - 1] =& $current;

					// Insert New tag
					if (!is_array($current) or (!in_array($tag, array_keys($current))))
					{
						$current[$tag] = $result;

						if ($attributes_data)
						{
							$current[$tag . '_attr'] = $attributes_data;
						}

						$repeated_tag_index[$tag . '_' . $level] = 1;

						$current =& $current[$tag];
					}
					else // There was another element with the same tag name
					{
						if (isset($current[$tag][0]))
						{
							// If there is a 0th element it is already an array
							$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
							$repeated_tag_index[$tag . '_' . $level]++;
						}
						else
						{
							// This section will make the value an array if multiple tags with the same name appear together
							$current[$tag] = array(
								$current[$tag],
								$result
							);

							// This will combine the existing item and the new item together to make an array

							$repeated_tag_index[$tag . '_' . $level] = 2;

							// The attribute of the last(0th) tag must be moved as well
							if (isset($current[$tag . '_attr']))
							{
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset($current[$tag . '_attr']);
							}
						}

						$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
						$current =& $current[$tag][$last_item_index];
					}
				}
				elseif ($type == "complete")
				{
					// Tags that ends in 1 line '<tag />'

					// See if the key is already taken. New Key
					if (!isset($current[$tag]))
					{
						$current[$tag]                           = $result;
						$repeated_tag_index[$tag . '_' . $level] = 1;

						if ($priority == 'tag' and $attributes_data)
						{
							$current[$tag . '_attr'] = $attributes_data;
						}
					}
					else
					{
						// If taken, put all things inside a list(array) If it is already an array...
						if (isset($current[$tag][0]) and is_array($current[$tag]))
						{
							// Push the new element into that array.
							$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

							if ($priority == 'tag' and $get_attributes and $attributes_data)
							{
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}

							$repeated_tag_index[$tag . '_' . $level]++;
						}
						else
						{
							// If it is not an array Make it an array using using the existing value and the new value

							$current[$tag] = array(
								$current[$tag],
								$result
							);
							$repeated_tag_index[$tag . '_' . $level] = 1;

							if ($priority == 'tag' and $get_attributes)
							{
								// The attribute of the last(0th) tag must be moved as well
								if (isset($current[$tag . '_attr']))
								{
									$current[$tag]['0_attr'] = $current[$tag . '_attr'];
									unset($current[$tag . '_attr']);
								}

								if ($attributes_data)
								{
									$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
								}
							}

							// 0 and 1 index is already taken
							$repeated_tag_index[$tag . '_' . $level]++;
						}
					}
				}
				elseif ($type == 'close')
				{
					$current =& $parent[$level - 1];
				}
			}

			return ($xml_array);
		}
	}
}
