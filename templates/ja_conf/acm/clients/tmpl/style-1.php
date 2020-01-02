<?php 
	$fullWidth 					= $helper->get('full-width');
	$columns						= $helper->get('columns');
	$count 							= $helper->getRows('client-item.client-logo');
	$gray								= $helper->get('img-gray');
	$opacity						= $helper->get('img-opacity');
	$clientLink					= $helper->get('client-link');
	$clientTitle				= $helper->get('client-title');
	$float = 0;
	
	if ($opacity=="") {
		$opacity = 100;
	}
	
	if (100%$columns) {
		$float = 0.01;
	}
	 
?>

<div id="uber-cliens-<?php echo $module->id; ?>" class="uber-cliens style-1 <?php if($gray) echo 'img-grayscale'; ?> <?php if($count > $columns): ?> multi-row <?php endif; ?>">
	<?php if($module->showtitle) : ?>
  	<div class="section-title clearfix">
	    <?php if($module->showtitle): ?>
				<h6 class="pull-left"><?php echo $module->title ?></h6>
			<?php endif; ?>

			<?php if($clientTitle) :?>
			<div class="clients-action pull-right">
				<a href="<?php echo $clientLink; ?>" title="" class="btn btn-sm btn-linear"><?php echo $clientTitle; ?></a>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	 <?php 
	 	for ($i=0; $i<$count; $i++) : 
	 	
		$clientName = $helper->get('client-item.client-name',$i);
		$clientLink = $helper->get('client-item.client-link',$i);
		$clientLogo = $helper->get('client-item.client-logo',$i);
		
		if ($i%$columns==0) echo '<div class="row">'; 
	?>
	
		<div class="col-xs-12 client-item" style="width:<?php echo number_format(100/$columns, 2, '.', ' ') - $float;?>%;">
			<div class="client-img">
				<?php if($clientLink):?><a href="<?php echo $clientLink; ?>" title="<?php echo $clientName; ?>" ><?php endif; ?>
					<img class="img-responsive" alt="<?php echo $clientName; ?>" src="<?php echo $clientLogo; ?>">
				<?php if($clientLink):?></a><?php endif; ?>
			</div>
		</div> 
		
	 	<?php if ( ($i%$columns==($columns-1)) || $i==($count-1) )  echo '</div>'; ?>
	 	
 	<?php endfor ?>
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