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
<form class="form-horizontal"  method="POST" name="csvform" ENCTYPE="multipart/form-data" id="csvform">
	<div class="alert alert-info"><?php echo JText::_('CSV_MESS');?></div>
	<div class="control-group">
		<label for="csvfile" class="control-label"><b><?php echo JText::_('UPL_CSV') ?></b></label>
		<div class="controls">
			<input name="csvfile" type="file" id="csvfile" class="input-file"/>
		</div>
	</div>
	<div class="control-group">
		<div class="clearfix">&nbsp;</div>
		<h4><?php echo JText::_('OPTIONAL_MESSAGE');?></h4>
		<textarea rows="3" id="personal_message" onchange="changeval_txtarea(this.value)"  cols="60" name="personal_message" wrap="soft" class="personal_message"><?php echo stripslashes($this->invitex_params->get("invitex_default_message")) ?></textarea>
	</div>
	<div class="clearfix"></div>
	<div class="form-actions center">
		<input class="btn btn-primary" type="button" name="import" value="<?php echo JText::_('IMPORT_CON');?>" onclick="upload('csvupload','csvform',<?php	if($this->user_is_a_guest) echo $this->user_is_a_guest; else echo 0;	?>);">
		<input type="button" name="preview" value="<?php echo JText::_('MSG_PRV') ?>" class="btn btn-info" onClick="mpreview('<?php echo $this->preview_url ?>','email')">
		<input type="hidden" name="option" value="com_invitex">
		<input type="hidden" name="task" value="sort_mail">
		<input	type="hidden" id="guest" name="guest"  class="guest_name_post"	value='' />
		<input type="hidden" name="rout" id="rout" value="other_tools">
		<?php echo JHtml::_( 'form.token' ); ?>
	</div>
</form>
