<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	29 March 2012
 * @file name	:	modules/mod_jblancesearch/tmpl/default.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
// no direct access
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('formbehavior.chosen', '.advancedSelect');

 $document = JFactory::getDocument();
 $direction = $document->getDirection();
 $config = JblanceHelper::getConfig();
 $app  		 = JFactory::getApplication();

 if($config->loadBootstrap){
 	JHtml::_('bootstrap.loadCss', true, $direction);
 }
 $document->addStyleSheet("modules/mod_jblancesearch/css/style.css");
$document->addStyleSheet("modules/mod_jblancesearch/css/new-style.css");

 $currencysym = $config->currencySymbol;

 $set_Itemid	= intval($params->get('set_itemid', 0));
 $Itemid = ($set_Itemid > 0) ? '&Itemid='.$set_Itemid : '';

 $sh_category 	= $params->get('category', 1);
 $sh_status 	= $params->get('status', 1);
 $sh_budget 	= $params->get('budget', 1);

 $keyword	  = $app->input->get('keyword', '', 'string');
 $status	  = $app->input->get('status', 'COM_JBLANCE_OPEN', 'string');
 $id_categ	  = $app->input->get('id_categ', array(), 'array');
 $categoryType=  $app->input->get('view','','string');
 //check of all other three fields are hidden
 $isOnlyKeywords = false;
 if($sh_category == 0 && $sh_status == 0 && $sh_budget == 0)
 	$isOnlyKeywords = true;
?>
<script type="text/javascript">
    <!--
    jQuery(document).ready(function($){
        $("#search_type").on("change", function(){
            var val = $("#search_type").val();
            if(val == "project"){
                $("input[name='view']").val('project');
                $("input[name='layout']").val('searchproject');
            }
            else if(val == "service"){
                $("input[name='view']").val('service');
                $("input[name='layout']").val('listservice');
            }
        });
        $("#search_type").hover(function(){
          $(".list-search-content").removeClass('xp-search-status');
          $(".search-down-img").removeClass('search-down-img').addClass('search-up-img');
        },function(){
            $(".list-search-content").addClass('xp-search-status');
            $(".search-up-img").removeClass('search-up-img').addClass('search-down-img');
        })
    });
    function click_action(value){
        if(value == "project"){
            jQuery("input[name='view']").val('project');
            jQuery("input[name='layout']").val('searchproject');
            jQuery("#search_type .service-name").html('需求');
            jQuery(".list-search-content").addClass('xp-search-status');
            jQuery(".search-up-img").removeClass('search-up-img').addClass('search-down-img');
        }
        else if(value == "service"){
            jQuery("input[name='view']").val('service');
            jQuery("input[name='layout']").val('listservice');
            jQuery("#search_type .service-name").html('服务商');
            jQuery(".list-search-content").addClass('xp-search-status');
            jQuery(".search-down-img").removeClass('search-up-img').addClass('search-down-img');
        }
    }
    //-->
</script>
<?php if($isOnlyKeywords == false) : ?>
    <div class="xp-search-right">
        <div class="xp-top">
            <form action="index.php" method="get" name="userForm" class="xp-search-right-left">
                <div class="show-select" id="search_type">
                    <div class="default-txt">
                        <?php if($categoryType != 'project'){?>
                        <div class="service-name">服务商</div>
                        <?php }else{
                         ?><div class="service-name">需求</div>
                         <?php
                        }?>
                        <div class="search-down-img"><img src="/images/search-down.png"></div></div>
                    <div class="list-search-content xp-search-status">
                        <div class="item" data-value="project" onclick="click_action('project')">需求</div>
                        <div class="item" data-value="service" onclick="click_action('service')">服务商</div>
                    </div>
                </div>
<!--            <select name="search_type" id="search_type" class="new-input-select">-->
<!--               <option value="project" >--><?php //echo JText::_('Project'); ?><!--</option>-->
<!--               <option value="service" >--><?php //echo JText::_('Service'); ?><!--</option>-->
<!--            </select>-->

                <div class="r-input"><input type="text" name="keyword" placeholder="请输入搜索字段" autocomplete="off"></div>
                <div class="r-btn"><button type="submit">搜索</button></div>
                <input type="hidden" name="option" value="com_jblance"/>
                <input type="hidden" name="view" value="project"/>
                <input type="hidden" name="layout" value="searchproject"/>
<!--                <input type="hidden" name="Itemid" value="--><?php //echo $set_Itemid; ?><!--"/>-->
            </form>
            <div class="xp-search-right-right">
                <div class="item"><a href="<?php echo JRoute::_('index.php?option=com_jblance&view=project&layout=editproject')?>">免费发布需求</a></div>
                <div class="item">或</div>
                <div class="item"><a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=categories&layout=events')?>">推广活动赚佣金</a></div>
            </div>
        </div>
        <div class="xp-bottom">
            <a href="<?php echo JRoute::_('index.php?keyword=亚马逊&option=com_jblance&view=project&layout=searchproject')?>">亚马逊</a>
            <a href="<?php echo JRoute::_('index.php?keyword=速卖通&option=com_jblance&view=project&layout=searchproject')?>">速卖通</a>
            <a href="<?php echo JRoute::_('index.php?keyword=eBay&option=com_jblance&view=project&layout=searchproject')?>">eBay</a>
            <a href="<?php echo JRoute::_('index.php?keyword=Shopee&option=com_jblance&view=project&layout=searchproject')?>">Shopee</a>
        </div>
    </div>

<!--  <form action="index.php" method="get" name="userForm" class="serarch-content">-->
<!--  	<div class='new_search'>-->
<!--		--><?php //if($sh_category == 1){ ?>
<!--			<div class="left_item">-->
<!--				<label class="control-label" for="id_categ">--><?php //echo JText::_('MOD_JBLANCE_CATEGORY'); ?><!--: </label>-->
<!--				<div class="controls">-->
<!--					--><?php //$list_categ = ModJblanceSearchHelper::getListJobCateg($id_categ);
//					echo $list_categ; ?>
<!--				</div>-->
<!--				<div class="star"></div>-->
<!--                <div class="search_input">-->
<!--                    <label class="control-label" for="keyword">--><?php //echo JText::_('MOD_JBLANCE_ENTER_KEYWORD'); ?><!--: </label>-->
<!--                    <input type="text" class="input-large" name="keyword" id="keyword" value="--><?php //echo $keyword; ?><!--" />-->
<!--                    <input type='text' placeholder="商标注册"/>-->
<!--                </div>-->
<!--			</div>-->
<!--		--><?php //} ?>
<!--		<div class="right_item">-->
<!--	        <div class="search_btn"><input type="submit" class="right_item_search_btn" value="--><?php //echo JText::_('MOD_JBLANCE_SEARCH'); ?><!--" /></div>-->
<!--	        <div class="right_item1"><a href='javascript:void(0);'>免费发布需求</a></div>-->
<!--	        <div class="right_item2"><span>或</span></div>-->
<!--	        <div class="right_item3"><a href="javascript:void(0);">免费找官方推荐</a></div>-->
<!--	    </div>-->
<!--    </div>-->

<!--      <div class="xp-search-right">-->
<!--          <div class="xp-top">-->
<!--              <form method="post" action="" class="xp-search-right-left">-->
<!--                  <div class="r-input"><input type="text" placeholder="请输入搜索字段"></div>-->
<!--                  <div class="r-btn"><button type="submit">搜索</button></div>-->
<!--              </form>-->
<!---->
<!--              <div class="xp-search-right-right">-->
<!--                  <div class="item"><a href="#">免费发布需求</a></div>-->
<!--                  <div class="item">或</div>-->
<!--                  <div class="item"><a href="#">免费找官方推荐</a></div>-->
<!--              </div>-->
<!--          </div>-->
<!---->
<!--          <div class="xp-bottom">-->
<!--              <a href="#">亚马逊</a><a href="#">速卖通</a><a href="#">eBay</a><a href="#">Shopee</a>-->
<!--          </div>-->
<!--      </div>-->

<!--	<input type="hidden" name="option" value="com_jblance"/>-->
<!--	<input type="hidden" name="view" value="project"/>-->
<!--	<input type="hidden" name="layout" value="searchproject"/>-->
<!--	<input type="hidden" name="Itemid" value="--><?php //echo $set_Itemid; ?><!--"/>-->

<!--</form>-->


<?php else : ?>

<form class="form-search text-center" id="search_form" action="index.php">
	<div class="">
		<input type="text" class="span4" name="keyword" id="keyword" placeholder="<?php echo JText::_('MOD_JBLANCE_SEARCH_KEYWORD_TIPS'); ?>" value="<?php echo $keyword; ?>" />
		<select name="search_type" id="search_type" class="input-small">
			<option value="project" ><?php echo JText::_('Project'); ?></option>
			<option value="service" ><?php echo JText::_('Service'); ?></option>
		</select>
		<button type="submit" class="btn btn-primary"><?php echo JText::_('MOD_JBLANCE_SEARCH'); ?></button>
	</div>

	<input type="hidden" name="option" value="com_jblance"/>
	<input type="hidden" name="view" value="project"/>
	<input type="hidden" name="layout" value="searchproject"/>
	<input type="hidden" name="Itemid" value="<?php echo $set_Itemid; ?>"/>



</form>
<?php endif; ?>
