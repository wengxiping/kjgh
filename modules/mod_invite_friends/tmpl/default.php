<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;
JHTML::_('behavior.modal');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
$invitexParams = JComponentHelper::getParams('com_invitex');
$user = JFactory::getUser();
$jinput = JFactory::getApplication()->input;
$component = $jinput->get("option", "", "STRING");
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true) . '/media/com_invitex/css/bootstrap-tokenfield.min.css');
$document->addStyleSheet(JUri::root(true) . '/media/com_invitex/css/invitex.css');

// Privacy consent
$session = JFactory::getSession();
$tncAccepted = $session->get('tj_send_invitations_consent');

$invitationTermsAndConditions = $invitexParams->get('invitationTermsAndConditions', '0');
$tNcArticleId = $invitexParams->get('tNcArticleId', '0');

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');
$model = JModelLegacy::getInstance('Article', 'ContentModel');
?>
<div id="privacy-policy-msg">
<?php
if ($invitationTermsAndConditions && $tNcArticleId)
{
	$contentTable = $model->getTable('Content', 'JTable');
	$contentTable->load(array('id' => $tNcArticleId));

	$slug = $contentTable->id . ':' . $contentTable->alias;
	$link = JRoute::_(ContentHelperRoute::getArticleRoute($slug, $contentTable->catid, $contentTable->language));

	if (empty($tncAccepted))
	{
		?>
		<div class="alert alert-info">
			<?php
			echo JText::sprintf('MOD_INVITE_FRIENDS_PRIVACY_CONSENT_MSG', $link);
			?>
		</div>
		<?php
	}
}
?>
</div>
<?php
if ($component != "com_invitex" || ($component == "com_invitex" && ($view = "stats" || $view = "urlstats" || $view = "resend")))
{
	$document->addScript(JUri::root(true) . '/media/com_invitex/js/bootstrap-tokenfield.min.js');
	$document->addScript(JUri::root(true) . '/media/com_invitex/js/invite.js');
?>
<script>
if(typeof(techjoomla) == 'undefined') {
	var techjoomla = {};
}

if(typeof techjoomla.jQuery == "undefined")
{
	techjoomla.jQuery = jQuery;
}
</script>
<?php
}

// Call helper function
ModInviteFriends::getLanguageConstant();
$guestInvitaion = $invitexParams->get('guest_invitation','','STRING');
$userId = $user->get('id');
$guest = (empty($userId))?1:0;
$buttonTitle = JText::_('MOD_INVITE_FRIENDS_INVITE_SUBMIT');
$styleBtn = '';
$disable = 0;

$title = $params->get('mod_title');

if (empty($guestInvitaion))
{
	if ($guest)
	{
		$styleBtn = 'opacity:0.5';
		$disable = 1;
	}
}
?>
<script>
techjoomla.jQuery(
	function() {
	jQuery('#module_invitee_email').tokenfield({
		createTokensOnBlur: true,
		minWidth: 120
		});

	jQuery('#module_invitee_email').on('tokenfield:createtoken', function (e){
	var data = e.attrs.value.split('|');
	e.attrs.value = data[1] || data[0];
	e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0];
	});

	jQuery('#module_invitee_email').on('tokenfield:createdtoken', function (e){
		var val = trim(e.attrs.value);
		var re = /\S+@\S+\.\S+/;
		var valid = re.test(val);

		if (!valid){
			response=-1;
			techjoomla.jQuery(e.relatedTarget).addClass('invalid');
			push_hidden_mailvalues(response,val);
			return;
		}
	})

	.on('tokenfield:edittoken', function (e){
		var val = trim(e.attrs.value);
		techjoomla.jQuery('#module_invitee_email').focus();
		remove_hidden_mailvalues(val);
	})

	.on('tokenfield:removedtoken', function (e){
		/*Remove values from hidden fields*/
		techjoomla.jQuery('#module_invitee_email').focus();
		remove_hidden_mailvalues(e.attrs.value);
	})
});

function inviteFriends()
{
	var mails='';

	var noAddressFound = Joomla.JText._('MOD_INVITE_FRIENDS_NO_FRIENDS_EMAIL_FOUND');
	var invitationSuccess = Joomla.JText._('MOD_INVITE_FRIENDS_INVITATION_SUCCESS');
	var noGuestName = Joomla.JText._('MOD_INVITE_FRIENDS_GUEST_NAME_ERROR');
	var selfInvite = Joomla.JText._('MOD_INVITE_FRIENDS_SELF_INVITE');

	if (techjoomla.jQuery('#quick_mod_guestinviter_name').length)
	{
		if (techjoomla.jQuery('#quick_mod_guestinviter_name').val() == '')
		{
			techjoomla.jQuery(".invite-friend-msg .msg").html(noGuestName);
			techjoomla.jQuery(".invite-friend-msg").show();
			techjoomla.jQuery(".invite-friend-msg").addClass('alert alert-danger');
			techjoomla.jQuery('#quick_mod_guestinviter_name').focus();

			return false;
		}
	}

	if (techjoomla.jQuery('#module_invitee_email').val() != '')
	{
		if (/\S+@\S+\.\S+/.test(techjoomla.jQuery('#module_invitee_email').val()))
		{
			mails = mails + techjoomla.jQuery('#module_invitee_email').val() + ',';
			techjoomla.jQuery('#module_invitee_email').val('');
		}
		else
		{
			techjoomla.jQuery(".invite-friend-msg .msg").html(Joomla.JText._('MOD_INVITE_FRIENDS_MAIL_CONTENT_WRONG'));
			techjoomla.jQuery(".invite-friend-msg").show();
			techjoomla.jQuery(".invite-friend-msg").addClass('alert alert-danger');
			techjoomla.jQuery('#module_invitee_email').focus();

			return false;
		}
	}
	else
	{
		techjoomla.jQuery(".invite-friend-msg .msg").html(noAddressFound);
		techjoomla.jQuery(".invite-friend-msg").show();
		techjoomla.jQuery(".invite-friend-msg").addClass('alert alert-danger');

		return false;
	}

	var email = techjoomla.jQuery('#quick_mod_guestinviter_name').val() + ','+ techjoomla.jQuery('#inviter-email').val();

	/* Ajax call to save the ordering. */
	techjoomla.jQuery.ajax
	({
		type: "POST",
		url: invitex_root_url + 'index.php?option=com_invitex&task=sendQuickInvites',
		data:{
			invite_mails:mails,
			guest:email,
			rout:'manual',
			invitex_mod_correct_mails:mails
		},
		beforeSend: function() {
			techjoomla.jQuery('#mod_email_invite_btn').val(Joomla.JText._('MOD_INVITE_FRIENDS_SENDING_INVITATIONS'));
			techjoomla.jQuery('#mod_email_invite_btn').prop('disabled', true);
			techjoomla.jQuery('.mod-invite-friends').css('opacity', '0.5');
		},
		success: function(result)
		{
			techjoomla.jQuery(".invite-friend-msg .msg").html(result.message);
			techjoomla.jQuery(".invite-friend-msg").show();
			techjoomla.jQuery(".invite-friend-msg").removeClass('alert alert-danger alert-success alert-info');

			if (result.status == 'self_invitaion' || result.status == 'no_emails' || result.status == 'no_consent')
			{
				techjoomla.jQuery(".invite-friend-msg").addClass('alert alert-danger');
			}
			else if (result.status == 'ri_mail')
			{
				techjoomla.jQuery(".invite-friend-msg").addClass('alert alert-info');
			}
			else if (result.status == 'success')
			{
				techjoomla.jQuery(".invite-friend-msg").addClass('alert alert-success');
			}
			else
			{
				alert(invitationSuccess);
				location.reload();
			}

			techjoomla.jQuery('#mod_email_invite_btn').val(Joomla.JText._('MOD_INVITE_FRIENDS_INVITE_SUBMIT'));
			techjoomla.jQuery('#mod_email_invite_btn').prop('disabled', false);
		},
		complete: function(xhr)
		{
			techjoomla.jQuery('.mod-invite-friends').css('opacity', '1');
			jQuery('#module_invitee_email').tokenfield('destroy');
			techjoomla.jQuery('#module_invitee_email').val('');
			jQuery('#module_invitee_email').tokenfield({
			createTokensOnBlur: true,
			minWidth: 120
			});
		}
	});

	return false;
}
</script>
<?php
$path = JPATH_SITE . '/components/com_invitex/helper.php';

if (!class_exists('CominvitexHelper'))
{
	JLoader::register('CominvitexHelper', $path);
	JLoader::load('CominvitexHelper');
}

$invitexHelper = new CominvitexHelper;

$invitexHelper->loadInvitexAssetFiles();

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_invitex');
}
?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="mod-invite-friends">
		<div class="invite-friend-title">
			<div class="row">
				<div class="title col-sm-12 center">
					<?php
					if (!empty($title))
					{
					?>
					<h3><?php echo JText::_($title);?></h3>
					<hr class="hr-condensed"/>
					<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php
		if (empty($guestInvitaion))
		{
			if ($guest)
			{
			?>
				<div class="invite-friend-login alert alert-info" >
					<div class="login-msg"><?php echo JText::_('MOD_INVITE_FRIENDS_LOGIN_TO_INVITE'); ?></div>
				</div>
			<?php
			}
		}
		?>
		<div class="invite-friend-msg" style="display: none;">
			<div class="msg invitex_word_break"></div>
		</div>
		<div class="invite-friend-form">
			<form role="form" action="" method="post">
				<?php
				// If guest invitaion is enabled then show name field
				if (!empty($guestInvitaion))
				{
					if ($guest)
					{
					?>
					<div class="form-group">
						<label class="control-label" for="quick_mod_guestinviter_name">
							<?php echo JText::_('MOD_INVITE_FRIENDS_MY_NAME');?>
						</label>
						<div class="controls">
							<input type="text" class="form-control" id="quick_mod_guestinviter_name" required="required" placeholder="<?php echo JText::_('MOD_INVITE_FRIENDS_YOUR_NAME');?>" >
						</div>
					</div>
					<?php
					}
				}
				?>
				<div class="form-group">
					<label class="control-label" for="module_invitee_email">
						<?php echo JText::_('MOD_INVITE_FRIENDS_ENTER_EMAIL');?>
					</label>
					<div class="invitex_email_box">
						<input type="email" class="form-control input-large tokenfield" name="module_invitee_email" id="module_invitee_email" placeholder="<?php echo JText::_('MOD_INVITE_FRIENDS_YOUR_EMAIL');?>">
					</div>
					<input type="hidden" class="invitex_fields_hidden" name="invitex_mod_correct_mails" id="invitex_mod_correct_mails" value="" />
				</div>
				<?php
				if ($invitationTermsAndConditions && $tNcArticleId)
				{
					if (!empty($tncAccepted))
					{
						?>
						<div class="alert alert-warning">
							<?php
							echo JText::sprintf('MOD_INVITE_FRIENDS_DECLINE_PRIVACY_CONSENT_MSG', $link);
							?>
						</div>
						<?php
					}
				}
				?>
				<div class="form-group">
					<input type="button" id="mod_email_invite_btn"  name ="<?php echo JText::_('MOD_INVITE_FRIENDS_INVITE_SUBMIT');?>" onClick="inviteFriends()" title ="<?php echo $buttonTitle;?>" <?php if ($disable) echo 'disabled'; ?> style="<?php echo $styleBtn; ?>" class="btn-primary btn-large btn-block form-control" value="<?php echo JText::_('MOD_INVITE_FRIENDS_INVITE_SUBMIT');?>" />
				</div>
				<div id="consentToken">
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</form>
		</div>
	</div>
</div>
