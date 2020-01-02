<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	23 January 2016
 * @file name	:	modules/mod_jblancemenu/tmpl/default.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 // no direct access
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('bootstrap.framework');

 $document 	= JFactory::getDocument();
 $direction = $document->getDirection();
 $config 	= JblanceHelper::getConfig();
 $user		= JFactory::getUser();
 $userType  = JblanceHelper::getUserType($user->id);
 
  if($config->loadBootstrap){
 	JHtml::_('bootstrap.loadCss', true, $direction);
 }
 
 $limit 	= $config->feedLimitDashboard;
 $notifys 	= JblanceHelper::getFeeds($limit, 'notify');	//get the notificataion feeds
 $newMsgs 	= JblanceHelper::countUnreadMsg();

 $link_messages = JRoute::_('index.php?option=com_jblance&view=message&layout=inbox');
 $link_logout 	= JRoute::_('index.php?option=com_users&task=user.logout&'.JSession::getFormToken().'=1&return='.$return);
 
 if($userType->joombriuser)
 	$link_profile = LinkHelper::GetProfileURL($user->id);
 else 
 	$link_profile = '#';
 	
 ?>
<script type="text/javascript">
<!--
function showElement(layer){
	var myLayer = document.getElementById(layer);
	if(myLayer.style.display == "none"){
		myLayer.style.display = "block";
		myLayer.backgroundPosition = "top";
	} 
	else { 
		myLayer.style.display = "none";
	}

	//set the status to read
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task=user.setfeedread&<?php echo JSession::getFormToken()."=1"; ?>",
		method: "POST",
		success: function(response){
			if(response == "OK"){
				//nothing
			}
			else {
				alert(":(");
			}
		}
	});
}
//-->
</script>

<style>
.navbar .dropdown-menu {
    margin-top: 0;
}
.navbar .dropdown-menu > li {
    border-top: 0px;
}
</style>

<div class="navbar <?php echo $fixed; ?>">
	<div class="navbar-inner">
		<div class="jb-container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".navbar-responsive-collapse">
				<span class="jbf-icon-menu"></span>
				<!-- <span class="jbf-icon-minus"></span>
				<span class="jbf-icon-minus"></span> -->
			</a>
			<a class="brand" href="<?php echo $link_profile; ?>"><?php echo $user->name; ?></a>
			<div class="nav-collapse navbar-responsive-collapse">
				<ul class="nav">
				<?php
				$bootstrap_menu_generator = new ModJblanceMenuGenerator();
				$bootstrap_menu = $bootstrap_menu_generator->Build_BootStrap_Menu($list, $path, $active_id, 1);
				echo $bootstrap_menu;
				?>
				</ul>
				
				<?php if(!$user->guest){ ?>
				<ul class="nav">
					<li>
						<a href="javascript:void(0);" onclick="javascript:showElement('notify-menu')">
							<i class="jbf-icon-notification-circle"></i>
							<?php 
							$countUnreadFeeds = ModJblanceMenuHelper::countUnreadFeeds();
							if($countUnreadFeeds) : ?>
							<span class="notify-count"><?php echo $countUnreadFeeds; ?></span>
							<?php endif; ?>
						</a>

						<div id="notify-menu" class="notify-menu" style="display: none;">
							<a href="javascript:void(0);" style="float: right; padding: 10px;" onclick="javascript:showElement('notify-menu')"><i class="jbf-icon-minus-circle"></i></a>
							<div class="jbl_h3title" style="padding: 5px;"><?php echo JText::_('COM_JBLANCE_NOTIFICATIONS'); ?></div>
							<div style="max-height: 400px;overflow:auto;">
						<?php
						if(count($notifys)){
							for ($i=0, $n=count($notifys); $i < $n; $i++) {
								$notify = $notifys[$i]; ?>
								<div class="media jb-borderbtm-dot">
									<?php echo $notify->logo; ?>
									<div class="media-body">
										<?php echo $notify->title; ?>
										<div>
								        	<i class="jbf-icon-calendar"></i> <?php echo $notify->daysago; ?>
								        </div>
									</div>
								</div>
							<?php
							}
						}
						else { ?>
							<div class="font16" style="padding: 5px; border-bottom: none;">
							<?php
								echo JText::_('COM_JBLANCE_NO_NEW_NOTIFICATION');
							?>
							</div>
						<?php } ?>
							</div>
						</div>
					</li>
					<li>
						 <a href="<?php echo $link_messages; ?>" title="New Messages">
							<i class="jbf-icon-envelope"></i>
							<?php if($newMsgs) : ?>
							<span class="notify-count"><?php echo $newMsgs; ?></span>
							<?php endif; ?>
						</a>
					</li>
				</ul>
				<ul class="nav pull-right">
					<li>
						<a href="<?php echo $link_logout; ?>" title="<?php echo JText::_('JLOGOUT'); ?>"><i class="jbf-icon-switch"></i></a>
					</li>
				</ul>
                    <?php } ?>
			</div><!-- /.nav-collapse -->
		</div>
	</div><!-- /navbar-inner -->
</div>
