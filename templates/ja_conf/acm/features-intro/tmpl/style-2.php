<?php
/**
 * ------------------------------------------------------------------------
 * JA Conf Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
	$featureHeading 	= $helper->get('features-heading');
	$count 					= $helper->getRows('data.title');
	$column 				= $helper->get('columns');	
?>


<div class="acm-features style-2">
	<?php if($featureHeading || $module->showtitle) : ?>
  	<div class="section-title">
	    <?php if($featureHeading) : ?>
	    <div class="title-intro">
	      <?php echo $featureHeading; ?>
	    </div>
	    <?php endif; ?>

	    <?php if($module->showtitle): ?>
				<h3 class="title-lead h1"><?php echo $module->title ?></h3>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div id="acm-feature-<?php echo $module->id; ?>">
		<div class="owl-carousel owl-theme">
			<?php 
				for ($i=0; $i<$count; $i++) : 
			?>
				<div class="features-item col">
					<div class="features-item-inner ja-animate" data-animation="move-from-bottom" data-delay="item-<?php echo $i ?>">
						<?php if($helper->get('data.img', $i)) : ?>
							<div class="features-img img-wrap">
								<img src="<?php echo $helper->get('data.img', $i) ?>" alt="" />
							</div>
						<?php endif ; ?>
						
						<?php if($helper->get('data.title', $i)) : ?>
							<h3><?php echo $helper->get('data.title', $i) ?></h3>
						<?php endif ; ?>
						
						<?php if($helper->get('data.description', $i)) : ?>
							<p><?php echo $helper->get('data.description', $i) ?></p>
						<?php endif ; ?>

						<?php if($helper->get('data.link', $i) && $helper->get('data.btn-value', $i)) : ?>
							<div class="more-link">
								<a href="<?php echo $helper->get('data.link', $i) ?>">
									<?php echo $helper->get('data.btn-value', $i) ?>
									<span class="fas fa-arrow-right"></span>
									<span class="element-invisible hidden">empty</span>
								</a>
							</div>
						<?php endif ; ?>
					</div>
				</div>
			<?php endfor ?>
		</div>
	</div>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
    $("#acm-feature-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
      addClassActive: true,
      items: <?php echo $column; ?>,
      margin: 64,
      responsive : {
      	0 : {
      		items: 1,
      	},

      	768 : {
      		items: 2,
      	},

      	979 : {
      		items: 2,
      	},

      	1199 : {
      		items: <?php echo $column; ?>,
      	}
      },
      loop: true,
      nav : true,
      navText : ["<span class='fas fa-arrow-left'></span>", "<span class='fas fa-arrow-right'></span>"],
      dots: false,
      autoplay: false
    });
  });
})(jQuery);
</script>