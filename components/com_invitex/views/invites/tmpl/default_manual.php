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

jimport('joomla.filesystem.folder');

$mainframe = JFactory::getApplication();
$session   = JFactory::getSession();
$document  = JFactory::getDocument();

$document->addScript(JUri::base() . 'media/com_invitex/js/bootstrap-tokenfield.min.js');
$document->addStyleSheet(JUri::base() . 'media/com_invitex/css/bootstrap-tokenfield.min.css');
$document->addStyleSheet(JUri::base() . 'media/com_invitex/css/tokenfield-typeahead.min.css');
?>
<form method="POST" name="manualform" id="manualform">
	<div>
		<?php
			$domains = $this->validdomains;

			if ($domains)
			{
				?>
		<div class="alert alert-info">
			<?php
				echo JText::_('SUPPORTED_DOMAINS_HEADING');

				if (is_array($domains))
				{
					$domain_str = implode(',', $domains);
				}
				else
				{
					$domain_str = $domains;
				}

				echo "<strong>" . $domain_str . "</strong>";
				?>
		</div>
		<?php
			}
			?>
		<div>
			<?php
				$useremail = '';

				if ($this->oluser)
				{
					$useremail = $this->oluser->email;
				}

				$enable_tagging = '';

				if ($this->invitex_params->get('manual_emailtags') == 1)
				{
					$enable_tagging = 'onkeypress=\'validate_email_step1(event,' . $this->invitex_params->get('allow_domain_validation') . ' ,' . json_encode($this->invitex_params->get('invite_domains')) . ' ,"' . $useremail . '")\'';
				}
				?>
			<div class="invitex_email_token invitex_manual_email_token_margin">
			<span class="invitex_label"><i>*</i>电子邮件:</span>
				<input type="text"
					class="inputbox input-medium token-input invitex_email_token_input"
					id="invitex_mail"
					name="invitex_mail"
					placeholder="<?php echo JText::_('INV_EMAIL');?>" />
			</div>
			<div class="text-muted"><?php echo JText::_('MANUAL_MESS_DESC');?></div>
			<input type="hidden" class="invitex_fields_hidden" name="invitex_correct_mails" id="invitex_correct_mails" value="" />
			<input type="hidden" class="invitex_fields_hidden" name="invitex_wrong_mails" id="invitex_wrong_mails" value="" />
			<!--
				<input type="hidden" name="option" value="com_invitex">
				<input type="hidden" name="task" value="sort_mail">
				<input type="hidden" id="guest" name="guest"  class="guest_name_post" value='<?php echo $this->user_is_a_guest;?>' />
				<input type="hidden" name="rout" id="rout" value="manual">
				-->
			<!-- <div class="clearfix">&nbsp;</div> -->
			<div>
				<span class="invitex_label">邮件内容:</span>
				<!-- <label for="personal_message" class="control-label">
				<h4><?php echo JText::_('OPTIONAL_MESSAGE');?></h4>
				</label> -->
				<textarea rows="3" id="personal_message" name="personal_message" class="personal_message" onchange="changeval_txtarea(this.value)"><?php echo stripslashes($this->invitex_params->get('invitex_default_message'));?></textarea>
			</div>
			<div class="invitex-form-actions center">
				<?php echo JHtml::_('form.token'); ?>
				<input type="hidden" name="option" value="com_invitex">
				<input type="hidden" name="task" value="sort_mail">
				<input type="hidden" id="guest" name="guest" class="guest_name_post" value='<?php echo $this->user_is_a_guest;?>' />
				<input type="hidden" name="rout" id="rout" value="manual">
				<input type="button" name="preview" value="<?php echo JText::_('MSG_PRV') ?>" class="btn btn-info" onClick="mpreview('<?php echo $this->preview_url;?>', 'email')" />
				<input class="btn btn-primary " type="button" value="<?php echo JText::_('SEND_INV'); ?>" name="quick_send"
					onclick='return upload_manual("<?php if ($this->oluser){ echo $this->oluser->email; } ?>", "manualform", <?php echo $this->user_is_a_guest;?>)'>
				
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

						if (!empty($limit_data->invitations_sent) and $limit and $limit >= $limit_data->invitations_sent)
						{
							$invitestobesent = $limit-$limit_data->invitations_sent;
							echo '<input type="hidden" name="invite_limit" id="invite_limit" value="'.$invitestobesent.'"/>';
						}
					}
					?>
			</div>
			<!-- <div class="clearfix">&nbsp;</div> -->
		</div>
	</div>
</form>
