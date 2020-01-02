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
$pricingStyle = $helper->get('pricing-style');
?>

<div class="acm-pricing" <?php if($helper->get('block-bg')) : ?> style="background-image: url('<?php echo $helper->get('block-bg'); ?>')"<?php endif; ?> >
	<div class="container pricing-table style-1">
		<div class="row">

			<?php
			$count = $helper->getCols('data');
			$features_count = $helper->getRows('data');
			if (!$count || !$features_count) {
				$count = $helper->count('pricing-col-name');
				$features_count = $helper->count('pricing-row-name');
			}
			?>

			<?php for ($col = 0; $col < $count; $col++) : ?>
				<div
					class="col col-md-<?php echo 12 / ($count); ?> <?php if ($helper->get('data.pricing-col-featured', $col)): ?> col-featured <?php endif ?>">
					<div class="col-header text-center">
						<h2><?php echo $helper->get('data.pricing-col-name', $col) ?></h2>
						<p><span class="big-number"><?php echo $helper->get('data.pricing-col-price', $col) ?></span></p>
					</div>
					<div class="col-body">
						<ul>
							<?php for ($row = 0; $row < $features_count; $row++) :
								$feature = $helper->getCell('data', $row, 0);
								$value = $helper->getCell('data', $row, $col + 1);
								$type = $value[0];
								
								if (!$feature) {
									// compatible with old data
									$feature = $helper->get('pricing-row-name', $row);
									$tmp = $helper->get('pricing-row-supportfor', $row);
									$value = ($tmp & pow(2, $col)) ? 'b1' : 'b0'; // b1: yes, b0: no
									$type = 'b'; // boolean
								}
								?>

							<?php if ($type == 't'): ?>
								<li class="row<?php echo($row % 2); ?>"><i class="fa fa-check-square"></i><?php echo substr($value, 1); ?></li>
							<?php elseif ($value == 'b1'): ?>
								<li class="row<?php echo($row % 2); ?>"><i class="fa fa-check-square"></i><?php echo $feature; ?></li>
							<?php endif ?>

							<?php endfor; ?>
						</ul>
					</div>
					<div class="col-footer text-center">
						<a
							class="btn btn-block btn-lg <?php if ($helper->get('data.pricing-col-featured', $col)): ?> btn-inverse <?php else: ?> btn-primary <?php endif ?>"
							title="<?php echo $helper->get('data.pricing-col-button', $col); ?>"
							href="<?php echo $helper->get('data.pricing-col-buttonlink', $col); ?>"><?php echo $helper->get('data.pricing-col-button', $col); ?></a>
					</div>
				</div>
			<?php endfor; ?>

		</div>
	</div>
</div>