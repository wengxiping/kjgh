<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>">
	<?php if($module->showtitle || $helper->get('block-intro')): ?>
    <?php if($helper->get('img')): ?>
        <div style="animation-duration: <?php echo $helper->get('animation_speed') ?>ms; -webkit-animation-duration: <?php echo $helper->get('animation_speed') ?>ms;" data-animation="<?php echo $helper->get('animation'); ?>" class="call-to-action-image">
            <img alt="" src="<?php echo $helper->get('img') ?>">
        </div>
    <?php endif; ?>
	<h3 class="section-title ">
		<?php if($module->showtitle): ?>
			<span><?php echo $module->title ?></span>
		<?php endif; ?>
		<?php if($helper->get('block-intro')): ?>
			<p class="container-sm section-intro hidden-xs"><?php echo $helper->get('block-intro'); ?></p>
		<?php endif; ?>
	</h3>
	<?php endif; ?>
	<div class="acm-cta style-1">
		<?php $count = $helper->getRows('data.button');  ?>

		<?php for ($i=0; $i<$count; $i++) : ?>
			<?php if($helper->get('data.button',$i) && $helper->get('data.link',$i)): ?>
			<a href="<?php echo $helper->get('data.link',$i) ?>" class="btn <?php if($helper->get('data.button_class',$i)): echo $helper->get('data.button_class',$i); else: echo 'btn-default'; endif; ?>"><?php echo $helper->get('data.button',$i) ?>
				<i class="fa fa-angle-right"></i>
			</a>
			<?php endif; ?>
		<?php endfor; ?>


	</div>
</div>
<style>
    .xp-spotlight{
        width: 1200px!important;padding: 40px 0!important;margin: 0 auto!important;
    }
    .xp-spotlight>.row{
        width:100%;padding: 0;margin: 0;display: flex;justify-content: space-between;align-content: center;
    }
    .xp-spotlight>.row>div{
        padding: 0;margin: 0;
    }
    .xp-spotlight>.row>div>div:nth-child(1){
        width: 100% !important;padding: 0!important;margin-bottom: 20px!important;
    }
    .xp-spotlight>.row>div>div:nth-child(2){
        width: 100% !important;padding: 0!important;
    }
    .xp-spotlight>.row>div>div .acm-cta.style-3.light.bg-image{
        padding: 0!important;margin: 0!important;
    }
    .xp-spotlight>.row>div>div .container{
        padding: 0!important;margin: 0!important;width: 100% !important;border: 0!important;
    }
    .xp-spotlight>.row>div>div .row{
        padding: 0!important;margin: 0!important;width: 100%;
    }
    .xp-ja-morgan{
        position: relative;
    }
    .xp-ja-morgan .img-icon{margin: 0!important;}
    .xp-ja-morgan .desc-p{position:absolute;bottom:10px;
        opacity: 1;
        font-size: 20px;
        font-family: MicrosoftYaHei;
        font-weight: 700;
        color: rgba(255,96,16,1);
        line-height: 26px;
        letter-spacing: 0;text-align: left}

    .bronze{width: 585px!important;height: 240px!important;background: #FFFFFF;position: relative;}
    .bronze>.head{
        width: 100%;
        position: absolute;top:140px;
        text-align: center;
        font-family: 微软雅!important;
        font-weight: bold!important;
        font-size: 20px!important;
    }
    .bronze>.section-inner>div{
        position: absolute;top:0;left: 150px!important;
    }
    .bronze>.section-inner>.acm-cta{
        position: absolute;top:182px;width: 286px;
        display: flex;justify-content: space-between;align-items: center;
    }

    .bronze>.section-inner>.acm-cta>a:nth-child(1){
        width: 138px;height: 38px;background: #FFFFFF!important;;border: 1px solid #EEEEEE;padding: 0!important;margin: 0!important;
        text-align: center;
        opacity: 1;
        font-size: 12px;
        font-family: MicrosoftYaHei;
        color: rgba(102,102,102,1);
        line-height: 38px;
        letter-spacing: 1px;
        border-radius: 0!important;
        box-shadow: none!important;
        text-shadow: none!important;
    }
    .bronze>.section-inner>.acm-cta>a:nth-child(1):hover{
        transition: all 1s;
        background: #fef3f0 !important;
        color: #0a0a0a!important;
    }
    .bronze>.section-inner>.acm-cta>a:nth-child(2){
        width: 138px;height: 38px;background:#FF6010!important;padding: 0!important;margin: 0!important;
        text-align: center;
        opacity: 1;
        font-size: 12px!important;
        font-weight: normal!important;
        font-family: MicrosoftYaHei;
        color: #FFFFFF!important;
        line-height: 38px;
        letter-spacing: 1px;
        border-radius: 0!important;
        border: none!important;
        box-shadow: none!important;
        text-shadow: none!important;
    }
    .bronze>.section-inner>.acm-cta>a:nth-child(2):hover{
        transition: all 1s;
        background:red!important;
    }
</style>
