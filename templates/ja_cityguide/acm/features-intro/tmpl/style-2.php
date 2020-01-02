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
	$count 					= $helper->getRows('data.title');
	$column 				= $helper->get('columns');
?>

<div class="acm-features style-2">
	<div class="row equal-height equal-height-child">
		<?php 
			for ($i=0; $i<$count; $i++) : 
		?>
			<div class="features-item col col-md-<?php echo $column; ?>">
				<div class="features-item-inner">
					<!-- Intro Image -->
					<?php if($helper->get('data.ft-img', $i)) : ?>
						<div class="features-img">
							<div class="img">
								<img src="<?php echo $helper->get('data.ft-img', $i) ?>" alt="" />
							</div>
						</div>
					<?php endif ; ?>
					
					<!-- Title -->
					<?php if($helper->get('data.title', $i)) : ?>
						<h3>
							<?php echo $helper->get('data.title', $i) ?>
						</h3>
					<?php endif ; ?>
					
					<!-- Description -->
					<?php if($helper->get('data.description', $i)) : ?>
						<p><?php echo $helper->get('data.description', $i) ?></p>
					<?php endif ; ?>

					<!-- Button -->
					<?php if($helper->get('data.btn-title', $i)) : ?>
						<div class="feature-action">
							<a class="btn btn-lg btn-<?php echo $helper->get('data.btn-type', $i) ?>" href="<?php echo $helper->get('data.btn-link', $i) ?>"><?php echo $helper->get('data.btn-title', $i) ?></a>
							</div>
					<?php endif ; ?>
				</div>
			</div>
		<?php endfor ?>
	</div>
</div>