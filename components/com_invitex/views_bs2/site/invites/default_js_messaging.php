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

if($this->friends)
{
?>
<form class="form-horizontal" id="inv_js_invitation_form" name="inv_js_invitation_form"  method="POST">
	<div id="inv_js_InvitationTabContainer">
		<div class="clearfix">&nbsp;</div>
		<h4><?php echo JText::_('OPTIONAL_MESSAGE');?></h4>
		<textarea class="js_messaging_personal_messaging" rows="3" onchange="changeval_txtarea(this.value)" id="personal_message" name="personal_message" wrap="soft" class="personal_message"><?php echo $this->invitex_params->get('invitex_default_message'); ?></textarea>
		<input type="hidden" name="message" value="<?php echo JText::_('SEND_MESSAGE');?>" id="messgae"/>
		<div class="pull-left invitex_contacts_headder_button">
			<input type='button' class="btn btn-success" onClick='selectAllFriends()' name='select_all' id='select_all' value="<?php  echo JText::_('COM_INVITEX_SELECT_ALL'); ?>" />
			<input type='button' class="btn btn-warning" onClick='deselectAllFriends()' name='diselect_all' id='diselect_all' value="<?php  echo JText::_('COM_INVITEX_DESELECT_ALL'); ?>" />
		</div>
		<div class="clearfix">&nbsp;</div>
		<div class="inv_js_community-invitation" id="inv_js_community-invitation">
			<div class="invitex_select_users_toinvite_body">
				<div id="inv_js_invitation_list" class="row-fluid">
					<?php
					foreach ( $this->friends as $friend )
					{
						if ($this->oluser->id == $friend->id)
						{
							continue;
						}

						if($this->oluser and $this->invitex_params->get("reg_direct")=='Jomsocial')
						{

							$onclick="joms.invitation.selectMember('#invitation-friend-".$friend->id."');";
						}
						?>
						<div class="inv_pm_friend_list_margin span4">
							<div class="inv_js_invitation_wrap">
								<img src="<?php echo $friend->avatar;?>" class="inv_js_invitation-avatar">
								<div class="inv_js_invitation_detail">
									<div class="inv_js_invitation_name">
										<div class="inv_js_invitation-check">
										<input type="checkbox" id="inv_js_friends-<?php echo $friend->id?>" name="inv_js_friends[]" value="<?php echo $friend->id?>" onclick="<?php if(!empty($onclick)) echo $onclick;?>"  class="thCheckbox" checked/>
										<label	for="inv_js_friends-<?php echo $friend->id?>" title="<?php echo $friend->name;?>"><?php echo JText::_('COM_INVITEX_SELECT');?></label>
										</div>
										<label for="inv_js_friends-name<?php echo $friend->id?>"></label>
										<span id="inv_js_friends-name<?php echo $friend->id?>" >
										<?php
											if(strlen($friend->name)>=50)
											{
												echo str_replace(" ","<br/>",$friend->name);
											}
											else
											{
												echo $friend->name;
											}
											?>
									</div>
								</div>
							</div>
						</div>
					<?php
					}
					?>
				</div>
			</div>
			<div class="separator"></div>
		</div>
		<div	class="form-actions">
			<input	class="btn btn-primary " type="button" name="inv_js_messgae_send" value="<?php echo JText::_('SEND_INV');?>" onclick="send_invitation();">
		</div>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="option" value="com_invitex">
		<input type="hidden" name="task" value="sort_mail">
		<input type="hidden" name="rout" id="rout" value="inv_js_messaging">
	</div>
</form>
<?php
}
else
{
?>
<div class="alert alert-error">
<?php echo JText::_('NO_FRIENDS');?>
</div>
<?php
}
