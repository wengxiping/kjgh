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

	$modTitle   = $module->title;
	$count		= $helper->getRows('data.content');
?>

<div id="acm-testimonial-<?php echo $module->id; ?>" class="acm-testimonial style-2">
	<div class="owl-carousel owl-theme">
		<?php 
			for ($i=0; $i<$count; $i++) : 
		?>
			<div class="testimonial-item col">
				<div class="testimonial-item-inner">
					<?php if($helper->get('data.avatar', $i)) :?>
					<div class="testimonial-avatar">
						<img src="<?php echo $helper->get('data.avatar', $i) ?>" alt="<?php echo $helper->get('data.name', $i) ?>"/>
					</div>
					<?php endif ;?>

					<div class="testimonial-content-wrap">
						<?php if($helper->get('data.content', $i)) : ?>
							<div class="testimonial-content"><?php echo $helper->get('data.content', $i) ?></div>
						<?php endif ; ?>

						<?php if($helper->get('data.name', $i)) : ?>
							<div class="testimonial-info">
								<?php if($helper->get('data.img', $i)) : ?>
									<div class="testimonial-img">
										<img src="<?php echo $helper->get('data.img', $i) ?>" alt="<?php echo $helper->get('data.name', $i) ?>" />
									</div>
								<?php endif ; ?>

								<div class="testimonial-detail">
									<?php if($helper->get('data.name', $i)) : ?>
										<?php echo $helper->get('data.name', $i) ?>
									<?php endif ; ?>
									<?php if($helper->get('data.name-position', $i)) : ?>
										<span><?php echo $helper->get('data.name-position', $i) ?></span>
									<?php endif ; ?>
								</div>
							</div>
						<?php endif ; ?>
					</div>
				</div>
			</div>
		<?php endfor ?>
	</div>
</div>


<script>
(function($){
  jQuery(document).ready(function($) {
    $("#acm-testimonial-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
      items: 1,
      singleItem : true,
      itemsScaleUp : true,
      nav : false,
      dots: true,
      animateOut: 'fadeOut',
      autoplay: true
    });
  });
})(jQuery);
</script>