<?php
/**
 * @package InviteX
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');

require_once(JPATH_SITE . "/components/com_invitex/models/emogrifier.php");
?>
<script type="text/javascript">
jQuery(document).ready(function() {
		var characters= 140;
		var remaining_chars=characters-jQuery("#twitter_message_body").val().length;
		jQuery("#counter").append("You have  <strong>"+ remaining_chars+"</strong> characters remaining");

		jQuery("#twitter_message_body").keyup(function(){
    		if(jQuery(this).val().length > characters){
        jQuery(this).val(jQuery(this).val().substr(0, characters));
			}

		var remaining = characters -  jQuery(this).val().length;
    jQuery("#counter").html("You have <strong>"+  remaining+"</strong> characters remaining");
    if(remaining <= 10)
    {
        jQuery("#counter").css("color","red");
    }
    else
    {
        jQuery("#counter").css("color","black");
    }


	});
});

</script>
<?php
		$cssdata='';
		$cssfile=JPATH_SITE."/media/com_invitex/css/invitex_mail.css";
		$cssdata = JFile::read($cssfile);

		$avail_replacements="<table><tr><td  colspan='2'><h3>".JText::_('TAGS_LIST')."</h3> ".JText::_('TAGS_LIST_INFO')."<hr /></td></tr>
											<tr><td>[NAME]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_NAME_DESC') . "</td></tr>
											<tr><td>[INVITER_NAME]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_INVITER_NAME_DESC') . "</td></tr>
											<tr><td>[INVITER_UNAME]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_INVITER_UNAME_DESC') . "</td></tr>
											<tr><td>[INVITER_PROFILEURL]</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_INVITER_PROFILEURL_DESC') . "</td></tr>
											<tr><td>[MESSAGE] 	</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_MESSAGE_DESC') . "</td></tr>
											<tr><td>[AVATAR] 	</td><td>" . JText::_('COM_INVITEX_TEMPLATES_TAGS_TEMPLATES_AVATAR_DESC') . "</td></tr>
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
		?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
		<form method='POST' name='adminForm' id='adminForm' action='' class="invitex_templates">

			<div class="row-fluid">
						<div class="alert alert-info">
								 <?php echo JText::_('LANGUAGE_STRINGS_NOTE');?>
						</div>
					</div>
			<div class="tabbable">
				<ul class="nav nav-tabs">
					<li class="active">
						 <a href="#html_template" data-toggle="tab"><?php echo JText::_('EMAIL');?></a>
					</li>
					<li><a href="#individual_pm_template" data-toggle="tab"><?php echo JText::_('INV_PM_INDIVIDUAL');?></a></li>
<!-- Hidded for Facebook as currently we are using send dilog box hence no need to provide configurable template>
<!--
					<li><a href="#mass_pm_template" data-toggle="tab"><?php echo JText::_('INV_PM_FOR_MASS');?></a></li>
-->
						<li><a href="#twitter_template" data-toggle="tab"><?php echo JText::_('TWITTER');?></a></li>
<!--
						<li><a href="#fb_request_template" data-toggle="tab"><?php echo JText::_('FB_REQUEST_TMPL');?></a></li>
-->
						<li><a href="#sms_template" data-toggle="tab"><?php echo JText::_('INV_SMS_TEMPLATE');?></a></li>
						<li><a href="#reminder_template" data-toggle="tab"><?php echo JText::_('REMINDER_TEMPLATE');?></a></li>
						<li><a href="#intelligent_template" data-toggle="tab"><?php echo JText::_('FRIENDS_ON_SITE_TEMPLATE');?></a></li>
				</ul>


			<div class="tab-content">
				<div class="tab-pane active" id="html_template">



					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<label class="control-label" for="message_subject" id="message_subject-lbl" title="<?php echo JText::_('E_SUBJECT_DES') ?>"><?php echo JText::_('E_SUBJECT') ?></label>
								<div class="controls">
									<input type="text" size="50" name="config[message_subject]" value="<?php echo $this->invitex_params->get('message_subject') ?>" id="message_subject" />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" title="<?php echo JText::_('E_BODY_DESC') ?>"><?php echo JText::_('E_BODY') ?></label>
								<div class="controls">
								</div>
							</div>
							<div class="control-group">
								<div class="controls">
									<?php
										// Code to Read CSS File
										// Condition to check if mbstring is enabled
										if (!function_exists('mb_convert_encoding'))
										{
											echo JText::_("MB_EXT");
											$emorgdata = $this->invitex_params->get('message_body');
										}
										else
										{
											$emogr = new Emogrifier($this->invitex_params->get('message_body'), $cssdata);
											$emorgdata = $emogr->emogrify();
										}

										$editor = JFactory::getEditor();
										echo $editor->display("config[message_body]", stripslashes($emorgdata), 600, 400, 40, 20, true, null, null, null, array("newlines" => "1"));
									?>
								</div>
							</div>
						</div><!--div span 6 -->

						<div class="span6">
							<?php echo $avail_replacements;?>
							<?php
							// This code echoes the plugin 'tags' on the right side of the config
							if (count($this->email_alert_plugin_names))
							{
								echo "<hr class='hr hr-condensed'/>";
								// echo "<p class='text text-info small'>" . JText::_('COM_INVITEX_FORM_LEGEND_JMA_PLUGINS') . "</p>";

								// This code echoes the plugin 'tags' on the right side of the config
								// Set index to 0
								$i = 0;

								foreach ($this->email_alert_plugin_names as $email_alert_plugin_name)
								{
									echo '[' . $email_alert_plugin_name . ']
										<p class="small">' . strip_tags($this->plugin_description_array[$i++]) . '</p>';
								}

								echo "<hr class='hr hr-condensed'/>";
							}
							?>
					</div><!--div span 3 -->
				</div><!--div row -->
			</div><!--tabpane: html template -->

			<div class="tab-pane" id="individual_pm_template">
				<div class="row-fluid">
					<div class="span6">

								 <div class="control-group">
										<label class="control-label" for="pm_message_body_sub" id="pm_message_body_sub-lbl" title="<?php echo JText::_('E_SUBJECT_DES') ?>"><?php echo JText::_('PM_SUBJECT') ?></label>
										<div class="controls">
											<input type="text" class="inputbox" size="70" name="config[pm_message_body_sub]" id="pm_message_body_sub" value="<?php echo stripslashes($this->invitex_params->get('pm_message_body_sub')) ?>" />
										</div>
									</div>
									<div class="control-group">
										<label class="control-label"><?php echo JText::_('PM_BODY') ?></label>
										<div class="controls">
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<textarea class="text_template" rows="20"  name="config[pm_message_body]" value=""><?php echo stripslashes($this->invitex_params->get('pm_message_body')) ?></textarea>
										</div>
									</div>


					</div><!--div span 6 -->
					<div class="span6">
								<?php echo $avail_replacements;?>
					</div><!--div span 3 -->
				</div><!--div row -->
			</div><!--tabpane: individual_pm_template -->

		<div class="tab-pane" id="mass_pm_template">
			<div class="row-fluid">
				<div class="span6">

								<div class="control-group">
										<div class="alert alert-info">
										<?php echo JText::_('PM_NO_REPLACE_NOTE') ?>
									</div>
								</div>
							 <div class="control-group">
									<label class="control-label" for="pm_message_body_no_replace_sub" id="pm_message_body_no_replace_sub-lbl" title="<?php echo JText::_('E_SUBJECT_DES') ?>"><?php echo JText::_('PM_SUBJECT') ?></label>
									<div class="controls">
										<input type="text" class="inputbox" size="70" id="pm_message_body_no_replace_sub" name="config[pm_message_body_no_replace_sub]" value="<?php echo stripslashes($this->invitex_params->get('pm_message_body_no_replace_sub')) ?>" />
									</div>
								</div>
								<div class="control-group">
									<label class="control-label"><?php echo JText::_('PM_BODY') ?></label>
									<div class="controls">
									</div>
								</div>
								<div class="control-group">
									<div class="controls">
										<textarea class="text_template" rows="20"  name="config[pm_message_body_no_replace]" value=""><?php echo stripslashes($this->invitex_params->get('pm_message_body_no_replace')) ?></textarea>
									</div>
								</div>

				</div><!--div span 6 -->
				<div class="span6">
							<?php echo $avail_replacements;?>
				</div><!--div span 3 -->
			</div><!--div row -->
		</div><!--tabpane: mass_pm_template -->

		<div class="tab-pane" id="twitter_template">
			<div class="row-fluid">
				<div class="span6">

								<div class="control-group">
									<label class="control-label"></label>
									<div class="controls">
											<div  id="counter"></div>
											<textarea class="text_template" rows="5"  id="twitter_message_body" name="config[twitter_message_body]" value=""><?php echo stripslashes($this->invitex_params->get('twitter_message_body')); ?></textarea>
									</div>
								</div>
								<div class="text-warning"><?php echo JText::_('COM_INVITEX_TWITTER_MESSAGE_LIMIT_NOTE');?></div>

				</div><!--div span 6 -->
				<div class="span6">
							<?php echo $avail_replacements;?>
				</div><!--div span 3 -->
			</div><!--div row -->
		</div><!--tabpane: twitter_template -->

		<div class="tab-pane" id="fb_request_template">
			<div class="row-fluid">
				<div class="span6">

								<div class="control-group">
									<div class="controls">
										<?php echo $editor->display("config[fb_request_body]",stripslashes($this->invitex_params->get('fb_request_body')),600,400,40,20,true);	?>
									</div>
								</div>

				</div><!--div span 6 -->
				<div class="span6">
							<?php echo $avail_replacements;?>
				</div><!--div span 3 -->
			</div><!--div row -->
		</div><!--tabpane: fb_request_template -->
		<!--SMS template-->
		<div class="tab-pane" id="sms_template">
			<div class="row-fluid">
				<div class="span6">

								<div class="control-group">
									<label class="control-label"></label>
									<div class="controls">
											<div  id="counter"></div>
											<textarea class="text_template" rows="5"  id="sms_message_body" name="config[sms_message_body]" value=""><?php echo stripslashes($this->invitex_params->get('sms_message_body')); ?></textarea>
									</div>
								</div>


				</div><!--div span 6 -->
				<div class="span6">
							<?php echo $avail_replacements;?>
				</div><!--div span 3 -->
			</div><!--div row -->
		</div>
		<!--tabpane: sms_template -->

			<div class="tab-pane" id="reminder_template">
			<div class="row-fluid">
				<div class="span6">

							 <div class="control-group">
									<label class="control-label" for="reminder_subject" id="reminder_subject-lbl" title="<?php echo JText::_('E_SUBJECT_DES') ?>"><?php echo JText::_('R_SUBJECT') ?></label>
									<div class="controls">
										<input type="text" size="50" name="config[reminder_subject]" id="reminder_subject" value="<?php echo $this->invitex_params->get('reminder_subject') ?>" id="reminder_subject" />
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" title="<?php echo JText::_('R_BODY_DESC') ?>"><?php echo JText::_('R_BODY') ?></label>
									<div class="controls">
									</div>
								</div>
								<div class="control-group">
									<div class="controls">
										<?php
												//Code to Read CSS File
													if(!function_exists('mb_convert_encoding'))		// condition to check if mbstring is enabled
													{
															echo JText::_("MB_EXT");
															$emorgdata	=	$this->invitex_params->get('reminder_body');
													}
													else
													{
															$emogr=new Emogrifier($this->invitex_params->get('reminder_body'),$cssdata);
															$emorgdata=$emogr->emogrify();
													}
													$editor      = JFactory::getEditor();
													echo $editor->display("config[reminder_body]",stripslashes($emorgdata),600,400,40,20,true, null, null, null, array("newlines" => "1"));
											?>
									</div>
								</div>


				</div><!--div span 6 -->
				<div class="span6">
					<?php echo $avail_replacements;?>
					<?php
					// This code echoes the plugin 'tags' on the right side of the config
					if (count($this->email_alert_plugin_names))
					{
						echo "<hr class='hr hr-condensed'/>";
						// echo "<p class='text text-info small'>" . JText::_('COM_INVITEX_FORM_LEGEND_JMA_PLUGINS') . "</p>";

						// This code echoes the plugin 'tags' on the right side of the config
						// Set index to 0
						$i = 0;

						foreach ($this->email_alert_plugin_names as $email_alert_plugin_name)
						{
							echo '[' . $email_alert_plugin_name . ']
								<p class="small">' . strip_tags($this->plugin_description_array[$i++]) . '</p>';
						}

						echo "<hr class='hr hr-condensed'/>";
					}
					?>
				</div><!--div span 3 -->
			</div><!--div row -->
		</div><!--tabpane:reminder_template -->

	  <div class="tab-pane" id="intelligent_template">
			<div class="row-fluid">
				<div class="span6">

							 <div class="control-group">
									<label class="control-label" for="friendsonsite_subject" id="friendsonsite_subject-lbl" title="<?php echo JText::_('E_SUBJECT_DES') ?>"><?php echo JText::_('COM_INVITEX_SUBJECT') ?></label>
									<div class="controls">
										<input type="text" size="50" name="config[friendsonsite_subject]" id="friendsonsite_subject" value="<?php echo $this->invitex_params->get('friendsonsite_subject') ?>" id="friendsonsite_subject" />
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" title="<?php echo JText::_('COM_INVITEX_BODY_DESC') ?>"><?php echo JText::_('COM_INVITEX_BODY') ?></label>
									<div class="controls">
									</div>
								</div>
								<div class="control-group">
									<div class="controls">
										<?php
												//Code to Read CSS File
													if(!function_exists('mb_convert_encoding'))		// condition to check if mbstring is enabled
													{
															echo JText::_("MB_EXT");
															$emorgdata	=	$this->invitex_params->get('friendsonsite_body');
													}
													else
													{
															$emogr=new Emogrifier($this->invitex_params->get('friendsonsite_body'),$cssdata);
															$emorgdata=$emogr->emogrify();
													}
													$editor      = JFactory::getEditor();
													echo $editor->display("config[friendsonsite_body]",stripslashes($emorgdata),600,400,40,20,true, null, null, null, array("newlines" => "1"));
											?>
									</div>
								</div>

				</div><!--div span 6 -->
				<div class="span6">
					<table>
						<tr><td  colspan='2'><h3><?php echo JText::_('TAGS_LIST');?></h3><?php echo JText::_('TAGS_LIST_INFO') ?><hr /></td></tr>
						<tr><td>[NAME]</td><td><?php echo JText::_('COM_INVITEX_TEMPLATES_TAGS_NAME_DESC');?></td></tr>
						<tr><td>[NUMBEROFFRIENDS]	</td><td>	<?php echo JText::_('COM_INVITEX_TEMPLATES_TAGS_PWIU_AUTO_DESC'); ?></td></tr>
						<tr><td>[FRINEDSONSITE] 	</td><td> <?php echo JText::_('COM_INVITEX_TEMPLATES_TAGS_FRIENDS_ON_SITE_DESC'); ?></td></tr>
						<tr><td>[SITENAME] 	</td><td><?php echo JText::_('COM_INVITEX_TEMPLATES_TAGS_SITENAME_DESC'); ?></td></tr>
						<tr><td>[UNSUBSCRIBE]</td><td><?php echo JText::_('COM_INVITEX_TEMPLATES_TAGS_UNSUBSCRIBE_DESC'); ?></td></tr>
					</table>
					<?php
					// This code echoes the plugin 'tags' on the right side of the config
					if (count($this->email_alert_plugin_names))
					{
						echo "<hr class='hr hr-condensed'/>";
						// echo "<p class='text text-info small'>" . JText::_('COM_INVITEX_FORM_LEGEND_JMA_PLUGINS') . "</p>";

						// This code echoes the plugin 'tags' on the right side of the config
						// Set index to 0
						$i = 0;

						foreach ($this->email_alert_plugin_names as $email_alert_plugin_name)
						{
							echo '[' . $email_alert_plugin_name . ']
								<p class="small">' . strip_tags($this->plugin_description_array[$i++]) . '</p>';
						}

						echo "<hr class='hr hr-condensed'/>";
					}
					?>
				</div><!--div span 3 -->
			</div><!--div row -->
		</div><!--tabpane:intelligent_template -->

	</div><!--tabcontent -->

</div><!-- div tabbale -->
		<input type="hidden" name="option" value="com_invitex" />
		<input type="hidden" name="view" value="config" />
		<input type="hidden" name="layout" value="templates" />
		<input type="hidden" name="task" value="config.save" />
    <input type="hidden" name="boxchecked" value="" />

		<?php echo JHTML::_( 'form.token' ); ?>

		</form>
</div><!-- div <?php echo INVITEX_WRAPPER_CLASS;?> -->

