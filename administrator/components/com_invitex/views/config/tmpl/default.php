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

	$document=JFactory::getDocument();
	if(JVERSION >= 3.0)
	{
		$menuCssOverrideJs="techjoomla.jQuery(document).ready(function(){
			techjoomla.jQuery('ul>li> a[href=\"index.php?option=com_invitex&view=config&layout=templates\"]:last').parent().removeClass('active');
		});";
	}
	else
	{
		$menuCssOverrideJs="techjoomla.jQuery(document).ready(function(){
		techjoomla.jQuery('ul>li> a[href$=\"index.php?option=com_invitex&view=config&layout=templates\"]:last').removeClass('active');
	});";
	}

	$document->addScriptDeclaration($menuCssOverrideJs);

	$communityfolder = JPATH_SITE . '/components/com_community';
	$cbfolder = JPATH_SITE . '/components/com_comprofiler';
	$esfolder = JPATH_SITE . '/components/com_easysocial';
	$jwfolder = JPATH_SITE . '/components/com_awdwall';
	$altafolder =JPATH_SITE . '/components/com_altauserpoints';
	$vmfolder=JPATH_SITE . '/components/com_virtuemart';
	$payplansfolder=JPATH_SITE . '/components/com_payplans';
	$yes_no_select = array(
			JHTML::_('select.option', '1', JText::_('Yes') ),
			JHTML::_('select.option', '0', JText::_('No') )
		);
	$activity_stream = array(
			JHTML::_('select.option', '-1', JText::_('SELECT') ),
			JHTML::_('select.option', '1', JText::_('TOTAL_NUM_INVITED_BY_USER') ),
			JHTML::_('select.option', '0', JText::_('USER_FOUND_NEW_FRIENDS') )
		);

	$login_method=array(
			JHTML::_('select.option', '0', JText::_('EVERY_LOGIN') ),
			JHTML::_('select.option', '1', JText::_('1ST LOGIN') )
		);
?>

<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<form method='POST' name='adminForm' id='adminForm' action='' class="invitex_settings">
		<?php
			// JHtmlsidebar for menu.
			if (JVERSION >= '3.0'):
				if (!empty( $this->sidebar)) : ?>
					<div id="j-sidebar-container" class="span2">
						<?php echo $this->sidebar; ?>
					</div>
					<div id="j-main-container" class="span10">
				<?php else : ?>
					<div id="j-main-container">
				<?php endif;
			endif;
		?>


		<div class="tabbable">
		    <ul class="nav nav-tabs">
    			<li class="active">
   					 <a href="#global_config" data-toggle="tab"><?php echo JText::_('GLOBAL_SET');?></a>
    			</li>
    			<li><a href="#reg_spec_config" data-toggle="tab"><?php echo JText::_('REG_SPEC');?></a></li>
    			<li><a href="#oi_config" data-toggle="tab"><?php echo JText::_('OI_CONFIG');?></a></li>
				<li><a href="#point_set" data-toggle="tab"><?php echo JText::_('POINT_SET');?></a></li>
				<li><a href="#notification" data-toggle="tab"><?php echo JText::_('INV_CONFIG_NOTIFICATION');?></a></li>
   		 </ul>

		<div class="tab-content">
			<div class="tab-pane active" id="global_config">
				<div class="row-fluid">
					<div class="span12">
					<fieldset class="form-horizontal">
							<legend><?php echo JText::_('GEN_SET');?></legend>
								<div class="control-group">
											<label class="control-label" for="configreg_direct" id="configreg_direct-lbl" title="<?php echo JText::_('REG_DIR_DESC') ?>"><?php echo JText::_('REG_DIR') ?></label>
											<div class="controls">
												<?php
												$arr=array();
												$arr[] = JHTML::_('select.option', 'Joomla', JText::_('INV_JOOMLA') );
												if(JFolder::exists($communityfolder))
													$arr[] =	JHTML::_('select.option', 'JomSocial', JText::_('INV_JS') );
												if(JFolder::exists($cbfolder))
													$arr[] = JHTML::_('select.option', 'Community Builder', JText::_('INV_CB') );
												if(JFolder::exists($jwfolder))
													$arr[] = JHTML::_('select.option', 'Jomwall', JText::_('INV_JW') );
												if(JFolder::exists($esfolder))
													$arr[] = JHTML::_('select.option', 'EasySocial', JText::_('INV_ES') );


												echo JHTML::_('select.genericlist', $arr, 'config[reg_direct]', null, 'value', 'text', $this->invitex_params['reg_direct']);?>
											</div>
								</div>

								<div class="control-group">
										<label class="control-label" for="configurlapi" id="configurlapi-lbl" title="<?php echo JText::_('ENABLE_URL_API_DES') ?>"><?php echo JText::_('ENABLE_URL_API') ?></label>
										<div class="controls">
											<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[urlapi]', null, 'value', 'text', $this->invitex_params['urlapi']);
											?>
										</div>
								</div>


								<div class="control-group">
												<label class="control-label" for="url_apikey" id="configurl_apikey-lbl" title="<?php echo JText::_('URL_API_DES') ?>"><?php echo JText::_('URL_API')?></label>
										<div class="controls">
											<input type="text" value="<?php echo $this->invitex_params['url_apikey'] ?>" name="config[url_apikey]" id="url_apikey">
										</div>
								</div>
								<div class="control-group">
											<label class="control-label" for="url_apikey" id="configurl_apikey-lbl" title="<?php echo JText::_('SEND_FRIENDS_ON_SITE_TYPE_INVITAION_DES') ?>"><?php echo JText::_('SEND_FRIENDS_ON_SITE_TYPE_INVITAION')?></label>
										<div class="controls">
										<?php
											 echo JHTML::_('select.genericlist', $yes_no_select, 'config[store_contact]', null, 'value', 'text', $this->invitex_params['store_contact']);
										 ?>
										</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="invitex_default_message" id="invitex_default_message-lbl" title="<?php echo JText::_('OPTIONAL_MESSAGE_DES') ?>"><?php echo JText::_('OPTIONAL_MESSAGE')?></label>
									<div class="controls">
										<textarea rows="5" id="invitex_default_message"  cols="50" name="config[invitex_default_message]" wrap="soft" class="inputbox"><?php echo stripslashes($this->invitex_params['invitex_default_message']) ?></textarea>
									</div>
								</div>
								<div class="control-group">
										<label class="control-label" for="configaloadjQuery" id="configaloadjQuery-lbl" title="<?php echo JText::_('COM_INVITEX_LOAD_JQUERY_DES') ?>"><?php echo JText::_('COM_INVITEX_LOAD_JQUERY')?></label>
										<div class="controls">
											<?php
											 echo JHTML::_('select.genericlist', $yes_no_select, 'config[load_jquery]', null, 'value', 'text', $this->invitex_params['load_jquery']);
											?>
										</div>
								</div>
								<div class="control-group">
										<label class="control-label" for="configaloadbootstrap" id="configaloadbootstrap-lbl" title="<?php echo JText::_('COM_INVITEX_LOAD_BOOTSTRAP_DES') ?>"><?php echo JText::_('COM_INVITEX_LOAD_BOOTSTRAP')?></label>
										<div class="controls">
											<?php
											 echo JHTML::_('select.genericlist', $yes_no_select, 'config[load_bootstrap]', null, 'value', 'text', $this->invitex_params['load_bootstrap']);
										 ?>
										</div>
								</div>
								<div class="control-group">
										<label class="control-label" for="configamanual_emailtags" id="configamanual_emailtags-lbl" title="<?php echo JText::_('COM_INVITEX_MANUAL_EMAIL_TAGS') ?>"><?php echo JText::_('COM_INVITEX_MANUAL_EMAIL_TAGS')?></label>
										<div class="controls">
											<?php
											 echo JHTML::_('select.genericlist', $yes_no_select, 'config[manual_emailtags]', null, 'value', 'text', $this->invitex_params['manual_emailtags']);
										 ?>
										</div>
								</div>
					</fieldset>



					<fieldset class="form-horizontal">
						  <legend><?php echo JText::_('INV_LOGIN_SETTINGS');?></legend>
								<div class="control-group" >
										<label class="control-label" for="invite_after_login" id="invite_after_login-lbl" title="<?php echo JText::_('INV_INVITE_AFTER_LOGIN_DESC') ?>"><?php echo JText::_('INV_INVITE_AFTER_LOGIN') ?></label>
										<div class="controls">
											<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[invite_after_login]', 'onclick="show_invite_after_login_div(this.value)" class="alow_invite_after_login"', 'value', 'text', $this->invitex_params['invite_after_login']);
											 ?>
										</div>
								</div>

								<div class="control-group invite_after_login_div" style="display:none">
										<label class="control-label" for="select_mothod_for_invite" id="invite_after_login-lbl" title="<?php echo JText::_('INV_SELECT_MOTHOD_FOR_INVITE_DESC') ?>"><?php echo JText::_('INV_SELECT_MOTHOD_FOR_INVITE') ?></label>
										<div class="controls">
											<?php
												 echo JHTML::_('select.radiolist', $login_method, 'config[select_mothod_for_invite]', null, 'value', 'text', $this->invitex_params['select_mothod_for_invite']);
											 ?>
										</div>
								</div>


								<div class="control-group invite_after_login_div"  style="display:none">
										<label class="control-label" for="redirect_url_after_login" id="redirect_url_after_login-lbl" title="<?php echo JText::_('INV_REDIRECTURL_AFTER_LOGIN_DESC') ?>"><?php echo JText::_('INV_REDIRECTURL_AFTER_LOGIN') ?></label>
										<div class="controls">
											<input type="text" value="<?php echo $this->invitex_params['redirect_url_after_login'] ?>" name="config[redirect_url_after_login]" id="edirect_url_after_login"/>
										</div>
								</div>
					</fieldset>




							<fieldset class="form-horizontal">
						  <legend><?php echo JText::_('DOMAIN_VAL');?></legend>
						     <div class="control-group">
											<label class="control-label" for="configallow_domain_validation" id="configallow_domain_validation-lbl" title="<?php echo JText::_('ALLOW_DOMAIN_VALIDATION_DES') ?>"><?php echo JText::_('ALLOW_DOMAIN_VALIDATION')?></label>
											<div class="controls">
												<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[allow_domain_validation]', null, 'value', 'text', $this->invitex_params['allow_domain_validation']);
											 ?>
										</div>
								</div>

								 <div class="control-group">
											<label class="control-label" for="configinvite_domains" id="configinvite_domains-lbl" title="<?php echo JText::_('ALLOWED_DOMAINS_DES') ?>"><?php echo JText::_('ALLOWED_DOMAINS')?></label>
									<div class="controls">
											Enter domain names seperated by coma(,) e.g. gmail.com, yahoo.com <br />
											<textarea rows="5" id="invite_domains"  cols="50" name="config[invite_domains]" wrap="soft" class="inputbox"><?php print_r(implode(',',$this->allowedDomains));?></textarea>
										</div>
								</div>
						<div class="control-group">
										<label class="control-label" for="configinclude_site_domain" id="configinclude_site_domain-lbl" title="<?php echo JText::_('INCLUDE_SITE_DOMAIN_FOR_VALIDATION_DES') ?>"><?php echo JText::_('INCLUDE_SITE_DOMAIN_FOR_VALIDATION')?></label>
										<div class="controls">
										<?php
											 echo JHTML::_('select.genericlist', $yes_no_select, 'config[include_site_domain]', null, 'value', 'text', $this->invitex_params['include_site_domain']);
										 ?>
										</div>
								</div>
						 </fieldset>


						<fieldset class="form-horizontal">
						  <legend><?php echo JText::_('LAYOUT_SET');?></legend>
						     <div class="control-group">
											<label class="control-label" for="configinv_look" id="configinv_look-lbl" title="<?php echo JText::_('INV_LOOK_DESC') ?>"><?php echo JText::_('INV_LOOK') ?></label>
												<div class="controls">
													<?php
														$opt = array(
															JHTML::_('select.option', '1', JText::_('INV_FB') ),
															JHTML::_('select.option', '0', JText::_('INV_NEW') ),
															JHTML::_('select.option', '2', JText::_('INV_BLACK_WHITE') )
														);
														echo JHTML::_('select.genericlist', $opt, 'config[inv_look]', null, 'value', 'text', $this->invitex_params['inv_look']);
													?>
												</div>
								</div>
								<?php	if(JFolder::exists($communityfolder)) { ?>
								<div class="control-group">
											<label class="control-label" for="configjstoolbar" id="configjstoolbar-lbl" title="<?php echo JText::_('JSTOOLBAR_DESC') ?>"><?php echo JText::_('JSTOOLBAR') ?></label>
												<div class="controls">
												<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[jstoolbar]', null, 'value', 'text', $this->invitex_params['jstoolbar']);
										 		?>
											</div>
								</div>
								<?php } ?>
								<div class="control-group">
											<label class="control-label" for="configshow_menu" id="configshow_menu-lbl" title="<?php echo JText::_('SHOW_MENU_BAR_DES') ?>"><?php echo JText::_('SHOW_MENU_BAR')?></label>
										<div class="controls">
											<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[show_menu]', null, 'value', 'text', $this->invitex_params['show_menu']);
										 		?>
										</div>
								</div>
								<div class="control-group">
											<label class="control-label" for="configshow_menu" id="configshow_menu-lbl"  title="<?php echo JText::_('ALLOWED_INVITE_METHODS_DESC') ?>"><?php echo JText::_('ALLOWED_INVITE_METHODS')?></label>
									<div class="controls">
										<?php echo $this->provider_methods_multiselect;?>
												<div class="input-append">
												<button type="button" class="btn" onclick="moveUpItem('configinvite_methods')"><img class="invitex_image" src="<?php echo JURI::root().'components/com_invitex/images/back/move_up.png';?>" /><?php echo JText::_('COM_INVITEX_MOVE_UP');?></button>
												<button type="button" class="btn" onclick="moveDownItem('configinvite_methods')"><img class="invitex_image" src="<?php echo JURI::root().'components/com_invitex/images/back/move_down.png';?>" /><?php echo JText::_('COM_INVITEX_MOVE_DOWN');?></button>
											</div>
										</div>
								</div>
						 </fieldset>

						<fieldset class="form-horizontal">
									<legend><?php echo JText::_('API_SET');?></legend>
									<div class="control-group">
											<label class="control-label" for="configinvite_apis" id="configinvite_apis-lbl"  title="<?php echo JText::_('SELECT_API_DES') ?>"><?php echo JText::_('SELECT_API')?></label>
											<div class="controls">
												<?php
												$apiselect = array();
												if($this->apiplugin)
												foreach($this->apiplugin as $api)
												{
													$apiname = ucfirst(str_replace('plug_techjoomlaAPI_', '',$api->element));
													$apiselect[] = JHTML::_('select.option',$api->element, $apiname);
												}

												if(!empty($apiselect))
												{
													echo JHTML::_('select.genericlist', $apiselect, "config[invite_apis][]", 'class="required" multiple size="6"  ', "value", "text", explode(',',$this->invitex_params['invite_apis']) );
												}
												else
													echo "<b>No Techjoomla API plugin is enabled..</b>"
													?>
												</div>
									</div>
									<div class="control-group">
											<label class="control-label" for="configenb_load_more" id="configenb_load_more-lbl" title="<?php echo JText::_('ENB_LOAD_MORE_DESC') ?>"><?php echo JText::_('ENB_LOAD_MORE') ?></label>
											<div class="controls">
												<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[enb_load_more]', null, 'value', 'text', $this->invitex_params['enb_load_more']);
										 		?>
											</div>
									</div>
									<div class="control-group">
											<label class="control-label" for="configcontacts_at_first_instance" id="configcontacts_at_first_instance-lbl" title="<?php echo JText::_('NUMBER_OF_CONTACTS_AT_FIRST_INSTANCE_DESC') ?>"><?php echo JText::_('NUMBER_OF_CONTACTS_AT_FIRST_INSTANCE');?></label>
											<div class="controls">
													<input type='text' name="config[contacts_at_first_instance]" id="contacts_at_first_instance" class="inputbox" value="<?php echo $this->invitex_params['contacts_at_first_instance'] ?>"/>
											</div>
									</div>
						</fieldset>
						<?php	if(JFolder::exists($communityfolder) || JFolder::exists($cbfolder) || JFolder::exists($jwfolder) || JFolder::exists($esfolder)) { ?>
						<fieldset class="form-horizontal">
						  <legend><?php echo JText::_('ACTIVITY_SET');?></legend>
						     <div class="control-group">
											<label class="control-label" for="configintegrate_activity_stream" id="configintegrate_activity_stream-lbl" title="<?php echo JText::_('INTEGRATE_JS_ACTIVITY_STREAM_DESC') ?>"><?php echo JText::_('INTEGRATE_JS_ACTIVITY_STREAM') ?></label>
										<div class="controls">
										<?php
												 echo JHTML::_('select.genericlist', $activity_stream, 'config[integrate_activity_stream][]', 'multiple size="5"', 'value', 'text', explode(',',$this->invitex_params['integrate_activity_stream']));
										 		?>
										</div>
									</div>
						 </fieldset>
						<?php } ?>


						<?php
						$if_broadcast=JPATH_SITE . '/components/com_broadcast';
						if(JFolder::exists($if_broadcast)) { ?>
						<fieldset class="form-horizontal">
						  <legend><?php echo JText::_('BROADCAST_ACTIVITY_SET');?></legend>
						     <div class="control-group">
											<label class="control-label" for="broadcast_activity_stream" id="broadcast_activity_stream-lbl" title="<?php echo JText::_('INTEGRATE_BROADCAST_ACTIVITY_STREAM_DESC') ?>"><?php echo JText::_('INTEGRATE_BROADCAST_ACTIVITY_STREAM') ?></label>
										<div class="controls">
										<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[broadcast_activity_stream]', 'multiple size="5"', 'value', 'text', $this->invitex_params['broadcast_activity_stream']);
										 		?>
										</div>
									</div>
						 </fieldset>
						<?php } ?>


						<fieldset class="form-horizontal">
								<legend><?php echo JText::_('BATCH_SET');?></legend>
								 <div class="control-group">
											<label class="control-label" for="configuse_sys" id="configuse_sys-lbl" title="<?php echo JText::_('USE_SYS_PLUG_DESC') ?>"><?php echo JText::_('USE_SYS_PLUG') ?></label>
										<div class="controls">
											<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[use_sys]', null, 'value', 'text', $this->invitex_params['use_sys']);
										 		?>
										</div>
									</div>
									 <div class="control-group">
											<label class="control-label" for="configfrom_address" id="configfrom_address-lbl"  title="<?php echo JText::_('FROM_ADDRESS_DESC') ?>"><?php echo JText::_('FROM_ADDRESS') ?></label>
											<div class="controls">
												<input type="text" name="config[from_address]" value="<?php echo $this->invitex_params['from_address'] ?>" id="from_address">
												</div>
									</div>
									 <div class="control-group">
											<label class="control-label" for="configenb_batch" id="configenb_batch-lbl" title="<?php echo JText::_('ENB_BATCH_DESC') ?>"><?php echo JText::_('ENB_BATCH') ?></label>
											<div class="controls">
											<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[enb_batch]', null, 'value', 'text', $this->invitex_params['enb_batch']);
										 		?>
										</div>
									</div>
								 <div class="control-group">
											<label class="control-label" for="inviter_percent" id="inviter_percent-lbl" title="<?php echo JText::_('PER_GOES_INV_DESC') ?>"><?php echo JText::_('PER_GOES_INV') ?></label>
											<div class="controls">
													<input type="text" name="config[inviter_percent]" value="<?php echo $this->invitex_params['inviter_percent'] ?>" id="inviter_percent">
										</div>
									</div>

								 <div class="control-group">
										<label class="control-label" for="private_key_cronjob" id="private_key_cronjob-lbl" title="<?php echo JText::_('PRIVATE_KEY_CRON_DESC') ?>"><?php echo JText::_('PRIVATE_KEY_CRON') ?></label>
										<div class="controls">
											<input type="text"  class="inputbox" name="config[private_key_cronjob]" width="40%" id="private_key_cronjob" value="<?php echo $this->invitex_params['private_key_cronjob'] ?>">
										</div>
								</div>

								 <div class="control-group">
										<label class="control-label" title="<?php echo JText::_('CRON_JOB_DESC') ?>"><?php echo JText::_('CRON_JOB') ?></label>
										<div class="controls" style ="font-weight:bolder;">
												<?php echo JURI::root().'index.php?option=com_invitex&tmpl=component&task=mailto&pkey='.$this->invitex_params['private_key_cronjob'];?>
										</div>
								</div>

						  </fieldset>

					<fieldset class="form-horizontal">
						  <legend><?php echo JText::_('GOOGLE_ANALYTICS_SET');?></legend>
						     <div class="control-group">
										<label class="control-label" for="configga_campaign_enable" id="configga_campaign_enable-lbl" title="<?php echo JText::_('GA_CAMPAIGN_ENABLE_DESC') ?>"><?php echo JText::_('GA_CAMPAIGN_ENABLE') ?></label>
											<div class="controls">
											<?php echo JHTML::_('select.genericlist', $yes_no_select, 'config[ga_campaign_enable]', null, 'value', 'text', $this->invitex_params['ga_campaign_enable']);?>
										</div>
									</div>
									 <div class="control-group">
										<label class="control-label" for="ga_campaign_name" id="ga_campaign_name-lbl"  title="<?php echo JText::_('GA_CAMPAIGN_NAME_DESC') ?>"><?php echo JText::_('GA_CAMPAIGN_NAME') ?></label>
											<div class="controls">
												<input type="text" name="config[ga_campaign_name]" value="<?php echo isset($this->invitex_params['ga_campaign_name'])?$this->invitex_params['ga_campaign_name']:'';  ?>" id="ga_campaign_name" />
										</div>
									</div>
									<div class="control-group">
											<label class="control-label" for="configga_campaign_source" id="configga_campaign_source-lbl" title="<?php echo JText::_('GA_CAMPAIGN_SRC_DESC') ?>"><?php echo JText::_('GA_CAMPAIGN_SRC') ?></label>
											<div class="controls">
												<input type="text" name="config[ga_campaign_source]" value="<?php echo isset($this->invitex_params['ga_campaign_source'])?$this->invitex_params['ga_campaign_source']:''; ?>" id="ga_campaign_source" />
										</div>
									</div>
									<div class="control-group">
											<label class="control-label" for="configga_campaign_medium" id="configga_campaign_medium-lbl" title="<?php echo JText::_('GA_CAMPAIGN_MED_DESC') ?>"><?php echo JText::_('GA_CAMPAIGN_MED') ?></label>
											<div class="controls">
												<input type="text" name="config[ga_campaign_medium]" value="<?php echo isset($this->invitex_params['ga_campaign_medium'])?$this->invitex_params['ga_campaign_medium']:''; ?>" id="ga_campaign_medium" />
										</div>
									</div>
						 </fieldset>
					</div><!-- div span12 -->
				</div><!-- div row_fluid -->
			</div><!-- div tab pane:global_config -->


			<div class="tab-pane" id="reg_spec_config">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
						  <legend><?php echo JText::_('GEN_SET');?></legend>
								<div class="control-group">
										<label class="control-label" for="configinvitation_during_reg" id="configinvitation_during_reg-lbl" title="<?php echo JText::_('INVITAION_DURING_REG_DESC') ?>"><?php echo JText::_('INVITAION_DURING_REG') ?></label>
										<div class="controls ">

												<?php
													 echo JHTML::_('select.genericlist', $yes_no_select, 'config[invitation_during_reg]', null, 'value', 'text', $this->invitex_params['invitation_during_reg']);
												 ?>

										</div>
								</div>

								<div class="control-group">
										<label class="control-label" for="configlanding_page_reg" id="configlanding_page_reg-lbl" title="<?php echo JText::_('INVITAION_LANDING_PAGE') ?>"><?php echo JText::_('INVITAION_LANDING_PAGE') ?></label>
										<div class="controls ">

												<?php

												$arr=array();
													$arr[] = JHTML::_('select.option', 'Joomla', JText::_('INV_JOOMLA') );
													if(JFolder::exists($communityfolder))
														$arr[] =	JHTML::_('select.option', 'JomSocial', JText::_('INV_JS') );
													if(JFolder::exists($cbfolder))
														$arr[] = JHTML::_('select.option', 'Community Builder', JText::_('INV_CB') );
													if(JFolder::exists($vmfolder))
														$arr[] = JHTML::_('select.option', 'Virtuemart', JText::_('INV_VM') );
													if(JFolder::exists($esfolder))
														$arr[] = JHTML::_('select.option', 'EasySocial', JText::_('INV_ES') );
													if(JFolder::exists($payplansfolder))
														$arr[] = JHTML::_('select.option', 'PayPlans', JText::_('INV_PAYPLANS') );

													 echo JHTML::_('select.genericlist', $arr, 'config[landing_page_reg]', null, 'value', 'text', $this->invitex_params['landing_page_reg']);
												 ?>

										</div>
								</div>

								<!--added a download link for cb redirectregister plugin-->
								<?php

												$cb_path=JPATH_SITE . '/components/com_comprofiler/plugin/user/plug_redirectasregister';
												if(JFolder::exists($cbfolder) && (!JFolder::exists($cb_path))){	?>

								<div class="control-group">

								<label class="control-label" ></label>

												<div class="controls">

													<span><?php echo JText::_('INV_NOTE_FOR_CB_RIDIRECT_PLUGIN') ?></span>
												 <a href="<?php echo JUri::root().'components/com_invitex/CB_plug_redirectasregister/plug_redirectasregister.zip';?>"><?php echo JText::_('INV_DOWNLOAD') ?></a>

												</div>

								</div>
								<?php	}	?>
						     <div class="control-group">
										<label class="control-label" for="configinvite_only" id="configinvite_only-lbl" title="<?php echo JText::_('INV_ONLY_DESC') ?>"><?php echo JText::_('INV_ONLY') ?></label>
										<div class="controls">
											<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[invite_only]', null, 'value', 'text', $this->invitex_params['invite_only']);
											 ?>
										</div>
								</div>

								<div class="control-group">
										<label class="control-label" for="guest_invitation" id="guest_invitation-lbl" title="<?php echo JText::_('INV_GUEST_INVITATION_DESC') ?>"><?php echo JText::_('INV_GUEST_INVITATION') ?></label>
										<div class="controls">
											<?php
												echo JHTML::_('select.genericlist', $yes_no_select, 'config[guest_invitation]', null, 'value', 'text', $this->invitex_params['guest_invitation']);

											?>
										</div>
										<label class="control-label" ></label>
										<div class="controls">
											<?php
												echo JText::_('INV_CAPTCHA_MESSAGE');
											?>
										</div>

								</div>



								<div class="control-group" >
												<label class="control-label" for="global_value" id="global_value-lbl" title="<?php echo JText::_('MAX_SENDS_DESC') ?>"><?php echo JText::_('MAX_SENDS') ?></label>
												<div class="controls">
													<input type="text" name="config[global_value]" value="<?php echo $this->invitex_params['global_value'] ?>" id="global_value">
												</div>
								</div>

								<div class="control-group">
										<label class="control-label" for="expiry" id="expiry-lbl" title="<?php echo JText::_('INVITE_VALIDITY_DESC') ?>"><?php echo JText::_('INVITE_VALIDITY')?></label>
									<div class="controls">
											<input type="text" value="<?php echo $this->invitex_params['expiry'] ?>" name="config[expiry]" id="expiry"/>
									</div>
								</div>

								<div class="control-group">
										<label class="control-label" for="per_user_invitation_limit" id="per_user_invitation_limit-lbl"  title="<?php echo JText::_('PER_USER_INVITATION_LIMIT_DES') ?>"><?php echo JText::_('PER_USER_INVITATION_LIMIT')?></label>
										<div class="controls">
											<input type="text" name="config[per_user_invitation_limit]" value="<?php echo $this->invitex_params['per_user_invitation_limit'] ?>" id="per_user_invitation_limit">
										</div>
								</div>
								<div class="control-group">
										<label class="control-label" for="any_invitation_url" id="any_invitation_url-lbl" title="<?php echo JText::_('ANY_INVITATION_URL_DES') ?>"><?php echo JText::_('ANY_INVITATION_URL')?></label>
										<div class="controls">
													<input type="text" name="config[any_invitation_url]" value="<?php echo $this->invitex_params['any_invitation_url'] ?>" id="any_invitation_url">
										</div>
								</div>
						 </fieldset>


						<fieldset class="form-horizontal">
									<legend><?php echo JText::_('AUTO_REM_SET');?></legend>
									<div class="control-group">
											<label class="control-label" for="configsend_auto_remind" id="configsend_auto_remind-lbl" title="<?php echo JText::_('SEND_AUTO_REMIND_DESC') ?>"><?php echo JText::_('SEND_AUTO_REMIND') ?></label>
											<div class="controls">
												<?php
												 echo JHTML::_('select.genericlist', $yes_no_select, 'config[send_auto_remind]', null, 'value', 'text', $this->invitex_params['send_auto_remind']);
											 ?>
												</div>
									</div>
									<div class="control-group">
											<label class="control-label" for="rem_after_days" id="rem_after_days-lbl" title="<?php echo JText::_('REM_AFTER_DAYS_DESC') ?>"><?php echo JText::_('REM_AFTER_DAYS') ?></label>
											<div class="controls">
												<input type='text' name="config[rem_after_days]" id="rem_after_days" class="inputbox" value="<?php echo $this->invitex_params['rem_after_days'] ?>"/>
											</div>
									</div>
									<div class="control-group">
											<label class="control-label" for="rem_repeat_times" id="rem_repeat_times-lbl" title="<?php echo JText::_('REPEAT_TIMES_DESC') ?>"><?php echo JText::_('REPEAT_TIMES') ?></label>
											<div class="controls">
												<input type='text' name="config[rem_repeat_times]" id="rem_repeat_times" class="inputbox" value="<?php echo $this->invitex_params['rem_repeat_times'] ?>"/>
											</div>
									</div>
									<div class="control-group">
											<label class="control-label" for="rem_every" id="rem_every-lbl" title="<?php echo JText::_('REM_EVERY_DESC') ?>"><?php echo JText::_('REM_EVERY') ?></label>
											<div class="controls">
												<input type='text' name="config[rem_every]" id="rem_every" class="inputbox" value="<?php echo $this->invitex_params['rem_every'] ?>"/>
											</div>
									</div>
										<div class="control-group">
													<div class="alert alert-info">
														<?php echo JText::sprintf('AUTOMATE_REM_NOTE',$this->invitex_params['rem_after_days'] ,$this->invitex_params['rem_repeat_times'],$this->invitex_params['rem_every']);?>
												</div>
									  </div>
					</fieldset>
				</div><!-- div span12 -->
				</div><!-- div row_fluid -->
			</div><!-- div tab pane:reg_spec_config -->
			<?php	$oi_path=JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';
			if(!JFile::exists($oi_path))
			{
			?>
			<div class="tab-pane" id="oi_config">
				<div class="row-fluid">
					<div class="span12">
						<?php	$download_link="<a href='http://openinviter.com/'>".JText::_('INV_OI_DOWNLOAD_LINK')."</a>";	?>
						<div>
							<?php	echo JText::sprintf('INV_OI_NOTICE',$download_link);?>
						</div>
					</div><!-- div span12 -->
				</div><!-- div row_fluid -->
			</div><!-- div tab pane: oi_config -->

			<?php	}
			else
			{
				require(JPATH_SITE."/components/com_invitex/openinviter/config.php");
				require_once(JPATH_SITE.'/components/com_invitex/openinviter/openinviter.php');
				require_once(JPATH_SITE."/components/com_invitex/models/emogrifier.php");
				$inviter = new OpenInviter();
					$oi_services=$inviter->getPlugins();
					$transport_curl = '';
					$transport_wget = '';
					if (isset($openinviter_settings['transport']) == 'curl')
						$transport_curl = ' selected ';
					else
						$transport_wget = ' selected ';
				?>
			<div class="tab-pane" id="oi_config">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<div class="control-group">
									<label class="control-label" title="<?php echo JText::_('AUTO_UPDATE_DESC') ?>"><?php echo JText::_('AUTO_UPDATE') ?></label>
									<div class="controls">
											<?php $url = JURI::root()."index.php?option=com_invitex&task=autoupdate&tmpl=component&pkey=".$this->invitex_params['private_key_cronjob'];?>
											<button class="btn btn-primary" type="button" onclick="autoup('<?php echo $url ?>','<?php echo JText::_('CONFRM_UP');?>')" ><?php echo JText::_('UPDATE'); ?></button>
									</div>
							</div>

						<div class="control-group">
									<label class="control-label" title="<?php echo JText::_('AUTO_UPDATE_CRON_DESC') ?>"><?php echo JText::_('AUTO_UPDATE_CRON') ?></label>
									<div class="controls" style ="font-weight:bolder;"><?php echo JURI::root().'index.php?option=com_invitex&task=autoupdate&pkey='.$this->invitex_params['private_key_cronjob']; ?>
									</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="oi_configtransport" id="oi_configtransport-lbl" title="<?php echo JText::_('TRANS_METHOD_DESC') ?>"><?php echo JText::_('TRANS_METHOD'); ?></label>
							<div class="controls">
									<select class="inputbox" name="oi_config[transport]" id="oi_configtransport">
										<option <?php echo $transport_curl?>> curl </option>
										<option <?php echo $transport_wget?>> wget </option>
									</select>
							</div>
						</div>

							<div class="control-group">
								<label class="control-label" for="oi_configtransport" id="oi_configtransport-lbl" title="<?php echo JText::_('PLG_ENABLE_DESC') ?>"><?php echo JText::_('PLG_ENABLE')?></label>
								<div class="controls">
									 <?php
									 if($this->invitex_params['plg_option']=='custom_select')
									 {
										 $custom="checked='checked'";
										 $all="";
									 }
									 else
									 {

										$all="checked='checked'";
										 $custom="";
									 }

							 		?>
							 	    <input type="radio" value="all" name="config[plg_option]" onClick="allselections();"  <?php echo $all;?>/>All
								    <input type="radio" value="custom_select" name="config[plg_option]" onclick="enableselections();" <?php echo $custom;?>/>Select  Item(s) from the List
								</div>
						</div>

							<div class="control-group">
						 	 	<label class="control-label">&nbsp;</label>
								 <div class="controls">
								     <?php
								     foreach ($oi_services as $type=>$providers)
								      {

									?>
								     <select id="selections<?php echo $type ?>" class="inputbox" multiple="multiple" size="15" name="config[selections][]">
								        <optgroup label="<?php echo $inviter->pluginTypes[$type]?>">
								        <?php
													$invitex_selections=explode(',',$this->invitex_params['selections']);
												 foreach ($providers as $provider=>$details)
								    		 {
								    				$s="";$d="";
														if($this->invitex_params['plg_option']=='custom_select')
														{
																 if(in_array($details['name'],$invitex_selections))
																 {
																		$s="selected";$d="disabled";
																	}
							?>
																	<option <?php echo $s;?> <?php echo $d;?>value="<?php echo $details['name']?>"><?php echo $details['name']?></option>

											<?php }
														if($this->invitex_params['plg_option']=='all')
														{?>
																	<option selected disabled value="<?php echo $details['name']?>"><?php echo $details['name']?></option>
											<?php } ?>
								  <?php } ?>
								  		</optgroup>
								         </select>
								  <?php } ?>
								 </div>
						</div>
						<div class="control-group">
							 <div class="alert alert-info">
								    <?php echo JText::_('OI_PLUG_NOTE');?>
								</div>
						</div>
			 	</fieldset>
					</div><!-- div span12 -->
				</div><!-- div row_fluid -->
			</div><!-- div tab pane: oi_config -->

			<?php	}	?>



			<div class="tab-pane" id="point_set">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
											<legend><?php echo JText::_('POINT_SET');?></legend>
												<div class="control-group">
															<div class="alert alert-info">
															<?php echo JText::_('JS_POINT_SYSTEM_NOTE');?>
															</div>
												</div>
													<div class="control-group">
																<div class="alert alert-info">
																	<?php
																		$aup_click_link="<strong><a href='".JURI::root()."components/com_invitex/altapoints/invitex_aup.zip'>".JText::_('HERE')."</a></strong>";
																		$aup_install_link="<strong><a href='".JURI::base()."index.php?option=com_altauserpoints&task=plugins' target='_blank'>".JText::_('HERE')."</a></strong>";
																	?>
																	<?php echo JText::sprintf('AUP_POINT_SYSTEM_NOTE',$aup_click_link,$aup_install_link,JText::_('POINTS_INVITER'),JText::_('POINTS_INVITEE'));?>

															</div>
												<div class="control-group">
														<label class="control-label" for="configpt_option" id="configpt_option-lbl" title="<?php echo JText::_('INTEGRATION_PT_DESC') ?>"><?php echo JText::_('INTEGRATION_PT')?></label>
														<div class="controls">
																<?php
																 $op1	="";
																 $op2 ="";
																 $op3 ="";
																 $op1 = JHTML::_('select.option', 'no',JText::_('No'));
																   $pt_option=array($op1);
																 if(JFolder::exists($communityfolder))
																 {
																	  $op2 = JHTML::_('select.option', 'jspt',JText::_('JSPT'));
																			array_push($pt_option,$op2);
																 }
																 if(JFolder::exists($esfolder))
																 {
																	  $op3 = JHTML::_('select.option', 'espt',JText::_('ESPT'));
																			array_push($pt_option,$op3);
																 }
																 if(JFolder::exists($altafolder))
																 {
																	$op4 = JHTML::_('select.option', 'alta',JText::_('ALTA_POINTS'));
																	  array_push($pt_option,$op4);
																 }

																   echo JHTML::_('select.radiolist' , $pt_option , 'config[pt_option]' , 'class="inputbox"', 'value', 'text', $this->invitex_params['pt_option'])?>
															</div>
												</div>

												 </div>
												 <div class="control-group">
															<label class="control-label" for="inviter_point" id="inviter_point-lbl" title="<?php echo JText::_('POINTS_INVITER_DESC') ?>"><?php echo JText::_('POINTS_INVITER');?></label>
														<div class="controls">
																<input type='text' name="config[inviter_point]" id="inviter_point" class="inputbox" value="<?php echo $this->invitex_params['inviter_point'] ?>"/>
														</div>
												</div>
												 <div class="control-group">
														<label class="control-label" for="inviter_point" id="inviter_point-lbl" title="<?php echo JText::_('POINTS_INVITEE_DESC') ?>"><?php echo JText::_('POINTS_INVITEE');?></label>
														<div class="controls">
															<input type='text' name="config[invitee_point]" id="invitee_point" class="inputbox" value="<?php echo $this->invitex_params['invitee_point'] ?>"/>
														</div>
												</div>
												<fieldset class="form-horizontal">
														<legend><?php echo JText::_('INV_POINTS_AFTER_EVERY_INVITE');?></legend>
												<!--POINTS ONLY FOR INVITOR FOR INVITATION SEND-->
												<div class="control-group">
															<label class="control-label" for="allow_point_after_invite" id="allow_point_after_invite-lbl" title="<?php echo JText::_('INV_POINTS_AFTER_EVERY_INVITE_DESC') ?>"><?php echo JText::_('INV_POINTS_AFTER_EVERY_INVITE');?></label>
														<div class="controls">
																<?php echo JHTML::_('select.genericlist', $yes_no_select, 'config[allow_point_after_invite]', null, 'value', 'text', $this->invitex_params['allow_point_after_invite']);	?>
														</div>
												</div>
												<div class="control-group">
															<label class="control-label" for="inviter_point_after_invite" id="inviter_point_after_invite-lbl" title="<?php echo JText::_('POINTS_INVITER_DESC') ?>"><?php echo JText::_('POINTS_INVITER');?></label>
														<div class="controls">
																<input type='text' name="config[inviter_point_after_invite]" id="inviter_point_after_invite" class="inputbox" value="<?php echo $this->invitex_params['inviter_point_after_invite'] ?>"/>
														</div>
												</div>
												</fieldset>

						</fieldset>
					</div><!-- div span12 -->
				</div><!-- div row_fluid -->
			</div><!-- div tab pane: point_set -->


			<div class="tab-pane" id="notification">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<legend><?php echo JText::_('INV_SHOW_NOTIFICATION_FOR');?></legend>
							<div class="control-group">
									<label class="control-label" for="joined_friend_notification" id="joined_friend_notification-lbl" title="<?php echo JText::_('INV_NEW_FRIEND_JOINED') ?>"><?php echo JText::_('INV_NEW_FRIEND_JOINED');?></label>
									<div class="controls">
											<?php echo JHTML::_('select.genericlist', $yes_no_select, 'config[joined_friend_notification]', null, 'value', 'text', $this->invitex_params['joined_friend_notification']);	?>
									</div>
							</div>
							<div class="control-group">
									<label class="control-label" for="invite_accepted_notification" id="invite_accepted_notification-lbl" title="<?php echo JText::_('INV_INVITE_ACCEPTED') ?>"><?php echo JText::_('INV_INVITE_ACCEPTED');?></label>
									<div class="controls">
											<?php echo JHTML::_('select.genericlist', $yes_no_select, 'config[invite_accepted_notification]', null, 'value', 'text', $this->invitex_params['invite_accepted_notification']);	?>
									</div>
							</div>
						</fieldset>
					</div><!-- div span12 -->
				</div><!-- div row_fluid -->
			</div><!-- div tab pane: oi_config -->

	</div><!-- div tab_content-->
</div><!-- div tabbale -->
		<input type="hidden" name="option" value="com_invitex" />
		<input type="hidden" name="task" value="save" />
		<input type="hidden" name="view" value="config" />
		<input type="hidden" name="controller" value="config" />
    <input type="hidden" name="boxchecked" value="" />

		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
</div><!-- div <?php echo INVITEX_WRAPPER_CLASS;?> -->
