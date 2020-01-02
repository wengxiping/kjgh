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
$count = $helper->count('btn-title');
$modTitle       = $module->title;
$moduleSub = $params->get('sub-heading');

?>

<div class="acm-features style-5">
	<div class="features-item" <?php if($helper->get('ft-bg')) : ?>style="background-image: url('<?php echo $helper->get('ft-bg') ;?>');"<?php endif ; ?>>
		<div class="container">
			<div class="group-item">
				<div class="wrap-content">
					<?php if ($moduleSub): ?>
						<div class="sub-heading">
							<span><?php echo $moduleSub; ?></span>		
						</div>
					<?php endif; ?>

					<?php if($module->showtitle) : ?>
						<h2>
							<?php echo $modTitle ?>
						</h2>
					<?php endif ; ?>
					
					<?php if($helper->get('description')) : ?>
						<p class="lead"><?php echo $helper->get('description') ?></p>
					<?php endif ; ?>
					
					<?php if($helper->get('btn-title')) : ?>
						<div class="btn-action">
							<?php for ($i=0; $i < $count; $i++) :?>
								<a class="btn btn-<?php echo $helper->get('btn-type', $i); ?>" href="<?php echo $helper->get('btn-link', $i); ?>"><?php echo $helper->get('btn-title', $i) ?> <span class="icon ion-ios-arrow-round-forward"></span>
							</a>
							<?php endfor; ?>
						</div>
					<?php endif ; ?>
				</div>
			</div>
		</div>
	</div>
</div>
