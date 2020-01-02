<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );

if($this->oluser )
{
//Load Footer template
	$this->invitex_params= $this->invhelperObj->getconfigData();
	$session = JFactory::getSession();
	$encoded_cons=json_encode($session->get('inv_orkut_contacts'));
	$mainframe=JFactory::getApplication();
	$this->invitex_params=
	$itemid = $this->itemid;
	$img_src="index.php?option=com_invitex&controller=invites&task=getcaptchaURL&Itemid=".$itemid."&oauth_token=".$_SESSION['oauth_token']."&oauth_verifier=".$_SESSION['oauth_verifier'];
	$captcha_style="style=display:block";
	$reg_direct=$this->invitex_params->get("reg_direct");

	$r_link=JRoute::_("index.php?option=com_invitex&view=invites&Itemid=".$itemid);
?>
<script>
window.onload=onload_registered;
function onload_registered()
{
document.getElementById('firstli').setAttribute('class','uiStep uiStepFirst uiallStepSelected');
document.getElementById('firstli').setAttribute('onclick',"show_page('<?php echo $r_link;?>')");
document.getElementById('firstli').style.cursor="pointer";
document.getElementById('secondli').setAttribute('class','uiStep uiallStepSelected');
document.getElementById('secondli').setAttribute('onclick',"show_page('<?php echo $r_link;?>')");
document.getElementById('secondli').style.cursor="pointer";
document.getElementById('thirdli').setAttribute('class','uiStep uiStepLast uiallStepSelected');
document.getElementById('invitex_ol').setAttribute('class','ix_oltab');
}
</script>
<div class="">
	<form id="captcha_form" action="" method="post" name="captcha_form">
	<div style="padding: 0 30px 0 30px;" >
		<div class="invitex_content" id="invitex_content">
				<table width=100% class="thTable" cellspacing="10" align=center cellpadding="0" style="border:none;">
					<tr id="additional_captcha" >
						<td><img src="<?php echo $img_src ?>"></td>
						<td class="mail">
								Captcha <input type="text" id="textcaptcha" name="textcaptcha" /><input type="hidden" id="tokencaptcha" name="tokencaptcha" value="<?php echo $session->get('inv_orkut_captcha_token');?>" />
						</td>
					</tr>
					<tr>
					<td><input type="button" name="send" value="<?php echo JText::_('AUTHORIZE') ?>" class="button" onclick="captcha_form.submit();"></td>
						<td class="mail">&nbsp;</td>
					</tr>
				</table>
			</div>

		</div>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="captcha" value="1">
		<input type="hidden" name="option" value="com_invitex">
		<input type="hidden" name="controller" value="invites">
		<input type="hidden" name="task" value="save">
		<div class="clearfix"></div>
	</form>
</div>
<br />
<?php

	//Load Footer template
	echo $this->loadTemplate('footer');
}
else
{
$title=JText::_('LOGIN_TITLE');?>
<div class="">
<div class="page-header"><h2><?php echo $title?></h2></div>
		<div class="invitex_content" id="invitex_content">
			<h3><?php echo JText::_('NON_LOGIN_MSG');?></h3>
	</div>
</div>
<?php
}

