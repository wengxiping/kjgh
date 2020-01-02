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

$mainframe = JFactory::getApplication();
$isguest   = $this->invitex_params->get('guest_invitation');

if ($this->oluser || $isguest == 1)
{
	$path=$this->invhelperObj->getViewpath('invites','default_steps');
	include $path;
	?>
	<script language="javascript">
		jQuery(document).ready(function(){
			jQuery('#secondli').addClass('uiStepSelected uiStepFirst');
			jQuery('#firstli').removeClass('uiStepSelected uiStepFirst');
			jQuery('#thirdli').removeClass('uiStepSelected uiStepFirst');
			jQuery('#proceed2').addClass('current active');
			jQuery('#proceed1').removeClass('proceed active');
		});

		techjoomla.jQuery("#load_more").click(function (){
			techjoomla.jQuery("#ajax-loading").ajaxStart(function(){
				techjoomla.jQuery(this).show();
			});
			techjoomla.jQuery("#ajax-loading").ajaxStop(function(){
				techjoomla.jQuery(this).hide();
				show_count();
			});
		});

		function chk_form(element,limit,limit_messge)
		{
			var elm=document.getElementById(element);
			var count=document.getElementById("count").value;
			var j=0;
			j=techjoomla.jQuery(".contacts_check:checked").length;

			if(!j){
				alert('Please select at least one user/userid to send invites');
				return false;
			}

			if(document.getElementById("invite_limit")){
				if(j>document.getElementById("invite_limit").value){
					alert("You can send out " +document.getElementById("invite_limit").value+ " Invites!");
					return false;
				}
			}

			if(parseInt(limit)>0 && parseInt(limit)<j){
				alert(limit_messge);
				return false;
			}

			var personal_message = document.getElementById("personal_message").value;

			if(personal_message==""){
				alert("Please enter Message");
				return false;
			}

			elm.submit();
		}

		/*techjoomla.jQuery(function (){
			jQuery('#contact_tab a:last').tab('show');
		})*/
	</script>
	<?php
		$session = JFactory::getSession();
		$this->invitex_params = $this->invhelperObj->getconfigData();
		$reg_direct = $this->invitex_params->get('reg_direct');
		$itemid     = $this->itemid;
		$limit      = $this->send_message_limit;
		/* This coding is for captcha for orkut*/
		$img_src = '';
		$captcha_style = "style=display:none";

		if (JFactory::getApplication()->input->get('captcha'))
		{
			$captcha = JFactory::getApplication()->input->get('captcha');

			if ($captcha == '1')
			{
				$img_src       = "index.php?option=com_invitex&controller=invites&task=getcaptchaURL&Itemid=" . $itemid . "&oauth_token=" . $_SESSION['oauth_token'] . "&oauth_verifier=" . $_SESSION['oauth_verifier'];
				$captcha_style = "style=display:block";
			}
		}
		/* Captcha ends */
		?>
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
		<form id="apis_message_form" action="" method="post" class="form-horizontal">
			<div class="clearfix">&nbsp;</div>
			<div class="personal_message_label">
				<label for="personal_message">
				<h4><?php echo JText::_('OPTIONAL_MESSAGE');?></h4>
				</label>
			</div>
			<div class="personal_message_text">
				<textarea id="personal_message" rows="3" name="personal_message"  class="required"><?php if($session->get('personal_message'))  echo stripslashes($session->get('personal_message')); else echo stripslashes($this->invitex_params->get('invitex_default_message')) ?></textarea>
			</div>
			<div>
				<table>
					<tr id="additional_captcha" <?php echo $captcha_style; ?>>
						<td><img src="<?php echo $img_src ?>"></td>
						<td class="mail">
							<div class="form-group">
								<?php echo JText::_('Captcha');?>
								<input type="text" id="textcaptcha" name="textcaptcha" class="form-control"/>
								<input type="hidden" id="tokencaptcha" name="tokencaptcha" value="<?php echo $session->get('inv_orkut_captcha_token');?>" />
							</div>
						</td>
					</tr>
				</table>
				<div class="clearfix"></div>
				<?php
					if ($session->get('api_used') == 'plug_techjoomlaAPI_linkedin')
					{
						?>
				<div class="alert alert-block"><?php echo JText::_('SENT_LIMIT');?></div>
				<?php
					}
					?>
				<div class="clearfix"></div>
				<?php
					$i = 0;
					$k = 0;
					$tc = 0;
					$r_mail = array();
				?>
				<div class="page-header">
				<h3><?php echo JText::_('COM_INVITEX_IMPORTED_CONTACTS');?></h3>
				</div>
				<div class="clearfix">&nbsp;</div>
				<!--TABS for selected contact-->
				<ul id="contact_tab" class="nav nav-tabs">
					<li class="active">
						<a href="#all_contact" data-toggle="tab"><?php echo JText::_('INV_ALL_CONTACTS'); ?></a>
					</li>
					<li>
						<a href="#selcted_contacts_div" data-toggle="tab"><?php  echo JText::_('INV_SELECTED_CONTACTS'); ?><span id="selcted_contacts_title"></span></a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="all_contact">
						<div class="invitex_select_users_toinvite_header">
							<?php
								if ($this->contacts)
								{
									$contacts = $this->contacts;
								}
							?>
							<div class="row">
							<div class="pull-left invitex_contacts_headder_button">
								<input type='button' class="btn btn-success" onClick='selectAll()' name='select_all' id='select_all' value="<?php  echo JText::_('COM_INVITEX_SELECT_ALL'); ?>"/>
								<input type='button' class="btn btn-warning" onClick='diselectAll()' name='diselect_all' id='diselect_all' value="<?php  echo JText::_('COM_INVITEX_DESELECT_ALL'); ?>" checked/>
								<?php
								$limit_message = JText::sprintf( 'API_SEND_INVITES_LIMIT_MSG', $limit, $session->get('api_used'));
								?>
								<input type="button" name="send" id="send_invites" value="<?php echo JText::_('SEND_INV') ?>" class="btn btn-primary" onclick="chk_form('apis_message_form','<?php echo $limit;?>','<?php echo addslashes($limit_message);?>');">
							</div>
							<div class="pull-right invitex_contacts_headder_search form-group">
								<input type="text" id="invitex_search" class="inputbox form-control" placeholder="<?php echo JText::_('TYPE_HERE');?>" />
							</div>
							</div>
						</div>
						<div class="invitex_select_users_toinvite_body">
							<div id="invitex_invitee_info">
								<?php
									if (!empty($contacts))
									{
									    //dump();die;
									?>
										<?php
										foreach ($contacts as $contact)
										{
								?>
											<div class="invitex_info col-md-6 col-sm-12" id="invitex_info_<?php echo $i;?>">
												<div class="pull-left invitex-margin-right-15">
												<?php
													if (!empty($contact->name))
													{
														$c_index = $contact->name;
													}
													else
													{
														$c_index = $contact->id;
													}
												?>
													<input name="<?php echo 'contacts['.$c_index.']'?>" type="checkbox" id="contact_<?php echo $i;?>" value="<?php echo $contact->id;?>" class='contacts_check' checked/>
												</div>
												<?php
												if ($session->get('api_message_type') != 'email')
												{
													if(isset($contact->picture_url))
													{
												?>
														<div class="picture pull-left" width="10%">
														<?php
														if($contact->picture_url)
														{
														?>
															<img src="<?php echo $contact->picture_url;?>" alt="" title="" width="50" height="50"/>
														<?php
														}
														else
														{
														?>
															<img src="media/com_invitex/images/apis/anonymous.png" alt="NO IMAGE" title="NO IMAGE" width="50" height="50"/>
														<?php
														}
														?>
														</div>
													<?php
													}
												}
													?>
												<div class="info pull-left">
													<label for="contact_<?php echo $i;?>">
													<?php
													if (!empty($contact->name))
													{
														echo "<b>" . $contact->name ." — ". $contact->phone . "</b><br/>";
													}

													if (isset($contact->id))
													{
														echo $contact->id;
													}
													?>
												</label>
												</div>
												<div class="clearfix">&nbsp;</div>
											</div>
										<?php
										$i++;
										}
									}
									else
									{
										echo JText::_('COM_INVITEX_NO_CONTACTS_FOUND');
									}
									// End foreach
									?>
							</div>
						</div>
					</div>
					<!--tab 1 all contact ends-->
					<!--tab2 selected contacts starts-->
					<div class=" invitex_select_users_toinvite_body tab-pane" id="selcted_contacts_div" class="invitex_select_users_toinvite_header">
						<table class="table table-striped"  id="selected_contact" class="invitex_select_users_toinvite_body">
						</table>
					</div>
					<!--tab 2 selected contacts ends-->
				</div>
				<!--tabbale ends-->
				<?php
					$display = 'none';

					if ($this->invitex_params->get("enb_load_more") || $session->get('api_used')== 'plug_techjoomlaAPI_twitter')
					{
						if ($session->get('api_used') == 'plug_techjoomlaAPI_facebook' || $session->get('api_used') == 'plug_techjoomlaAPI_linkedin' || $session->get('api_used') == 'plug_techjoomlaAPI_yahoo' || $session->get('api_used') == 'plug_techjoomlaAPI_twitter')
						{
							$display = 'block';
						}
						else
						{
							$display = 'none';
						}
					}
					?>
				<input type="button" class="btn btn-primary" name="load_more" id="load_more" style="display:<?php echo $display ; ?>" value="<?php echo JText::_('LOAD_MORE');?>" onclick="load_more_contacts('<?php echo $session->get('api_used')?>','<?php echo $session->get('api_message_type'); ?>','invitex_invitee_info'); show_count();">
				<div id="ajax-loading" class="ajax-loading" style="display:none;">&nbsp;&nbsp;&nbsp;</div>
				<?php echo JHtml::_('form.token'); ?>
				<input type="hidden" name="offset" id="offset" value="0">
				<input type="hidden" name="limit" id="limit" value="<?php echo ($session->get('api_used')== 'plug_techjoomlaAPI_twitter') ?'99' : $this->invitex_params->get('contacts_at_first_instance'); ?>">
				<input type="hidden" id="count" name="count" value="<?php echo count($this->contacts);?>">
				<input type="hidden" name="option" value="com_invitex">
				<input type="hidden" name="controller" value="invites">
				<input type="hidden" name="task" value="save">
				<?php
					$invitestobesent = '-1';

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

						if (empty($limit_data->invitations_sent))
						{
							$limit_data->invitations_sent = 0;
						}

						if ($limit && $limit >= $limit_data->invitations_sent)
						{
							$invitestobesent = $limit-$limit_data->invitations_sent;
							echo '<input type="hidden" name="invite_limit" id="invite_limit" value="' . $invitestobesent . '"/>';
						}
					}
					?>
				<div class="clearfix"></div>
			</div>
			<div class="row">
			<?php
				$path = $path = $this->invhelperObj->getViewpath('invites', 'default_registered_users');
				include $path;
			?>
			</div>
		</form>
	</div>
	<div class="clearfix"></div>
	<?php
		if (JFactory::getApplication()->input->get('invite_anywhere')!='1')
		{
			// Load Footer template
			$path = $this->invhelperObj->getViewpath('invites', 'default_footer');
			include $path;
		}
}
// Ask for login
else
{
	$title = JText::_('LOGIN_TITLE'); ?>
<div class="">
	<div class="page-header">
		<h2><?php echo $title?></h2>
	</div>
	<div class="invitex_content" id="invitex_content">
		<h3><?php echo JText::_('NON_LOGIN_MSG');?></h3>
	</div>
</div>
<?php
}
