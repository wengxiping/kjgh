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
$mainframe = JFactory::getApplication();
$session = JFactory::getSession();
?>
<form  method="POST" name="advanced_manualform" id="advanced_manualform">
	<div class="alert alert-info">
		<?php echo JText::_('ADVANCED_MANUAL_MESS') ?>:
	</div>
		<?php
		$useremail = '';

		if ($this->oluser)
		{
			$useremail = $this->oluser->email;
		}

		$enable_tagging = '';

		if ($this->invitex_params->get('manual_emailtags') == 1)
		{
			$enable_tagging = 'onkeypress=\'validate_email_step1(event,' . $this->invitex_params->get('allow_domain_validation') . ' ,' . json_encode(explode(",", $this->invitex_params->get('invite_domains'))) . ' ,"' . $useremail . '")\'';
		}

	?>
	<div class="row-fluid">
		<div id="com_invitex_repeating_block_manual0" class="com_invitex_repeating_block span7">
			<div class="form-inline">
				<div class="control-group">
					<div>
						<input type="text" placeholder="<?php echo JText::_('INV_NAME'); ?>" value="" class="inputbox input-medium required" id="invitee_name1" name="invitee_name[]">
						<input type="text" placeholder="<?php echo JText::_('INV_EMAIL'); ?>" value="" class="inputbox input-medium required" id="invitee_email1" name="invitee_email[]">
					</div>
				</div><!--control-group-->
			</div><!--This is a repating block of html-->
		</div>
		<div class="com_invitex_add_button span4">
			<div class="form-inline">
				<div class="control-group">
					<button class="btn btn-small btn-success" type="button" id="add"	onclick="addClone_inv('com_invitex_repeating_block_manual','com_invitex_repeating_block_manual','remove_button_div_manual','icon-minus-sign ies-minus');"	title="Add">
						<i class="<?php echo INV_ICON_PLUS;?>"></i>
					</button>
				</div><!--control-group-->
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<input type="hidden" class="invitex_fields_hidden" name="invitex_correct_mails" id="invitex_correct_mails" value="" />
		<input type="hidden" class="invitex_fields_hidden" name="invitex_wrong_mails" id="invitex_wrong_mails" value="" />
		<textarea rows="3" id="personal_message"  cols="60" name="personal_message" wrap="soft" class="personal_message" onchange="changeval_txtarea(this.value)"><?php echo stripslashes($this->invitex_params->get('invitex_default_message')) ?></textarea>
	</div>
	<div class="row-fluid">
		<div class="form-actions">
			<input class="btn btn-primary " type="button" onclick="submit_adv_form()"  value="<?php echo JText::_('SEND_INV'); ?>" name="quick_send">
			<input type="button" name="preview" value="<?php echo JText::_('MSG_PRV') ?>" class="btn  btn-info" onClick="mpreview('<?php echo $this->preview_url ?>','email')">
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" name="option" value="com_invitex">
	<input type="hidden" name="task" value="sort_mail">
	<input	type="hidden" id="guest" name="guest" class="guest_name_post"	value='' />
	<input type="hidden" name="rout" id="rout" value="manual">
	<input type="hidden" name="api_message_type" id="api_message_type" value="email"/>
	<input type="hidden" name="manual_method_type" id="manual_method_type" value="advanced">
	<?php
	if (!$session->get('invite_anywhere'))
	{
		$limit_data = $this->limit_data;
		$limit = 0;

		if (empty($limit_data->limit))
		{
			$limit = $this->invitex_params->get('per_user_invitation_limit');
		}
		else
		{
			$limit = $limit_data->limit;
		}

		if ($limit)
		{
			$invitestobesent = $limit;

			if (isset($limit_data->invitations_sent))
			{
				$invitestobesent = $limit-$limit_data->invitations_sent;
			}
			?>
			<input type="hidden" name="invite_limit" id="invite_limit" value="<?php echo $invitestobesent;?>"/>;
			<?php
		}
	}
	?>
</form>

