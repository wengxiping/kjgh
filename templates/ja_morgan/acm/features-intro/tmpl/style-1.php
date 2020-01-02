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
	$modTitle       = $module->title;
	$moduleSub 			= $params->get('sub-heading');
	$featuresIntro 	= $helper->get('block-intro');
	$count 					= $helper->getRows('data.title');
	$column 				= $helper->get('columns');
	$slide 					= $helper->get('slide');
?>

<div class="acm-features style-1">
	<div class="row">
		<?php if($module->showtitle || $moduleSub) : ?>
		<div class="col-md-4 col-lg-4">
			<div class="section-title">
				<?php if ($moduleSub): ?>
					<div class="sub-heading">
						<span><?php echo $moduleSub; ?></span>
					</div>
				<?php endif; ?>

				<?php if($module->showtitle) : ?>
					<h3>
						<?php echo $modTitle ?>
					</h3>
				<?php endif ; ?>

				<?php if($featuresIntro): ?>
				<p class="acm-features-intro lead">
					<?php echo $featuresIntro; ?>
				</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>

		<div class="col-md-8 col-lg-8">
			<div id="acm-feature-<?php echo $module->id; ?>" class="acm-feature-<?php echo ($slide == 1) ? 'slide' : 'block' ?>">
				<div class="<?php echo ($slide == 1) ? 'owl-carousel owl-theme' : 'row equal-height' ?>">
					<?php
						for ($i=0; $i<$count; $i++) :
					?>
						<div class="features-item col <?php if($slide == 0) echo 'col-md-' . 12/$column; ?>">
							<div class="features-item-inner">
								<?php if($helper->get('data.img', $i)) : ?>
									<div class="features-img">
										<img src="<?php echo $helper->get('data.img', $i) ?>" alt="<?php echo $helper->get('data.title', $i) ?>" />
									</div>
								<?php endif ; ?>

								<?php if($helper->get('data.title', $i)) : ?>
									<h4><?php echo $helper->get('data.title', $i) ?></h4>
								<?php endif ; ?>

								<?php if($helper->get('data.description', $i)) : ?>
									<p><?php echo $helper->get('data.description', $i) ?></p>
								<?php endif ; ?>

								<?php if($helper->get('data.link', $i)) : ?>
									<div class="action-link-icon">
										<a href="<?php echo $helper->get('data.link', $i) ?>">
											<span class="ion-ios-arrow-round-forward"></span>
										</a>
									</div>
								<?php endif ; ?>
							</div>
						</div>
					<?php endfor ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
	<?php if ($slide == 1) : ?>
    $("#acm-feature-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
      addClassActive: true,
      items: <?php echo $column; ?>,
      itemsScaleUp : true,
      nav : true,
      navText : ["<span class='fa fa-angle-left'></span>", "<span class='fa fa-angle-right'></span>"],
      dots: false,
      autoPlay: false,
      responsive : {
				0 : {
				    items: 1,
				},
				767 : {
				    items: <?php echo $column; ?>
				}
			}
    });

	<?php else : ?>
		$("#acm-feature-<?php echo $module->id; ?>.acm-feature-block").parents(".t3-section.padding-bottom").addClass(" block-style");
	<?php endif; ?>
  });
})(jQuery);
</script>