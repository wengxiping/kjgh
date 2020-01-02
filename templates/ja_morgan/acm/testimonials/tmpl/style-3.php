<?php
/**
 * ------------------------------------------------------------------------
 * JA Morgan Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
	$moduleTitle = $module->title;
  	$moduleSub = $params->get('sub-heading');
	$count 	= $helper->getRows('data.name');
	$lang = JFactory::getLanguage();
  	$dir = $lang->get('rtl');
?>

<div class="acm-testimonial style-3">
	<div class="container">
		<div class="testimonial-inner">
			<div class="section-title">
				<?php if ($moduleSub): ?>
					<div class="sub-heading">
						<span><?php echo $moduleSub; ?></span>		
					</div>
				<?php endif; ?>

				<?php if($module->showtitle) : ?>
					<h2>
						<?php echo $moduleTitle ?>
					</h2>
				<?php endif ; ?>
			</div>

			<div class="testimonial-content">
				<div id="acm-testimonial-<?php echo $module->id; ?>">
					<div class="owl-carousel owl-theme">
						<?php 
							for ($i=0; $i<$count; $i++) : 
						?>
							<div class="testimonial-item-wrap">
								<div class="testimonial-item-inner">
									<?php if($helper->get('data.member-description', $i)) : ?>
										<div class="lead-desc"><?php echo $helper->get('data.member-description', $i) ?></div>
									<?php endif ; ?>

									<?php if($helper->get('data.img', $i)) : ?>
										<div class="testimonial-img">
											<img src="<?php echo $helper->get('data.img', $i) ?>" alt="" />
										</div>
									<?php endif ; ?>

									<?php if($helper->get('data.name', $i)) : ?>
										<h5 class="testimonial-name"><?php echo $helper->get('data.name', $i) ?></h5>
									<?php endif ; ?>
										
									<?php if($helper->get('data.member-position', $i)) : ?>
										<div class="testimonial-position"><?php echo $helper->get('data.member-position', $i) ?></div>
									<?php endif ; ?>
								</div>
							</div>
						<?php endfor ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
    $("#acm-testimonial-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
    	rtl:<?php echo($dir == 0) ? 'false' : 'true' ;?>,
      center: true,
      margin: 30,
      dots: true,
      loop: true,
      responsive : {
		0 : {
		    items: 1,
		},
		768 : {
		    items: 2,
		},
		992 : {
		    items: 3
		}
	}
    });
  });
})(jQuery);
</script>