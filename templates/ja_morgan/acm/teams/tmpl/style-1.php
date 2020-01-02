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
	$column = 12/($helper->get('columns'));

	$modTitle       = $module->title;
	$moduleSub = $params->get('sub-heading');
?>

<div class="acm-teams style-1 align-center">
	<?php if($module->showtitle || $moduleSub) : ?>
	<div class="section-title">
		<?php if ($moduleSub): ?>
			<div class="sub-heading">
				<span><?php echo $moduleSub; ?></span>		
			</div>
		<?php endif; ?>

		<?php if($module->showtitle) : ?>
			<h3>
				<?php echo $modTitle ?>
			</h3>
		<?php endif ; ?>
	</div>
	<?php endif ; ?>
	
	<div class="row equal-height equal-height-child">
		<?php for ($i=0; $i<$count; $i++) : ?>
			<div class="col col-sm-6 col-md-6 col-lg-<?php echo $column ?>">
				<div class="teams-item">
					<?php if($helper->get('avatar', $i)) : ?>
						<div class="avatar">
							<?php if($helper->get('link', $i)) : ?>
								<a href="<?php echo $helper->get('link', $i) ?>" title="<?php echo $helper->get('title', $i) ?>">
							<?php endif ;?>
								<img src="<?php echo $helper->get('avatar', $i) ?>" alt="<?php echo $helper->get('title', $i) ?>" />
							<?php if($helper->get('link', $i)) : ?>
								</a>
							<?php endif ;?>
						</div>
					<?php endif ; ?>
					
					<div class="member-info">
						<?php if($helper->get('title', $i)) : ?>
							<h4>
								<?php if($helper->get('link', $i)) : ?>
									<a href="<?php echo $helper->get('link', $i) ?>">
								<?php endif ; ?>

								<?php echo $helper->get('title', $i) ?>

								<?php if($helper->get('link', $i)) : ?>
									</a>
								<?php endif ; ?>
							</h4>
						<?php endif ; ?>

						<?php if($helper->get('position', $i)) : ?>
							<span class="position">
								<?php echo $helper->get('position', $i) ?>
							</span>
						<?php endif ; ?>

						<?php if($helper->get('link', $i)) : ?>
							<div class="action-link-icon">
								<a class="action" href="<?php echo $helper->get('link', $i) ?>">
									<span class="ion-ios-arrow-round-forward"></span>
								</a>
							</div>
						<?php endif ; ?>
					</div>
				</div>
			</div>
		<?php endfor ?>
	</div>

	<?php if($helper->get('more-title')) : ?>
		<div class="teams-action">
			<a class="btn btn-lg btn-<?php echo $helper->get('color-link') ?>" href="<?php echo $helper->get('more-link') ?>"><?php echo $helper->get('more-title') ?> <span class="ion-android-arrow-forward"></span></a>
		</div>
		<?php endif ; ?>
</div>