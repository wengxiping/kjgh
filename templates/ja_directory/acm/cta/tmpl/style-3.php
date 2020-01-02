<?php
  $ctaImg 				= $helper->get('img');
  $ctaBackground  = 'background-image: url('.$ctaImg.'); background-attachment: fixed; background-repeat: no-repeat; background-size: cover;';
?>
<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>">
	<div class="acm-cta style-3 <?php echo $helper->get('style'); ?> <?php if($ctaImg): echo 'bg-image'; endif; ?>" <?php if($ctaImg): echo 'style="'.$ctaBackground.'"'; endif; ?> >
	  <div class="container">
			<div class="row">
				<div class="<?php echo $helper->get('text-align'); ?>">
					<div class="cta-showcase-item">

						<?php if($module->showtitle): ?>
							<h2 class="cta-showcase-header"><?php echo $module->title ?></h2>
						<?php endif; ?>

						<?php if($helper->get('block-intro')): ?>
							<p class="cta-showcase-intro"><?php echo $helper->get('block-intro'); ?></p>
						<?php endif; ?>

						<?php
							$count = $helper->getRows('data.button');
						?>
						<nav class="cta-showcase-actions">
							<?php for ($i=0; $i<$count; $i++) : ?>
								<a href="<?php echo $helper->get ('data.link',$i) ?>" target="_blank" class="<?php echo $helper->get ('data.button_class',$i) ?>"><i class="fa fa-angle-right"></i><?php echo $helper->get ('data.button',$i) ?></a>
							<?php endfor;?>
						</nav>
					</div>
				</div>
			</div>
	  </div>
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
        padding: 0;margin: 0;width: 380px;
    }
    .xp-spotlight>.row>div>div:nth-child(1){
        width: 100% !important;height: 258px;padding: 0!important;background: #FFF!important;margin-bottom: 20px!important;
    }
    .xp-spotlight>.row>div>div:nth-child(2){
        width: 100% !important;height: 258px;padding: 0!important;background: #FFF!important;
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
    .xp-spotlight>.row>div>div .row>.text-left{
        height: 258px;padding: 0!important;margin: 0!important;width: 100% !important;display: flex;align-content: center;align-items: center;flex-direction: column;
    }
    .xp-spotlight>.row>div>div .cta-showcase-item{
        height: 258px!important;
        display: flex;justify-content: center;align-content: center;flex-direction: column;
    }
    .xp-spotlight>.row>div>div .cta-showcase-item .cta-showcase-header{
        margin: 0 auto!important;
        opacity: 1;
        font-size: 20px!important;
        font-family: MicrosoftYaHei;
        font-weight: 700;
        color: rgba(51,51,51,1);
        letter-spacing: 0px;
        height: 26px!important;
        line-height: 26px!important;
        text-align: center;
    }

    .xp-spotlight>.row>div>div .cta-showcase-item>.cta-showcase-intro{
        width: 280px;
        height: 60px;
        opacity: 1;
        font-size: 14px;
        font-family: MicrosoftYaHei;
        color: rgba(102,102,102,1);
        line-height: 20px;
        letter-spacing: 0px;
        margin: 24px 0 29px 0;
    }
    .xp-spotlight>.row>div>div .cta-showcase-item >.cta-showcase-actions{
        display: flex;justify-content: space-between;align-content: center;flex-flow: row-reverse;

    }
    .xp-spotlight>.row>div>div .cta-showcase-item >.cta-showcase-actions>a{
        padding: 0!important;margin: 0!important;box-shadow: none!important;text-shadow: none!important;border-radius: 0!important;border: none!important;
    }
    .xp-spotlight>.row>div>div .cta-showcase-item >.cta-showcase-actions>a:nth-child(1){
        width: 138px;height: 28px;background: #FF6010!important;
        opacity: 1;
        font-size: 12px!important;
        font-family: MicrosoftYaHei;
        color: rgba(255,255,255,1) !important;
        line-height: 28px;
        letter-spacing: 0px;
    }
    .xp-spotlight>.row>div>div .cta-showcase-item >.cta-showcase-actions>a:nth-child(2){
        width: 138px;height: 28px;background: #FFFFFF!important;
        opacity: 1;
        font-size: 12px!important;
        font-family: MicrosoftYaHei;
        color: #666666!important;
        line-height: 28px;
        letter-spacing: 0px;
        border: 1px solid #EEEEEE!important;
    }


    /*成为合作伙伴 start */
    .become-partner{
        width: 100%;background: #FFFFFF;position: relative;
    }
    .become-partner>.section-inner.section-lighter.section-introducing.section-border{
        padding: 0!important;margin: 0!important;
    }
    .become-partner .container.product-features{
        width: 100% !important;padding: 0!important;margin: 0!important;
    }
    .become-partner .acm-features.style-5.style-light .features-header{
        width: 1200px;text-align: center;margin: 0 auto!important;
    }
    .become-partner .acm-features.style-5.style-light .features-header  .features-title{
        margin: 54px 0 47px 0;
    }
    .become-partner .acm-features.style-5.style-light .features-header  .features-title .rw-words{
        display: none;
    }
    .become-partner .acm-features.style-5.style-light .features-header .features-title .rw-words-text{
        height: 20px;
        opacity: 1;
        font-size: 14px;
        font-family: MicrosoftYaHei;
        color: rgba(153,153,153,1);
        line-height: 20px;
        letter-spacing: 1px;
    }
    .become-partner .acm-features.style-5.style-light .features-content{
        display: flex;justify-content: center;align-items: center;width: 1200px;margin: auto;
    }
    .become-partner .acm-features.style-5.style-light .features-content>div{
        width: 240px;display: flex;justify-content: space-between;align-items: center;flex-direction: column;
    }
    .become-partner .acm-features.style-5.style-light .features-content>div:nth-child(1){
        padding-left: 84px;
    }
    .become-partner .acm-features.style-5.style-light .features-content>div:nth-child(2){
        padding-left:40px;
    }
    .become-partner .acm-features.style-5.style-light .features-content>div:nth-child(4){
        padding-right:40px;
    }
    .become-partner .acm-features.style-5.style-light .features-content>div:nth-child(5){
        padding-right:70px;
    }
    .become-partner .step-outside{
        position: absolute;top: 240px;width: 100%;
    }
    .become-partner .step-outside>.step-bg{
        margin: auto;width: 1200px;
    }
    .become-partner .icon-wrapper.icon-wrapper-show{
        /*width: 72px!important;height: 72px!important;border-radius: 50%;background: #F3F3F3;  margin-bottom: 70px;*/
    }
    .become-partner .acm-features.style-5.style-light .features-content>div>.intro-content{
        width: 100%;display: flex;justify-content: center;align-items: center;flex-direction: column;

    }
    .become-partner .acm-features.style-5.style-light .features-content>div>.intro-content>h3{
        padding: 0!important;
        opacity: 1;
        font-size: 16px!important;
        font-family: MicrosoftYaHei;
        color: rgba(51,51,51,1);
        line-height: 21px;
        letter-spacing: 1.14px;
        margin-bottom: 20px;
    }
    .become-partner .acm-features.style-5.style-light .features-content>div>.intro-content>p{
        border: 1px solid #FF8F1F;margin: 0!important;padding: 0 4px!important;border-radius: 11px;
        opacity: 1;
        letter-spacing: 0px;
        text-align: center;
    }
    .become-partner .acm-features.style-5.style-light .features-content>div>.intro-content>p>a{
        color: rgba(255,143,31,1) !important;
        font-size: 12px!important;
        font-family: MicrosoftYaHei;
    }
    .become-partner .step-outside>.step-bg>img{
        width: 100%;
    }
    /*成为合作伙伴 end */

</style>
