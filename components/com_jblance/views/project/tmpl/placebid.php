<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	26 March 2012
 * @file name	:	views/project/tmpl/placebid.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows details of the project (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('behavior.modal', 'a.jb-modal');

 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 $doc->addScript("components/com_jblance/js/upclick-min.js");
 $doc->addStyleSheet("components/com_jblance/css/customer/service.css");
$doc->addStyleSheet("components/com_jblance/css/customer/new_service.css");

 $project = $this->project;
 $bid 	  = $this->bid;

 $config 		  = JblanceHelper::getConfig();
 $currencysym 	  = $config->currencySymbol;
 $currencycode	  = $config->currencyCode;
 $reviewMessages  = $config->reviewMessages;
 $sealProjectBids = $config->sealProjectBids;

 $user = JFactory::getUser();
 $finance = JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper

 JblanceHelper::setJoomBriToken();
 ?>
 <script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);
	var budgetmin = parseFloat('<?php echo $project->budgetmin; ?>');
	var budgetmax = parseFloat('<?php echo $project->budgetmax; ?>');
	var bidamount = jQuery("#amount").val();

	if(valid == true){
		//check if agreement is selected
		if(jQuery("#is_nda_signed").length && !jQuery("#is_nda_signed:checked").length){
			alert('<?php echo JText::_('COM_JBLANCE_PLEASE_REVIEW_AGREE_NDA', true); ?>');
			return false;
		}
		//check the amount is within the budget range
		if(bidamount < budgetmin || bidamount > budgetmax){
			alert('<?php echo JText::sprintf('COM_JBLANCE_PLEASE_ENTER_AMOUNT_BETWEEN_BUDGET_RANGE', JblanceHelper::formatCurrency($project->budgetmin), JblanceHelper::formatCurrency($project->budgetmax), array('jsSafe'=>true)); ?>');
			return false;
		}
		//check for hours/day
		var project_type = '<?php echo $project->project_type; ?>';
		if(project_type == "COM_JBLANCE_HOURLY"){
			var cmt_prd = jQuery("#commitment_period").val();
			var cmt_int = jQuery("#commitment_interval").val();
			var delivery = jQuery("#delivery").val();
			if(cmt_int == "COM_JBLANCE_DAY" && delivery > 24){
				alert('<?php echo JText::_('COM_JBLANCE_HOURS_PER_DAY_EXCEEDED'); ?>');
				return false;
			}
		}

		jQuery("#submitbtn").prop("disabled", true);
		jQuery("#submitbtn").prop("value", '<?php echo JText::_('COM_JBLANCE_SAVING'); ?>');

    }
    else {
    	var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
    	if($("amount").hasClass("invalid") || $("delivery").hasClass("invalid")){
	    	msg = msg+'\n\n* '+'<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_AMOUNT_IN_NUMERIC_ONLY', true); ?>';
	    }
		alert(msg);
		return false;
    }
	return true;
}
function togglePrivateMsg() {
	if(jQuery("#sendpm:checked").length){
		jQuery("#messagediv").css("display", "block");
		jQuery("#message").addClass("required").prop("required", "required");
	}
	else {
		jQuery("#messagediv").css("display", "none");
		jQuery("#message").removeClass("required").removeProp("required");
	}
}
function switchChoose(object){
    console.log(jQuery(object).find(".img-ipt").val());
    if(jQuery(object).find(".img-ipt").is(":checked")){
        jQuery(object).find('.selected-img').removeClass('selected-img').addClass('default-img');
        jQuery(object).find(".img-ipt").attr('checked',false);
    }else{
        jQuery(object).find('.default-img').removeClass('default-img').addClass('selected-img');
        jQuery(object).find(".img-ipt").attr('checked',true);

    }
    console.log(jQuery(object).find(".img-ipt").is(":checked"));
}
jQuery(document).ready(function($){
	if($("#uploadmessage").length){
		attachFile('uploadmessage', 'message.attachfile');
	}
});
//-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormBid" id="userFormBid" class="form-validate form-horizontal" onsubmit="return validateForm(this);" enctype="multipart/form-data" style="width: 100%;">
	<div class="bidTitle">需求大厅>需求详情><span>投标</span></div>
	<div class="bidBox">
    <?php
    $lastSubscr = $finance->getLastSubscription($user->id);
    if($lastSubscr->bids_allowed > 0) :
        ?>
    <div class="bidBox-title"><span>投标出价</span><span>需求投标还剩（<?php echo $lastSubscr->bids_left; ?>/<?php echo $lastSubscr->bids_allowed; ?>）</span></div>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_BID_ON_PROJECT').' : '.$project->project_title; ?></div>

	<!-- 设计图没有先注释 -->
	<!-- <div class="bid_project_left pull-right">
	    <div><span class="font26"><?php echo $lastSubscr->bids_left; ?></span>/<span><?php echo $lastSubscr->bids_allowed; ?></span></div>
	    <div><?php echo JText::_('COM_JBLANCE_BIDS_LEFT'); ?></div>
	</div> -->
	<?php endif; ?>
	<div class="control-group">
		<label class="control-label" for="amount"><i>*</i><?php echo JText::_('COM_JBLANCE_YOUR_BID_FOR_PROJECT'); ?>:</label>
		<div class="controls">
			<div class="input-prepend input-append">
				<!-- <span class="add-on"><?php echo $currencysym; ?></span> -->
				<input type="text" style="background: none;outline: none;border:none;box-shadow: none;" name="amount" id="amount" class="input-small required validate-numeric" value="<?php echo $bid->amount; ?>" />
				<span class="add-on">/<?php echo $currencycode; ?><?php echo ($project->project_type == 'COM_JBLANCE_HOURLY') ? ' / '.JText::_('COM_JBLANCE_HR') : ''; ?></span>
			</div>
		</div>
		<span class="help-inline font12">
			<?php echo JText::_('COM_JBLANCE_BUDGET_RANGE'); ?> : <?php echo JblanceHelper::formatCurrency($project->budgetmin, true, false, 0); ?> ~ <?php echo JblanceHelper::formatCurrency($project->budgetmax, true, false, 0).' '.$currencycode; ?><?php echo ($project->project_type == 'COM_JBLANCE_HOURLY') ? ' / '.JText::_('COM_JBLANCE_HR') : ''; ?>
		</span>
	</div>
	<?php
	if($project->project_type == 'COM_JBLANCE_HOURLY'){
		$commitment = new JRegistry;
		$commitment->loadString($project->commitment);
		?>
	<div class="control-group">
		<label class="control-label" for="delivery"><i>*</i><?php echo JText::_('COM_JBLANCE_WORK_FOR'); ?>:</label>
		<div class="controls">
			<div class="input-append">
				<input type="text" style="background: none;outline: none;border:none;box-shadow: none;" name="delivery" id="delivery" class="input-small required validate-numeric" value="<?php echo $bid->delivery; ?>" />
				<span class="add-on"><?php echo JText::_('COM_JBLANCE_HOURS_PER').' '.JText::_($commitment->get('interval')); ?></span>
				<input type="hidden" id="commitment_period" value="<?php echo $commitment->get('period'); ?>" />
				<input type="hidden" id="commitment_interval" value="<?php echo $commitment->get('interval'); ?>" />
			</div>
			<span class="help-inline font12">
				<?php echo JText::_('COM_JBLANCE_HOURS_OF_WORK_REQUIRED'); ?> :
				<?php
				if($commitment->get('undefined') == 'notsure'){
					echo JText::_('COM_JBLANCE_UNSPECIFIED');
				}
				else {
					echo $commitment->get('period').' '.JText::_('COM_JBLANCE_HOURS_PER').' '.JText::_($commitment->get('interval'));
				}
				?>
			</span>
		</div>
	</div>
	<?php } ?>
	<?php
	if($project->project_type == 'COM_JBLANCE_FIXED'){ ?>
	<div class="control-group">
		<label class="control-label" for="delivery"><i>*</i><?php echo JText::_('COM_JBLANCE_DAYS_DELIVER_PROJECT'); ?>:</label>
		<div class="controls">
			<div class="input-append">
				<input type="text" style="background: none;outline: none;border:none;box-shadow: none;"  name="delivery" id="delivery" class="input-small required validate-numeric" value="<?php echo $bid->delivery; ?>" />
				<span class="add-on">/<?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="control-group">
		<label class="control-label" for="details"><?php echo JText::_('COM_JBLANCE_BID_DETAILS'); ?>: </label>
		<div class="controls">
			<textarea name="details" id="details" rows="5" class="input-xlarge required"><?php echo $bid->details; ?></textarea>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<!-- hide the outbid notification for sealed projects -->
			<?php if(!($sealProjectBids || $this->project->is_sealed)) : ?>
			<div class="checkbox" style="cursor: pointer;display: flex;justify-content: flex-start;align-items: center;padding: 0!important;" onclick="switchChoose(this)">
                <div class="default-img"></div>
				<input class="img-ipt" type="checkbox" name="outbid" id="outbid" value="1" <?php echo ($bid->outbid == 1) ? 'checked' : ''; ?> />
 				 &nbsp;&nbsp;<?php echo JText::_('COM_JBLANCE_NOTIFY_OUT_BIDS'); ?>
			</div>
			<?php endif; ?>

			<!-- show the PM only for the first time placing bid -->
			<?php if($bid->id == 0) : ?>
			<div class="checkbox" style="cursor: pointer;display: flex;justify-content: flex-start;align-items: center;padding: 0!important;" onclick="switchChoose(this);togglePrivateMsg();">
                <div class="default-img"></div>
                <input class="img-ipt"  type="checkbox" name="sendpm" id="sendpm" value="1" onclick="javascript:togglePrivateMsg();" />
                &nbsp;&nbsp;<?php echo JText::_('COM_JBLANCE_SEND_PM_TO_PUBLISHER'); ?>
			</div>
			<div id="messagediv" style="display: none;">
				<textarea name="message" id="message" rows="5" class="input-xlarge"></textarea>
				<div class="sp10">&nbsp;</div>
				<div id="ajax-container-uploadmessage"></div>
				<div id="file-attached-uploadmessage"></div>
				<input type="button" id="uploadmessage" value="<?php echo JText::_('COM_JBLANCE_ATTACH_FILE'); ?>" class="btn">
				<?php
				$tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_ATTACH_FILE'), JText::_('COM_JBLANCE_ALLOWED_FILE_TYPES').' : '.$config->projectFileText.'<br>'.JText::_('COM_JBLANCE_MAXIMUM_FILE_SIZE').' : '.$config->projectMaxsize.' kB');
				?>
				<span class="hasTooltip" title="<?php echo $tipmsg; ?>"><i class="jbf-icon-question-sign"></i></span>
				<input class="img-ipt" type="hidden" name="subject" value="<?php echo $project->project_title;?>" />
				<?php if($reviewMessages) : ?>
				<p class="alert"><?php echo JText::_('COM_JBLANCE_MESSAGE_WILL_BE_MODERATED_BEFORE_SENT_TO_RECIPIENT'); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- show the agreement form is it is NDA project and not signed -->
	<?php if($this->project->is_nda && !($bid->is_nda_signed)) : ?>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_SIGN_NDA'); ?></div>
	<div class="well well-small jbbox-gradient">
		<?php
		$ndaFile  =  JURI::root().'components/com_jblance/images/nda.txt';
		echo JText::sprintf('COM_JBLANCE_NDA_BID_MUST_AGREE', $ndaFile); ?>
		<div class="sp20">&nbsp;</div>
		<label class="checkbox"><input type="checkbox" name="is_nda_signed" id="is_nda_signed" value="1" /><?php echo JText::_('COM_JBLANCE_NDA_FREELANCER_AGREE_TO_NDA'); ?></label>
	</div>
	<?php elseif($this->project->is_nda && $bid->is_nda_signed) : ?>
		<div class="alert alert-info"><?php echo JText::_('COM_JBLANCE_NDA_ALREADY_SIGNED_AGREEMENT');?>
		<!-- Show attachment if found -->
		<?php
		if(!empty($bid->attachment)) : ?>
			<div style="display: inline;">
			<?php echo LinkHelper::getDownloadLink('nda', $bid->id, 'project.download'); ?>
			</div>
		<?php
		endif;
		?>
		</div>
	<?php endif; ?>

	<div class="clearfix"></div>
	<div class="form-actions">
		<input type="button" value="<?php echo JText::_('COM_JBLANCE_CANCEL'); ?>" onclick="javascript:history.back();" class="btn btn-primary cancel" />
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SAVE'); ?>" class="btn btn-primary" id="submitbtn" />
	</div>

	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="project.savebid" />
	<input type="hidden" name="id" value="<?php echo $bid->id;?>" />
	<input type="hidden" name="project_id" value="<?php echo $project->id;?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
