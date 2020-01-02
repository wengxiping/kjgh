<?php
/**
 * @package InviteX
 * @copyright Copyright (C) 2009 -2015 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */


defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
$document = JFactory::getDocument();
$document->addScript(JURI::base().'components/com_invitex/assets/js/invitex.js');

?>

	<script type="text/javascript">

	Joomla.submitbutton = function(task)
	{
		if(task=='types.save')
		{
			var f = document.adminForm;
			if (document.formvalidator.isValid(f))
			{
				Joomla.submitform(task, document.getElementById('adminForm'));
				return true;
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
				return false;
			}

		}
		else
		{
			document.location='index.php?option=com_invitex&view=types'
		}
	}

	jQuery(document).ready(function()
	{
		var characters= 140;
		var remaining_chars=characters-jQuery("#type_template_twitter").val().length;
		jQuery("#type_counter").append("You have  <strong>"+ remaining_chars+"</strong> characters remaining");

		jQuery("#type_template_twitter").keyup(function(){
			if(jQuery(this).val().length > characters){
		jQuery(this).val(jQuery(this).val().substr(0, characters));
			}

		var remaining = characters -  jQuery(this).val().length;
		jQuery("#type_counter").html("You have <strong>"+  remaining+"</strong> characters remaining");
		if(remaining <= 10)
		{
			jQuery("#type_counter").css("color","red");
		}
		else
		{
			jQuery("#type_counter").css("color","black");
		}
		});

	});

	</script>

<?php
		$task	= JFactory::getApplication()->input->get('task');
		$type='';
		if($task=='edit')
		{
				$type=$this->type_data;
		}

		$communityfolder = JPATH_SITE . '/components/com_community';
		$manual=$oi_email=$oi_social=$apis=$csv=$inv_by_url=$JS_messaging=$integrate_activity_stream_yes=$integrate_activity_stream_no="";
		$invite_methods='';
		$pre_slected_api=array();


		$avail_replacements="<table><tr><td  colspan='2'><h3>".JText::_('TAGS_LIST')."</h3> ".JText::_('TAGS_LIST_INFO')."<hr /></td></tr>
											<tr><td>[NAME]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_NAME_DESC') . "</td></tr>
											<tr><td>[INVITER_NAME]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_INVITER_NAME_DESC') . "</td></tr>
											<tr><td>[INVITER_UNAME]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_INVITER_UNAME_DESC') . "</td></tr>
											<tr><td>[INVITER_PROFILEURL]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_INVITER_PROFILEURL_DESC') . "</td></tr>
											<tr><td>[INVITETYPENAME]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_INVITETYPENAME_DESC') . "</td></tr>
											<tr><td>[MESSAGE] 	</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_MESSAGE_DESC') . "</td></tr>
											<tr><td>[AVATAR] 	</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_AVATAR_DESC') . "</td></tr>
											<tr><td>[JOIN] 	</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_JOIN_DESC') . "</td></tr>
											<tr><td>[SITENAME] 	</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_SITENAME_DESC') . "</td></tr>
											<tr><td>[SITELINK]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_SITELINK_DESC') . "</td></tr>
											<tr><td>[SUBSCRIBE] 	</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_SUBSCRIBE_DESC') . "</td></tr>
											<tr><td>[UNSUBSCRIBE]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_UNSUBSCRIBE_DESC') . "</td></tr>
											<tr><td>[PWIU]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_PWIU_DESC') . "</td></tr>
											<tr><td>[SITEURL]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_SITEURL_DESC') . "</td></tr>
											<tr><td>[EXPIRYDAYS]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_EXPIRYDAYS_DESC') . "</td></tr>
											<tr><td>[cbfield:cb_fieldname]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_CBFIELD_DESC') . "</td></tr>
											<tr><td>[jsfield:FIELD_CODE]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_JSFIELDS_DESC') . "</td></tr>
											<tr><td>[esfield:unique_key]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_ESFIELDS_DESC') . "</td></tr>
											</table>";

	  $yes_no_select = array(
				JHTML::_('select.option', '1', JText::_('COM_INVITEX_YES') ),
				JHTML::_('select.option', '0', JText::_('COM_INVITEX_NO') )
			);
?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">

 <form method="POST" name="adminForm" id='adminForm' class="form-validate" >
		<div class="tabbable">
		    <ul class="nav nav-tabs">
				<li class="active">	<a href="#type_specific_config" data-toggle="tab"><?php echo JText::_('TYPE_SETTING');?></a></li>
				<li><a href="#email_tmpl_config" data-toggle="tab"><?php echo JText::_('EMAIL');?></a></li>
				<li><a href="#pm_individual_config" data-toggle="tab"><?php echo JText::_('INV_PM_INDIVIDUAL');?></a></li>
<!-- Hidded for Facebook as currently we are using send dilog box hence no need to provide configurable template>
<!--
				<li><a href="#pm_individual_config" data-toggle="tab"><?php echo JText::_('INV_PM_FOR_MASS');?></a></li>
-->
				<li><a href="#twitter_config" data-toggle="tab"><?php echo JText::_('TWITTER');?></a></li>
<!--
				<li><a href="#fb_request_config" data-toggle="tab"><?php echo JText::_('FB_REQUEST_TMPL');?></a></li>
-->
				<li><a href="#sms_template" data-toggle="tab"><?php echo JText::_('INV_SMS_TEMPLATE');?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="type_specific_config">
						<div class="row-fluid">
							<div class="span12 form-horizontal">
								<fieldset>
						  		<legend><?php echo JText::_('COM_INVITEX_TYPE_SPEC_CONFIG');?></legend>
												<div class="control-group">
														<label class="control-label" for="type_name" id="configreg_direct-lbl" title="<?php echo JText::_('TYPE_NAME_DESC') ?>"><?php echo JText::_('TYPE_NAME') ?><span class="star">&nbsp;*</span></label>
													<div class="controls">
														<input type="text" name="type[name]" id="type_name" class="required" value="<?php echo ($type)? $type->name : '' ;?>">
														</div>
											 	</div>
												<div class="control-group">
														<label class="control-label" for="type_internal_name" id="configreg_direct-lbl" title="<?php echo JText::_('TYPE_INT_NAME_DESC') ?>"><?php echo JText::_('TYPE_INT_NAME') ?><span class="star">&nbsp;*</span></label>
													<div class="controls">
														<input type="text" name="type[internal_name]" class="required" id="type_internal_name" value="<?php echo ($type)? $type->internal_name : '' ;?>">
														</div>
											 	</div>
												<div class="control-group">
														<label class="control-label" id="description" title="<?php echo JText::_('TYPE_DESC_DESC') ?>"><?php echo JText::_('TYPE_DESC') ?></label>
													<div class="controls">
														<textarea class="inputbox " cols="60" rows= "10" name="type[description]"><?php echo ($type)? $type->description : '' ;?></textarea>
													</div>
											 	</div>
												<div class="control-group">
														<label class="control-label" id="personal_message" title="<?php echo JText::_('COM_INVITEX_TYPE_PERSONAL_MESSAGE_DESC') ?>"><?php echo JText::_('COM_INVITEX_TYPE_PERSONAL_MESSAGE') ?></label>
													<div class="controls">
														<textarea class="inputbox " cols="60" rows= "10" name="type[personal_message]"><?php echo ($type)? $type->personal_message : '' ;?></textarea>
													</div>
											 	</div>
											<?php 	if($type){?>
												<div class="control-group">
														<label class="control-label" for="configreg_direct" id="configreg_direct-lbl" title="<?php echo JText::_('TYPE_WIDGET_DESC') ?>"><?php echo JText::_('TYPE_WIDGET') ?></label>
													<div class="controls">
														<textarea class="inputbox" cols="60" rows= "5" name="type[widget]"><?php echo ($type) ? stripslashes($type->widget) : '' ;?></textarea>
													</div>
											 	</div>
									<?php }?>
												<div class="control-group">
														<label class="control-label" for="configreg_direct" id="configreg_direct-lbl" title="<?php echo JText::_('TYPE_CAT_ACT_DESC') ?>"><?php echo JText::_('TYPE_CAT_ACT') ?></label>
													<div class="controls">
														<textarea class="inputbox" cols="60" rows= "5" name="type[catch_action]" ><?php echo ($type)? stripslashes($type->catch_action) : '' ;?></textarea>
													</div>
											 	</div>
								</fieldset>

								<fieldset class="form-horizontal">
						  		<legend><?php echo JText::_('TYPE_INV_METHOD_SET')?></legend>
												<div class="control-group">
														<label class="control-label" for="typeinvite_methods" id="configreg_direct-lbl" title="<?php echo JText::_('ALLOWED_INVITE_METHODS_DESC') ?>"><?php echo JText::_('ALLOWED_INVITE_METHODS') ?></label>
													<div class="controls">
														<?php echo $this->provider_methods_multiselect;?>
															<?php if( JVERSION<3.0)
															{?>
															<div class="input-append">
																<button type="button" class="btn" onclick="moveUpItem('typeinvite_methods')"><img class="invitex_image" src="<?php echo JURI::root().'components/com_invitex/assets/images/back/move_up.png';?>" /><?php echo JText::_('COM_INVITEX_MOVE_UP');?></button>
																<button type="button" class="btn" onclick="moveDownItem('typeinvite_methods')"><img class="invitex_image" src="<?php echo JURI::root().'components/com_invitex/assets/images/back/move_down.png';?>" /><?php echo JText::_('COM_INVITEX_MOVE_DOWN');?></button>
															</div>
															<?php
															}?>
											 		</div>
												</div>
												<div class="control-group">
														<label class="control-label invitex-max-width-160" for="typeinvite_apis" id="configreg_direct-lbl" title="<?php echo JText::_('SELECT_API_DESC') ?>"><?php echo JText::_('SELECT_API') ?></label>
													<div class="controls">
														<?php
																$apiselect = array();
																if($type && $type->invite_apis)
											 						 $pre_slected_api=explode(',',$type->invite_apis );
																foreach($this->apiplugin as $api)
																{
																	$apiname = ucfirst(str_replace('plug_techjoomlaAPI_', '',$api->element));
																	$apiselect[] = JHTML::_('select.option',$api->element, $apiname);
																}
																if(!empty($apiselect))
																	echo JHTML::_('select.genericlist', $apiselect, "type[invite_apis][]", 'class="" multiple size="6" id="typeinvite_methods" ', "value", "text", $pre_slected_api);
																else
																	echo "<b>No Techjoomla API plugin is enabled..</b>"
																?>
														</div>
											 	</div>
										</fieldset>
										<?php	if(JFolder::exists($communityfolder)) { ?>
										<fieldset class="form-horizontal">
												<legend><?php echo JText::_('ACTIVITY_SET');?></legend>
													<div class="control-group">
												 		<label class="control-label" for="configreg_direct" id="configreg_direct-lbl" title="<?php echo JText::_('INTEGRATE_JS_ACTIVITY_STREAM_DESC') ?>"><?php echo JText::_('INTEGRATE_JS_ACTIVITY_STREAM') ?></label>
														<div class="controls">
															<?php
																$default = "";

																if (isset($type->integrate_activity_stream))
																{
																	$default = $type->integrate_activity_stream;

																}

																 echo JHTML::_('select.genericlist', $yes_no_select, 'type[integrate_activity_stream]', null, 'value', 'text', $default );
														 	?>
															</div>
													</div>
										 </fieldset>
						<?php } ?>
							</div><!--span12 -->
						</div><!--row-fluid -->
				</div><!-- tab-pane:type_specific_config -->

				<div class="tab-pane form-vertical" id="email_tmpl_config">
					<div class="row-fluid invitex_templates">
							<div class="span6">
										<div class="control-group">
											<label class="control-label" for="message_subject" id="message_subject-lbl" title="<?php echo JText::_('E_SUBJECT_DES') ?>"><?php echo JText::_('E_SUBJECT') ?></label>
											<div class="controls">
												 <input type="text" size="50" name="type[template_html_subject]" value="<?php echo ($type)? $type->template_html_subject : '';?>" id="template_html_subject" />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" title="<?php echo JText::_('E_BODY_DESC') ?>"><?php echo JText::_('E_BODY') ?></label>
											<div class="controls"></div>
										</div>
										<div class="control-group">
											<label class="control-label"></label>
											<div class="controls">
												<?php
													$editor      = JFactory::getEditor();
													echo $editor->display("type[template_html]",($type)? stripslashes($type->template_html) : '',600,400,40,20,true);
											?>
											</div>
										</div>
								</div><!--div span-template -->
								<div class="span6">
											<?php echo $avail_replacements;?>
								</div><!--div span-tags -->
					</div><!--row-fluid -->
				</div><!-- tab-pane:email_tmpl_config -->

				<div class="tab-pane form-vertical" id="pm_individual_config">
					<div class="row-fluid invitex_templates">
							<div class="span6">
										<div class="control-group">
											<label class="control-label" for="message_subject" id="message_subject-lbl"><?php echo JText::_('PM_SUBJECT') ?></label>
											<div class="controls">
												<input type="text" class="inputbox" size="70" name="type[template_text_subject]" value="<?php echo ($type)? $type->template_text_subject : '';?>" />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label"><?php echo JText::_('PM_BODY') ?></label>
											<div class="controls"></div>
										</div>
										<div class="control-group">
											<label class="control-label"></label>
											<div class="controls">
												<textarea class="text_template" cols="60" rows="20" name="type[template_text]" ><?php echo ($type)? stripslashes($type->template_text) : '';?></textarea>
											</div>
										</div>
								</div><!--div span-template -->
								<div class="span6">
											<?php echo $avail_replacements;?>
								</div><!--div span-tags -->
					</div><!--row-fluid -->
				</div><!-- tab-pane:pm_individual_config -->

				<div class="tab-pane form-vertical" id="pm_mass_config">
					<div class="row-fluid invitex_templates">
							<div class="span6">
										<div class="control-group">
											<label class="control-label" for="message_subject" id="message_subject-lbl"><?php echo JText::_('PM_SUBJECT') ?></label>
											<div class="controls">
												<input type="text" class="inputbox" size="70" name="type[common_template_text_subject]" value="<?php echo ($type)? $type->common_template_text_subject : '';?>" />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label"><?php echo JText::_('PM_BODY') ?></label>
											<div class="controls"></div>
										</div>
										<div class="control-group">
											<label class="control-label"></label>
											<div class="controls">
												<textarea class="text_template" cols="60" rows="20" name="type[common_template_text]" ><?php echo ($type)? stripslashes($type->common_template_text) : '';?></textarea>
											</div>
										</div>
								</div><!--div span-template -->
								<div class="span6">
											<?php echo $avail_replacements;?>
								</div><!--div span-tags -->
					</div><!--row-fluid -->
				</div><!-- tab-pane:pm_mass_config -->

				<div class="tab-pane form-vertical" id="twitter_config">
					<div class="row-fluid invitex_templates">
							<div class="span6">
										<div class="control-group">
											<label class="control-label"></label>
											<div class="controls">
												<textarea class="text_template" cols="60" rows="5" name="type[template_twitter]" id="type[template_twitter]" id="twitter_text"><?php echo ($type)? stripslashes($type->template_twitter) : '';?></textarea>
											</div>
										</div>
								</div><!--div span-template -->
								<div class="span6">
											<?php echo $avail_replacements;?>
								</div><!--div span-tags -->
					</div><!--row-fluid -->
				</div><!-- tab-pane:twitter_config -->

				<div class="tab-pane form-vertical" id="fb_request_config">
					<div class="row-fluid invitex_templates">
							<div class="span6">
										<div class="control-group">
											<label class="control-label"></label>
											<div class="controls">
												<?php echo $editor->display("type[template_fb_request]",($type)? stripslashes($type->template_fb_request) : '',600,400,40,20,true);	?>
											</div>
										</div>
								</div><!--div span-template -->
								<div class="span6">
									<?php echo $avail_replacements;?>
								</div><!--div span-tags -->
					</div><!--row-fluid -->
				</div><!-- tab-pane:fb_config -->

				<div class="tab-pane form-vertical" id="sms_template">
					<div class="row-fluid invitex_templates">
							<div class="span6">
										<div class="control-group">
											<label class="control-label"></label>
											<div class="controls">
												<textarea class="text_template" cols="60" rows="5" name="type[template_sms]" id="type[template_sms]" id="template_sms"><?php echo ($type)? stripslashes($type->template_sms) : '';?></textarea>
											</div>
										</div>
								</div><!--div span-template -->
								<div class="span6">
											<?php echo $avail_replacements;?>
								</div><!--div span-tags -->
					</div><!--row-fluid -->
				</div><!-- tab-pane:twitter_config -->

			</div><!-- tab-content -->
		</div><!-- tabbable -->

		<input type="hidden" name="option" value="com_invitex" />
		<input type="hidden" name="task" value="save" />
		<input type="hidden" name="controller" value="types" />
		<input type="hidden" name="edit" value="<?php echo ($type)? stripslashes($type->id) : '';?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>


