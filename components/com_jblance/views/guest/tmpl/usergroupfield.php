<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/guest/tmpl/usergroupfield.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	User Groups (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');

 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
$doc->addStyleSheet("components/com_jblance/css/xiping_pricing.css");
 $doc->addStyleSheet("components/com_jblance/css/register/xp_register_final.css");
 $app = JFactory::getApplication();
 $user= JFactory::getUser();
 $model = $this->getModel();
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper

 //set the chosen plan in the session
 $session = JFactory::getSession();
 $ugid = $session->get('ugid', 0, 'register');
 $accountInfo 	= $session->get('userInfo', null, 'register');

 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 $userInfo = $jbuser->getUserGroupInfo(null, $ugid);

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

 //if the user is already registered, accoutnInfo will be empty.
 if(empty($accountInfo)){
 	$accountInfo['username'] = $user->username;
 	$accountInfo['name'] = $user->name;
 }

 $step = $app->input->get('step', 0, 'int');
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
var category_text=[]
<?php if($maxSkills > 0){ ?>
jQuery(document).ready(function($){
	if($("#id_category").length){
		$("#id_category").change(updateSkillCount);
		updateSkillCount();
	}


	$(".item").click(function(){

		if($(".list-category-group .select-item").length==15 && $(this).attr("data-category-type") == "1"){
			return false;
		}
		addCategoryInput($(this).attr('data-category-id'),$(this).attr("data-category-txt"));
		if($(this).find("div").hasClass("choose-img")){//增加
			$(this).find(".choose-img").removeClass('choose-img').addClass('select-img');
			$(this).addClass('select-item');$(this).find(".txt").addClass('select-txt');
			$(this).attr('data-category-type',0);
		}else{//减少
			$(this).find(".select-img").removeClass('select-img').addClass('choose-img');
			$(this).removeClass('select-item');$(this).find(".txt").removeClass('select-txt');
			$(this).attr('data-category-type',1);
		}

		$("#skill_left_span").text("请添加("+$(".list-category-group .select-item").length+"/"+<?php echo $maxSkills;?>+")");
		if($(".list-category-group .select-item").length>0){
			$(".category-add").html("<div class='txt'>完成添加</div>");

		}else{
			$('.category-add').html("<div class='add-img'></div><div class='txt'>添加</div>")
		}

	})
	$(".category-add").click(function(){
		//添加显示列表
		if($("#list-category").hasClass("list-category-hidden")){
			$("#list-category").removeClass("list-category-hidden");
			if($(".list-category-group .select-item").length>0){
			   $(".category-add").html("<div class='txt'>完成添加</div>");


			}else{

			   $(this).html("<div class='add-img'></div><div class='txt'>添加</div>")
			}

			//$(this).html("<div class='txt'>编辑</div>");
		}else{
			$("#list-category").addClass("list-category-hidden");
			if($(".list-category-group .select-item").length>0){
			 $(".category-add").html("<div class='txt'>编辑</div>");
			   var t=" : ";
			   category_text.forEach(function(item){
				   t +=item.category_name+"、";
				   console.log(item);
			   });
			   t = t.substring(0, t.length - 1);

			   $("#category-text").html(t);
			}else{

			   $(this).html("<div class='add-img'></div><div class='txt'>添加</div>")
			}


		};
	});
});

<?php } ?>

function updateSkillCount(){
	sel = jQuery("#id_category option:selected").length;
	jQuery("#skill_left_span").html(sel);
}

function addCategoryInput(value,txt){

	if(jQuery("#id-category-hidden-list").find("input").hasClass("id_category"+value)){//存在就去掉

		jQuery("#id-category-hidden-list").find("input[class=id_category"+value+"]").remove();
		category_text.forEach(function(item,index){

			if(item.id === value){
			  category_text.splice(index,1);
			}

		});
	}else{//添加
	jQuery("#id-category-hidden-list").append("<input type='hidden' name='id_category[]' value="+value+" class='id_category"+value+"'>");
	   category_text.push({'id':value,'category_name':txt});
	}

}
//-->
</script>
<?php
if($step)
	//echo JblanceHelper::getProgressBar($step);
?>
<div class="row-fluid">
    <div class="span12 pricing comparsion">
        <div class="head">
            <div class="register"><?php echo JText::_('COM_JBLANCE_REGISTER')?></div>
            <div class="register-text"><?php echo JText::_('COM_JBLANCE_WELCOME')?></div>
            <div class="register-step-3">
                <div class="img"></div>
            </div>
            <div class="register-step-txt">
                <div class="txt"><?php echo JText::_('COM_JBLANCE_SUBSCRIBETO')?></div>
                <div class="txt"><?php echo JText::_('COM_JBLANCE_ACCOUNT_REGISTER')?></div>
                <div class="txt"><?php echo JText::_('COM_JBLANCE_COMPLATE_MESSAGE')?></div>
            </div>
        </div>
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userGroup" class="xp-form form-validate form-horizontal" onsubmit="return validateForm(this);" enctype="multipart/form-data" novalidate>
<!--	<div class="jbl_h3title">--><?php //echo JText::_('COM_JBLANCE_PROFILE_INFO'); ?><!--</div>-->
	<div class="xp-register-final">
		<div class="xp-head"><?php echo JText::_('COM_JBLANCE_IMPROVE_ACCOUNT_INFORMATION')?></div>
		<div class="xp-content">
			<div class="xp-group">
				<label class="xp-left"><?php echo JText::_('COM_JBLANCE_USERNAME')?></label>
				<div class="xp-no-right">
					<?php echo $accountInfo['username']; ?>
				</div>
			</div>
			<div class="xp-group">
				<label class="xp-left"><?php echo JText::_('COM_JBLANCE_USERNICKNAME')?></label>
				<div class="xp-no-right">
					<?php echo $accountInfo['name']; ?>
				</div>
			</div>

			<!-- Company Name should be visible only to users who can post job -->
			<?php if($userInfo->allowPostProjects) : ?>
				<div class="xp-group">
					<label class="xp-left" for="biz_name"><span class="redfont">*</span>公司名称：</label>
					<div class="xp-right">
						<input class="input-medium required" type="text" name="biz_name" id="biz_name" value="" />
					</div>
				</div>
			<?php endif; ?>
			<!-- Skills and hourly rate should be visible only to users who can work/bid -->
			<?php if($userInfo->allowBidProjects) : ?>
				<div class="xp-group">
					<label class="xp-left" for="rate">小时收费：</label>
					<div class="xp-new-right">
						<input class="input-mini required validate-numeric" type="text" name="rate" id="rate" value="" placeholder='请填写'/>
						<span>元/小时</span>
					</div>
				</div>
				<div class="xp-group">
					<label class="xp-left" for="id_category"><span class="redfont">*</span>分类信息：</label>
					<div class="xp-right-category">
						<?php if($maxSkills > 0){ ?>
							<div class='category-right'><div id="skill_left_span" class="font14">请添加(<?php echo '0';?>/<?php echo $maxSkills; ?>)</div><div class="category-text" id='category-text'></div></div>

						<?php } ?>

						<?php
						//$attribs = 'class="input-medium required" size="20" multiple ';
						//$categtree = $select->getSelectCategoryTree('id_category[]', 0, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '', true);
						//echo $categtree;
						//$attribs = '';
						//$select->getCheckCategoryTree('id_category[]', array(), $attribs); ?>
						<?php
						//$attribs = "class='input-xxlarge required' multiple";
						//echo $select->getNewSelectCategoryTree('id_category[]');
						?>

						<div class='category-add'><div class='add-img'></div><div class='txt'>添加</div></div>
						<div id="list-category" class='list-category list-category-hidden'>
						  <div class='id-category-hidden-list' id="id-category-hidden-list"></div>
						    <?php
						     echo $select->getNewSelectCategoryTree('id_category[]');
						    ?>

						</div>
					</div>
				</div>
			<?php endif; ?>
            <div class="xp-group">
				<label class="xp-left" for="mobile"><span class="redfont">*</span>联系电话：</label>
				<div class="xp-right">
					<input class="input-large" type="text" name="mobile" id="mobile" value="" />
				</div>
			</div>


            <div class="xp-group">
				<label class="xp-left" for="level1"><span class="redfont">*</span>联系地址：</label>
				<div class="xp-right-select" id="location_info">
					<?php
					$attribs = array('class' => 'input-medium required', 'data-level-id' => '1', 'onchange' => 'getLocation(this, \'project.getlocationajax\');');

					echo $select->getSelectLocationCascade('location_level[]', '', 'COM_JBLANCE_PLEASE_SELECT', $attribs, 'level1');
					?>
					<input type="hidden" name="id_location" id="id_location" value="" />
					<div id="ajax-container" class="dis-inl-blk"></div>
				</div>
			</div>

			<div class="xp-group">
				<label class="xp-left" for="address"><span class="redfont">*</span>街道门牌：</label>
				<div class="xp-right-textarea">
					<textarea name="address" id="address" rows="3" class="required" style="resize: none;"></textarea>
				</div>
			</div>

			<div class="xp-group">
				<label class="xp-left" for="postcode"><span class="redfont">*</span>邮编：</label>
				<div class="xp-right">
					<input class="input-small required" type="text" name="postcode" id="postcode" value="" />
				</div>
			</div>




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
							<?php
							foreach($children as $ct){
								if($ct->parent == $pt->id){ ?>
									<div class="xp-group">
										<?php
										$labelsuffix = '';
										if($ct->field_type == 'Checkbox') $labelsuffix = '[]'; //added to validate checkbox
										//echo '<pre>';
										//print_r($ct);
										?>
										<label class="xp-left" for="custom_field_<?php echo $ct->id.$labelsuffix; ?>"><?php echo JText::_($ct->field_title); ?> <span class="redfont"><?php echo ($ct->required)? '*' : ''; ?></span>:</label>

										<?php $fields->getNewFieldHTML($ct); ?>

									</div>
									<?php
								}
							} ?>
						<?php
					}
				}
			}
			?>
		</div>

		<div class="button-group">
			<input type="submit" value="保存信息" class="btn btn-primary" />
		</div>
	</div>


	<input type="hidden" name="option" value="com_jblance">
	<input type="hidden" name="task" value="guest.saveusernew">
	<?php echo JHtml::_('form.token'); ?>
</form>
    </div>
</div>
