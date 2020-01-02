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
	$fullWidth = $helper->get('full-width');
?>
<?php if($module->showtitle || $helper->get('block-intro')): ?>
<h3 class="section-title ">
	<?php if($module->showtitle): ?>
		<span><?php echo $module->title ?></span>
	<?php endif; ?>
</h3>
<?php endif; ?>

<div class="uber-stats style-1 <?php echo $helper->get('acm-style'); ?> <?php if($fullWidth): ?>full-width <?php endif; ?>">
	<?php if(!$fullWidth): ?><div class="container"><?php endif; ?>
  
  <ul class="stats-list <?php if(!$fullWidth): ?>row<?php endif; ?>">
    <?php $count=$helper->getRows('data-style-2.stats-count'); ?>
    <?php for ($i=0; $i<$count; $i++) : ?>
    <?php if ($helper->get ('data-style-2.stats-count', $i)) : ?>
    <?php 
    	$colNumber = 2;
			if($count<12 && (12%$count==0)) {
				$colNumber = $count;
			} elseif(12%$count!=0) {
				$colNumber = $count-1;
			}
		?>
    <li class="col-md-<?php echo (12/$colNumber) ?> col-sm-6 stats-asset" <?php if(12%$count!=0 && $i==($count-1)): ?>style="margin-top: 40px;"<?php endif; ?> >
  <?php if($helper->get ('data-style-2.font-icon', $i)) : ?>
    <span class="stats-item-icon" <?php if($helper->get ('data-style-2.stats-color', $i)): ?> style="color: <?php echo $helper->get ('data-style-2.stats-color', $i) ?>;" <?php endif; ?>>
    	<i class="fa <?php echo $helper->get ('data-style-2.font-icon', $i) ?>"></i>
    </span>
  <?php endif ;?>
  
	<?php if($helper->get('data-style-2.img-icon', $i)) : ?>
	<div class="img-icon">
		<img src="<?php echo $helper->get('data-style-2.img-icon', $i) ?>" alt="" />
	</div>
	<?php endif ; ?>
    
    <span class="stats-item-counter" data-to="<?php echo $helper->get ('data-style-2.stats-count', $i) ?>" data-from="0" data-speed="2000" data-refresh-interval="20">
			<?php echo $helper->get ('data-style-2.stats-count', $i) ?>
		</span>
		
    <?php if ($helper->get ('data-style-2.stats-name', $i)) : ?>
      <span class="stats-subject"><?php echo $helper->get ('data-style-2.stats-name', $i) ?></span>
    <?php endif; ?>
    </li>
    <?php endif; ?>
  <?php endfor;?>
  </ul>
  <?php if(!$fullWidth): ?></div><?php endif; ?>
</div>