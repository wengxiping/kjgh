<?php
/**
 * ------------------------------------------------------------------------
 * Uber Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

	defined('_JEXEC') or die;
	
	$doc = JFactory::getDocument();
	$doc->addScript (T3_TEMPLATE_URL.'/acm/gallery/js/isotope.pkgd.min.js');
	$doc->addScript (T3_TEMPLATE_URL.'/acm/gallery/js/ekko-lightbox.js');
	$doc->addScript (T3_TEMPLATE_URL.'/acm/gallery/js/imagesloaded.pkgd.min.js');
	
	$col 								= $helper->get('col') ; 
?>

<div class="acm-gallery style-1">
	<div class="isotope-layout">
		<div class="isotope" style="margin: 0 -<?php echo $helper->get('gutter')/2; ?>px">
			<?php if($helper->get('text-1')) :?>
				<div class="mask"></div>
			<?php endif ;?>
			
			<div class="item grid-sizer grid-xs-<?php echo $helper->get('colmb'); ?> grid-sm-<?php echo $helper->get('coltb'); ?> grid-md-<?php echo $helper->get('coldt'); ?>"></div>
			
			<?php
				$count = $helper->getRows('gallery.img'); 
			 ?>
			 
			 <?php for ($i=0; $i<$count; $i++) : ?>
			 <?php $itemsize = $helper->get('gallery.selectitem', $i) ; ?>
				<?php if($helper->get ('gallery.img', $i)):?>
					<div class="item item-<?php echo $itemsize; ?> grid-xs-<?php echo $helper->get('colmb'); ?> grid-sm-<?php echo $helper->get('coltb'); ?> grid-md-<?php echo $helper->get('coldt'); ?> <?php echo $helper->get('animation') ?>" style="padding: 0 <?php echo $helper->get('gutter')/2; ?>px <?php echo $helper->get('gutter'); ?>px;">
						<div class="item-image"><a href="<?php echo $helper->get ('gallery.img', $i) ?>" data-parent=".isotope"  data-toggle="lightbox" data-gallery="gallery" ><img src="<?php echo $helper->get ('gallery.img', $i) ?>" alt="gallery" ></a></div>
					</div>
				<?php endif ; ?>
			<?php endfor ?>
			
		</div>

		<?php if($helper->get('text-1')): ?>
			<div class="caption">
				<p><?php echo $helper->get('text-1') ?></p>
			</div>
		<?php endif ;?>
	</div>
</div>