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

  $align = $helper->get('align');

  if ($align==1):
  	$contentAlign = "content-right";
  	$featuresContentPull = "col-sm-12 col-xs-12 col-md-6 pull-right";
  	$featuresImgPull = " col-md-6 pull-left";
  else:
  	$contentAlign = "content-left";
  	$featuresContentPull = "col-sm-12 col-xs-12 col-md-6 pull-left";
  	$featuresImgPull = " col-md-6 pull-right";
  endif;

  $moduleTitle = $module->title;
  $moduleSub = $params->get('sub-heading');

  $items_position = $helper->get('position');
	$mods = JModuleHelper::getModules($items_position);

	$mod            = $module->id;
?>

<div id="acm-features-<?php echo $mod; ?>" class="acm-features style-3 <?php echo $helper->get('features-style'); ?>">
	<div class="row <?php echo $contentAlign; ?>">
		<?php if($helper->get('id-video')) : ?>
			<div class="img-icon dekstop">
				<a class="html5lightbox" data-group="myvideo-<?php echo $mod; ?>" href="https://www.youtube.com/watch?v=<?php echo $helper->get('id-video') ?>" title="">
				    <i class="fa fa-play" aria-hidden="true"></i>
				</a>
			</div>
		<?php endif ; ?>

		<?php if($helper->get('img-features')) : ?>
		<div class="features-image <?php echo $featuresImgPull; ?>">
			<img src="<?php echo $helper->get('img-features'); ?>" alt="<?php echo $moduleTitle; ?>"/>

			<?php if($helper->get('id-video')) : ?>
				<div class="img-icon mobile">
					<a class="html5lightbox" data-group="myvideo-<?php echo $mod; ?>" href="https://www.youtube.com/watch?v=<?php echo $helper->get('id-video') ?>" title="">
					    <i class="fa fa-play" aria-hidden="true"></i>
					</a>
				</div>
			<?php endif ; ?>
		</div>
		<?php endif; ?>

		<div class="<?php echo $featuresContentPull; ?>" >
			<div class="features-content">
				<?php if($module->showtitle || $helper->get('description')) : ?>
				<div class="section-title">
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

					<?php if ($helper->get('description')): ?>
						<div class="lead">
							<?php echo $helper->get('description'); ?>
						</div>
					<?php endif ; ?>
				</div>
				<?php endif ; ?>

				<!-- Load More Position -->
				<?php if($items_position) :?>
					<div class="features-module-wrap">
					<?php
						echo $helper->renderModules($items_position,
							array(
								'style'=>'raw',
								'active'=>0,
								'tag'=>'div'
							))
						?>
					</div>
				<?php endif ;?>
				<!-- // Load More Position -->

				<?php if ($helper->get('btn-link') || $helper->get('btn-title')): ?>
				<div class="features-action">
					<a href="<?php echo $helper->get('btn-link') ;?>" title="<?php echo $helper->get('btn-title') ;?>" class="btn btn-primary">
						<?php echo $helper->get('btn-title') ;?><span class="ion-ios-arrow-round-forward"></span>
					</a>
				</div>
				<?php endif ; ?>
				<!--- //Features Content -->
			</div>
		</div>
	</div>
</div>

<script>
(function($){
	jQuery(document).ready(function($) {

	    //Popup video
	    $("#acm-features-<?php echo $mod; ?> .html5lightbox").html5lightbox({
	      autoslide: true,
	      showplaybutton: false,
	      jsfolder: "<?php echo JUri::base(true).'/templates/ja_morgan/js/html5lightbox/' ?>"
	    });
	});
})(jQuery);
</script>