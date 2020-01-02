<?php
/**
 * ------------------------------------------------------------------------
 * JA City Guide Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
	$count 					= $helper->getRows('data.description');		
?>


<div class="acm-features style-1">
	<div id="acm-feature-<?php echo $module->id; ?>" >
		<div class="owl-carousel owl-theme">
			<?php 
				for ($i=0; $i<$count; $i++) : 
			?>
				<div class="features-item">
					<div class="features-item-inner row">
						<!-- Image Slide -->
						<?php if($helper->get('data.img', $i)) : ?>
							<div class="col-xs-12 col-sm-6">
								<div class="features-img">
									<img src="<?php echo $helper->get('data.img', $i) ?>" alt="Slide Image" />
								</div>
							</div>
						<?php endif ; ?>

						<!-- Content -->
						<div class="col-xs-12 col-sm-6 features-text-wrap">
							<div class="features-text">
								<?php if($helper->get('data.avatar', $i)) : ?>
									<div class="avatar">
										<img src="<?php echo $helper->get('data.avatar', $i) ?>" alt="avatar" />
									</div>
								<?php endif ; ?>
								
								<?php if($helper->get('data.description', $i)) : ?>
									<div class="description"><?php echo $helper->get('data.description', $i) ?></div>
								<?php endif ; ?>

								<?php if($helper->get('data.author', $i) || $helper->get('data.author-position', $i)) : ?>
									<div class="info-user">
										<?php echo $helper->get('data.author', $i) ?>

										<?php if($helper->get('data.author-position', $i)) : ?>
										<span><?php echo $helper->get('data.author-position', $i) ?></span>
										<?php endif ; ?>
									</div>
								<?php endif ; ?>
							</div>
						</div>
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
      items: 1,
      loop: true,
      nav : false,
      navText : ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
      dots: true,
      autoPlay: false
    });
  });
})(jQuery);
</script>