<?php 
	defined('_JEXEC') or die;

	$featuresImg 				= $helper->get('block-bg');
	$fullWidth = $helper->get('full-width');

	$svgStyle1 = '
		<svg class="item__svg" width="50px" height="50px" viewBox="0 0 50 50">
			<g class="">
				<path id="deco1" d="M 18.9,8.037 C 23.26,4.667 35.25,6.706 35.09,12.41 34.95,17.34 31.17,16.8 31.24,24.81 31.29,30.11 38.25,31.92 36.85,37.91 34.94,46.06 13.77,46.75 11.76,38.63 9.868,30.97 17.15,29.22 18.36,24.01 19.57,18.82 12.38,13.07 18.9,8.037 Z" />
			</g>
		</svg>';

	$svgEffect1 = 'data-animation-path-duration="1200" data-animation-path-easing="easeOutExpo" data-path-elasticity="400" data-morph-path="M 18.4,12.74 C 23.54,9.238999999999999 31.97,7.926999999999998 35.99,13.22 38.32,16.3 35.71,21.66 35.58,25.88 35.48,29.12 37.13,33.29 35.29,35.6 30.61,41.44 20.51,41.93 15.37,36.72 12.38,33.68 12.86,27.21 13.61,22.52 14.21,18.78 15.7,14.57 18.4,12.74 Z"';

	$svgStyle2 = '
		<svg class="item__svg" width="50px" height="50px" viewBox="0 0 50 50">
			<g class="">
				<path id="deco2" d="M 4.799,29.57 C 4.425,25.29 7.233,19.57 11.04,16.7 16.96,12.24 25.9,10.61 32.96,12.88 38.13,14.54 42.8,19.3 44.37,24.5 45.33,27.7 46.49,32.24 42.46,34.35 36.9,37.27 30.42,31.01 23.91,31.9 19.09,32.55 15.3,38.0 10.76,37.34 6.497,36.73 5.068,32.64 4.799,29.57 Z" />
			</g>
		</svg>';

	$svgEffect2 = 'data-animation-path-duration="1200" data-animation-path-easing="easeOutExpo"  data-path-elasticity="400" data-morph-path="M 3.003,24.04 C 3.135,18.62 3.967,12.0 8.595,8.614 16.25,3.012 30.2,0.3296 37.87,5.908 41.31,8.414 38.36,14.0 39.51,17.88 41.16,23.44 47.78,28.07 46.95,33.77 46.26,38.41 42.09,42.9 37.38,44.77 28.61,48.25 16.18,48.24 8.779,42.7 3.304,38.6 2.846,30.5 3.003,24.04 Z"';

	$svgStyle3 = '
		<svg class="item__svg" width="50px" height="50px" viewBox="0 0 50 50">
			<g class="">
				<path id="deco3" d="M 45.15,18.58 C 44.15,26.62 33.96,30.5 27.23,35.02 20.77,39.36 22.67,44.47 18.26,44.79 13.28,45.14 8.397,39.99 6.637,35.31 3.46,26.84 4.116,14.18 11.2,8.544 18.61,2.6329999999999984 31.38,5.4099999999999994 39.6,10.14 42.52,11.82 45.56,15.24 45.15,18.58 Z" />
			</g>
		</svg>';

	$svgEffect3 = 'data-animation-path-duration="1200" data-animation-path-easing="easeOutExpo"  data-path-elasticity="400" data-morph-path="M 36.81,4.642 C 46.1,9.669 47.37,26.62 42.23,35.84 37.91,43.6 25.96,48.48 17.5,45.75 10.75,43.57 1.265,32.98 6.093,27.77 9.518,24.08 15.4,37.93 19.42,34.89 25.07,30.6 11.6,20.41 14.84,14.09 18.48,7.002 29.8,0.8455 36.81,4.642 Z"';
?>

<div class="acm-features <?php echo $helper->get('features-style'); ?> style-1">
	<?php if($helper->get('features-description')) : ?>
		<h2 class="features-description"><?php echo $helper->get('features-description'); ?></h2>
	<?php endif ; ?>
	
	<div class="row">
	<?php $count = $helper->getRows('data.title'); ?>
	<?php $column = 12/($helper->get('columns')); ?>
	<?php for ($i=0; $i<$count; $i++) : ?>
	
		<div class="features-item item col-sm-<?php echo $column ?>"
			<?php if($helper->get('data.svg-style', $i) == 1) {
						echo $svgEffect1;
			}elseif($helper->get('data.svg-style', $i) == 2) {
					echo $svgEffect2;
			} elseif($helper->get('data.svg-style', $i) == 3) {
					echo $svgEffect3;
			}?> >
			
			<?php if($helper->get('data.font-icon', $i) || $helper->get('data.img-icon', $i)) : ?>
			<div class="feature-icon">
				<div class="item-svg">
					<?php if($helper->get('data.svg-style', $i) == 1) {
						echo $svgStyle1;
					}elseif($helper->get('data.svg-style', $i) == 2) {
							echo $svgStyle2;
						} elseif($helper->get('data.svg-style', $i) == 3) {
							echo $svgStyle3;
						}?>
				</div>

				<?php if($helper->get('data.font-icon', $i)) : ?>
					<div class="font-icon">
						<span class="<?php echo $helper->get('data.font-icon', $i) ; ?>"></span>
					</div>
				<?php endif ; ?>

				<?php if($helper->get('data.img-icon', $i)) : ?>
					<div class="img-icon">
						<img src="<?php echo $helper->get('data.img-icon', $i) ?>" alt="" />
					</div>
				<?php endif ; ?>
			</div>
			<?php endif ; ?>
			
			<?php if($helper->get('data.title', $i)) : ?>
				<h3><?php echo $helper->get('data.title', $i) ?></h3>
			<?php endif ; ?>
			
			<?php if($helper->get('data.description', $i)) : ?>
				<span><?php echo $helper->get('data.description', $i) ?></span>
			<?php endif ; ?>

			<?php if($helper->get('data.link', $i)) : ?>
				<div class="features-action">
					<a href="<?php echo $helper->get('data.link', $i) ?>" title="<?php echo $helper->get('data.title', $i) ?>"><span class="fas fa-arrow-right"></span><span class="element-invisible hidden">empty</span></a>
						
				</div>
			<?php endif ; ?>
		</div>
	<?php endfor ?>
	</div>
</div>

<script src="<?php echo (T3_TEMPLATE_URL.'/acm/features-intro/js/anime.min.js') ;?>"></script>
<script src="<?php echo (T3_TEMPLATE_URL.'/acm/features-intro/js/main.js') ;?>"></script>

