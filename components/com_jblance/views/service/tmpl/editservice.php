<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	04 November 2014
 * @file name	:	views/service/tmpl/editservice.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of services provided by users (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('formbehavior.chosen', '#id_category');

 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/dropzone.js");
 $doc->addScript("components/com_jblance/js/utility.js");
 $doc->addScript("components/com_jblance/js/autosize.js");
 $doc->addStyleSheet("components/com_jblance/css/customer/service.css");
$doc->addStyleSheet("components/com_jblance/css/customer/new_service.css");

 $select 	  	 = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 $user 		  	 = JFactory::getUser();
 $config 	  	 = JblanceHelper::getConfig();
 $currencysym 	 = $config->currencySymbol;
 $currencycod 	 = $config->currencyCode;
 $minBasePrice 	 = $config->minServiceBasePrice;
 $reviewServices = $config->reviewServices;

 $row = $this->row;
 $isNew = ($row->id == 0) ? true : false;
 $title = $isNew ? JText::_('COM_JBLANCE_ADD_SERVICE') : JText::_('COM_JBLANCE_EDIT_SERVICE');

 $attachments = JBMediaHelper::processAttachment($row->attachment, 'service', false);
 $registry = new JRegistry();
 $registry->loadArray($attachments);
 $serviceImageMockFile = $registry->toString();

 //get the service charge and fees based on the plan
 $plan 				 = JblanceHelper::whichPlan($user->id);
 $chargePerService	 = $plan->flChargePerService;
 $serviceFee	 	 = $plan->flFeePercentPerService;

 JblanceHelper::setJoomBriToken();
 ?>

<script type="text/javascript">
// <!--
function validateForm(f){

	var valid = document.formvalidator.isValid(f);
	var filecount = jQuery("input[name='serviceImage[]']").length;
	var minBasePrice = parseInt('<?php echo $minBasePrice; ?>');

	//validate price
	if(jQuery("#price").val() < minBasePrice){
		alert('<?php echo JText::sprintf('COM_JBLANCE_MINIMUM_SERVICE_BASE_PRICE_IS', JblanceHelper::formatCurrency($minBasePrice), array('jsSafe'=>true)); ?>');
		jQuery("#price").focus();
		return false;
	}
	//validate duration
	if(jQuery("#duration").val() <= 0){
		alert('<?php echo JText::_('COM_JBLANCE_ENTER_VALUE_GREATER_THAN_EQUAL_TO_ONE', true); ?>');
		jQuery("#duration").focus();
		return false;
	}
	//validate file
	if(filecount == 0){
		alert('<?php echo JText::_('COM_JBLANCE_MUST_UPLOAD_FILE_BEFORE_SAVING', true); ?>');
		return false;
	}
	//validate fast delivery duration
	if(jQuery("#extra-fast-enabled:checked").length){
		if(parseInt(jQuery("#extra-fast-duration").val()) >= parseInt(jQuery("#duration").val())){
			alert('<?php echo JText::_('COM_JBLANCE_FAST_DELIVERY_CANNOT_GREATER_THAN_EQUAL_TO_BASE_DURATION', true); ?>');
			jQuery("#extra-fast-duration").focus();
			return false;
		}
	}

	if(valid == true){
		jQuery("#submitbtn").prop("disabled", true);
		jQuery("#submitbtn").val('<?php echo JText::_('COM_JBLANCE_SAVING', true); ?>');

    }
    else {
	    var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
		alert(msg);
		return false;
    }
	return true;
}

function switchLabel(object){
    if(jQuery(object).find("input[type=checkbox]").is(":checked")){
        jQuery(object).find(".selected-img").removeClass('selected-img').addClass('default-img');
        jQuery(object).find("input[type=checkbox]").attr('checked',false);
    }else{
        jQuery(object).find("input[type=checkbox]").attr('checked',true);
        jQuery(object).find(".default-img").removeClass('default-img').addClass('selected-img');
    }
}

jQuery(document).ready(function($){
	$("input.service-extra-checkbox").on("click", processExtraFields);
	$("input.service-extra-checkbox").triggerHandler("click");
});

var processExtraFields = function(){
	var row = jQuery(this).data("extra-row");	//get the row name or number

	if(this.checked)
		jQuery("input[type='text'][data-extra-row='"+ row +"']").addClass("required").prop("required", "required");
	else
		jQuery("input[type='text'][data-extra-row='"+ row +"']").removeClass("required").removeProp("required");

}

jQuery(document).ready(function($){
	var params = { 'maxFileSize' : 2, 'maxFiles' : 5, 'uploadTask' : 'ajax.dzuploadfile', 'removeTask' : 'ajax.dzremovefile', 'acceptedFiles' : 'image/*', 'uploadType' : 'service'};	//todo: add size and max files to config
	createDropzone('service-image', 'serviceImage', '<?php echo $serviceImageMockFile; ?>', params);

	autosize($("#description"));
});

// -->
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate form-inline" onsubmit="return validateForm(this);" enctype="multipart/form-data" novalidate>
<div class="myserviceBox">
	<div class="jbl_h3title title"><?php echo $title; ?></div>
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_SERVICE_DETAILS'); ?></legend>
		<div class="control-group">
		    <span><i>*</i><?php echo JText::_('COM_JBLANCE_SERVICE_TITLE'); ?>:</span>
			<label class="control-label" for="service_title"><?php echo JText::_('COM_JBLANCE_SERVICE_TITLE'); ?> :</label>
			<div class="controls">
				<input type="text" class="input-xxlarge required hasTooltip" name="service_title" id="service_title" placeholder="请填写" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_SERVICE_TITLE')); ?>" value="<?php echo $row->service_title;?>" />
			</div>
		</div>
		<div class="control-group">
		    <span><i>*</i><?php echo JText::_('COM_JBLANCE_SKILLS'); ?>:</span>
			<label class="control-label" for="id_category"><?php echo JText::_('COM_JBLANCE_SKILLS'); ?> :</label>
			<div class="controls controlsCategory">
				<?php
				$attribs = "class='input-xxlarge required' size='5' MULTIPLE";
				echo $select->getSelectCategoryTree('id_category[]', explode(',', $this->row->id_category), '', $attribs, '', true); ?>
				<span class="addCategory"><i class="glyphicon glyphicon-plus"></i> 添加</span>
			</div>
		</div>
		<div class="control-group">
		    <span><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?>:</span>
			<label class="control-label" for="description"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?> :</label>
			<div class="controls">
				<textarea name="description" id="description" class="input-xxlarge required hasTooltip" rows="5" style="max-height: 300px; height:72px !important;" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_SERVICE_DESCTIPTION')); ?>" placeholder="<?php echo JText::_('COM_JBLANCE_SERVICECONTENTDESCEIBE') ?>"><?php echo $row->description; ?></textarea>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_SERVERCONTENT'); ?></legend>
		<div class="controlsPrice">
			<div class="control-group">
			<span><i>*</i><?php echo JText::_('COM_JBLANCE_PRICE_AND_DURATION'); ?>:</span>
			<label class="control-label" for="price"><?php echo JText::_('COM_JBLANCE_I_WILL_DO_FOR'); ?> :</label>
			<div class="controls">
				<div class="input-prepend">
					<input class="input-mini required validate-numeric" type="text" name="price" id="price" value="<?php echo $row->price; ?>" />
				</div>
				<i class="unit">/元</i>
			</div>
		</div>
            &nbsp;&nbsp;
<!--			<span class="add-on currency">--><?php //echo $currencysym; ?><!--</span>-->
			<div class="control-group">
				<label class="control-label" for="duration"><?php echo JText::_('COM_JBLANCE_I_WILL_DO_IN'); ?> :</label>
				<div class="controls">
					<div class="input-append">
						<input class="input-mini required validate-numeric" type="text" name="duration" id="duration" value="<?php echo $row->duration; ?>" />
				   </div>
				<i class="add-on unit">/<?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></i>
			</div>
			</div>
		</div>
		<div class="uploadImageBox">
		<div class="control-group">
			<span><i>*</i><?php echo JText::_('COM_JBLANCE_SERVICE_IMAGES'); ?>:</span>
			<div class="uploadImage">
				<span class="addCategory"><i class="glyphicon glyphicon-plus"></i><?php echo JText::_('COM_JBLANCE_ADD_FILES'); ?></span>

			</div>

		</div>
		<div class="uploadImgDes">推荐上传 (680x426) 图片，展示效果最佳</div>
		<?php echo JBMediaHelper::renderDropzone('service-image'); ?>
		</div>

		<div class="control-group">
			<span><?php echo JText::_('COM_JBLANCE_INSTRUCTIONS_TO_BUYERS'); ?>:</span>
			<!-- <label class="control-label" for="description"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?> :</label> -->
			<div class="controls">
				<textarea name="instruction" id="instruction" class="input-xxlarge hasTooltip" rows="5" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_SERVICE_INSTRUCTION')); ?>"><?php echo $row->instruction; ?></textarea>
			</div>
		</div>
	</fieldset>


	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_ADD_ONS'); ?></legend>
		<?php
		$options = 3;
		$registry = new JRegistry;
		$registry->loadString($row->extras);
		$extras = $registry->toObject();

		//if is set, then set the value else initialise
		if(!isset($extras->fast)){
			$checked  = '';
			$price 	  = '';
			$duration = '';
		}
		else {
			$checked  = ($extras->fast->enabled) ? 'checked' : '';
			$price 	  = $extras->fast->price;
			$duration = $extras->fast->duration;
		}
		?>

        <div class="xp-well">
            <div class="xp-well-line">
                <div class="xp-well-line-left">
                    <div class="checkbox" onclick="switchLabel(this);">
                        <div class="<?php echo $checked?"selected-img":"default-img"?>"></div>
                        <input type="hidden" name="extras[fast][enabled]" value="0" /> <!-- this is added when checkbox is not checked -->
                        <input type="checkbox" id="extra-fast-enabled" name="extras[fast][enabled]" class="service-extra-checkbox" value="1" <?php echo $checked; ?> data-extra-row="fast" />
                        <span>方案1：</span>
                    </div>
                </div>
                <div class="xp-well-line-right">
                    <div class="input-width">
                        <input class="input-mini validate-numeric" type="text" name="extras[fast][price]" id="extra-fast-price" value="<?php echo $price; ?>" data-extra-row="fast" />
                        <span>/ 元</span>
                    </div>
                    <span class="middle"><?php echo JText::_('COM_JBLANCE_UNIT'); ?></span>
                    <div class="input-width">
                        <input class="input-mini validate-numeric" type="text" name="extras[fast][duration]" id="extra-fast-duration" value="<?php echo $duration; ?>" data-extra-row="fast" />
                        <span>/ <?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="xp-well">
            <div class="xp-well-line">
                <div class="xp-well-line-left">
                    <label class="checkbox"></label>
                </div>
                <div class="xp-well-line-right">
                    <span class="label label-warning xp-label"><?php echo JText::_('COM_JBLANCE_FAST_DELIVERY'); ?></span>
                    <span class="label-text"><?php echo JText::_('COM_JBLANCE_FAST_DELIVERY_DESC'); ?></span>
                </div>
            </div>
        </div>

		<?php
		for($i=0; $i < $options; $i++){
			if(!isset($extras->$i)){
				$checked	 = '';
				$description = '';
				$price 	  	 = '';
				$duration 	 = '';
			}
			else {
				$checked 	 = ($extras->$i->enabled) ? 'checked' : '';
				$description = $extras->$i->description;
				$price 		 = $extras->$i->price;
				$duration 	 = $extras->$i->duration;
			}
		?>
		<div class="xp-well xp-margin-top">
			<div class="xp-well-line">
				<div class="xp-well-line-left">
					<div class="checkbox" onclick="switchLabel(this);">
                        <div class="<?php echo $checked?"selected-img":"default-img"?>"></div>
						<input type="hidden" name="extras[<?php echo $i; ?>][enabled]" value="0" /> <!-- this is added when checkbox is not checked -->
						<input type="checkbox" id="extra-<?php echo $i; ?>-enabled" name="extras[<?php echo $i; ?>][enabled]" class="service-extra-checkbox" value="1" <?php echo $checked; ?> data-extra-row="<?php echo $i; ?>" />
                        <span>方案<?php echo $i+2;?>：</span>
                    </div>
					<input type="hidden" class="extra-description" name="extras[<?php echo $i; ?>][description]" id="extra-<?php echo $i; ?>-desc" placeholder="<?php echo JText::_('COM_JBLANCE_I_WILL'); ?>" value="<?php echo "方案".($i+2);?>" data-extra-row="<?php echo $i; ?>" />
				</div>
				<div class="xp-well-line-right">
					<div class="input-width">
						<input class="input-mini validate-numeric"  style="" type="text" name="extras[<?php echo $i; ?>][price]" id="extra-<?php echo $i; ?>-price" value="<?php echo $price; ?>" data-extra-row="<?php echo $i; ?>" />
                        <span>/ 元</span>
                    </div>
                    <span class="middle"><?php echo JText::_('COM_JBLANCE_UNIT'); ?></span>
					<div class="input-width">
						<input class="input-mini validate-numeric" type="text" name="extras[<?php echo $i; ?>][duration]" id="extra-<?php echo $i; ?>-duration" value="<?php echo $duration; ?>" data-extra-row="<?php echo $i; ?>" />
                        <span>/ <?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php
		} ?>
	</fieldset>

<!--	 --><?php //if($chargePerService > 0 || $serviceFee > 0){ ?>
<!--	<div class="alert alert-info">-->
<!--		<h4>--><?php //echo JText::_('COM_JBLANCE_CHARGES'); ?><!--</h4>-->
<!--		<ul>-->
<!--		--><?php //if($chargePerService > 0) : ?>
<!--			<li>-->
<!--			--><?php //echo JText::sprintf('COM_JBLANCE_CHARGE_PER_SERVICE_INFO', JblanceHelper::formatCurrency($chargePerService)); ?>
<!--			</li>-->
<!--		--><?php //endif; ?>
<!--		--><?php //if($serviceFee > 0) : ?>
<!--			<li>-->
<!--			--><?php //echo JText::sprintf('COM_JBLANCE_SERVICE_FEE_INFO', $serviceFee); ?>
<!--			</li>-->
<!--		--><?php //endif; ?>
<!--		</ul>-->
<!--	</div>-->
<!--	--><?php //} ?>
<!---->
<!--	--><?php //if($reviewServices){ ?>
<!--	<div class="alert alert-block">-->
<!--		<h4>--><?php //echo JText::_('COM_JBLANCE_APPROVAL_NOTICE'); ?><!--</h4>-->
<!--		--><?php //echo JText::_('COM_JBLANCE_SERVICE_WILL_BE_REVIEWED_BY_ADMIN_BEFORE_PUBLISH'); ?>
<!--	</div>-->
<!--	--><?php //} ?>

	<div class="form-actions">
		<input type="button" value="<?php echo JText::_('COM_JBLANCE_CANCEL'); ?>" onclick="javascript:history.back();" class="btn" />
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SAVE_SERVICE'); ?>" class="btn btn-primary" id="submitbtn" />
	</div>

	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="service.saveservice" />
	<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>

</form>
