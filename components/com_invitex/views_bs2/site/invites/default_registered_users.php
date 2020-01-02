<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'activity.integration.stream' );
jimport( 'activity.socialintegration.profiledata' );

$mainframe = JFactory::getApplication();
$isguest = $this->invitex_params->get('guest_invitation');

if ($this->oluser || $isguest == 1)
{
	$document=JFactory::getDocument();
	$session = JFactory::getSession();
	$itemid = $this->itemid;

	// If some emails are already invited then show message
	if (!$session->get('invite_mails') && $session->get('already_invited_mails'))
	{
		$i_mail = $session->get('already_invited_mails');
		$number = count($i_mail);
		$resend_link = "<a href='".JRoute::_('index.php?option=com_invitex&view=invites&layout=resend&rout=resend&Itemid='.$itemid,false)."'>".JText::_('RE_SEND')."</a>";
		$mainframe->enqueueMessage(JText::sprintf('INV_ALREADY_INVITED_MSG',$number,$resend_link));
	}

	$plugType = $registered_msg = $continue_button = $unsub_msg = '';
	$plugType = $session->get('OI_plugType');

	//IF GUEST USER DONT SHOW ADD A FRIEND OPTION HENCE JOOMLA SELECTED
	if ($isguest && !$this->oluser)
	{
		$reg_direct = 'Joomla';
	}
	else
	{
		$reg_direct = $this->invitex_params->get('reg_direct');
	}

	$r_mail = $session->get('registered_mails');

	// Remove email id of logged in user
	foreach ($r_mail as $k => $email)
	{
		if ($email == JFactory::getUser()->email)
		{
			unset($r_mail[$k]);
		}
	}

	$inv = 0;
	$r = 0;

	if ($session->get('invite_mails') && $r_mail)
	{
		$registered_msg = JText::_('REGISTERED_MSG');
	}

	if (!$session->get('invite_mails') && $r_mail)
	{
		$registered_msg=JText::_('ALL_REGISTERED_MSG');
	}

	if ($session->get('invite_mails') && !$r_mail)
	{
		$registered_msg= '';
	}

	$js_friend = array();
	$cb_friend = array();

	if (!empty($this->jsfriend))
	{
		$js_friend = $this->jsfriend;
	}

	$jsinvitedfriend = array();

	if (!empty($this->jsinvitedfriend))
	{
		$jsinvitedfriend = $this->jsinvitedfriend;
	}

	$esinvitedfriend = array();

	//For easy social friends and invited friends
	if ($this->invitex_params->get('reg_direct') == 'EasySocial')
	{
		$es_friend = array();
		$es_friend = $this->esfriend;
		$esinvitedfriend = $this->esinvitedfriend;
	}

	if (!empty($this->cbfriend))
	{
		$cb_friend = $this->cbfriend;
	}

	$onload_redirect=JRoute::_('index.php?option=com_invitex&view=invites&Itemid='.$itemid,false);
?>
</ br>
<div class="">
	<div class="page-header invitex_registered_friend_message">
		<h3><?php echo $registered_msg;?></h3>
	</div>
	<form method="POST" name="add_friend_form" class="form-horizontal" id="add_friend_form">
		<?php
		$i=0;
		$j=0;

		if (!empty($r_mail))
		{
			foreach ($r_mail as $m)
			{
				$database = JFactory::getDbo();
				$sql1 = "";

				if ($reg_direct == 'Community Builder')
				{
					$sql1="select u.name,u.id,cu.avatar from #__users AS u,#__comprofiler AS cu where u.email='".$m."' AND u.id = cu.user_id";

					$database->setQuery($sql1);
					$user = $database->loadObject();
					$joomla_user = JFactory::getUser($user->id);
					$img_path = $this->invhelperObj->sociallibraryobj->getAvatar($joomla_user);
					$link = JRoute::_($this->invhelperObj->sociallibraryobj->getProfileUrl($joomla_user));
					?>
					<div class="invitex_info span6">
						<a href="<?php echo $link;?>"><img class='invitex_user_image' src='<?php echo $img_path;?>'></a>
						<div class='invitex_user_image_text'>
							<a href="<?php echo $link;?>"><?php echo $user->name;?></a>
							<div>
								<?php echo $m;?>
							</div>
							<?php
								if (!in_array($user->id, $cb_friend))
								{
							?>
									<input type='hidden' name='user_ids[]' value='<?php echo $user->id;?>'>
									<input type="button" id="friendtoadd<?php echo $user->id;?>" onclick="addAsFriend('<?php echo $user->id;?>')" class="btn btn-info" value="<?php echo JText::_('ADD_AS_FRIEND');?>">
									<?php
									$j++;
								}
								else
								{
								?>
									<b><p><?php echo JText::_('CONNECTED');?></p></b>
								<?php
								}
							?>
						</div>
					</div>
				<?php
				}

				if($reg_direct == 'JomSocial')
				{
					$sql1 = "select u.name,u.id from #__users AS u where u.email='".$m."'";
					$database->setQuery($sql1);
					$u = $database->loadObject();
					$user = CFactory::getUser($u->id);
					$joomla_user = JFactory::getUser($u->id);
					$img_path = $this->invhelperObj->sociallibraryobj->getAvatar($joomla_user);
					$link = JRoute::_($this->invhelperObj->sociallibraryobj->getProfileUrl($joomla_user));
				?>
					<div class="invitex_info span6">
						<a href="<?php echo $link;?>"><img class='invitex_user_image' src='<?php echo $img_path;?>'></a>
							<div class='invitex_user_image_text'>
								<a href='<?php echo $link;?>'><?php echo $user->name;?></a>
								<br /><?php echo $m;?>
									<?php
									if (!in_array($user->id, $js_friend))
									{
										if (in_array($user->id, $jsinvitedfriend))
										{
										?>
											<p><?php echo JText::_('APPROVAL_PENDING');?></p>
										<?php
										}
										else
										{
										?>
											<br /><input type='hidden' name='user_ids[]' value='<?php echo $user->id;?>'>
											<input type="button" id="friendtoadd<?php echo $user->id;?>" onclick="addAsFriend('<?php echo $user->id;?>')" class="btn btn-info" value="<?php echo JText::_('ADD_AS_FRIEND');?>">
											<?php
											$j++;
										}
									}
									else
									{
									?>
										<b><p><?php echo JText::_('CONNECTED');?></p></b>
									<?php
									}
									?>
							</div>
					</div>
				<?php
				}

				if ($reg_direct == 'EasySocial')
				{
					require_once( JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php' );
					$sql1 = "select u.name,u.id from #__users as u where u.email='".$m."'";
					$database->setQuery($sql1);
					$u = $database->loadObject();
					$user = Foundry::user($u->id);

					$joomla_user = JFactory::getUser($u->id);
					$img_path = $this->invhelperObj->sociallibraryobj->getAvatar($joomla_user);
					$link = JRoute::_($this->invhelperObj->sociallibraryobj->getProfileUrl($joomla_user));
				?>
					<div class="invitex_info span6">
						<a href = '<?php echo $link;?>'><img class='invitex_user_image' src='<?php echo $img_path;?>'></a>
							<div class='invitex_user_image_text'>
								<a href='<?php echo $link;?>'><?php echo $user->name;?></a>
								<br /><?php echo $m;?>
								<?php
									if (!in_array($user->id, $es_friend))
									{
										if(in_array($user->id, $esinvitedfriend))
										{
										?>
											<p><?php echo JText::_('APPROVAL_PENDING');?></p>
										<?php
										}
										else
										{
										?>
											<br /><input type='hidden' name='user_ids[]' value='<?php echo $user->id;?>'>
											<input type="button" id="friendtoadd<?php echo $user->id;?>" onclick="addAsFriend('<?php echo $user->id;?>')" class="btn btn-info" value="<?php echo JText::_('ADD_AS_FRIEND');?>">
											<?php
											$j++;
										}
									}
									else
									{
									?>
										<b><p><?php echo JText::_('CONNECTED');?></p></b>
									<?php
									}
									?>
							</div>
					</div>
				<?php
				}

				if ($reg_direct=='Joomla' || $reg_direct=='Jomwall')
				{
				?>
					<div class="invitex_info span6"><?php echo $m;?></div>
				<?php
				}

				$i++ ;
			}//End of for Each
		}
		?>
		<div class="clearfix"></div>
		<?php
		if ($session->get('unsubscribe_mails'))
		{
			$unsub_msg=JText::_('BLOCKED_INVITATION_MSG'); ?>
			<div><h3><?php echo $unsub_msg; ?></h3></div>
			<?php
			foreach($session->get('unsubscribe_mails') as $mail)
			{
			?>
				<div><?php echo $mail;?></div>
			<?php
			}
			?>
		<?php
		}
		?>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" id="action" name="action" value=""/>
		<input type="hidden" id="ecount" name="ecount" value=""/>
		<input type="hidden" name="option" value="com_invitex" />
		<input type="hidden" name="controller" value="invites"/>
	</form>
</div>

<div class="separator"></div>
<?php
}
else
{
$title = JText::_('LOGIN_TITLE');?>
<div class="">
	<div class="page-header"><h2><?php echo $title?></h2></div>
		<div class="invitex_content" id="invitex_content">
				<h3><?php echo JText::_('NON_LOGIN_MSG');?></h3>
		</div>
	</div>
</div>
<?php
}
?>
