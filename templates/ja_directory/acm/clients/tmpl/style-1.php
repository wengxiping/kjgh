<?php
	$fullWidth 					= $helper->get('full-width');
	$columns						= $helper->get('columns');
	$style							= $helper->get('acm-style');
	$count 							= $helper->getRows('client-item.client-logo');
	$gray								= $helper->get('img-gray');
	$opacity						= $helper->get('img-opacity');
	$float = 0;

	if ($opacity=="") {
		$opacity = 100;
	}

	if (100%$columns) {
		$float = 0.01;
	}

	$blockImg 				= $helper->get('block-bg');
	$blockImgBg  			= 'background-image: url("'.$blockImg.'"); background-repeat: no-repeat; background-size: cover; background-position: center center;';

    $document = JFactory::getDocument();
    $document->addStyleSheet("templates/ja_directory/acm/clients/css/new-style.css");
?>
<div class="xp-section-container <?php echo $helper->get('block-extra-class'); ?>" <?php if($blockImg): echo 'style="'.$blockImgBg.'"'; endif; ?>>
	<?php if($module->showtitle || $helper->get('block-intro')): ?>
	<div class="section-title">
        <img src="templates/ja_directory/acm/gallery/images/left-arrow.png">
		<?php if($module->showtitle): ?>
			<div class="txt"><?php echo $module->title ?></div>
		<?php endif; ?>
		<?php if($helper->get('block-intro')): ?>
			<p class="container-sm section-intro hidden-xs"><?php echo $helper->get('block-intro'); ?></p>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<div id="uber-cliens-<?php echo $module->id; ?>" class="uber-cliens style-1 <?php if($gray): ?> img-grayscale <?php endif; ?> <?php echo $style; ?> <?php if($fullWidth): ?>full-width <?php endif; ?> <?php if($count > $columns): ?> multi-row <?php endif; ?>">
		<?php if(!$fullWidth): ?><div class="xp-container"><?php endif; ?>

		 <?php
		 	for ($i=0; $i<$count; $i++) :
			$clientName = $helper->get('client-item.client-name',$i);
			$clientLink = $helper->get('client-item.client-link',$i);
			$clientLogo = $helper->get('client-item.client-logo',$i);
//			print_r($clientLogo);
			if ($i%$columns==0) echo '<div class="xp-row">';
		?>

			<div class="new-client-img">
				<div class="new-client-img-c" style="background: url('<?php echo $clientLogo;?>') no-repeat center center;width: 100%;height: 100%;background-size: contain;">
<!--					-->
<!--                    <a href="--><?php //echo $clientLink;?><!--"><img src=""></a>-->
<!--                    --><?php //if($clientLink):?><!--<a href="--><?php //echo $clientLink; ?><!--" title="--><?php //echo $clientName; ?><!--" >--><?php //endif; ?>
<!--						<img class="img-responsive" alt="--><?php //echo $clientName; ?><!--" src="--><?php //echo $clientLogo; ?><!--">-->
<!--					--><?php //if($clientLink):?><!--</a>--><?php //endif; ?>
				</div>
			</div>

		 	<?php if ( ($i%$columns==($columns-1)) || $i==($count-1) )  echo '</div>'; ?>

	 	<?php endfor ?>

	  <?php if(!$fullWidth): ?></div><?php endif; ?>
	</div>

	<?php if($opacity>=0 && $opacity<=100): ?>
	<script>
	(function ($) {
		$(document).ready(function(){
			$('#uber-cliens-<?php echo $module->id ?> .client-img img.img-responsive').css({
				'filter':'alpha(opacity=<?php echo $opacity ?>)',
				'zoom':'1',
				'opacity':'<?php echo $opacity/100 ?>'
			});
		});
	})(jQuery);
	</script>
	<?php endif; ?>
</div>

