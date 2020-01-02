<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.controller');

/**
 * Invitex Component Controller
 *
 * @package     Com_Invitex
 * @subpackage  site
 * @since       1.5
 */
class InvitexController extends JControllerLegacy
{
	/**
	 * Constructor
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->invhelperObj = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();
	}

	/**
	 * Method to show a Invitex view
	 *
	 * @param   BOOLEAN  $cachable   falag for cache
	 *
	 * @param   BOOLEAN  $urlparams  url
	 *
	 * @access	public
	 * @since	1.5
	 *
	 * @return null
	 */
	public function display($cachable = false, $urlparams = false)
	{
		if (!JFactory::getApplication()->input->get('view'))
		{
			JFactory::getApplication()->input->set('view', 'invites');
		}

		$vName = JFactory::getApplication()->input->get('view', 'config');

		switch ($vName)
		{
			case 'stats':
				$vLayout = JFactory::getApplication()->input->get('layout', 'default');
				$mName = 'stats';
			break;

			case 'invites':
				$vName = 'invites';
				$vLayout = JFactory::getApplication()->input->get('layout', 'default');
				$mName = 'invites';
			break;

			case 'urlstats':
				$vName = 'urlstats';
				$vLayout = JFactory::getApplication()->input->get('layout', 'default');
				$mName = 'urlstats';
			break;

			case 'namecard':
				$vName = 'namecard';
				$vLayout = JFactory::getApplication()->input->get('layout', 'default');
				$mName = 'namecard';
			break;
			default:
				$vName = 'invites';
				$vLayout = JFactory::getApplication()->input->get('layout', 'default');
				$mName = 'invites';
		}

		$document = JFactory::getDocument();
		$vType = $document->getType();

		// Get/Create the view
		$view = $this->getView($vName, $vType);

		// Get/Create the model
		if ($model = $this->getModel($mName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($vLayout);

		// Display the view
		// $view->display();
		parent::display();
	}

	/**
	 * method to send invites after cron job is executed
	 *
	 * @return null
	 */
	public function mailto()
	{
		$plug_call = 0;
		$model = $this->getModel('invites');
		$model->mailto($plug_call);

		jexit();
	}

	/**
	 * auto update
	 *
	 * @return null
	 */
	public function autoupdate()
	{
		$private_key = $this->invitex_params->get("private_key_cronjob");

		$private_keyinurl = JFactory::getApplication()->input->get('pkey', 'default');

		if ($private_key != $private_keyinurl)
		{
			echo JText::_('AUTOUPDATE_ERROR');
		}
		else
		{
			include JPATH_SITE . "/components/com_invitex/openinviter/autoupdate.php";
		}
	}

	/**
	 * Sigin up
	 *
	 * @return null
	 */
	public function sign_up()
	{
		$model = $this->getModel('invites');
		$model->sign_up();
	}

	/**
	 * This function review FB request
	 *
	 * @return null
	 */
	public function FBRequestReview()
	{
		JSession::checkToken() or die('Invalid Token');

		$model = $this->getModel('invites');
		$model->FBRequestReview();
	}

	/**
	 * This function remove user from subscribers list
	 *
	 * @return null
	 */
	public function unSubscribe()
	{
		$model = $this->getModel('invites');
		$model->unSubscribe();
	}

	/**
	 * This function is triggered when Invitee clicks on Confirm Unsubs button
	 *
	 * @return null
	 */
	public function unSubscribeConfirm()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		$input = JFactory::getApplication()->input;
		$post = $input->post;
		$model = $this->getModel('invites');
		$res = $model->unSubscribeConfirm($post);

		header('Content-type: application/json');

		echo json_encode($res);
		jexit();
	}

	/**
	 * Function to sort mail
	 *
	 * @return url
	 */
	public function sort_mail()
	{
		JSession::checkToken() or die('Invalid Token');

		$model = $this->getModel('invites');
		$model->sort_mail();
	}

	/**
	 * Function to send mail using quick module
	 *
	 * @return url
	 */
	public function sendQuickInvites()
	{
		$model = $this->getModel('invites');
		$result = $model->sendQuickInvites();

		header('Content-type: application/json');

		echo json_encode($result);

		jexit();
	}

	/**
	 * Function to skip
	 *
	 * @return url
	 */
	public function skip()
	{
		$this->setRedirect($this->getskipURLonLogin());

		$to_direct = $this->invitex_params->get("landing_page_reg");
		$mainframe = JFactory::getApplication();

		if (isset($_COOKIE['invitex_reg_user']) && $_COOKIE['invitex_reg_user'] != '')
		{
			setcookie("invitex_reg_user", '', -time(), "/");

			if (strcmp($to_direct, "JomSocial") == 0)
			{
				include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
				$link = CRoute::_('index.php?option=com_community&view=register&task=registerSucess');
				$mainframe->redirect($link);
			}
			elseif (strcmp($to_direct, "Community Builder") == 0)
			{
				echo JText::_("COM_INVITEX_CB_REG_COMPLETE_CONF");
			}
			elseif (strcmp($to_direct, "Joomla") == 0 || strcmp($to_direct, "Jomwall") == 0)
			{
				$params = JComponentHelper::getParams('com_users');
				$useractivation = $params->get('useractivation');

				// Redirect to the profile screen.
				if ($useractivation == '2')
				{
					$this->setMessage(JText::_('COM_INVITEX_COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
				}
				elseif ($useractivation == '1')
				{
					$this->setMessage(JText::_('COM_INVITEX_COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
				}
				else
				{
					$this->setMessage(JText::_('COM_INVITEX_COM_USERS_REGISTRATION_SAVE_SUCCESS'));
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
				}
			}
			elseif(strcmp($to_direct, "Virtuemart") == 0)
			{
				echo JText::_("COM_INVITEX_VM_REG_COMPLETE_CONF");
				$this->setMessage(JText::_('COM_INVITEX_COM_USERS_REGISTRATION_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_virtuemart&view=user&layout=default', false));
			}
			elseif(strcmp($to_direct, "EasySocial") == 0)
			{
				echo JText::_("COM_INVITEX_ES_REG_COMPLETE_CONF");
				$session = JFactory::getSession();
				$user_es = $session->get('user_es');
				$this->setMessage(JText::_('COM_INVITEX_COM_USERS_REGISTRATION_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_easysocial&view=registration&layout=completed&id=1&userid=' . $user_es, false));
			}

			if (strcmp($to_direct, "PayPlans") == 0)
			{
				$session = JFactory::getSession();
				$payplans_invoice_key = $session->get('payplans_invoice_key');
				$this->setRedirect(JRoute::_('index.php?option=com_payplans&view=invoice&task=confirm&invoice_key=' . $payplans_invoice_key, false));
			}
		}
		elseif (isset($_COOKIE['invitex_after_login']) && $_COOKIE['invitex_after_login'] != '')
		{
			setcookie("invitex_after_login", '', -time(), "/");
			$this->setRedirect($this->getskipURLonLogin());
		}
	}

	/**
	 * Function to get skip button after login
	 *
	 * @return url
	 */
	public function getskipURLonLogin()
	{
		if ($this->invitex_params->get('invite_after_login') == 1)
		{
			if ($this->invitex_params->get('redirect_url_after_login') != '')
			{
				$returnURL = $this->invitex_params->get('redirect_url_after_login');
			}
			else
			{
				if ($this->invitex_params->get('reg_direct') == 'Joomla')
				{
					$returnURL = JRoute::_($this->get_redirecturl_joomla());
				}
				elseif($this->invitex_params->get('reg_direct') == 'JomSocial')
				{
					$returnview = $this->get_loginredirecturl_js();

					if ($returnview)
					{
						include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
						$returnURL = CRoute::_('index.php?option=com_community&view=' . $this->get_loginredirecturl_js());
					}
					else
					{
						$returnURL = JRoute::_($this->get_redirecturl_joomla());
					}
				}
				elseif($this->invitex_params->get('reg_direct') == 'Community Builder')
				{
					$returnURL = JRoute::_('index.php?option=com_comprofiler');
				}
				elseif($this->invitex_params->get('reg_direct') == 'EasySocial')
				{
					require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
					$return = Foundry::getCallback();

					// If return value is empty, always redirect back to the dashboard
					if (!$return)
					{
						$return = FRoute::dashboard(array(), false);
					}

					$returnURL	=	JRoute::_($return);
				}
			}
		}

		return $returnURL;
	}

	/**
	 * Function to get login redirect URL
	 *
	 * @return url
	 */
	public function get_loginredirecturl_js()
	{
		$db = JFactory::getDbo();
		$query = "SELECT `params` FROM #__community_config WHERE name='config'";
		$db->setQuery($query);
		$res = $db->loadresult();
		$res = json_decode($res);

		return $res->redirect_login;
	}

	/**
	 * Function to get refdirect URL
	 *
	 * @return url
	 */
	public function get_redirecturl_joomla()
	{
		jimport('joomla.application.module.helper');

		$module = JModuleHelper::getModule('login');
		$moduleParams = new JRegistry($module->params);
		$moduleParams->loadString($module->params);
		$login_menu_set = $moduleParams->get('login', '');

		$returnURL = 'index.php?';

		if ($login_menu_set)
		{
			require_once JPATH_SITE . '/modules/mod_login/helper.php';
			$URL = ModLoginHelper::getReturnURL($moduleParams, 'login');
			$returnURL = base64_decode($URL);
		}

		return $returnURL;
	}
}
