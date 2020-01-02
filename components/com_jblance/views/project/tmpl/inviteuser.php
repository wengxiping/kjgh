<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	5 July 2014
 * @file name	:	views/project/tmpl/inviteuser.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Invite Users for Private Invite project (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('formbehavior.chosen', '.advancedSelect');
 
 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 $doc->addScript("components/com_jblance/js/barrating.js");
 $doc->addScript("components/com_jblance/js/bootstrap-slider.js");
 $doc->addStyleSheet("components/com_jblance/css/barrating.css");
 $doc->addStyleSheet("components/com_jblance/css/slider.css");
 
 $app  = JFactory::getApplication();
 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 
 $rating	  = $app->input->get('rating', 0, 'int');
 $hourly_rate = $app->input->get('hourly_rate', '', 'string');
 
 $invited_user_id = explode(',', $this->project->invite_user_id);		//get the array of list of user ids already invited.
 
 $action = JRoute::_('index.php?option=com_jblance&view=project&layout=inviteuser&id='.$this->project->id);
 
 JblanceHelper::setJoomBriToken();
 ?>
<script>
<!--
function validateForm(f){
	if(!jQuery("input[name='invite_userid[]']:checked").length){
		alert('<?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_USERS_FROM_THE_LIST', true); ?>');
		return false;
	}
	else {
		var form = document.frmInviteUser;
		form.task.value = 'project.saveinviteuser';
		form.submit();
	}
}

jQuery(document).ready(function($) {
    $("#rating").barrating("show", {
        showSelectedRating:false,
        onSelect: function(value, text, event) {
            if (typeof(event) !== 'undefined') {
              // rating was selected by a user
              //console.log(event.target);
            } else {
              // rating was selected programmatically
              // by calling `set` method
            	document.frmInviteUser.submit();
            }
        }
    });

	/* $("#hourly_rate").sliderz({
	
	}); */

	$('#hourly_rate').sliderz().on('slideStop', function(e){
		document.frmInviteUser.submit();
	  });
});
//-->
</script>
<form action="<?php echo $action; ?>" method="post" name="frmInviteUser" id="frmInviteUser" class="form-validate form-inline" onsubmit="return validateForm(this);" enctype="multipart/form-data">
	<div class="row-fluid top10">
		<div class="span6">
      		<div class="control-group">
				<label class="control-label" for="rating"><?php echo JText::_('COM_JBLANCE_RATING'); ?></label>
				<div class="controls brating" title="<?php echo JText::_('COM_JBLANCE_RATING_ABOVE'); ?>">
					<?php echo $select->getSelectRating('rating', $rating); ?>
				</div>
			</div>
		</div>
		<div class="span6">
      		<div class="control-group">
				<label class="control-label" for="project_type"><?php echo JText::_('COM_JBLANCE_HOURLY_RATE'); ?></label>
				<div class="controls">
					<label class="radio">
						<?php 
						$projectModel = JModelLegacy::getInstance('user', 'JblanceModel');
						$limit = $projectModel->getMaxMinHourlyLimit(); 
						$sliderValue = (empty($hourly_rate)) ? $limit->minlimit.','.$limit->maxlimit : $hourly_rate;
						?>
						<b style="margin-right: 15px;"><?php echo JblanceHelper::formatCurrency($limit->minlimit, true, false, 0); ?></b>
						<input type="text" name="hourly_rate" id="hourly_rate" class="input-xlarge" value="<?php echo $hourly_rate; ?>" data-slider-min="<?php echo $limit->minlimit; ?>" data-slider-max="<?php echo $limit->maxlimit; ?>" data-slider-step="5" data-slider-value="[<?php echo $sliderValue; ?>]" style="display: none; margin-top: 20px;" />
		 				<b style="margin-left: 15px;"><?php echo JblanceHelper::formatCurrency($limit->maxlimit, true, false, 0); ?></b>		
					</label>
				</div>
			</div>
		</div>
	</div>	
	
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_INVITE_USERS').' : '.$this->project->project_title; ?></div>
	<?php
	for ($i=0, $x=count($this->rows); $i < $x; $i++){
		$row = $this->rows[$i];
		
		$checked = (in_array($row->user_id, $invited_user_id)) ? 'checked' : '';
		$status = $jbuser->isOnline($row->user_id);		//get user online status
		
		$isFavourite = JblanceHelper::checkFavorite($row->user_id, 'profile');
		?>
	<div class="media">
	
		<input type="checkbox" name="invite_userid[]" id="invite_userid_<?php echo $row->user_id; ?>" value="<?php echo $row->user_id; ?>" <?php echo $checked; ?> style="float: left; margin: 20px 10px 0 0;"/>
		<?php
		$attrib = 'width=48 height=48 class="img-polaroid"';
		$avatar = JblanceHelper::getLogo($row->user_id, $attrib);
		echo !empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar, '', '', ' pull-left') : '&nbsp;' ?>
		<div class="media-body">
			<h5 class="media-heading">
				<?php $stats = ($status) ? 'online' : 'offline'; ?>
				<span class="online-status <?php echo $stats; ?>" title="<?php echo JText::_('COM_JBLANCE_'.strtoupper($stats)); ?>"></span>
				<?php echo LinkHelper::GetProfileLink($row->user_id, $row->name); ?> <small><?php echo $row->username; ?></small>
				<span id="fav-msg-<?php echo $row->user_id; ?>" class="pull-right">
					<?php if($isFavourite > 0) : ?>
					<a onclick="favourite('<?php echo $row->user_id; ?>', -1,'profile');" href="javascript:void(0);" class="btn btn-mini btn-danger"><span class="jbf-icon-minus-circle"></span> <?php echo JText::_('COM_JBLANCE_REMOVE_FAVOURITE')?></a>
					<?php else : ?>
					<a onclick="favourite('<?php echo $row->user_id; ?>', 1,'profile');" href="javascript:void(0);" class="btn btn-mini"><span class="jbf-icon-plus-circle"></span> <?php echo JText::_('COM_JBLANCE_ADD_FAVOURITE')?></a>
					<?php endif; ?>
				</span>
			</h5>
			<div>
				<?php $rate = JblanceHelper::getAvarageRate($row->user_id, true); ?>
				<?php if($row->rate > 0){ ?>
				<span class="font14" style="margin-left: 10px;"><?php echo JblanceHelper::formatCurrency($row->rate, true, true, 0).'/'.JText::_('COM_JBLANCE_HR'); ?></span>
				<?php } ?>
			</div>
			<?php if(!empty($row->id_category)){ ?>
			<div class="boldfont font12">
				<?php echo JText::_('COM_JBLANCE_SKILLS'); ?>: <?php echo JblanceHelper::getCategoryNames($row->id_category); ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="lineseparator"></div>
 	<?php 
	}
 ?>
	<div class="pagination">
		<?php echo $this->pageNav->getListFooter(); ?>
	</div>
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_INVITE_USERS'); ?>" class="btn btn-primary" />
	</div>
	<input type="hidden" name="option" value="com_jblance" />	
	<input type="hidden" name="view" value="project" />
	<input type="hidden" name="layout" value="inviteuser" />
	<input type="hidden" name="task" value="" />	
	<input type="hidden" name="id" value="<?php echo $this->project->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
