<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/guest/tmpl/register.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	User Groups (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');

 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 $doc->addScript("components/com_jblance/js/simplemodal.js");
 $doc->addStyleSheet("components/com_jblance/css/simplemodal.css");
$doc->addStyleSheet("components/com_jblance/css/xiping_pricing.css");
$doc->addStyleSheet("components/com_jblance/css/register/xp_register.css");
 $app = JFactory::getApplication();
 $user	= JFactory::getUser();
 $model = $this->getModel();
 $config = JblanceHelper::getConfig();
 $taxpercent = $config->taxPercent;
 $taxname = $config->taxName;

 $session = JFactory::getSession();
 $ugid = $session->get('ugid', 0, 'register');
 $planChosen = $session->get('planChosen', 0, 'register');
 $planId = $session->get('planid', 0, 'register');
 $skipPlan = $session->get('skipPlan', 0, 'register');
 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 if(empty($planId)){	//this is to check if the user has selected plan and entered this page
	$link = JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
	$app->redirect($link);
 }

 $step = $app->input->get('step', 0, 'int');
 JText::script('COM_JBLANCE_AVAILABLE');

 $termid = $config->termArticleId;
 //$link = JRoute::_("index.php?option=com_content&view=article&id=".$termid.'&tmpl=component');
 $link =  JUri::root()."index.php?option=com_content&view=article&id=".$termid."&tmpl=component";

 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);

	//check password equals password2
	if(jQuery("#password").val() != jQuery("#password2").val()){
		alert('<?php echo JText::_('COM_JBLANCE_VERIFY_PASSWORD_INVALID', true); ?>');
		return false;
	}

	if(valid == true){
		if(!jQuery("#agree").is(":checked")){
			alert('请同意协议！！！');
			return false;
		}
    }
    else {
	    alert('<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>');
		return false;
    }
	return true;
}

function checkAction(type){
//	console.log(jQuery("#agree").is(":checked"));
	console.log(jQuery("#agree").is(":checked"),type);
	switch (type){
		case 1:
			if(jQuery("#agree").is(":checked")){
				jQuery('.checkbox-select-img').removeClass('checkbox-select-img').addClass('checkbox-default-img');
				jQuery("#agree").attr('checked',false);
			}else{
				jQuery('.checkbox-default-img').removeClass('checkbox-default-img').addClass('checkbox-select-img');
				jQuery("#agree").attr('checked',true);
			}
			break;
		default:
			if(jQuery("#agree").is(":checked")){
				jQuery('.checkbox-select-img').removeClass('checkbox-select-img').addClass('checkbox-default-img');
				jQuery("#agree").attr('checked',false);
			}else{
				jQuery('.checkbox-default-img').removeClass('checkbox-default-img').addClass('checkbox-select-img');
				jQuery("#agree").attr('checked',true);
			}
	}
}
jQuery(function($){
	$("a.jb-modal").click(function(e){
		e.preventDefault();
		JoomBriSM.popupURL('<?php echo $link; ?>');
	});
   $('#submit-form').click(function(){
	   $(this).submit();
   })
});
//-->
</script>
<?php
if($step)
//	echo JblanceHelper::getProgressBar($step);
?>
<div class="row-fluid">
    <div class="span12 pricing comparsion">
        <div class="head">
            <div class="register"><?php echo JText::_('COM_JBLANCE_REGISTER')?></div>
            <div class="register-text"><?php echo JText::_('COM_JBLANCE_WELCOME')?></div>
            <div class="register-step-1">
                <div class="img"></div>
            </div>
            <div class="register-step-txt">
                <div class="txt"><?php echo JText::_('COM_JBLANCE_SUBSCRIBETO')?></div>
                <div class="txt"><?php echo JText::_('COM_JBLANCE_ACCOUNT_REGISTER')?></div>
                <div class="txt"><?php echo JText::_('COM_JBLANCE_COMPLATE_MESSAGE')?></div>
            </div>
        </div>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="regNewUser" class="form-horizontal form-validate xp-form" onsubmit="return validateForm(this);" enctype="multipart/form-data">
<!--<div class="jbl_h3title">--><?php //echo JText::_('COM_JBLANCE_ACCOUNT_INFO'); ?><!--</div>-->
<?php //echo JText::_('COM_JBLANCE_FIELDS_COMPULSORY'); ?>

	<?php if(!$skipPlan) { ?>
	<div class="xp-profile">
	<div class="xp-head"><?php echo JText::_('COM_JBLANCE_USERINDENTIFY')?></div>
	<div class="xp-content">
		<div class="xp-group">
			<div class="xp-top">
                <?php echo JText::_('COM_JBLANCE_PLAN_NAME'); ?>: </div>
			<div class="xp-bottom">
				<?php $sub_id = $planChosen['plan_id'];
				echo $planChosen['planname'.$sub_id]; ?>
			</div>
		</div>

		<div class="xp-group">
			<div class="xp-top"><?php echo JText::_('COM_JBLANCE_PLAN_DURATION'); ?>: </div>
			<div class="xp-bottom">
				<?php echo $planChosen['planperiod'.$sub_id]; ?>
			</div>
		</div>
		<div class="xp-group">
			<div class="xp-top"><?php echo JText::_('COM_JBLANCE_BONUS_FUND'); ?>: </div>
			<div class="xp-bottom">
				<?php echo JblanceHelper::formatCurrency($planChosen['plancredit'.$sub_id]); ?>
			</div>
		</div>
		<?php
        $totalamt = $planChosen['price'.$sub_id];
		if($totalamt > 0) :
			?>
		<div class="xp-group">
				<div class="xp-top"><?php echo JText::_('COM_JBLANCE_PAY_MODE'); ?>: </div>
				<div class="xp-bottom">
					<?php echo JblanceHelper::getGwayName($planChosen['gateway']); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="xp-group">
			<div class="xp-top"><?php echo JText::_('COM_JBLANCE_TOTAL_AMOUNT'); ?>: </div>
			<div class="xp-bottom">
				<?php
				$totalamt = $planChosen['price'.$sub_id];
				if($taxpercent > 0){
					$taxamt = $totalamt * ($taxpercent/100);
					$totalamt = $taxamt + $totalamt;
				}
				echo JblanceHelper::formatCurrency($totalamt);
				if($taxpercent > 0 && $totalamt > 0){
					//echo ' ('.JblanceHelper::formatCurrency($planChosen['price'.$sub_id]).' + '.JblanceHelper::formatCurrency($taxamt).')';
				}
				?>
			</div>
		</div>
	</div>

	</div>
	<?php } ?>

	<div class="xp-user-message">
		<div class="xp-head"><?php echo JText::_('COM_JBLANCE_FILL_ACCOUNT_INFORMATION')?></div>
		<div class="xp-content">
			<div class="xp-group">
				<label class="xp-left" for="name"> <span class="redfont">*</span><?php echo JText::_('COM_JBLANCE_USERNICKNAME')?></label>
				<div class="xp-right">
					<input class="input-large required" type="text" name="name" id="name" />
				</div>
			</div>

			<div class="xp-group">
				<label class="xp-left" for="username"><span class="redfont">*</span><?php echo JText::_('COM_JBLANCE_USERNAME')?></label>
				<div class="xp-right">
					<input type="text" autocomplete="off" name="username" id="username" class="input-large hasTooltip required validate-username" onchange="checkAvailable(this);" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_USERNAME')); ?>" />
					<div id="status_username" class="dis-inl-blk"></div>
				</div>
			</div>
			<div class="xp-group">
				<label class="xp-left" for="email"><span class="redfont">*</span><?php echo JText::_('COM_JBLANCE_USEREMAIL')?></label>
				<div class="xp-right">
					<input type="text" autocomplete="off" name="email" id="email" class="input-large hasTooltip required validate-email" onchange="checkAvailable(this);" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_EMAIL')); ?>" />
					<div id="status_email" class="dis-inl-blk"></div>
				</div>
			</div>
			<div class="xp-group">
				<label class="xp-left" for="password"><span class="redfont">*</span><?php echo JText::_('COM_JBLANCE_USERPASSWORD')?></label>
				<div class="xp-right">
					<input type="password" autocomplete="off" name="password" id="password" class="input-large hasTooltip required validate-password" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_PASSWORD')); ?>" />
				</div>
			</div>
			<div class="xp-group">
				<label class="xp-left" for="password2"><span class="redfont">*</span><?php echo JText::_('COM_JBLANCE_USERPASSWORD_COMFIRM')?></label>
				<div class="xp-right">
					<input type="password" autocomplete="off" name="password2" id="password2" class="input-large hasTooltip required validate-password" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_REPASSWORD')); ?>" />
				</div>
			</div>

		</div>

		<div class="xp-rule-content">
			<div class="xp-rule">
				<div class="rule-tab"></div>
				<div class="rule-group">
					<div class="checkbox-default-img" onclick="checkAction(1);"><input type="checkbox" name="agree" id="agree"></div>
					<label for="agree"  onclick="checkAction(0);"><?php echo JText::_('COM_JBLANCE_READANDACCEPT')?></label>
					<a href="http://www.baidu.com"><?php echo JText::_('COM_JBLANCE_WORDS')?></a>
					<label for="agree"  onclick="checkAction(0);"><?php echo JText::_('COM_JBLANCE_AGREEMENTPLATFORM')?></label>
				</div>
			</div>
		</div>

		<div class="button-group">
			<div class="button-left" onclick="history.go(-1);"><?php echo JText::_("COM_JBLANCE_PREN_STEP")?></div>
			<div class="button-right"><input type="submit" value="<?php echo JText::_('COM_JBLANCE_CREATEACCOUNT')?>"></div>
		</div>

	</div>
<!--	<p>--><?php //echo JText::sprintf('COM_JBLANCE_BY_CLICKING_YOU_AGREE', ''); ?><!--</p>-->
<!--	<div class="form-actions">-->
<!--		<input type="submit" value="--><?php //echo JText::_( 'COM_JBLANCE_I_ACCEPT_CREATE_MY_ACCOUNT' ); ?><!--" class="btn btn-primary" />-->
<!--	</div>-->

	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="guest.grabuseraccountinfo" />
	<?php echo JHtml::_('form.token'); ?>
</form>
    </div>
</div>
