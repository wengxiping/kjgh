<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 March 2012
 * @file name	:	views/user/tmpl/editprofile.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Edit profile (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');
 
 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");

 $user= JFactory::getUser();
 $model = $this->getModel();
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 
 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 $userInfo = $jbuser->getUserGroupInfo($user->id, null);
 
 $config 	  = JblanceHelper::getConfig();
 $currencysym = $config->currencySymbol;	
 $currencycod = $config->currencyCode;	
 $maxSkills   = $config->maxSkills;	
 
 $chosenArray = array();
 if($maxSkills > 0){
 	$chosenArray['max_selected_options'] = $maxSkills;
 }
 $chosenArray['placeholder_text_multiple'] = JText::_('COM_JBLANCE_PLEASE_SELECT_SKILLS_FROM_THE_LIST');
 
 JHtml::_('formbehavior.chosen', '#id_category', null, $chosenArray);
 
 JText::script('COM_JBLANCE_CLOSE');
 
 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
function validateForm(f){
	if(jQuery("#id_category").length){
		if(!jQuery("#id_category option:selected").length){
			alert('<?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_SKILLS_FROM_THE_LIST', true); ?>');
			return false;
		}
	}

	if (document.formvalidator.isValid(f)) {
		
    }
    else {
	    var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
	    if(jQuery("#rate").length && jQuery("#rate").hasClass("invalid")){
	    	msg = msg+'\n\n* '+'<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_AMOUNT_IN_NUMERIC_ONLY', true); ?>';
	    }
		alert(msg);
		return false;
    }
	return true;
}

<?php if($maxSkills > 0){ ?>
jQuery(document).ready(function($){
	if($("#id_category").length){
		$("#id_category").change(updateSkillCount);
		updateSkillCount();
	}
});
<?php } ?>

function updateSkillCount(){
	sel = jQuery("#id_category option:selected").length;
	jQuery("#skill_left_span").html(sel);
}

function editLocation(){
	jQuery("#level1").css("display", "inline-block").addClass("required");
}
//-->
</script>
<?php include_once(JPATH_COMPONENT.'/views/profilemenu.php'); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="editProfile" id="frmEditProfile" class="form-validate form-horizontal" onsubmit="return validateForm(this);" enctype="multipart/form-data" novalidate>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_EDIT_PROFILE'); ?></div>
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_USER_INFORMATION'); ?></legend>
		<div class="control-group">
			<label class="control-label"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?>:</label>
			<div class="controls">
				<?php echo  $this->userInfo->username; ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="name"><?php echo JText::_('COM_JBLANCE_NAME'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<input class="input-medium required" type="text" name="name" id="name" value="<?php echo $this->userInfo->name; ?>" />
			</div>
		</div>
		<!-- Company Name should be visible only to users who can post job -->
		<?php if($userInfo->allowPostProjects) : ?>
		<div class="control-group">
			<label class="control-label" for="biz_name"><?php echo JText::_('COM_JBLANCE_BUSINESS_NAME'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<input class="input-medium required" type="text" name="biz_name" id="biz_name" value="<?php echo $this->userInfo->biz_name; ?>" />
			</div>
		</div>
		<?php endif; ?>
		<!-- Skills and hourly rate should be visible only to users who can work/bid -->
		<?php if($userInfo->allowBidProjects) : ?>
		<div class="control-group">
			<label class="control-label" for="rate"><?php echo JText::_('COM_JBLANCE_HOURLY_RATE'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on"><?php echo $currencysym; ?></span>
					<input class="input-mini required validate-numeric" type="text" name="rate" id="rate" value="<?php echo $this->userInfo->rate; ?>" />
					<span class="add-on"><?php echo $currencycod.' / '.JText::_('COM_JBLANCE_HOUR'); ?></span>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="id_category"><?php echo JText::_('COM_JBLANCE_SKILLS'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<?php if($maxSkills > 0){ ?>
				<div class="bid_project_left pull-left">
		    		<div><span id="skill_left_span" class="font26"><?php echo count(explode(',', $this->userInfo->id_category))?></span>/<span><?php echo $maxSkills; ?></span></div>
		    		<div><?php echo JText::_('COM_JBLANCE_SKILLS'); ?></div>
				</div>
				<div class="clearfix"></div>
				<div class="sp10">&nbsp;</div>
				<?php } ?>
				<?php 
				//$attribs = 'class="input-medium required" size="20" multiple ';
				//$categtree = $select->getSelectCategoryTree('id_category[]', explode(',', $this->userInfo->id_category), 'COM_JBLANCE_PLEASE_SELECT', $attribs, '', true);
				//echo $categtree; 
				//$attribs = '';
				//$select->getCheckCategoryTree('id_category[]', explode(',', $this->userInfo->id_category), $attribs); ?>
				<?php
				$attribs = "class='input-xxlarge required' multiple";
				echo $select->getSelectCategoryTree('id_category[]', explode(',', $this->userInfo->id_category), '', $attribs, '', true);
				?>
			</div>
		</div>
		<?php endif; ?>
	</fieldset>
	
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_CONTACT_INFORMATION'); ?></legend>
		<div class="control-group">
			<label class="control-label" for="address"><?php echo JText::_('COM_JBLANCE_ADDRESS'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<textarea name="address" id="address" rows="3" class="input-xlarge required"><?php echo $this->userInfo->address; ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="level1"><?php echo JText::_('COM_JBLANCE_LOCATION'); ?> <span class="redfont">*</span>:</label>
			<?php 
			if($this->userInfo->id_location > 0){ ?>
				<div class="controls">
					<?php echo JblanceHelper::getLocationNames($this->userInfo->id_location); ?>
					<button type="button" class="btn btn-mini" onclick="editLocation();"><?php echo JText::_('COM_JBLANCE_EDIT'); ?></button>
				</div>
			<?php 	
			}
			?>
			<div class="controls controls-row" id="location_info">
				<?php 
				$attribs = array('class' => 'input-medium', 'data-level-id' => '1', 'onchange' => 'getLocation(this, \'project.getlocationajax\');');
				
				if($this->userInfo->id_location == 0){
					$attribs['class'] = 'input-medium required';
					$attribs['style'] = 'display: inline-block;';
				}
				else {
					$attribs['style'] = 'display: none;';
				}
				echo $select->getSelectLocationCascade('location_level[]', '', 'COM_JBLANCE_PLEASE_SELECT', $attribs, 'level1');
				?>
				<input type="hidden" name="id_location" id="id_location" value="<?php echo $this->userInfo->id_location; ?>" />
				<div id="ajax-container" class="dis-inl-blk"></div>	
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="postcode"><?php echo JText::_('COM_JBLANCE_ZIP_POSTCODE'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<input class="input-small required" type="text" name="postcode" id="postcode" value="<?php echo $this->userInfo->postcode; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="mobile"><?php echo JText::_('COM_JBLANCE_CONTACT_NUMBER'); ?> :</label>
			<div class="controls">
				<input class="input-large" type="text" name="mobile" id="mobile" value="<?php echo $this->userInfo->mobile; ?>" />
			</div>
		</div>
	</fieldset>
	
	<!-- Show the following profile fields only for JoomBri Profile -->
	<?php 
	$joombriProfile = false;
	$profileInteg = JblanceHelper::getProfile();
	$profileUrl = $profileInteg->getEditURL();
	if($profileInteg instanceof JoombriProfileJoombri){
		$joombriProfile = true;
	}
	
	if($joombriProfile){
		if(empty($this->fields)){
			echo '<p class="alert">'.JText::_('COM_JBLANCE_NO_PROFILE_FIELD_ASSIGNED_FOR_USERGROUP').'</p>';
		}
		$fields = JblanceHelper::get('helper.fields');		// create an instance of the class fieldsHelper
		
		$parents = $children = array();
		//isolate parent and childr
		foreach($this->fields as $ct){
			if($ct->parent == 0)
				$parents[] = $ct;
			else
				$children[] = $ct;
		}
			
		if(count($parents)){
			foreach($parents as $pt){ ?>
		<fieldset class="<?php echo $pt->class; ?>">
			<legend><?php echo JText::_($pt->field_title); ?></legend>
			<?php
			foreach($children as $ct){
				if($ct->parent == $pt->id){ ?>
			<div class="control-group">
					<?php
					$labelsuffix = '';
					if($ct->field_type == 'Checkbox') $labelsuffix = '[]'; //added to validate checkbox
					?>
					<label class="control-label" for="custom_field_<?php echo $ct->id.$labelsuffix; ?>"><?php echo JText::_($ct->field_title); ?><span class="redfont"><?php echo ($ct->required)? '*' : ''; ?></span>:</label>
				<div class="controls controls-row">
					<?php $fields->getFieldHTML($ct, $user->id); ?>
				</div>
			</div>
			<?php
				}
			} ?>
		</fieldset>
				<?php
			}
		}

	}	//end of $joombriProfile 'if'
	else {
		echo JText::sprintf('COM_JBLANCE_CLICK_HERE_FOR_OTHER_PROFILE', $profileUrl).'<BR>';
	}
	?>
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SAVE'); ?>" class="btn btn-primary" />
	</div>
	<input type="hidden" name="option" value="com_jblance">
	<input type="hidden" name="task" value="user.saveprofile">
	<input type="hidden" name="id" value="<?php echo $this->userInfo->id; ?>">
	<?php echo JHtml::_('form.token'); ?>
</form>
