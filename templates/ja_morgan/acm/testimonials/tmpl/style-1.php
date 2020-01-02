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
	$moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');
	$testimonialTitle 	= $helper->get('block-title');
	$testimonialIntro 	= $helper->get('block-intro');
	$count 					= $helper->getRows('data.title');
	$column 				= $helper->get('columns');
?>

<div class="acm-testimonial style-1 align-center">
	<div class="container">
		<div class="testimonial-item">
			<?php if($module->showtitle || $moduleSub) : ?>
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
			</div>
			<?php endif ; ?>

			<div class="testimonial-content">
				<div id="acm-testimonial-<?php echo $module->id; ?>">
					<div class="owl-carousel owl-theme">
						<?php 
							for ($i=0; $i<$count; $i++) : 
						?>
							<div class="testimonial-item <?php if(!$helper->get('data.link', $i)) : ?><?php echo "no-button"; ?><?php endif ; ?>">
								<div class="testimonial-item-inner owl-height">
									<!-- Intro Image -->
									<?php if($helper->get('data.img', $i)) : ?>
										<div class="testimonial-img">
											<img src="<?php echo $helper->get('data.img', $i) ?>" alt="" />

											<?php if($helper->get('data.label', $i)) : ?>
												<div class="label-highlight"><?php echo $helper->get('data.label', $i) ?></div>
											<?php endif ; ?>
										</div>
									<?php endif ; ?>
									
									<!-- Title -->
									<?php if($helper->get('data.title', $i)) : ?>
										<h4><?php echo $helper->get('data.title', $i) ?></h4>
									<?php endif ; ?>

									<!-- Description -->
									<?php if($helper->get('data.desc', $i)) : ?>
										<p><?php echo $helper->get('data.desc', $i) ?></p>
									<?php endif ; ?>

									<!-- Link -->
									<?php if($helper->get('data.link', $i)) : ?>
										<div class="action-link-icon">
											<a href="<?php echo $helper->get('data.link', $i) ?>"><span class="ion-ios-arrow-round-forward"></span></a>
										</div>
									<?php endif ; ?>
								</div>
							</div>
						<?php endfor ?>
					</div>
				</div>
			</div>

			<!-- Button -->
			<?php if($helper->get('btn-title')) : ?>
				<div class="btn-action">
					<a class="btn btn-primary" href="<?php echo $helper->get('btn-link'); ?>"><?php echo $helper->get('btn-title') ?> <span class="icon ion-ios-arrow-round-forward"></span>
					</a>
				</div>
			<?php endif ; ?>
		</div>
	</div>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
    $("#acm-testimonial-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
		addClassActive: true,
		itemsScaleUp : true,
		nav : false,
		navText : ["<span class='fa fa-angle-left'></span>", "<span class='fa fa-angle-right'></span>"],
		dots: true,
		autoPlay: false,
		responsive : {
			0 : {
			    items: 1,
			},
			767 : {
			    items: 2,
			},
			992 : {
			    items: <?php echo $column; ?>
			}
		}
    });
  });
})(jQuery);
</script>