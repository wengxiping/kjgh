<?php
$featuresImg = $helper->get('block-bg');
$fullWidth = $helper->get('full-width');
$featuresBackground = 'background-image: url(' . $featuresImg . '); background-repeat: no-repeat; background-size: cover; background-position: center center;';
?>

<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>" <?php if ($featuresImg): echo 'style="' . $featuresBackground . '"'; endif; ?>>
    <?php if ($module->showtitle || $helper->get('block-intro')): ?>

        <div class="header-content">
            <h3 class="section-title">
                <img src="templates/ja_directory/acm/gallery/images/left-arrow.png">
                <?php if ($module->showtitle): ?>
                    <span><?php echo $module->title ?></span>
                <?php endif; ?>
                <?php if ($helper->get('block-intro')): ?>
                    <p class="container-sm section-intro hidden-xs"><?php echo $helper->get('block-intro'); ?></p>
                <?php endif; ?>
            </h3>
            <?php
              $number = $helper->getRows('data.title');
              if($number>2):
            ?>
            <div class="xp-page-content">
                <div class="pre-page"
                     onclick="javascript:action_page_feature(<?php echo $number; ?>,<?php echo $module->id; ?>,0,2);">
                    <img src="./images/pre.png"/></div>
                <div class="next-page"
                     onclick="javascript:action_page_feature(<?php echo $number; ?>,<?php echo $module->id; ?>,1,2);">
                    <img src="./images/next.png"/></div>
            </div>
            <?php endif;?>
        </div>

    <?php endif; ?>
    <div class="acm-features <?php echo $helper->get('features-style'); ?> style-1">
        <?php if (!$fullWidth): ?>
        <div class="xp-container"><?php endif; ?>
            <?php if ($helper->get('features-description')) : ?>
                <h2 class="features-description"><?php echo $helper->get('features-description'); ?></h2>
            <?php endif; ?>
            <div class="xp-row xp-row-page<?php echo $module->id; ?> <?php if (!$fullWidth): ?><?php else: ?> clearfix <?php endif; ?> ">
                <?php $count = $helper->getRows('data.title'); ?>
                <?php $column = 12 / ($helper->get('columns'));
                $class = 1; ?>
                <?php for ($i = 0, $j = 0; $i < $count; $i++) : ?>

                    <div class="xp-item features-item col-sm-<?php echo $column ?> <?php echo 'show'.(floor($i/2)==0?floor($i/2).' show':floor($i/2));?>">
                        <?php if ($helper->get('data.font-icon', $i)) : ?>
                            <div class="font-icon">
                                <i class="<?php echo $helper->get('data.font-icon', $i); ?>"></i>
                            </div>
                        <?php endif; ?>
                        <div class="h-image">
                            <?php if ($helper->get('data.title', $i)) : ?>
                                <div class="name"><?php echo $helper->get('data.title', $i) ?></div>
                            <?php endif; ?>

                            <?php if ($helper->get('data.img-icon', $i)) : ?>
                                <div class="profile-img">
                                    <img src="<?php echo $helper->get('data.img-icon', $i) ?>" alt=""/>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($helper->get('data.description', $i)) : ?>
                            <p><?php echo $helper->get('data.description', $i) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php
                    if ($j == 1) {
                        $j == 0;
                        $class++;
                    }
                    $j++;
                endfor ?>
            </div>
            <?php if (!$fullWidth): ?></div><?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    var _current = [];
    var _class = "";
    var _id = [];
    function action_page_feature(total, id,status,pre_page) {
        console.log(total, id,status,pre_page);
        if (_current.length > 0 || !status) {
            _current.forEach(function(item,index){
                //循环数据存在和不存在 更新和添加
                if(_id.indexOf(id)<0){//表示数组中没有该id存在
                    _id.push(id);
                    _current.push({id: id, current_page: 1});
                    _class = 1;
                    displayShowHiddenAction_feature(id,_class,status);
                    return;
                }else{//更新，并结束
                    if(item.id == id){
                        item.current_page = pageNumStatus_feature(total,status,item.current_page,pre_page);
                        _class = item.current_page;
                        displayShowHiddenAction_feature(id,_class,status);
                        return;
                    }
                }
            });
        } else {
            _id.push(id);
            _class = 1;
            _current.push({id: id, current_page: 1});
            displayShowHiddenAction_feature(id,_class,status);
        }

    }
    function pageNumStatus_feature(total,status,num,pre_page){
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
    function displayShowHiddenAction_feature(objectClassId,num){

        var object = jQuery(".xp-row-page"+objectClassId).find(".xp-item");
        object.each(function(index,item){
            jQuery(item).removeClass('show');

            if(jQuery(item).hasClass('show'+num)){
                jQuery(item).addClass('show');
            }
        });
    }
</script>
