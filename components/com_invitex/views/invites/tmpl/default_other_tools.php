<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.filesystem.folder' );
?>
<form  method="POST" name="csvform" ENCTYPE="multipart/form-data" id="csvform">
	
	<div class="form-group">
		<!-- <label for="csvfile" class=""><h4><?php echo JText::_('UPL_CSV') ?></h4></label> -->
		<span class="invitex_label"><i>*</i><?php echo JText::_('UPL_CSV') ?>:</span>
		<input name="csvfile" type="file" id="csvfile" class="input-file"/>
		<div class="upload"><img src="components/com_invitex/images/upload.png" alt=""><span>文件上传</span> </div>
	</div>
	<div class="alert alert-info"><?php echo JText::_('CSV_MESS');?></div>
	<div>
		<!-- <div class="clearfix">&nbsp;</div> -->
		<!-- <h4><?php echo JText::_('OPTIONAL_MESSAGE');?></h4> -->
		<span class="invitex_label">邮件内容:</span>
		<textarea rows="3" id="personal_message" onchange="changeval_txtarea(this.value)"  cols="60" name="personal_message" wrap="soft" class="personal_message"><?php echo stripslashes($this->invitex_params->get("invitex_default_message")) ?></textarea>
	</div>
	<!-- <div class="clearfix"></div> -->
	<div class="invitex-form-actions center">
		<input type="button" name="preview" value="<?php echo JText::_('MSG_PRV') ?>" class="btn btn-info" onClick="mpreview('<?php echo $this->preview_url ?>','email')">	
		<input class="btn btn-primary" type="button" name="import" value="<?php echo JText::_('IMPORT_CON');?>" onclick="upload('csvupload','csvform',<?php	if($this->user_is_a_guest) echo $this->user_is_a_guest; else echo 0;	?>);">
		<input type="hidden" name="option" value="com_invitex">
		<input type="hidden" name="task" value="sort_mail">
		<input	type="hidden" id="guest" name="guest"  class="guest_name_post"	value='' />
		<input type="hidden" name="rout" id="rout" value="other_tools">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
