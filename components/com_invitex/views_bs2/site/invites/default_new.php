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

if(JFolder::exists(JPATH_SITE."/components/com_community"))
	require_once(JPATH_SITE."/components/com_community/libraries/core.php");


$mainframe = JFactory::getApplication();
$document=JFactory::getDocument();
$document->addStyleSheet(JUri::base().'media/com_invitex/css/black_n_white_layout.css');
?>
		<script type="text/javascript">
			techjoomla.jQuery(document).ready(function () {
				jQuery("#inv_jsfriend-search-filter").focus(function(){ this.value='';
				jQuery("#inv_jsfriend-search-filter").quicksearch("#inv_js_InvitationTabContainer #inv_js_invitation_list #inv_friend_li", {
					delay : 300,
					noResults: '#noresults',
					stripeRows: ['odd', 'even'],
				});



			});



			var characters= techjoomla.jQuery(".thCheckbox:checked").length;
				techjoomla.jQuery("#select_count").append("Selcted contacts: <strong>"+ characters+"</strong>");

				techjoomla.jQuery(".thCheckbox").click(function(){
								techjoomla.jQuery("#select_count").html('');
								characters= techjoomla.jQuery(".thCheckbox:checked").length;
				       	techjoomla.jQuery("#select_count").append("Selected contacts:  <strong>"+ characters+"</strong>");
				   });

		 });
			function send_invitation(){
					document.inv_js_invitation_form.submit();;
			}
			function showinvitebuttondiv(thisli,id)
			{

					techjoomla.jQuery(".invitex_li").each(function(){

						if(this.id!=thisli.id)
						{
								techjoomla.jQuery(this).removeClass("animiate");
								techjoomla.jQuery(this).find('img').animate({
														width:"64px",
														height: "64px",
														fontSize: "2em",
													  }, 1);
						}
						if(this.id==thisli.id)
						{
							techjoomla.jQuery(thisli).addClass("animiate");
							techjoomla.jQuery('.animiate').find('img').animate({
														width:"75px",
														height: "75px",
														fontSize: "2em",
													  }, 1 );


						}

					});

					techjoomla.jQuery.each(techjoomla.jQuery('.tab-pane'),function(){
						if(this.id==id)
						  document.getElementById(this.id).style.display="block";
						 else
							document.getElementById(this.id).style.display="none";
					});

			}


			function toggleAll(element)
			{
				var form=document.forms.inv_js_invitation_form;
				for(z=0; z<form.length;z++)
				{
					if(form[z].type == 'checkbox')
					{
						form[z].checked = element.checked;
					}
				}
				show_count();
			}

			function show_api_div(forn_name,api_used,message_type)
			{
				document.getElementById('try_new_div').style.display="block";
				document.getElementById('api_used').value=api_used;
				document.getElementById('api_message_type').value=message_type;
			}
		</script>

<?php
$isguest=$this->invitex_params->get('guest_invitation');
if($this->oluser || $isguest==1)
{
	if(!$this->oluser && $isguest==1)
	{
		$user_is_a_guest=1;
	}
		$mainframe = JFactory::getApplication();

		$session = JFactory::getSession();
		$document   = JFactory::getDocument();
		$invite_methods=$invite_apis=$invite_anywhere=$invite_url=$invite_type='';
		if($this->oluser)
		$uid=$this->oluser->id;
		else
		$uid=0;
		$itemid = $this->itemid;
		if(JFactory::getApplication()->input->get('fb_redirect','','get'))
		{
				if(JFactory::getApplication()->input->get('fb_redirect','','get')=="success")
					$mainframe->redirect('index.php?option=com_invitex&view=invites&Itemid='.$itemid, "Invites Sent Succesfully");
		}


		if($this->invitex_params->get('invite_methods'))
			$invite_methods=$this->invitex_params->get('invite_methods');
		if($this->invitex_params->get('invite_apis'))
			$invite_apis=$this->invitex_params->get('invite_apis');

		$onload_redirect=JRoute::_('index.php?option=com_invitex&view=invites&layout&&layout=default_new&Itemid='.$itemid,false);


		if(isset($_SERVER['HTTP_REFERER']))
   	 	$referer = $_SERVER['HTTP_REFERER'];

		include(JPATH_COMPONENT_SITE.'/views/invites/tmpl/menu.php');
		/* set session vaiable to blank */

		$_SESSION['oauth_token']='';
		$_SESSION['oauth_verifier']='';
		$friends='';
		$invite_anywhere=JJFactory::getApplication()->input->get('invite_anywhere','','get');
		if($invite_anywhere	== '1')
		{

					$this->invhelperObj->setSession();
					$session->set('invite_anywhere', $invite_anywhere);

					if(JFactory::getApplication()->input->get('invite_url','','get')){
					if($session->get('invite_url') != JFactory::getApplication()->input->get('invite_url','','get')){
							$referer=rawurldecode(JFactory::getApplication()->input->get('invite_url','','get'));
							$session->set('invite_url',$referer);
						}
					}
					if(isset($_SERVER['HTTP_REFERER']))
						$referer = $_SERVER['HTTP_REFERER'];

					if(!$session->get('invite_url')){
						if($referer)
							$session->set('invite_url',$referer);
					}

					if(isset($_SERVER['HTTP_REFERER']))
						$referer = $_SERVER['HTTP_REFERER'];

					if(!$session->get('invite_url')){
						if($referer)
							$session->set('invite_url',$referer);
					}

					if(JFactory::getApplication()->input->get('tag','','get'))
							$session->set('invite_tag',JFactory::getApplication()->input->get('tag','','get'));

					$session->set('invite_type',JFactory::getApplication()->input->get('invite_type','','INT'));

					$typedata=$this->invhelperObj->types_data(JFactory::getApplication()->input->get('invite_type', '', 'INT'));

					$invite_methods=$typedata->invite_methods;
					$invite_apis=$typedata->invite_apis;

					$jspath = JPATH_ROOT.'/components/com_community';
					if(JFolder::exists($jspath)){
						include_once($jspath.'/libraries/core.php');
						// Include Messaging library
						// Add onclick action
						$friendsModel	= CFactory::getModel( 'Friends' );
						$friends		= $friendsModel->getFriends( $uid , 'name' , false);
					}
			 $onload_redirect=JRoute::_('index.php?option=com_invitex&view=invites&invite_type='.JFactory::getApplication()->input->get('invite_type','','INT').'&invite_url='.JFactory::getApplication()->input->get('invite_url','','get').'&catch_act=&invite_anywhere=1&Itemid='.$itemid,false);
		}
		else
		{
			$session->set('invite_anywhere', '');
			$session->set('invite_tag','');
			$session->set('invite_type','');
			$session->set('invite_url','');
		}

		if($this->invitex_params->get('show_menu')=='1')
		{
		?>
			<script>
			window.onload=set_redirect;
			function set_redirect()
			{
				techjoomla.jQuery('#proceed1').addClass('current');
				techjoomla.jQuery('#proceed1 .label').addClass('badge-inverse');

				techjoomla.jQuery('#proceed2').removeClass('current');
				techjoomla.jQuery('#proceed2 .label').removeClass('badge-inverse');

				document.getElementById('bar').style['width']="30%";
			}
			</script>
		<?php
		}
		?>

		<script>
		function upload(val,formName,user_is_a_guest)
		{
			switch (val)
			{
				case 'invitex':
					if (document.getElementById("email_box").value == "") {
					alert("Please enter an email-id to import contacts.");
					return false;
					}
					if (document.getElementById("password_box").value == "") {
					alert("Please enter a password.");
					return false;
					}
					if (document.getElementById("provider_box").value == "") {
					alert("Please select an email provider.");
					return false;
					}
					if(user_is_a_guest==1)
					{
						var valid_guest = set_guest_name(formName);
							if(!valid_guest)
							{
								return false;
							}
					}
					document.emailimportform.submit();
					break;

					case 'social_invitex':
					if (document.getElementById("social_email").value == "") {
					alert("Please enter an email-id to import contacts.");
					return false;
					}
					if (document.getElementById("social_password").value == "") {
					alert("Please enter a password.");
					return false;
					}
					if (document.getElementById("social_provider").value == "") {
					alert("Please select an email provider.");
					return false;
					}
					if(user_is_a_guest==1)
					{
						var valid_guest = set_guest_name(formName);
							if(!valid_guest)
							{
								return false;
							}
					}
					document.socialimportform.submit();
					break;

			case 'csvupload':
					default:
					if (document.getElementById("csvfile").value == "") {
					alert("Please upload a CSV file.");
					return false;
					}
					if(user_is_a_guest==1)
					{
						var valid_guest = set_guest_name(formName);
							if(!valid_guest)
							{
								return false;
							}
					}
					document.csvform.submit();
					break;
			}
		}

		function display_api(thisli,form_name,img_path,img_name,api_used,api_message_type,user_is_a_guest)
		{

			showinvitebuttondiv(thisli,invite_apis_form)
			techjoomla.jQuery("#invite_apis_form form").attr('id',form_name);
			techjoomla.jQuery("#invite_apis_form form").attr('name',form_name);
			//techjoomla.jQuery("#invite_apis_form form a").attr(\'href\',"javascript:document."+form_name+".submit()");
			//techjoomla.jQuery("#invite_apis_form form a img").attr(\'src\',img_path+img_name);
			techjoomla.jQuery("#invite_apis_form form #api_used").attr('value',api_used);
			techjoomla.jQuery("#invite_apis_form form #api_message_type").attr('value',api_message_type);

			var to_ste_name_guest="";
			if(user_is_a_guest==1){
				to_ste_name_guest="onclick=\"return(set_guest_name(\'"+form_name+"\'))\" ";
			}

			var connect_image="<img class=\"invitex_image_connect\" src=\'"+img_path+"large/"+img_name+"\' />";
			var connect_btn= "<button id=\"form_connect_btn\" type=\"submit\" class=\"btn btn-primary \" "+to_ste_name_guest+"  >'<?php echo JText::_("INV_CONNECT");?>'</button>";

			techjoomla.jQuery("#connect_btn_image_div").html(connect_image);

			techjoomla.jQuery("#connect_btn_div").html(connect_btn);
			techjoomla.jQuery("#invite_apis_form").show();
		}
		</script>
<?php

		$html='';
		$invite_methods = explode(',',$invite_methods);
		$invite_apis = explode(',',$invite_apis);

		$oi_plugin_selection=array();
		$oi_path = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';
		if(JFile::exists($oi_path)){
			require_once( JPATH_BASE . '/components/com_invitex/openinviter/openinviter.php');
			require(JPATH_SITE."/components/com_invitex/openinviter/config.php");

			$inviter = new openinviter();
			$oi_services=$inviter->getPlugins();
			if(null !== $this->invitex_params->get('selections'))
			$oi_plugin_selection =explode(',',$this->invitex_params->get('selections'));
		}
	  	$user_is_a_guest=0; //parameter used to know whether the user is a guest or a registred user.



?>

<!--FOR GUEST USER...SHOWN CAPTCHA-->
<div class="">
	<div class="invitex_black_n_white row-fluid">
		<div class="span12 tabbable tabs-left inv_tabbable">
			<?php

			if(!$this->oluser && $isguest==1)
			{
				$user_is_a_guest=1;
			?>

			<form class="form-horizontal"  method="POST" name="guest_info" id="guest_info">
			<div class="well">
				<div class="control-group">
						<label class="control-label" for="guest_name" title="<?php echo JText::_('INV_GUEST_NAME');?>">
							<?php echo JText::_('INV_GUEST_NAME');?>
						</label>
						<div class="controls">
							<input	type="textbox" id="guest_name" placeholder="Your name"	value='' />
						</div>
				</div>

				<div class="control-group">

						<label class="control-label" for="city" title="<?php echo JText::_('Enter Captcha');?>">
							<?php echo JText::_('Enter Captcha');?>
						</label>
						<div class="controls">
							<?php
							JPluginHelper::importPlugin('captcha');
							$dispatcher = JDispatcher::getInstance();
							$dispatcher->trigger('onInit','dynamic_recaptcha_1');
							?>
							<div id="dynamic_recaptcha_1"></div>
						</div>
				</div>
			</div>
			</form>

			<?php	}	?>


		<div class="well well-small top_div_icon" >
        <ul class="thumbnails icon_ul">
         <?php
					$api_used='';
					$message_type='';
					$img_name='';
					$show_api=0;
					$class='';
					$active_tab='';

					$img_name='';
					$li_count=1;
					foreach($invite_methods as  $ind=>$method)
					{
						if($li_count==1){
							$class='active';
							$active_tab=$method;
						}
						else
							$class='';
						?>
						<?php if($method == 'social_apis')
						{
							if(!empty($invite_apis))
							{
								$result=$this->renderAPIicons;

								$img_path=JUri::root()."media/com_invitex/images/methods/icons1/";
								$api_cnt=1;
								$cnt=0;
								for($i=0; $i<count($result); $i++)
								{
									if(isset($result[$i]['message_type']) && $result[$i]['message_type']=='pm'){
									 $form_name=$result[$i]['name'].'_connect_form';

									  if($li_count==1){
										$api_used=$result[$i]['api_used'];
										$message_type=$result[$i]['message_type'];
										$img_name=$result[$i]['img_file_name'];
										$active_tab=$form_name;
										$show_api=1;
									}
									else
									{

										$class='';
									 }
									 ?>


										<li class="invitex_li" id="list_invitex_li<?php	echo $li_count;	?>" for='<?php echo $form_name;?>'  name="invite_apis" onclick="display_api(this,'<?php echo $form_name ?>','<?php echo $img_path ?>','<?php echo $result[$i]['img_file_name'] ?>','<?php echo $result[$i]['api_used'] ?>','<?php echo $result[$i]['message_type'] ?>',<?php	echo $user_is_a_guest;	?>);">
											<div id="<?php echo 'api_'.$api_cnt.'_content'?>" >
													<img title="<?php 	echo $result[$i]['name'];	?>" src="<?php	echo $img_path.$result[$i]['img_file_name'] ?>" />
											</div>
										</li>
						<?php
									$li_count++;
									}

								}
							}
						}
						else if($method == 'email_apis')
						{
							if(!empty($invite_apis))
							{
								$result=$this->renderAPIicons;
								$img_path=JUri::root()."components/com_invitex/images/methods/icons1/";
								$api_cnt=1;
								$cnt=0;
								for($i=0; $i<count($result); $i++)
								{
									if(isset($result[$i]['message_type']) && $result[$i]['message_type']=='email'){
									 $form_name=$result[$i]['name'].'_connect_form';

									 if($li_count==1){
										$api_used=$result[$i]['api_used'];
										$message_type=$result[$i]['message_type'];
										$img_name=$result[$i]['img_file_name'];
										$active_tab=$form_name;
										$show_api=1;
									}
									else
									{

										$class='';
									 }
									 ?>
									<li class="invitex_li" id="list_invitex_li<?php	echo $li_count;	?>" for='<?php echo $form_name;?>'  name="invite_apis" onclick="display_api(this,'<?php echo $form_name ?>','<?php echo $img_path ?>','<?php echo $result[$i]['img_file_name'] ?>','<?php echo $result[$i]['api_used'] ?>','<?php echo $result[$i]['message_type'] ?>',<?php	echo $user_is_a_guest;	?>);">
											<div id="<?php echo 'api_'.$api_cnt.'_content'?>">
													<img  title="<?php 	echo $result[$i]['name'];	?>" src="<?php	echo $img_path.$result[$i]['img_file_name'] ?>" />
											</div>
									</li>
						<?php
									$li_count++;
									}

								}
							}
						}
						else{  ?>
						<?php	if(!($method=='oi_email' || $method=='oi_social')){
								$method_tooltip='';
								if($method=='other_tools')
								{
									$method_tooltip="CSV import";
								}
								else if($method=='inv_by_url')
								{
									$method_tooltip="Invite By URL";
								}
							?>
						<li class="invitex_li" id="list_invitex_li<?php	echo $li_count;	?>" onclick="showinvitebuttondiv(this,'<?php echo $method ?>')">
										<div>

												<img title="<?php 	echo $method_tooltip;	?>" src="<?php echo JUri::root().'components/com_invitex/images/methods/icons1/'.$method.'.png';?>" alt="">
										</div>
									<div class="clearfix"></div>

						</li>

							<?php
									$li_count++;
								}

							}

				 } 	?>
			 </ul>

			<?php	if((in_array('oi_email',$invite_methods)) || in_array('oi_social',$invite_methods)){	?>
					<ul class="thumbnails " >
					<li id="list_invitex_li<?php	echo $li_count;	?>" class="invitex_li" style="float:right">
						    <div class="btn-group">
								<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
								<?php echo JText::_('INV_OTHERS'); ?>
									<span class="caret"></span>
								</a>
								<ul class="dropdown-menu">
									<?php	if(in_array('oi_email',$invite_methods)){	?>
									<li class="inv_other_mmethods_li" onclick="showinvitebuttondiv(this,'oi_email')"><?php echo JText::_('INV_METHOD_OI_EMAIL'); ?></li>
									<?php } ?>
									<?php	if(in_array('oi_social',$invite_methods)){	?>
									<li class="inv_other_mmethods_li" onclick="showinvitebuttondiv(this,'oi_social')"><?php echo JText::_('INV_METHOD_OI_SOCIAL'); ?></li>
									<?php } ?>
								</ul>
							</div>
						</li>
					 </ul>
				<?php	}	?>




        </div><!--well div...align center-->
        <div class="tab-content">
		<div id="steps_div" >
		<?php	include(JPATH_COMPONENT_SITE.'/views/invites/tmpl/steps.php');	?>
		</div>
	  <div id="manual" class="tab-pane" name="inv_methods" style="<?php echo ($active_tab=='manual')?'display:block':'display:none'?>">
			<form class="form-horizontal"  method="POST" name="manualform" id="manualform">
				<div class="alert alert-info"><?php echo JText::_('MANUAL_MESS') ?></div>
					<ul class='selections' id="selections">
						<?php
							$this->invitex_params_for_emails=array();
							$this->invitex_params_for_emails[]=$this->invitex_params->get('allow_domain_validation');
							if($this->invitex_params->get('allow_domain_validation')==1)
							$this->invitex_params_for_emails[]=$this->invitex_params->get('invite_domains');
							//if tagging enable
							$useremail = '';
							if($this->oluser)
							{
								$useremail = $this->oluser->email;
							}
							$enable_tagging = '';
							if($this->invitex_params->get('manual_emailtags') == 1)
							{
								$enable_tagging = 'onkeypress=\'validate_email_step1(event,'. $this->invitex_params->get('allow_domain_validation').' ,'.json_encode(explode(",",$this->invitex_params->get('invite_domains'))).' ,"'.$useremail.'")\'';
							}

						?>
						<input type="text" class="fields" id="invitex_mail" <?php echo $enable_tagging; ?>  value=""/>
						<input type="hidden" class="invitex_fields_hidden" name="invitex_correct_mails" id="invitex_correct_mails" value="" />
						<input type="hidden" class="invitex_fields_hidden" name="invitex_wrong_mails" id="invitex_wrong_mails" value="" />
					</ul>
					<div class="alert alert-error" id="invalid_email_message" style="display:none">
							<?php echo JText::_('INVALID_EMAIL_MESSAGE'); ?>

							<?php
								$domains=$this->validdomains;
								if($domains)
								{
										echo JText::_('SUPPORTED_DOMAINS_HEADING');
										$domain_str=implode(',',$domains);
										echo "<b>".	$domain_str ."</b>";
								}
									?>

					</div>
					<div class="clearfix">&nbsp;</div>
					<h4><?php echo JText::_('OPTIONAL_MESSAGE'); ?></h4>

					<div class="separator"></div>
					<div class="well"><textarea rows="3" id="personal_message" name="personal_message" wrap="soft" class="personal_message"><?php echo stripslashes($this->invitex_params->get('invitex_default_message')) ?></textarea></div>
					<div class="form-actions">
						<input class="btn btn-primary " type="button"  value="<?php echo JText::_('SEND_INV'); ?>" name="quick_send" onclick='return upload_manual("<?php echo JText::_('ATLEAST_ONE');?>","<?php echo JText::_('ALL_WRONG_EMAILS');?>","<?php echo JText::_('INVITES_LEFT_MSG');?>","<?php echo JText::_('INCORRECT_EMAILS_REMOVED');?>",<?php echo $this->invitex_params->get('allow_domain_validation') ?>,<?php echo json_encode(explode(",",$this->invitex_params->get('invite_domains'))); ?>,"<?php if($this->oluser){ echo $this->oluser->email;	}	?>","manualform",<?php echo $user_is_a_guest; ?>)'>
					</div>


					<div class="clearfix"></div>
					<?php echo JHtml::_( 'form.token' ); ?>
					<input type="hidden" name="option" value="com_invitex">
					<input type="hidden" name="task" value="sort_mail">
					<input type="hidden" name="rout" id="rout" value="manual">
					<input	type="hidden" id="guest" name="guest"	value='' />
					<?php
					if(!$session->get('invite_anywhere'))
					{
						$limit_data=$this->limit_data;
						$limit=0;
						if(!isset($limit_data->limit))
									$limit=$this->invitex_params->get('per_user_invitation_limit');
						else
									$limit = $limit_data->limit;
						if($limit && $limit >= $limit_data->invitations_sent)
						{
							$invitestobesent = $limit-$limit_data->invitations_sent;
							echo '<input type="hidden" name="invite_limit" id="invite_limit" value="'.$invitestobesent.'"/>';
						}
					}
					?>
			</form>
	  </div>


	<div id="invite_apis_form" style="<?php echo ($show_api==1)? 'display:block':'display:none'?>" class="tab-pane" name="tab-pane-div">
	</div>
			<div id="oi_email" class="tab-pane" style="<?php echo ($active_tab=='oi_email')?'display:block':'display:none'?>">
				<form class="form-horizontal" method="POST" name="emailimportform" id="emailimportform">
								<fieldset>
										<div class="alert alert-info"><?php echo JText::_('IMPORT_MESS') ?></div>
										<div class="control-group">
											   <label for="email_box" class="control-label"><span class="label"><?php echo JText::_('USER_ID') ?></span></label>
													<div class="controls">
														<input class="inputbox" type="text" name="email_box" id="email_box" value="">
													</div>
											</div>
											<div class="control-group">
													<label for="password_box" class="control-label"><span class="label"><?php echo JText::_('PASS') ?></span></label>
													<div class="controls">
															<input class="inputbox" type="password" name="password_box" id="password_box" value="">
													</div>
											</div>
											<div class="control-group">
													<label for='provider_box' class="control-label"><span class="label"><?php echo JText::_('PROVIDER') ?></span></label>
													<div class="controls">
															<select name="provider_box" class='thSelect' id="provider_box">
															<?php
															$i=1;
															?>
															<?php
															foreach($oi_services as $type=>$providers)
															{ ?>
																<?php if($inviter->pluginTypes[$type]=='Email Providers') {
																$s="";
																foreach($providers as $provider=>$details)
																{
																			if($this->invitex_params->get('plg_option')=='custom_select')
																			{
																				if(in_array($details['name'], $oi_plugin_selection))
																				{
																				?>
																					<option value="<?php echo $provider?>" ><?php echo $details['name'];?></option>
																			<?php
																				}
																			}
																			else
																			{
																				?>
																				<option value="<?php echo $provider?>" ><?php echo $details['name'];?></option>
																		<?php
																			}
																}
																	?>

															<?php
																}
															}
															?>
													</select>
												</div>
											</div>
											<div class="form-actions">
													<input class="btn btn-primary " type="button" name="import" value="<?php echo JText::_('IMPORT_CON');?>" onclick="upload('invitex','emailimportform',<?php	echo $user_is_a_guest;	?>);">
											</div>
									</fieldset>

										<div class="clearfix"></div>
										<?php echo JHtml::_( 'form.token' ); ?>
										<input type="hidden" name="option" value="com_invitex">
										<input type="hidden" name="task" value="sort_mail">
										<input type="hidden" name="rout" id="rout" value="OI_import">
										<input	type="hidden" id="guest" name="guest"	value='' />
										<input type="hidden" name="import_type" id="import_type" value="email">
						</form>
          </div>

			<div id="oi_social" class="tab-pane" style="<?php echo ($active_tab=='oi_social')?'display:block':'display:none'?>">
				<form class="form-horizontal"  method="POST" name="socialimportform" id="socialimportform">
									<fieldset>
											<div class="alert alert-info"><?php echo JText::_('IMPORT_MESS') ?></div>
											<div class="control-group">
														<label for="email_box" class="control-label"><span class="label"><?php echo JText::_('USER_ID') ?></span></label>
													 <div class="controls">
														<input class="inputbox" type="text" name="social_email" id="social_email" value="">
													</div>
											</div>
											<div class="control-group">
															<label for="password_box" class="control-label"><span class="label"><?php echo JText::_('PASS') ?></sapn></label>
															<div class="controls">
															<input class="inputbox" type="password" name="social_password" id="social_password" value="">
														</div>
											</div>
											<div class="control-group">
														<label for='provider_box' class="control-label"><span class="label"><?php echo JText::_('PROVIDER') ?></span></label>
														<div class="controls">
															<select name="social_provider" class='thSelect' id="social_provider">
															<?php
															$i=1;?>
															<?php
															foreach($oi_services as $type=>$providers)
															{ ?>
								 										<?php if($inviter->pluginTypes[$type]=='Social Networks') {?>
									 									<?php $s="";
							 											foreach($providers as $provider=>$details)
								 										{
																				if($this->invitex_params->get('plg_option')=='custom_select')
																				{
										   										if(in_array($details['name'], $oi_plugin_selection)) { ?>
										    									<option value="<?php echo $provider?>" ><?php echo $details['name'];?></option>
																<?php   	}
																				}
																				else {?>
																					<option value="<?php echo $provider?>" ><?php echo $details['name'];?></option>
						 							  <?php  			}
																	}?>

											<?php   				}
														}
																 ?>
													</select>
												</div>
											</div>
											<div class="form-actions">
													<input class="btn btn-primary " type="button" name="social_import" value="<?php echo JText::_('IMPORT_CON');?>" onclick="upload('social_invitex','socialimportform',<?php	echo $user_is_a_guest;	?>);">
											<div>
									</fieldset>

										<div class="clearfix"></div>
										<?php echo JHtml::_( 'form.token' ); ?>
										<input type="hidden" name="option" value="com_invitex">
										<input type="hidden" name="task" value="sort_mail">
										<input	type="hidden" id="guest" name="guest"	value='' />
										<input type="hidden" name="rout" id="rout" value="OI_import">
										<input type="hidden" name="import_type" id="import_type" value="social">
						</form>
          </div>

					<div id="other_tools" class="tab-pane" style="<?php echo ($active_tab=='other_tools')?'display:block':'display:none'?>">
						  <form class="form-horizontal"  method="POST" name="csvform" ENCTYPE="multipart/form-data" id="csvform">
											<fieldset>
														<div class="alert alert-info"><?php echo JText::_('CSV_MESS');?></div>
														<div class="control-group">
														     <label for="csvfile" class="control-label"><span class="label"><?php echo JText::_('UPL_CSV') ?>:</span></label>
																<div class="controls">
																		<input name="csvfile" type="file" id="csvfile" class="input-file"/>
																</div>
														</div>
														<div class="form-actions">
															<input class="btn btn-primary " type="button" name="import" value="<?php echo JText::_('IMPORT_CON');?>" onclick="upload('csvupload','csvform',<?php	echo $user_is_a_guest;	?>);">
														<div>
												</fieldset>
								<div class="clearfix"></div>
										<?php echo JHtml::_( 'form.token' ); ?>
										<input type="hidden" name="option" value="com_invitex">
										<input type="hidden" name="task" value="sort_mail">
										<input	type="hidden" id="guest" name="guest"	value='' />
										<input type="hidden" name="rout" id="rout" value="other_tools">
						</form>
          </div>


					<div id="inv_by_url" class="tab-pane" style="<?php echo ($active_tab=='inv_by_url')?'display:block':'display:none'?>">
									<form class="form-horizontal">
										<fieldset>
											<div class="alert alert-info"><?php echo JText::_('INVIT_URL_DES');?></div>
										<?php
										$invURL = $this->invhelperObj->getinviteURL();

										if (strpos($invURL, '?') !== false)
										{
											$invURL .= "&method_of_invite=invite_by_url";
										}
										else
										{
											$invURL .= "?method_of_invite=invite_by_url";
										}

										$invURL = $this->invhelperObj->givShortURL($invURL);
									    ?>
										 <div class="control-group">
												 <label for="invite_url" class="control-label"><span class="label"><?php echo JText::_('INV_URL_LABLE');?>:</span></label>
												<div class="controls">
													<input class="inputbox" type="text" name="invite_url" readonly="true" value="<?php echo $invURL; ?>"/>
												</div>
									  </div>
									</fieldset>
									</form>
          </div>

				<div id="js_messaging" class="tab-pane" style="<?php echo ($active_tab=='js_messaging')?'display:block':'display:none'?>">
						<?php if($friends){ ?>
           <form class="form-horizontal" id="inv_js_invitation_form" name="inv_js_invitation_form"  method="POST">
									<fieldset>
										<div id="invitation-error"></div>

										<div>
											<div class="label label-info" id="select_count" ></div>
											<div id="inv_jsfriend_search_filter_div" ><input type="text" id="inv_jsfriend-search-filter" name="inv_jsfriend-search-filter" class="inputbox" placeholder="type your friend's name here..."></div>
									</div>

										<div id="inv_js_InvitationTabContainer">
												<div class="inv_js_community-invitation" id="inv_js_community-invitation" style="display: block;">
													<!--for select all check box-->
													<input type='checkbox' onClick='toggleAll(this)' name='toggle_all' id='toggle_all' value="Select All" title="Select/Deselect all" checked/>
													<!--end-->
													<div id="js_srcolable_div" >
														<ul id="inv_js_invitation_list">
													<?php
														foreach( $friends as $friend )
														{
															if($uid!= $friend->id)
															{
																?>
																	<li id="inv_friend_li" >
																		<div class="inv_js_invitation_wrap">
																			<img src="<?php echo $friend->getThumbAvatar();?>" class="inv_js_invitation-avatar">
																			<div class="inv_js_invitation_detail">
																					<div class="inv_js_invitation_name"><?php echo $friend->getDisplayName();?></div>
																					<div class="inv_js_invitation-check">
																								<input type="checkbox" id="inv_js_friends-<?php echo $friend->id?>" name="inv_js_friends[]" value="<?php echo $friend->id?>" onclick="joms.invitation.selectMember('#invitation-friend-<?php echo $friend->id?>');" class="thCheckbox contacts_check" checked/>
																								<label for="inv_js_friends-<?php echo $friend->id?>">Select</label>
																					</div>
																			</div>
																		</div>
																	</li>
													<?php
																}
														}
													?>
																</ul>
														</div>
														<input type="hidden" name="message" value="<?php echo JText::_('SEND_MESSAGE');?>" id="messgae"/>
														<div class="separator"></div>
														<div class="clearfix">&nbsp;</div>
														<h4 class="label"><?php echo JText::_('OPTIONAL_MESSAGE'); ?></h4>
														<div class="separator"></div>
														<div class="well">
															<textarea rows="3" id="inv_js_personal_message" name="inv_js_personal_message" wrap="soft" class="personal_message"><?php echo $this->invitex_params->get('invitex_default_message') ?></textarea>
														</div>
														</div>
														<div class="clearfix"></div>
														<div class="form-actions">
															<input class="btn btn-primary " type="button" name="inv_js_messgae_send" value="<?php echo JText::_('SEND_INV');?>" onclick="send_invitation();">
														</div>
															<?php echo JHtml::_( 'form.token' ); ?>
															<input type="hidden" name="option" value="com_invitex">
															<input type="hidden" name="task" value="sort_mail">
															<input type="hidden" name="rout" id="rout" value="inv_js_messaging">
											</fieldset>
								</form>
								<?php  }
								else
								{?>
										    <div class="alert alert-error">
   																	<?php echo JText::_('NO_FRIENDS');?>
   											</div>

							<?php } ?>
						</div>
        </div><!--tab-content -->
      </div><!--tabbable -->
     </div><!--black n white layout div-->
</div>
	<?php
	if(JFactory::getApplication()->input->get('invite_anywhere')!='1')
	{
		//Load Footer template
		echo $this->loadTemplate('footer');
	}
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
	 } ?>
