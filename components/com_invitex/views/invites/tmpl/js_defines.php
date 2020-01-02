<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

// Define constants
if (JVERSION < '3.0')
{
	// Icon constants.
	define('INV_ICON_CHECKMARK', " icon-ok-sign");
	define('INV_ICON_MINUS', " icon-minus");
	define('INV_ICON_PLUS', " icon-plus-sign");
	define('INV_ICON_EDIT', " icon-apply ");
	define('INV_ICON_CART', " icon-shopping-cart");
	define('INV_ICON_BACK', " icon-arrow-left");
	define('INV_ICON_REMOVE', " icon-remove");

	// Define wrapper class
	if (!defined('INVITEX_WRAPPER_CLASS'))
	{
		define('INVITEX_WRAPPER_CLASS', "invitex-wrapper techjoomla-bootstrap");
	}
}
else
{
	// Icon constants.
	define('INV_ICON_CHECKMARK', " icon-checkmark");
	define('INV_ICON_MINUS', " icon-minus-2");
	define('INV_ICON_PLUS', " icon-plus-2");
	define('INV_ICON_EDIT', " icon-pencil-2");
	define('INV_ICON_CART', " icon-cart");
	define('INV_ICON_BACK', " icon-arrow-left-2");
	define('INV_ICON_REMOVE', " icon-cancel-2");

	// Define wrapper class
	if (!defined('INVITEX_WRAPPER_CLASS'))
	{
		define('INVITEX_WRAPPER_CLASS', "invitex-wrapper");
	}
}

$cominvitexHelper = new cominvitexHelper;
$invitex_params = $cominvitexHelper->getconfigData();

$domains = $invitex_params->get('invite_domains');
$invite_domains_str = '';

if (!empty($domains))
{
	// $invite_domains_str = json_encode($invitex_params->get('invite_domains'));

	$invite_domains_str = trim($invitex_params->get('invite_domains'));
}

$allow_domain_validation = $invitex_params->get('allow_domain_validation');

$show_menu = 0;

if (($invitex_params->get('show_menu')))
{
	$show_menu = $invitex_params->get('show_menu');
}

$isGuest = 1;

if (!empty($inv_user->id))
{
	$isGuest = 0;
}
?>
<script type="text/javascript">
var allow_domain_validation="<?php echo $allow_domain_validation;?>";
var invite_domains_str="<?php echo $invite_domains_str; ?>";
var self_email="";
var manual_emailtags="<?php  echo $invitex_params->get('manual_emailtags');?>";
var isGuest="<?php $isGuest;?>";
var api_used_global="";
var sms_num =1;
var field_lenght_sms=0;
var field_lenght_manual=0;
var inv_messagae_type_preview='';
var inv_messagae_type_preview_msg='';
var show_menu="<?php echo $show_menu; ?>";
var send_invite_button_text='<?php echo JText::_("SEND_INV");?>';
var connect_invite_button_text='<?php echo JText::_("INV_CONNECT");?>';
var iconplus="<?php echo INV_ICON_PLUS;?>";
var iconminus="<?php echo INV_ICON_MINUS;?>";
var no_more_contacts_msg="<?php echo JText::_('NO_MORE_CONTACTS');?>";
var invites_sent_success_msg="<?php echo JText::_('INVITE_SUCESS');?>";
var invites_sent_error_msg="<?php echo JText::_('SOCIAL_ERROR');?>";
</script>
