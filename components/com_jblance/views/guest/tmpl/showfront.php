<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/guest/tmpl/showfront.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	User Groups (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');

 $doc = JFactory::getDocument();
 $doc->addStyleSheet("components/com_jblance/css/pricing.css");

$doc->addStyleSheet("components/com_jblance/css/xiping_pricing.css");

 $app  	= JFactory::getApplication();
 $user	= JFactory::getUser();
 $model = $this->getModel();

 $config = JblanceHelper::getConfig();
 $link_dashboard = 'index.php?option=com_jblance&view=user&layout=dashboard';

 //check if app key/secret is empty. If empty, do not show the FB connect button
 $showFbConnect = false;
 $loginUrl = '';
 $app_id = $config->fbApikey;
 $app_sec = $config->fbAppsecret;
 if(!empty($app_id) || !empty($app_sec)){
 	$showFbConnect = true;

 	jbimport('Facebook.autoload');
 	$fb = new Facebook\Facebook([
 	    'app_id' => $app_id,
 	    'app_secret' => $app_sec,
 	    'default_graph_version' => 'v3.3',
 	]);
 	$helper = $fb->getRedirectLoginHelper();

 	$permissions = ['email']; // Optional permissions
 	$fb_callback = JUri::base().'/index.php?option=com_jblance&task=user.fblogin';
 	$loginUrl = $helper->getLoginUrl($fb_callback, $permissions);
 }
?>

<script type="text/javascript">
<!--
jQuery(document).ready(function($){
	$("#signup").click(function(){
		$("html, body").animate({
			scrollTop: $("#ugselect").offset().top
		}, 500);
	});
});

function selectRole(ugId){
	jQuery("button.active").removeClass("active btn-success");
	jQuery('#btn_ug_id'+ugId).addClass("active btn-success");
}

function tjFrom(form_name){
    jQuery("#"+form_name).submit();
}
//-->
</script>

<?php
$usersConfig = JComponentHelper::getParams('com_users');
if($usersConfig->get('allowUserRegistration') == '0'){ ?>
<div class="alert alert-error">
	<h4><?php echo JText::_('COM_JBLANCE_REGISTRATION_DISABLED'); ?></h4>
	<?php echo JText::_('COM_JBLANCE_REGISTRATION_DISABLED_MESSAGE'); ?>
</div>
<?php
}
?>

<div class="row-fluid jbbox-shadow jbbox-gradient" style="display: none;">
	<div class="span8">
		<div class="introduction">
			<h2><?php echo JText::_($config->welcomeTitle); ?></h2>
			<ul id="featurelist">
				<li><?php echo JText::_('COM_JBLANCE_HIRE_ONLINE_FRACTION_COST'); ?></li>
				<li><?php echo JText::_('COM_JBLANCE_OUTSOURCE_ANYTHING_YOU_CAN_THINK'); ?></li>
	            <li><?php echo JText::_('COM_JBLANCE_PROGRAMMERS_DESIGNERS_CONTENT_WRITERS_READY'); ?></li>
	            <li><?php echo JText::_('COM_JBLANCE_PAY_FREELANCERS_ONCE_HAPPY_WITH_WORK'); ?></li>
			</ul>
			<?php if($user->guest) : ?>
			<a href="#ugselect" id="signup" class="btn btn-large btn-primary"><?php echo JText::_('COM_JBLANCE_SIGN_UP_NOW'); ?></a>
			<?php else: ?>
			<a href="#ugselect" id="signup" class="btn btn-large btn-primary"><?php echo JText::_('COM_JBLANCE_CHOOSE_YOUR_ROLE'); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<div class="span4">
	<!-- if user is guest -->
        <?php if($user->guest) : ?>
	    <div class="jb-loginform">
	    	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="login" id="form-login">
	       		<h3><?php echo JText::_('COM_JBLANCE_MEMBERS_LOGIN'); ?></h3>
	        	<div class="control-group">
					<label class="control-label" for="username"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?>:</label>
						<div class="controls">
							<div class="input-prepend input-append">
      							<span class="add-on"><i class="jbf-icon-user"></i></span>
								<input type="text" class="input-medium" name="username" id="username" />
								<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" class="btn" title="<?php echo JText::_('COM_JBLANCE_FORGOT_YOUR_USERNAME').'?'; ?>" tabindex="-1">
									<i class="jbf-icon-question-sign"></i>
								</a>
							</div>
						</div>
					</div>
		        	<div class="control-group">
						<label class="control-label" for="password"><?php echo JText::_('COM_JBLANCE_PASSWORD'); ?>:</label>
						<div class="controls">
							<div class="input-prepend input-append">
      							<span class="add-on"><i class="jbf-icon-lock"></i></span>
								<input type="password" class="input-medium" name="password" id="password" />
								<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" class="btn" title="<?php echo JText::_('COM_JBLANCE_FORGOT_YOUR_PASSWORD').'?'; ?>" tabindex="-1">
									<i class="jbf-icon-question-sign"></i>
								</a>
							</div>
						</div>
					</div>
					<div class="control-group">
					    <div class="controls">
					      <label class="checkbox">
					        <input type="checkbox" alt="Remember me" value="yes" id="remember" name="remember" /><?php echo JText::_('COM_JBLANCE_REMEMBER_ME'); ?>
						</label>
						<button type="submit" name="submit" id="submit" class="btn btn-success btn-small"><?php echo JText::_('COM_JBLANCE_LOGIN'); ?></button>
						<?php
						if($loginUrl != '' && $showFbConnect){ ?>
						<a class="btn btn-primary btn-small" href="<?php echo $loginUrl; ?>">
							<span><?php echo JText::_('COM_JBLANCE_SIGN_IN_WITH_FACEBOOK'); ?></span>
						</a>
						<?php
						} ?>
				    </div>
				</div>
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="user.login" />
				<input type="hidden" name="return" value="<?php echo base64_encode($link_dashboard); ?>" />
				<?php echo JHtml::_('form.token'); ?>
	        </form>
		</div>
	<?php else : ?>
		<div class="jb-loginform">
			<h4><?php echo JText::sprintf('COM_JBLANCE_WELCOME_USER', $user->name); ?></h4>
		</div>
	<?php endif; ?>
	</div>
</div>

	<div style="clear:both;"></div>
<div class="sp20">&nbsp;</div>

<a id="ugselect"></a>
	<?php
	$totGroups = count($this->userGroups);
	if($totGroups==0){
		echo '<p class="alert alert-error">'.JText::_('COM_JBLANCE_NO_USERGROUP_ENABLED').'</p>';
	}?>
<div class="xp_content">
    <div class="xp_item xp_item_top">
        <div class="xp_font"><?php echo JText::_('COM_JBLANCE_WELCOME')?></div>
    </div>
    <div class="xp_item">
        <div class="xp_item_box">
            <div class="xp_left">
                <div class="img"></div>
            </div>
            <div class="xp_right">

    <?php
	for($i = 0; $i < $totGroups; $i++){
		$userGroup = $this->userGroups[$i];
		if($i % 2 == 0){
		?>
	<div class="xp-contanier">
		<?php
		}?>
		<div class="xp-contanier-item">
		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userGroup<?php echo $userGroup->id?>" id="userGroup<?php echo $userGroup->id?>">
			<div class="userrole-name text-center" style="display: none;">
			<?php if($userGroup->approval == 1) : ?>
			<div class="pull-right"><span class="label label-important"><?php echo JText::_('COM_JBLANCE_REQUIRE_APPROVAL'); ?></span></div>
			<?php endif; ?>
			<h2 style="display: none;"><?php echo $userGroup->name; ?></h2>
			</div>
<!--			--><?php //echo stripslashes($userGroup->description); ?>
<!--			<hr>-->

            <div class="xp_right_top <?php if($i==0)echo'bottom-margin';?>  xp-hover<?php echo $i;?>">
                <div class="<?php if($i==0){echo'img-top';}else{echo'img-bottom';};?>">
                    <img src="/components/com_jblance/images/register/<?php if($i==0){echo'xp_register_left_top';}else{echo'xp_register_left_bottom';};?>.png" style="width: 100%;height: 100%;">
                    <div class="xp_btn">
                        <div class="text-center">
                            <button class="xp_button" type="button"  onclick="javascript:tjFrom('userGroup<?php echo $userGroup->id; ?>');"> <?php echo $userGroup->name;?></button>
                        </div>
                    </div>
                </div>
                <!-- <div class="xp-status-line<?php echo $i?>"></div> -->
            </div>


			<input type="hidden" name="check" value="post" />
			<input type="hidden" name="ugid" value="<?php echo $userGroup->id; ?>" />
			<input type="hidden" name="option" value="com_jblance" />
			<input type="hidden" name="task" value="guest.grabusergroupinfo" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
        </div>
        <?php if($i % 2 == 1 || $i==($totGroups-1)){ ?>
	</div>
        <?php }?>
	<?php
	}?>
            </div>
                <div class="xp-new-right">
                    <div class="xp-new-right-content">
                        <div class="xp-btn"><a href="<?php echo JRoute::_('index.php?option=com_affiliatetracker&view=account&layout=form',false)?>">我要加入联盟</a></div>
                    </div>
                    <!-- <div class="xp-new-right-line"></div> -->
                </div>
            </div>
        </div>
    </div>
</div>
	<p class="alert alert-info" style="display: none;">
	<?php echo JText::_('COM_JBLANCE_REQUIRE_APPROVAL_NOTE'); ?>
	</p>
