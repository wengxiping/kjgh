<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	13 March 2012
 * @file name	:	views/admproject/tmpl/edituser.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows list of Users (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
  
 use Joomla\Utilities\ArrayHelper;
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('formbehavior.chosen', 'select.advancedSelect');
 JHtml::_('formbehavior.chosen', '#id_category', null, array('placeholder_text_multiple'=>JText::_('COM_JBLANCE_PLEASE_SELECT_SKILLS_FROM_THE_LIST')));
 JHtml::_('behavior.tabstate');

 $app  	 = JFactory::getApplication();
 $user	 = JFactory::getUser();
 $model  = $this->getModel();
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 $fields = JblanceHelper::get('helper.fields');		// create an instance of the class FieldsHelper
 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class userHelper
 $cid 	 = $app->input->get('cid', array(), 'array');
 $cid	 = ArrayHelper::toInteger($cid);
 
 $doc = JFactory::getDocument();
 $doc->addScript(JURI::root()."components/com_jblance/js/utility.js");
 $doc->addScript(JURI::root()."components/com_jblance/js/cropit.js");
 $doc->addStyleSheet(JURI::root().'components/com_jblance/css/style.css');
 
 $config = JblanceHelper::getConfig();
 $dformat = $config->dateFormat;
 $currencysym = $config->currencySymbol;
 $currencycod = $config->currencyCode;
 
 $hasJBProfile = JblanceHelper::hasJBProfile($cid[0]);	//check if the user has JoomBri profile
 
 if($hasJBProfile){
	 $userInfo = $jbuser->getUserGroupInfo($cid[0], null);
 }
 
 $jbuserInfo 	= $jbuser->getUser($this->row->user_id);
 $upload_type = (empty($jbuserInfo->picture)) ? 'NO_UPLOAD_CROP' : 'CROP_ONLY';
 
 JText::script('COM_JBLANCE_CLOSE');
 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
jQuery(document).ready(function($){
	JoomBri.uploadCropPicture('admproject.uploadpicture', '<?php echo JblanceHelper::getLogoUrl($this->row->user_id, ""); ?>', '<?php echo JblanceHelper::getLogoUrl($this->row->user_id, "original"); ?>');
});

Joomla.submitbutton = function(task){
	if (task == 'admproject.canceluser' || document.formvalidator.isValid(document.getElementById('edituser-form'))) {
		Joomla.submitform(task, document.getElementById('edituser-form'));
	}
	else {
		alert('<?php echo $this->escape(JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY'));?>');
	}
}
function editLocation(){
	jQuery("#level1").css("display", "inline-block").addClass("required");
}
//-->
</script>
<form action="index.php" method="post" name="adminForm" id="edituser-form" class="form-validate" enctype="multipart/form-data">
<div class="form-horizontal">
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JBLANCE_GENERAL', true)); ?>
	<div class="row-fluid">
		<div class="span7">
			<fieldset>
				<legend><?php echo JText::_('COM_JBLANCE_USER_INFORMATION'); ?></legend>
				<div class="control-group">
					<label class="control-label" for="username"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?><span class="redfont">*</span>:</label>
					<div class="controls">
						<?php echo $this->lists;?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="name"><?php echo JText::_('COM_JBLANCE_NAME'); ?><span class="redfont">*</span>:</label>
					<div class="controls">
						<input class="input-large required" type="text" name="name" id="name" size="50" maxlength="100" value="<?php echo $this->row->name; ?>" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="ug_id"><?php echo JText::_('COM_JBLANCE_USER_GROUP'); ?><span class="redfont">*</span>:</label>
					<div class="controls">
						<?php echo $this->grpLists;
						if($hasJBProfile){
						    $tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_USER_GROUP'), JText::_('COM_JBLANCE_CHANGE_USERGROUP_WARNING'));
						?>
						<span class="hasTooltip" title="<?php echo $tipmsg; ?>"><i class="icon-question-sign"></i></span>
						<?php 
                        }?>
					</div>
				</div>
				<!-- Company Name should be visible only to users who can post job and has JoomBri profile -->
				<?php if($hasJBProfile && $userInfo->allowPostProjects) : ?>
				<div class="control-group">
					<label class="control-label" for="biz_name"><?php echo JText::_('COM_JBLANCE_BUSINESS_NAME'); ?><span class="redfont">*</span>:</label>
					<div class="controls">
						<input class="input-large required" type="text" name="biz_name" id="biz_name" size="50" maxlength="100" value="<?php echo $this->row->biz_name; ?>" />
					</div>
				</div>
				<?php endif; ?>
				<!-- Skills and hourly rate should be visible only to users who can work/bid -->
				<?php if($hasJBProfile && $userInfo->allowBidProjects) : ?>
				<div class="control-group">
					<label class="control-label" for="rate"><?php echo JText::_('COM_JBLANCE_HOURLY_RATE'); ?><span class="redfont">*</span>:</label>
					<div class="controls">
						<div class="input-prepend input-append">
							<span class="add-on"><?php echo $currencysym; ?></span>
							<input class="input-mini required validate-numeric" type="text" name="rate" id="rate" value="<?php echo $this->row->rate; ?>" />
							<span class="add-on"><?php echo $currencycod.' / '.JText::_('COM_JBLANCE_HOUR'); ?></span>
						</div>						
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="id_category"><?php echo JText::_('COM_JBLANCE_SKILLS'); ?><span class="redfont">*</span>:</label>
					<div class="controls">
						<?php 
						//$attribs = '';
						//$select->getCheckCategoryTree('id_category[]', explode(',', $this->row->id_category), $attribs); ?>
						<?php 
						$attribs = "class='input-xxlarge required' size='5' MULTIPLE";
						echo $select->getSelectCategoryTree('id_category[]', explode(',', $this->row->id_category), '', $attribs, '', true); ?>
					</div>
				</div>
				<?php endif; ?>
			</fieldset>
		</div>
		<div class="span5">
			<fieldset>
				<legend><?php echo JText::_('COM_JBLANCE_PROFILE_PICTURE'); ?></legend>
				<div class="row-fluid">
					<div class="span12">
						<div class="cropit-image-view">
							<input type="file" name="profile_file" class="cropit-image-input" style="display: none;" />
							<div class="cropit-preview-container">
								<div class="cropit-preview"></div>
							</div>
							
							<div class="slider-wrapper">
								<span class="icon icon-image font14"></span>
								<input type="range" class="cropit-image-zoom-input" min="0" max="1" step="0.01">
								<span class="icon icon-image font20"></span>
							</div>
						</div>
						<div class="btns horizontal">
							<div id="upload-message"></div>
							<button type="button" class="btn select-image-btn"><?php echo JText::_('COM_JBLANCE_UPLOAD_NEW'); ?></button>
							<button type="button" class="btn btn-success crop-save" style="/*display: none;*/"><?php echo JText::_('COM_JBLANCE_CROP_AND_SAVE'); ?></button>
							<button type="button" class="btn btn-danger remove-picture" data-user-id="<?php echo $this->row->user_id; ?>" data-remove-task="admproject.removepicture"><?php echo JText::_('COM_JBLANCE_REMOVE_PICTURE'); ?></button>
							<input type="hidden" name="upload_type" id="upload_type" value="<?php echo $upload_type; ?>" />
						</div>
					</div>
				</div>
				<hr class="hr-condensed">
				<div class="row-fluid">
					<div class="span12">
						<div class="">
							<strong><?php echo JText::_('COM_JBLANCE_THUMBNAIL'); ?>:</strong><br>
							<div class="current-profile-picture">
								<div class="cropit-preview" style="cursor: auto;"></div>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of general tab -->
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'profile', JText::_('COM_JBLANCE_PROFILE', true)); ?>
	<div class="row-fluid">
		<div class="span6">	
			<fieldset>
				<legend><?php echo JText::_('COM_JBLANCE_CONTACT_INFORMATION'); ?></legend>
				<div class="control-group">
					<label class="control-label" for="address"><?php echo JText::_('COM_JBLANCE_ADDRESS'); ?> <span class="redfont">*</span>:</label>
					<div class="controls">
						<textarea name="address" id="address" rows="3" class="input-xlarge required"><?php echo $this->row->address; ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="level1"><?php echo JText::_('COM_JBLANCE_LOCATION'); ?> <span class="redfont">*</span>:</label>
					<?php 
					if($this->row->id_location > 0){ ?>
						<div class="controls">
							<?php echo JblanceHelper::getLocationNames($this->row->id_location); ?>
							<button type="button" class="btn btn-mini" onclick="editLocation();"><?php echo JText::_('JACTION_EDIT'); ?></button>
						</div>
					<?php 	
					}
					?>
					<div class="controls controls-row" id="location_info">
						<?php 
						$attribs = array('class' => 'input-medium', 'data-level-id' => '1', 'onchange' => 'getLocation(this, \'admproject.getlocationajax\');');
						
						if($this->row->id_location == 0){
							$attribs['class'] = 'input-medium required';
							$attribs['style'] = 'display: inline-block;';
						}
						else {
							$attribs['style'] = 'display: none;';
						}
						echo $select->getSelectLocationCascade('location_level[]', '', 'COM_JBLANCE_PLEASE_SELECT', $attribs, 'level1');
						?>
						<input type="hidden" name="id_location" id="id_location" value="<?php echo $this->row->id_location; ?>" />
						<div id="ajax-container" class="dis-inl-blk"></div>	
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="postcode"><?php echo JText::_('COM_JBLANCE_ZIP_POSTCODE'); ?> <span class="redfont">*</span>:</label>
					<div class="controls">
						<input class="input-small required" type="text" name="postcode" id="postcode" value="<?php echo $this->row->postcode; ?>" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="mobile"><?php echo JText::_('COM_JBLANCE_CONTACT_NUMBER'); ?> :</label>
					<div class="controls">
						<input class="input-large" type="text" name="mobile" id="mobile" value="<?php echo $this->row->mobile; ?>" />
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span6">
			<?php 
			if(empty($this->fields)){
				echo '<div class="alert alert-error">'.JText::_('COM_JBLANCE_NO_PROFILE_FIELD_ASSIGNED_FOR_USERGROUP').'</div>';
			}
			
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
			<fieldset>
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
						<?php $fields->getFieldHTML($ct, $cid[0]); ?>
					</div>
				</div>
				<?php
					}
				} ?>
			</fieldset>
					<?php
				}
			}
		?>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of profile tab -->
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'transaction', JText::_('COM_JBLANCE_TRANSACTIONS_HISTORY', true)); ?>
	<div class="row-fluid">
		<div class="span5">
			<fieldset>
				<legend><?php echo JText::_('COM_JBLANCE_ADD_DEDUCT_FUND'); ?></legend>
				<div class="control-group">
		    		<label class="control-label"><?php echo JText::_('COM_JBLANCE_TOTAL_AVAILABLE_BALANCE'); ?>:</label>
					<div class="controls">
						<?php
						$totalFund = JblanceHelper::getTotalFund($this->row->user_id);
						echo JblanceHelper::formatCurrency($totalFund); ?>
					</div>
			  	</div>
				<div class="control-group">
		    		<label class="control-label" for="fund"><?php echo JText::_('COM_JBLANCE_FUNDS'); ?>:</label>
					<div class="controls">						
						<input class="input-small" type="text" name="fund" id="fund" maxlength="255" value="0" />
					</div>
			  	</div>
				<div class="control-group">
		    		<label class="control-label" for="type_fund"><?php echo JText::_('COM_JBLANCE_TYPE'); ?>:</label>
					<div class="controls">						
						<select name="type_fund" class="input-small advancedSelect">
							<option value="p"><?php echo JText::_('COM_JBLANCE_ADD'); ?></option>
							<option value="m"><?php echo JText::_('COM_JBLANCE_DEDUCT'); ?></option>
						</select>
					</div>
			  	</div>
				<div class="control-group">
		    		<label class="control-label" for="desc_fund"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?>:</label>
					<div class="controls">						
						<input class="input-large" type="text" name="desc_fund" id="desc_fund" maxlength="255" />
					</div>
			  	</div>
			</fieldset>
		</div>
		<div class="span7">
			<fieldset>
				<legend><?php echo JText::_('COM_JBLANCE_TRANSACTIONS_HISTORY'); ?></legend>
				<div style="max-height: 800px; overflow: auto;">
					<table class="table table-striped table-bordered">
						<thead>
							<tr class="jbj_rowhead">
								<th>
									<?php echo '#'; ?>
								</th>
								<th width="15%" align="left">
									<?php echo JText::_('COM_JBLANCE_DATE'); ?>
								</th>
								<th width="50%" align="left">
									<?php echo JText::_('COM_JBLANCE_TRANSACTION'); ?>
								</th>
								<th width="15%" align="left">
									<?php echo JText::_('COM_JBLANCE_FUND_IN'); ?>
								</th>
								<th width="15%" align="left">
									<?php echo JText::_('COM_JBLANCE_FUND_OUT'); ?>
								</th>				
								<th width="15%" align="left">
								</th>				
							</tr>
						</thead>
						<tbody>
						<?php
						for ($i=0, $n=count($this->trans); $i < $n; $i++) {
							$tran = $this->trans[$i];
							?>
							<tr id="tr_trans_<?php echo $tran->id; ?>">
								<td>
									<?php echo $i+1; ?>
								</td>
								<td>
									<?php  echo JHtml::_('date', $tran->date_trans, $dformat); ?>				
								</td>
								<td>
									<?php echo $tran->transaction; ?>
								</td>
								<td align="right">
									<?php echo $tran->fund_plus > 0  ? $tran->fund_plus : " "; ?> 
								</td>
								<td align="right">
									<?php echo $tran->fund_minus > 0  ? $tran->fund_minus : " "; ?> 
								</td>
								<td align="center">
									<a class="remFeed btn btn-micro" onclick="removeTransaction('<?php echo $tran->id; ?>');" href="javascript:void(0);" title="<?php echo JText::_('COM_JBLANCE_REMOVE'); ?>"><i class="icon-unpublish"></i></a>
								</td>				
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</fieldset>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of transaction tab -->
	
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	
	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="view" value="admproject" />
	<input type="hidden" name="layout" value="edituser" />
	<input type="hidden" name="task" value="">
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="user_id" id="user_id" value="<?php echo $cid[0]; ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid[0]; ?>">
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
