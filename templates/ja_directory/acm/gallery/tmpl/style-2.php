<?php
/**
 * ------------------------------------------------------------------------
 * Uber Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$doc->addScript (T3_TEMPLATE_URL.'/acm/gallery/js/isotope.pkgd.min.js');
$doc->addScript (T3_TEMPLATE_URL.'/acm/gallery/js/ekko-lightbox.js');
$doc->addScript (T3_TEMPLATE_URL.'/acm/gallery/js/imagesloaded.pkgd.min.js');
$doc->addStyleSheet(T3_TEMPLATE_URL."/acm/gallery/css/new-style.css");
//$doc->addScript(T3_TEMPLATE_URL."/acm/gallery/js/new-script.js");
?>

<?php
	$col 					= $helper->get('col') ;
	$btnText			= $helper->get('btn-text');
	$btnClass			= $helper->get('btn-class');
	$btnLink			= $helper->get('btn-link');
	$style							= $helper->get('acm-style');
	$col 								= $helper->get('col') ;
	$hoverAnimation			= $helper->get('hover-animation');

	if(!$hoverAnimation) {
		$hoverAnimation = 'none';
	}

	$blockImg 				= $helper->get('block-bg');
	$blockImgBg  			= 'background-image: url('.$blockImg.'); background-repeat: no-repeat; background-size: cover; background-position: center center;';
?>
<div class="xp-section-inner <?php echo $style; ?> <?php echo $helper->get('block-extra-class'); ?>" <?php if($blockImg): echo 'style="'.$blockImgBg.'"'; endif; ?>>
	<div class="xp-content">
        <?php if($module->showtitle || $helper->get('block-intro')): ?>
        <div class="header-page">
            <div class="section-title ">
                <img src="templates/ja_directory/acm/gallery/images/left-arrow.png">
                <?php if($module->showtitle): ?>
                    <span><?php echo $module->title ?></span>
                <?php endif; ?>
                <?php if($helper->get('block-intro')): ?>
                    <p class="container-sm section-intro hidden-xs"><?php echo $helper->get('block-intro'); ?></p>
                <?php endif; ?>
            </div>
            <?php if($helper->getRows('gallery.img')>8):?>
            <div class="xp-content-page">
                <div class="xp-pageleft" onclick="javascript:action_page(<?php echo $helper->getRows('gallery.img');?>,<?php echo $module->id;?>,0,8)"><img src="./images/pre.png"/></div>
                <div class="xp-pageright" onclick="javascript:action_page(<?php echo $helper->getRows('gallery.img');?>,<?php echo $module->id;?>,1,8)"><img src="./images/next.png"/></div>
            </div>
             <?php endif;?>
        </div>

        <?php endif; ?>
        <div class="xp-gallery-content acm-gallery style-2 style-<?php echo $hoverAnimation; ?>">
            <div class="isotope-layout <?php echo $helper->get('fullwidth'); ?>">
                <div class="xp-content-border xp-content-border-new<?php echo $module->id;?>">
                    <?php if($helper->get('text-1')) :?>
                        <div class="mask"></div>
                    <?php endif ; ?>
                    <?php
                        $count = $helper->getRows('gallery.img');
                       for ($i=0; $i<$count; $i++) : ?>
                        <?php
                        $itemsize 		= $helper->get('gallery.selectitem', $i);
                        $itemTitle		= $helper->get('gallery.title', $i);
                        $itemDetails	= $helper->get('gallery.details', $i);
                        $itemLink			= $helper->get('gallery.link', $i);
                        ?>
                        <?php if($helper->get ('gallery.img', $i)):?>
                            <div class="list-content  xp-row-gallery<?php  if($i==0){echo 0,' show';}else{echo floor($i/8)==0?floor($i/8)." show":floor($i/8);}?>">
                                <a class="item-mask" href="<?php echo $itemLink; ?>"></a>
                                <div class="item-image">
                                    <?php if($hoverAnimation=='swiper'): ?>
                                        <a class="item-mask" href="<?php echo $itemLink; ?>"></a>
                                    <?php endif ; ?>
                                    <?php if($hoverAnimation!='swiper'): ?>
                                        <?php if($itemLink):?><a href="<?php echo $itemLink; ?>" title="<?php echo $itemTitle; ?>"><?php endif ; ?>
                                        <img src="<?php echo $helper->get ('gallery.img', $i) ?>" >
                                        <?php if($itemLink):?></a><?php endif ; ?>
                                    <?php endif ; ?>
                                </div>
                                <?php if($itemTitle || $itemDetails): ?>
                                    <div class="item-details">
                                        <?php if($itemTitle): ?><h4><?php if($itemLink):?><a href="<?php echo $itemLink; ?>" title="<?php echo $itemTitle; ?>"><?php endif ; ?><?php echo $itemTitle; ?><?php if($itemLink):?></a><?php endif ; ?></h4><?php endif ; ?>
                                        <?php if($itemDetails): ?><span><?php echo $itemDetails; ?></span><?php endif ; ?>
                                    </div>
                                <?php endif ; ?>
                            </div>
                        <?php endif ; ?>
                    <?php endfor ?>

                </div>

                <?php if($helper->get('text-1')): ?>
                    <div class="caption">
                        <p><?php echo $helper->get('text-1') ?></p>
                    </div>
                <?php endif ;?>

                <?php if($btnText): ?>
                <a class="btn <?php echo $btnClass; ?>" href="<?php echo $btnLink; ?>"><?php echo $btnText; ?> <i class="fa fa-angle-right"></i></a>
                <?php endif ;?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var _current = [];
    var _class = "";
    var _id = [];
    function action_page(total, id,status,pre_page) {
        if (_current.length > 0 || !status) {
            _current.forEach(function(item,index){
                //循环数据存在和不存在 更新和添加
                if(_id.indexOf(id)<0){//表示数组中没有该id存在
                    _id.push(id);
                    _current.push({id: id, current_page: 1});
                    _class = 1;
                    displayShowHiddenAction(id,_class,status);
                    return;
                }else{//更新，并结束
                    if(item.id == id){
                        item.current_page = pageNumStatus(total,status,item.current_page,pre_page);
                        _class = item.current_page;
                        displayShowHiddenAction(id,_class,status);
                        return;
                    }
                }
            });
        } else {
            _id.push(id);
            _class = 1;
            _current.push({id: id, current_page: 1});
            displayShowHiddenAction(id,_class,status);
        }

    }
    function pageNumStatus(total,status,num,pre_page){
        var totalPage = Math.ceil(total/pre_page) - 1;
        if(status == 1){
            if(num<totalPage){
                num = parseInt(num) +1;
            }else{
                num = totalPage;
            }
        }else{
            if(num == 0){
                num = 0;
            }else{
                num = num - 1;
            }
        }
        return num;
    }
    function displayShowHiddenAction(objectClassId,num){

        var object = jQuery(".xp-content-border-new"+objectClassId).find(".list-content");
        object.each(function(index,item){
            jQuery(item).removeClass('show');
            console.log(item,num);
            if(jQuery(item).hasClass('xp-row-gallery'+num)){
                jQuery(item).addClass('show');
            }
        });
    }
</script>
