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

$count = $helper->count('gallery-image');
$col = $helper->get('number_col');
$viewType = $helper->get('view_type');
?>
<div id="acm-gallery-<?php echo $module->id ;?>" class="acm-gallery clearfix">

  <?php if($viewType) :?>
  <div class="owl-carousel owl-theme">
  <?php endif ;?>

		<?php
      for ($i=0; $i < $count; $i++) :
    ?>
		<div class="item col-xs-6 col-sm-3 col-md-<?php echo (12/$col); ?>">
      <div class="item-inner">
          <img src="<?php echo $helper->get('gallery-image', $i); ?>" alt="" />
      </div>
		</div>
		<?php endfor; ?>

  <?php if($viewType) :?>
  </div>
  <?php endif ;?>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
    $("#acm-gallery-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
      items: <?php echo $col ;?>,
      loop: true,
      singleItem : false,
      itemsScaleUp : true,
      navigation : true,
      navigationText : ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
      pagination: false,
      paginationNumbers : false,
      merge: false,
      mergeFit: true,
      slideBy: 1,
      autoPlay: false,
      responsive : {
        // breakpoint from 0 up
        0 : {
          items: 1
        },

        // breakpoint from 480 up
        480 : {
          items: 2
        },

        // breakpoint from 768 up
        768 : {
          items: 3
        },

        // breakpoint from 992 up
        992 : {
          items: 4
        },

        // breakpoint from 1440 up
        1440 : {
          items: <?php echo $col ;?>
        }
      }
    });
  });
})(jQuery);
</script>