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
	$count = $helper->getRows('title');
	$column = 12/($helper->get('columns'));

	$moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');
?>

<div class="acm-statics style-1">
	<div class="row">
		<?php for ($i=0; $i<$count; $i++) : ?>
			<div class="col col-sm-6 col-md-3 col-lg-<?php echo $column ?>">
				<div class="statics-item">
					<?php if($helper->get('link', $i)) : ?>
						<a href="<?php echo $helper->get('link', $i) ?>">
					<?php endif ; ?>

						<?php if($helper->get('number', $i)) : ?>
							<div class="number h1">
								<span data-to="<?php echo $helper->get('number', $i) ; ?>" data-from="0" data-speed="2000" data-refresh-interval="20"><?php echo $helper->get('number', $i) ; ?></span>
							</div>
						<?php endif ; ?>
						
						<?php if($helper->get('title', $i)) : ?>
							<h5>
								<?php echo $helper->get('title', $i) ?>
							</h5>
						<?php endif ; ?>

					<?php if($helper->get('link', $i)) : ?>
						</a>
					<?php endif ; ?>
				</div>
			</div>
		<?php endfor ?>
	</div>
</div>