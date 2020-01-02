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

<div class="acm-cta style-2">
	<div class="container">
		<div class="row <?php echo $helper->get('block-extra-class'); ?>">
			<?php if($helper->get('img')): ?>
			<div class="col-xs-12 col-sm-4 hidden-sm hidden-xs acm-cta-image">
				<img alt="" src="<?php echo $helper->get('img') ?>">
			</div>
			<?php endif; ?>

			<div class="col-xs-12 col-sm-8 acm-cta-intro">
				<?php if($module->showtitle): ?>
				<h3 class="section-title ">
						<span><?php echo $module->title ?></span>
				</h3>
				<?php endif; ?>
				<?php if($helper->get('block-intro')): ?>
					<div class="container-sm section-intro"><?php echo $helper->get('block-intro'); ?></div>
				<?php endif; ?>	

				<div class="acm-cta-buttons">
					<?php $count = $helper->getRows('data.button');  ?>
				
					<?php for ($i=0; $i<$count; $i++) : ?>
						<?php if($helper->get('data.button',$i) && $helper->get('data.link',$i)): ?>
						<a href="<?php echo $helper->get('data.link',$i) ?>" class="btn <?php if($helper->get('data.button_class',$i)): echo $helper->get('data.button_class',$i); else: echo 'btn-default'; endif; ?>">
							<div class="pull-left"><?php echo $helper->get('data.button',$i) ?></div>
							<?php if($helper->get('data.btn-icon',$i)) : ?><div class="pull-right"><i class="<?php echo $helper->get('data.btn-icon',$i) ?>"></i></div><?php endif ;?>
						</a>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</div>
</div>