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

<div class="acm-features style-6">
	<div class="features-item">
		<div class="container">
			<div class="row">
				<div class="col-md-4 pull-left">
					<?php if ($moduleSub): ?>
						<div class="sub-heading">
							<span><?php echo $moduleSub; ?></span>	
						</div>
					<?php endif; ?>
				</div>
				<div class="col-md-8 pull-right">
					<div class="content-right">
						<?php if($module->showtitle) : ?>
							<div class="title">
								<h2>
									<?php echo $modTitle ?>
								</h2>
							</div>
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
</div>
