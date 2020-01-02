
<?php
/**
 * ------------------------------------------------------------------------
 * JA Morgan Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/new_featured.css");

	$count = $helper->getRows('title');
	$column = 12/($helper->get('columns'));
	$titleAlign = '';

	$moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');

	if($helper->get('title-align')) {
		$titleAlign = 'col-md-offset-6 col-md-4';
	}
?>


<div class="acm-features style-2 style-<?php echo $helper->get('features-style') ;?> align-<?php echo $helper->get('features-align') ;?>">
	<?php if($module->showtitle || $moduleSub) : ?>
	<!-- 设计稿没有，暂时隐藏 -->
	<div  class="section-title <?php echo $titleAlign ;?>">
	<!-- Module Title -->
		<?php if ($moduleSub): ?>
			<div class="sub-heading">
				<span><?php echo $moduleSub; ?></span>
			</div>
		<?php endif; ?>

		<?php if($module->showtitle) : ?>
		<h3><?php echo $moduleTitle ?></h3>
		<?php endif; ?>
	<!-- // Module Title -->
	</div>
	<?php endif ; ?>
	<div class='container'>
		<div class="row equal-height equal-height-child">
			<?php for ($i=0; $i<$count; $i++) : ?>
				<div class="col col-sm-6 col-md-<?php echo $column ?> status xp-ja-morgan">
					<div class="features-item ">
						<?php if($helper->get('font-icon', $i)) : ?>
							<div class="font-icon">
								<span class="<?php echo $helper->get('font-icon', $i) ; ?>"></span>
							</div>
						<?php endif ; ?>

						<?php if($helper->get('img-icon', $i)) : ?>
							<div class="img-icon">
								<img src="<?php echo $helper->get('img-icon', $i) ?>" alt="" />
							</div>
						<?php endif ; ?>

						<?php if($helper->get('description', $i)) : ?>
							<p><?php echo $helper->get('description', $i) ?></p>
						<?php endif ; ?>
					</div>
					<?php if($helper->get('title', $i)) : ?>
							<p class='desc-p'><?php echo $helper->get('title', $i) ?></p>
						<?php endif ; ?>
				</div>
			<?php endfor ?>
		</div>
	</div>


	<?php if($helper->get('title-more')) : ?>
	<div class="link-action">
		<a href="<?php echo $helper->get('link-more') ;?>" class="btn btn-primary" title="<?php echo $helper->get('title-more') ;?>">
			<?php echo $helper->get('title-more') ;?>
			<span class="ion-ios-arrow-round-forward"></span>
		</a>
	</div>
	<?php endif ; ?>
</div>
<style>
    .section-title{padding-top: 54px!important;}
    .section-title h3{
        height: 35px;
        opacity: 1;
        font-size: 26px;
        font-family: 微软雅!important;
        font-weight: normal!important;
        color: rgba(51,51,51,1);
        line-height: 35px;
        letter-spacing: 0px;
    }
    .lm-partners h2{
        font-family: 微软雅!important;
        font-weight: normal!important;
    }
    .acm-hero{padding: 0!important;}
    .acm-hero .container{
        padding: 4% 0 8% 0!important;
    }
    .acm-hero .hero-heading{
        height: 69px;
        opacity: 1;
        font-size: 52px;
        font-family: MicrosoftYaHei;
        font-weight: 700;
        color: rgba(255,255,255,1) !important;
        line-height: 69px;
        letter-spacing: 2.08px;
        margin: 0!important;padding: 0!important;
    }
    .acm-hero .hero-intro{
        height: 26px;
        opacity: 1;
        font-size: 20px;
        font-family: MicrosoftYaHei;
        color: rgba(255,255,255,1) !important;
        line-height: 26px;
        letter-spacing: 0px;
        margin: 16px 0 100px 0!important;padding: 0!important;
    }
    .acm-hero .hero-btn-actions{
            margin: 0!important;padding: 0!important;
    }
    .acm-hero .hero-btn-actions .btn{
        padding: 0!important;margin: 0!important;
        width: 200px!important;
        height: 42px!important;
        line-height: 42px;
        opacity: 1;
        font-size: 16px!important;
        font-family: 微软雅黑!important;
        font-weight: lighter!important;
        color: rgba(255,255,255,1);
        letter-spacing: 2px;
        text-align: center!important;
        background: none!important;
        border: 1px solid #FFFFFF!important;
        box-shadow: none!important;
        border-radius: 0!important;

    }
    .acm-hero .hero-btn-actions .btn:hover{
        opacity: 0.8;
    }
    .style-box{background:#FFFFFF;}
    .t3-section{background:#FFFFFF;}

</style>
