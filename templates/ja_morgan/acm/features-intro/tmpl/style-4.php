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
	$count = $helper->getRows('title');
	$moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');

?>

<div class="acm-features align-center style-4">
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

		<?php if($helper->get('ft-desc')) : ?>
		<h3><?php echo $helper->get('ft-desc') ; ?></h3>
		<?php endif; ?>
	<!-- // Module Title -->
	</div>
	<?php endif ; ?>
	<div class="row equal-height equal-height-child">
		<?php for ($i=0; $i<$count; $i++) : ?>
			<div class="col col-md-12 col-lg-6">
				<div class="features-item content-<?php echo $helper->get('content-align', $i) ?> <?php echo $helper->get('content-bg', $i) ?>">

					<div class="img-intro" style="background-image: url('<?php echo $helper->get('img-intro', $i) ?>');">
					</div>	

					<div class="content-wrap">
						<?php if($helper->get('font-icon', $i)) : ?>
							<div class="font-icon">
								<span class="<?php echo $helper->get('font-icon', $i) ; ?>"></span>
							</div>
						<?php endif ; ?>

						<?php if($helper->get('img-icon', $i)) : ?>
							<div class="img-icon">
								<img alt="<?php echo $helper->get('title', $i) ?>" src="<?php echo $helper->get('img-icon', $i) ; ?>" />
							</div>
						<?php endif ; ?>
						
						<?php if($helper->get('title', $i)) : ?>
							<h4><?php echo $helper->get('title', $i) ?></h4>
						<?php endif ; ?>
						
						<?php if($helper->get('description', $i)) : ?>
							<p><?php echo $helper->get('description', $i) ?></p>
						<?php endif ; ?>

						<?php if($helper->get('ft-link-title', $i)) : ?>
							<a href="<?php echo $helper->get('ft-link', $i) ?>"><?php echo $helper->get('ft-link-title', $i) ?><span class="ion-ios-arrow-round-forward"></span></a>
						<?php endif ; ?>
					</div>
				</div>
			</div>
		<?php endfor ?>
	</div>

	<?php if($helper->get('title-more')) : ?>
	<div class="link-action">
		<a href="<?php echo $helper->get('link-more') ;?>" class="btn btn-primary" title="<?php echo $helper->get('title-more') ;?>">
			<?php echo $helper->get('title-more') ;?>
			<span class="ion-ios-arrow-round-forward"></span>	
		</a>
	</div>
	<?php endif ; ?>
</div>