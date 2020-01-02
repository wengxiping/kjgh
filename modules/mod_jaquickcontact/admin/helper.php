<?php

/**
 * ------------------------------------------------------------------------
 * JA Quick Contact Module for J3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
if (!defined('_JEXEC')) {
	define('_JEXEC', 1);

	// no direct access
	defined('_JEXEC') or die('Restricted access');

	$path = dirname(dirname(dirname(dirname(__FILE__))));
	define('JPATH_BASE', $path);

	if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
		//Apache CGI
		$_SERVER['PHP_SELF'] = rtrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/\\');
	} else {
		//Others
		$_SERVER['SCRIPT_NAME'] = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');
	}

	require_once (JPATH_BASE . '/includes/defines.php');
	require_once (JPATH_BASE . '/includes/framework.php');
	JDEBUG ? $_PROFILER->mark('afterLoad') : null;

	/**
	 * CREATE THE APPLICATION
	 *
	 * NOTE :
	 */
	$mainframe = JFactory::getApplication('site');

	/**
	 * INITIALISE THE APPLICATION
	 *
	 * NOTE :
	 */
	//JPluginHelper::importPlugin('system');
	// trigger the onAfterInitialise events
	//JDEBUG ? $_PROFILER->mark('afterInitialise') : null;
	//$mainframe->triggerEvent('onAfterInitialise');
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.application.module.helper');
jimport('joomla.html.parameter');
$task = filter_input(INPUT_GET, 'japaramaction');

if ($task == 'sendEmail') {
	$lang = JFactory::getLanguage();
	$lang->load('lib_joomla');
	$lang->load('mod_jaquickcontact');
	$JAAdminAnim = new JAAdminAnim();
	$JAAdminAnim->sendEmail();
}

/**
 * Send mail ajax class
 */
class JAAdminAnim {

	/**
	 *
	 * Send mail contact
	 */
	function sendEmail() {
		$mainframe = JFactory::getApplication();
		$input = $mainframe->input;
		//session_start();
		$sessionjson = JFactory::getSession();
		// Initialize some variables		
		$captcha = JPluginHelper::importPlugin('content', 'captcha');
        $cp_plugin = JPluginHelper::getPlugin('content', 'captcha');
        $cp_plgParams = new JRegistry($cp_plugin->params);
        $secretkey = $cp_plgParams->get('captcha_systems-recaptcha-PriKey' , '');
        $captcha_systems = $cp_plgParams->get('captcha_systems', '');
        $_cp_plugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
        if ($captcha_systems == 'invisible') {
            $_cp_plugin = JPluginHelper::getPlugin('captcha', 'recaptcha_invisible');
            $secretkey = $cp_plgParams->get('captcha_systems-invisible-PriKey' , '');
        }

        if (!empty($_cp_plugin)) {
            $_cp_plgParams = new JRegistry($_cp_plugin->params);
            if ($secretkey == '')
                $secretkey = $_cp_plgParams->get('private_key', '');
        }

		$client = JApplicationHelper::getClientInfo($input->getInt('client', 0));

		$post = $input->post;

		//print_r($post);
		$module = JModuleHelper::getModule('mod_jaquickcontact');

		$params = new JRegistry($module->params);

		JSession::checkToken() or die('{"error":"'.JText::_('SESSION_CHECK_FAILED').'"}');
		
		$email = $post->getString('email');
		$name = $post->getString('name');
		$text = str_replace("~", "<br />", $post->getString('text'));
		$subject = $post->getString('subject');

		$header = "From: $email";
		$error1 = array();

		$pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
		if (!preg_match($pattern, $email)) {
			$error1['email'] = JText::_("EMAIL REQUIRE");
		}
		if (!$name) {
			$error1['name'] = JText::_("NAME_REQUIRE");
		}
		if (!$subject) {
			$error1['subject'] = JText::_("SUBJECT_REQUIRE");
		}
		if (strlen($text) > $params->get('max_chars', 1000) || strlen($text) < 5) {
			$error1['text'] = JText::_('MESSAGE_REQUIRE');
		}

		if ($captcha) {
            $post = JFactory::getApplication()->input->post;
            $dispathcher = JEventDispatcher::getInstance();
            if ($captcha_systems == 'recaptcha' || $captcha_systems == 'invisible') {
                $res = array(true); // the check had failed so we tempo remove the check.
                $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?response='.$post->getString('g-recaptcha-response').'&secret=' . $secretkey . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
                if (is_string($response)) {
                    $response = json_decode($response);
                }
                if ($response->success == false) {
                    $error1['captcha_code'] = JText::_('CAPTCHA_SPAMER');
                }
            } else {
                $res = $mainframe->triggerEvent('onValidateCaptcha', array($post->get('captcha_code')));
            }
            if (!$res[0]) {
                $error1['captcha_code'] = JText::_('CAPTCHA_REQUIRE');
            }
		}

		$error_msg = implode("<br/>", $error1);
		//echo count($error1);exit;
		if (count($error1) == 0) {
			$message = "
				Name: $name <br/>
				Email: $email <br/> ";
			$message .= "<br/>	            
				$text
				";
			$email_copy = ($post->get('email_copy') == 'true') ? 1 : 0;
			if ($post->get('email_copy') == 1) {
				$email_copy = $post->get('email_copy');
			}
			$adminemail = $mainframe->getCfg('mailfrom');
			$recipient = $params->get('recipient', $adminemail);
			$recipient = preg_split("/[\s]*[,][\s]*/", $recipient);
			$mail = JFactory::getMailer();

			$mail->addRecipient($recipient);

			if ($email_copy == 1) {
				$mail->addRecipient($email);
			}

			$mail->IsHTML(true);
			$mail->setSender(array($email, $subject));
			$mail->setSubject($subject);
			$mail->setBody($message);
			
			$result = array();
			try {
				$success = $mail->Send();
				$messEnqueue = $mainframe->getMessageQueue();
				if ($success === true) {
					$thanks = $params->get('thank_msg', JText::_('THANK_YOU'));
					$result['successful'] = $thanks;
				} else if ($success instanceof Exception) {
					$status = $success->getMessage();
					$result['error'] = $status;
				} else if(count($messEnqueue)) {
					$status = JText::_($messEnqueue[0]['message']);
					$result['error'] = $status;
				} else {
					$status = JText::_('ERROR_SEND_MAIL');
					$result['error'] = $status;
				}
			} catch (Exception $exc) {
				$result['error'] = $exc->getMessage();
			}

			die(json_encode($result));
		} else {
			$result['error'] = $error_msg;
			die(json_encode($result));
		}
	}

}
