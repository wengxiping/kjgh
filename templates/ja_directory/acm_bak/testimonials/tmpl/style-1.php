<?php
/**
 * ------------------------------------------------------------------------
 * JA Directory Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
?>

<?php
  $count = $helper->getRows('data-special.testimonial-text');
  $textColor = $helper->get('text-color');
  $authorTextColor = $helper->get('author-info-color');
  $fullWidth = $helper->get('full-width');
?>
<div class="acm-testimonials style-1 <?php if($fullWidth): ?>full-width <?php endif; ?>">
  <?php if(!$fullWidth): ?><div class="container"><?php endif; ?>

    <!-- BEGIN: TESTIMONIALS STYLE 1 -->
  	<div id="acm-testimonials-<?php echo $module->id ?>" class="testimonial-content carousel slide" data-ride="carousel" data-interval="false">
	
      <div class="carousel-inner">
       <?php for ($i=0; $i<$count; $i++) : ?>
        <div class="item <?php if($i<1) echo "active"; ?>">
			<?php if ($helper->get ('data-special.author-img', $i)) : ?>
	        <div class="author-info" <?php if($authorTextColor) : ?> style="color: <?php echo $authorTextColor; ?>;" <?php endif; ?>>
	          	<span class="author-image"><img src="<?php echo $helper->get ('data-special.author-img', $i) ?>" alt="Author Avatar" /></span>
	        </div>
			 <?php endif; ?>
        
	        <?php if ($helper->get ('data-special.testimonial-text', $i) || $helper->get ('data-special.author-name', $i) || $helper->get ('data-special.author-title', $i) ) : ?>
	        <div class="author-detail">
	           <p class="testimonial-text" <?php if($textColor) : ?> style="color: <?php echo $textColor; ?>;" <?php endif; ?>>
	            <?php echo $helper->get ('data-special.testimonial-text', $i) ?>
	           </p> 
	           
	          <?php if ( $helper->get ('data-special.author-name', $i) || $helper->get ('data-special.author-title', $i) ) : ?>
	            <div class="author-info-text" style="color: <?php echo $authorTextColor; ?>;">
	              <span class="author-name"><?php echo $helper->get ('data-special.author-name', $i) ?>, </span>
	            </div>
	          <?php endif; ?>
	        </div>
	        <?php endif; ?>
        </div>
       <?php endfor ?>
      </div>
	
	<ol class="carousel-indicators">
	<?php for ($i=0; $i<$count; $i++) : ?>
		<li data-target="#acm-testimonials-<?php echo $module->id ?>" data-slide-to="<?php echo $i ;?>" class="<?php if($i==0) echo 'active' ?>"></li>
	<?php endfor ?>
	</ol>
      
			<!-- Controls -->
			<?php if($helper->get('enable-controls')): ?>
			<a data-slide="prev" role="button" href="#acm-testimonials-<?php echo $module->id ?>" class="left carousel-control"><i class="fa fa-angle-left"></i></a>
			<a data-slide="next" role="button" href="#acm-testimonials-<?php echo $module->id ?>" class="right carousel-control"><i class="fa fa-angle-right"></i></a>
			<?php endif; ?>
    </div>
    <!-- END: TESTIMONIALS STYLE 1 -->
    
  <?php if(!$fullWidth): ?></div><?php endif; ?>

</div>