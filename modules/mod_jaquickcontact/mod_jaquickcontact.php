<?php
/**
 * ------------------------------------------------------------------------
 * JA Quick Contact Module for J3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');
$mainframe = JFactory::getApplication();
$input = $mainframe->input;

JHTML::_('behavior.framework', true);
JHTML::_('behavior.tooltip');
include_once(dirname(__FILE__) . '/assets/asset.php');

$do_submit = $input->get('do_submit');
$status = null;

$captcha = JPluginHelper::importPlugin('content', 'captcha');
$sitekey='';
$secretkey='';
$captcha_systems='';
if ($captcha) {
    $cp_plugin = JPluginHelper::getPlugin('content', 'captcha');
    $cp_plgParams = new JRegistry($cp_plugin->params);
    $sitekey = $cp_plgParams->get('captcha_systems-recaptcha-PubKey' , '');
    $secretkey = $cp_plgParams->get('captcha_systems-recaptcha-PriKey' , '');
    $captcha_systems = $cp_plgParams->get('captcha_systems', '');
    $_cp_plugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
    if ($captcha_systems == 'invisible') {
        $_cp_plugin = JPluginHelper::getPlugin('captcha', 'recaptcha_invisible');
        $sitekey = $cp_plgParams->get('captcha_systems-invisible-PubKey' , '');
        $secretkey = $cp_plgParams->get('captcha_systems-invisible-PriKey' , '');
    }

    if (!empty($_cp_plugin)) {
        $_cp_plgParams = new JRegistry($_cp_plugin->params);
        if ($sitekey == '')
            $sitekey = $_cp_plgParams->get('public_key', '');
        if ($secretkey == '')
            $secretkey = $_cp_plgParams->get('private_key', '');
    }
}

$user = JFactory::getUser();
$name = isset($user->username) ? $user->username : '';
$email = isset($user->email) ? $user->email : '';
$text = $params->get('temp_message','');
$use_ajax = $params->get("use_ajax", "0");
$subject = $params->get('subject');
$senderlabel = $params->get('sender_label', JText::_('ENTER_YOUR_NAME'));
$email_label = $params->get('email_label', JText::_('EMAIL_ADDRESS'));
$subject_label = $params->get('subject_label', JText::_('ENTER_YOUR_SUBJECT'));
$message_label = $params->get('message_label', JText::_('ENTER_YOUR_MESSAGE'));
$error = array();

if ($do_submit) {
    $name = stripslashes($input->getString('name', $name));
    $email = stripslashes($input->getString('email', $email));
    $subject = stripslashes($input->getString('subject', $subject));
    $text = $input->getString('text');

	$pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
	if(!preg_match($pattern, $email)){
		$error['email'] = JText::_("EMAIL REQUIRE");
	}
    
    if (!$name) {
        $error['name'] = JText::_("NAME_REQUIRE");
    }
    if (!$subject) {
        $error['subject'] = JText::_("SUBJECT_REQUIRE");
    }
    if (strlen($text) > $params->get('max_chars', 1000) || strlen($text) < 5) {
        $error['text'] = JText::_('MESSAGE_REQUIRE');
    }

	if ($captcha) {
		$post = JFactory::getApplication()->input->post;
		if ($captcha_systems == 'recaptcha' || $captcha_systems == 'invisible') {
            $res = array(true); // the check had failed so we tempo remove the check.
            $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?response='.$_POST['g-recaptcha-response'].'&secret=' . $secretkey . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
            if (is_string($response)) {
                $response = json_decode($response);
            }
            if ($response->success == false) {
                $error['captcha_code'] = JText::_('CAPTCHA_SPAMER');
            }
        } else {
			$res = $mainframe->triggerEvent('onValidateCaptcha', array($post->get('captcha_code')));
		}
		if (!$res[0]) {
			$error['captcha_code'] = JText::_('CAPTCHA_REQUIRE');
		}
	}
	
    if (count($error) == 0) {
        $message = "
            ".JText::_('Name').": $name <br/>
            ".JText::_('Email').": $email <br/> ";
        $message .= "<br/>" . nl2br($text);
        $email_copy = ($input->get('email_copy', 0) == 1) ? 1 : 0;
        $adminemail = $mainframe->getCfg('mailfrom');
        $header = "From: $adminemail";
        $recipient = $params->get('recipient', $adminemail);
		$recipient = preg_split("/[\s]*[,][\s]*/", $recipient);
        $mail = JFactory::getMailer();
		$mail->addRecipient($recipient);		
		
        if ($params->get('show_email_copy', 0) && ($email_copy == 1)) {
            $mail->addRecipient($email);
        }
        $mail->isHtml(true);
        $mail->setSender(array($adminemail, $subject));
        $mail->setSubject($subject);
        $mail->setBody($message);
        try {
            $success = $mail->Send();
            $messEnqueue = $mainframe->getMessageQueue();
            if ($success === true) {
                $status = $params->get('thank_msg', JText::_('THANK_YOU'));
                $mainframe->enqueueMessage($status);
                $url_redirect = $params->get('redirect_url', 'index.php');
                $mainframe->redirect($url_redirect);
            } else if ($success instanceof Exception) {
                $status = $success->getMessage();
            } else if(count($messEnqueue)) {
                $status = JText::_($messEnqueue[0]['message']);
            } else {
                $status = JText::_('ERROR_SEND_MAIL');
            }
        } catch (Exception $exc) {
            $status = $exc->getMessage();
        }

    }
}

if (!empty($use_ajax) && $use_ajax == '1') {
    require (JModuleHelper::getLayoutPath('mod_jaquickcontact', 'ajax_layout'));
} else {
    require (JModuleHelper::getLayoutPath('mod_jaquickcontact'));
}