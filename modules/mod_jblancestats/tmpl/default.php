<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	25 June 2012
 * @file name	:	modules/mod_jblancestats/tmpl/default.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 // no direct access
 defined('_JEXEC') or die('Restricted access');

 $document = JFactory::getDocument();
 $direction = $document->getDirection();
 $config = JblanceHelper::getConfig();

 if($config->loadBootstrap){
 	JHtml::_('bootstrap.loadCss', true, $direction);
 }

 $document->addStyleSheet("modules/mod_jblancestats/css/style.css");
 $document->addStyleSheet("components/com_jblance/css/style.css");

 $sh_users 		= $params->get('total_users', 1);
 $sh_active 	= $params->get('active_projects', 1);
 $sh_total 		= $params->get('total_projects', 1);
 $display_type 	= $params->get('display_type', 'vertical');
?>
<?php //if($display_type == 'vertical') : ?>
<!--<div class="form-horizontal">-->
<!--	--><?php //if($sh_users) : ?>
<!--	<div class="control-group">-->
<!--		<label class="control-label nopadding">--><?php //echo JText::_('MOD_JBLANCE_LABEL_TOTAL_USERS'); ?><!--: </label>-->
<!--		<div class="controls">-->
<!--			--><?php //echo $total_users; ?>
<!--		</div>-->
<!--	</div>-->
<!--	--><?php //endif; ?>
<!--	--><?php //if($sh_active) : ?>
<!--	<div class="control-group">-->
<!--		<label class="control-label nopadding">--><?php //echo JText::_('MOD_JBLANCE_LABEL_TOTAL_OPEN_PROJECTS'); ?><!--: </label>-->
<!--		<div class="controls">-->
<!--			--><?php //echo $active_projects; ?>
<!--		</div>-->
<!--	</div>-->
<!--	--><?php //endif; ?>
<!--	--><?php //if($sh_total) : ?>
<!--	<div class="control-group">-->
<!--		<label class="control-label nopadding">--><?php //echo JText::_('MOD_JBLANCE_LABEL_TOTAL_PROJECTS'); ?><!--: </label>-->
<!--		<div class="controls">-->
<!--			--><?php //echo $total_projects; ?>
<!--		</div>-->
<!--	</div>-->
<!--	--><?php //endif; ?>
<!--</div>-->
<?php //elseif($display_type == 'horizontal') : ?>
<!--<ul class="inline row-fluid statistics">-->
<!--	<li class="span4">-->
<!--		<span class="statcount">--><?php //echo $total_users; ?><!--</span> --><?php //echo JText::_('MOD_JBLANCE_LABEL_TOTAL_USERS'); ?>
<!--	</li>-->
<!--	<li class="span4">-->
<!--		<span class="statcount">--><?php //echo $active_projects; ?><!--</span> --><?php //echo JText::_('MOD_JBLANCE_LABEL_TOTAL_OPEN_PROJECTS'); ?>
<!--	</li>-->
<!--	<li class="span4">-->
<!--		<span class="statcount">--><?php //echo $total_projects; ?><!--</span> --><?php //echo JText::_('MOD_JBLANCE_LABEL_TOTAL_PROJECTS'); ?>
<!--	</li>-->
<!--</ul>-->
<?php //endif; ?>
<div class="bg-block">
    <div id="c1"></div>
    <div class="title-show">今日话事</div>
    <div class="bg-1"><img src="./images/cyclotron.png"></div>
    <div class="bg-2"><img src="./images/cyclbg.png"></div>
    <div  class="container-box">
        <div class="txt">
            <div class="item">
                <div class="top"><?php echo $total_users;?></div>
                <div class="bottom">总用户</div>
            </div>
            <div class="item">
                <div class="top"><?php echo $active_projects;?></div>
                <div class="bottom">总开发项目</div>
            </div>
            <div class="item">
                <div class="top"><?php echo $total_projects;?></div>
                <div class="bottom">总项目</div>
            </div>
        </div>
    </div>
</div>
<script src="https://gw.alipayobjects.com/as/g/datavis/assets/1.0.5/g2/3.0.0/g2.min.js"></script>
<script type="text/javascript">
    const data = [
        { genre: '总用户', sold: <?php echo $total_users; ?>,color:"#37ADFF" },
        { genre: '总开放项目', sold: <?php echo $active_projects;?>,color:"#FF6A00" },
        { genre: '总项目', sold: <?php echo $total_projects;?> ,color:"#FF8F1F"},
    ];

    const chart = new G2.Chart({
        container: 'c1',
        width: 260,
        height: 260,
        padding:{top: 0, right: 30, bottom: 40, left:55}
    });
    chart.coord('theta', {
        radius: 0.75,
        innerRadius: 0.6
    });
    chart.legend(false);
    chart.axis(false);
    chart.tooltip(true, {
        showTitle: false,
        inPlot: false,
    });
    chart.source(data,{
        sold: {
            formatter: function formatter(val) {
                val = val + "%";
                return val;
            }
        }
    });
    chart.intervalStack().position('sold').color('genre',['#37ADFF','#FF6A00', '#FF8F1F']);
    // Step 4: 渲染图表
    chart.render();
</script>
<style>
    #c1{
        position: absolute;z-index: 2;
    }
    .bg-block{
        position: relative;
    }
    .bg-1{
        position: absolute;right:10px;top:-5px;
    }
    .bg-2{
        position: absolute;
        top: 6px;
        left: 38px;
        z-index: 1;
    }
    .title-show{
        position: absolute;top:10px;
        margin-left: 10px;
        opacity: 1;
        font-size: 16px;
        font-family: MicrosoftYaHei;
        font-weight: 700;
        color: rgba(255,96,16,1);
        line-height: 21px;
        letter-spacing: 0px;
    }
    .container-box{
        position: absolute;top:220px;width: 294px;
    }
    .txt{
        display: flex;justify-content: space-between;align-items: center;width: 100%;padding: 0 30px;
    }
    .txt .item{
        display: flex;justify-content: space-between;align-items: center;flex-direction: column;
    }
    .item:nth-child(1) .top{
        height: 16px;
        opacity: 1;
        font-size: 18px;
        font-family: MicrosoftYaHei;
        color: #37ADFF;
        line-height: 16px;
        letter-spacing: 0px;
    }
    .item:nth-child(2) .top{
        height: 16px;
        opacity: 1;
        font-size: 18px;
        font-family: MicrosoftYaHei;
        color: #FF6A00;
        line-height: 16px;
        letter-spacing: 0px;
    }
    .item:nth-child(3) .top{
        height: 16px;
        opacity: 1;
        font-size: 18px;
        font-family: MicrosoftYaHei;
        color: #FF8F1F;
        line-height: 16px;
        letter-spacing: 0px;
    }
    .item .bottom{
        height: 16px;
        opacity: 1;
        font-size: 12px;
        font-family: MicrosoftYaHei;
        color: rgba(102,102,102,1);
        line-height: 16px;
        letter-spacing: 0px;
        margin-top: 5px;
    }
</style>
