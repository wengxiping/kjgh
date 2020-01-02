<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<form name="<?php echo $this->active_tab;?>" id="" method="POST">
	<div class="alert alert-info">
		<span class="icon-lock"></span>
		<?php echo JText::_('INV_API_NOTE');?>
	</div>
	<div class="row-fluid ">
		<div class="span2" >
			<span id="connect_btn_image_div">
				<!--
					<img src="<?php echo $this->img_path."/large/".$this->img_name?>" />
				-->
			</span>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="option" value="com_invitex"/>
			<input type="hidden" name="controller" value="invites"/>
			<input type="hidden" id="guest" name="guest"  class="guest_name_post"	value="<?php if(!empty($this->user_is_a_guest)) echo $this->user_is_a_guest;?>" />
			<input type="hidden" name="task" value="get_request_token"/>
			<input type="hidden" name="api_used" id="api_used" value="<?php echo $this->api_used;?>"/>
			<input type="hidden" name="api_message_type" id="api_message_type" value="<?php echo $this->message_type;?>"/>
		</div>
		<div class="span10">
			<div class="clearfix">&nbsp;</div>
			<h4 for="personal_message" class="control-label social_email_label_personal_message"><?php echo JText::_('OPTIONAL_MESSAGE');?></h4>
			<textarea rows="3" id="personal_message" name="personal_message" onchange="changeval_txtarea(this.value)" class="personal_message social_email_personal_message"><?php echo stripslashes($this->invitex_params->get('invitex_default_message')) ?></textarea>
		</div>
	</div>
	<div class="form-actions center">
		<button id="form_connect_btn" type="submit" class="btn btn-primary"
			<?php if ($this->user_is_a_guest): ?>
			onclick="return(set_guest_name('<?php echo $this->active_tab;?>'))"
			<?php endif;?> >
		<?php echo JText::_("INV_CONNECT");?>
		</button>
		<?php // Added in v2.9.7 ?>
		<span id="form_dynamic_html"></span>
		<input type="button" id="invtex_msg_preview" name="preview" value="<?php echo JText::_('MSG_PRV') ?>" class="btn  btn-info" onClick="mpreview('<?php echo $this->preview_url ?>')">
	</div>
</form>
