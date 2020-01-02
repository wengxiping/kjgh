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

$doc    = JFactory::getDocument();
//$doc->addStyleSheet("components/com_jblance/css/customer/new_featured.css");

$count = $helper->count('btn-title');
$check_arrow = $helper->get('hero-arrow') ;
$modTitle       = $module->title;
$moduleSub = $params->get('sub-heading');
$mod            = $module->id;

?>

<div id="acm-hero-<?php echo $mod; ?>" class="acm-hero style-1 align-<?php echo $helper->get('hero-align') ;?>">
	<div class="hero-item <?php echo $helper->get('hero-space') ;?>">

		<?php if($helper->get('ft-bg')) : ?>
			<div class="ft-bg<?php if($helper->get('ft-bg-xs')) echo ' hidden-xs' ?>" style="background-image: url('<?php echo $helper->get('ft-bg') ;?>');"></div>
		<?php endif ; ?>

		<?php if($helper->get('ft-bg-xs')) : ?>
			<div class="ft-bg-xs visible-xs" style="background-image: url('<?php echo $helper->get('ft-bg-xs') ;?>');"></div>
		<?php endif ; ?>

		<div class="container">
			<div class="group-item">
				<div class="wrap-content">
					<?php if ($moduleSub): ?>
						<div class="sub-heading">
							<span><?php echo $moduleSub; ?></span>
						</div>
					<?php endif; ?>

					<?php if($module->showtitle) : ?>
						<p class='title'><?php echo $modTitle ?></p>
					<?php endif ; ?>

					<?php if($helper->get('description')) : ?>
					<!-- 设计稿没有 暂时隐藏-->
						<p class="lead" ><?php echo $helper->get('description') ?></p>
					<?php endif ; ?>

					<?php if($helper->get('btn-title')) : ?>
					<!-- 设计稿没有 暂时隐藏-->
						<div class="btn-action" >
							<?php for ($i=0; $i < $count; $i++) :?>
								<a class="btn btn-<?php echo $helper->get('btn-type', $i); ?>" href="<?php echo $helper->get('btn-link', $i); ?>"><?php echo $helper->get('btn-title', $i) ?> <span class="icon ion-ios-arrow-round-forward"></span>
							</a>
							<?php endfor; ?>
						</div>
					<?php endif ; ?>
				</div>

				<?php if($helper->get('id-video')) : ?>
					<div class="img-icon">
						<a class="html5lightbox" data-group="myvideo-<?php echo $mod; ?>" href="https://www.youtube.com/watch?v=<?php echo $helper->get('id-video') ?>" title="">
						    <i class="fa fa-play" aria-hidden="true"></i>
						</a>
					</div>
				<?php endif ; ?>
			</div>

			<!-- Arrow -->
			<?php if($check_arrow == "yes"): ?>
			  	<span id="next-section" class="icon-bottom">
			    	<span class="ion-ios-arrow-down" aria-hidden="true"></span>
			    </span>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
(function($){
	jQuery(document).ready(function($) {

		// Arrow buttom
	  	if($('.hero-item #next-section').length){
		    $posNext = $('#next-section').offset().top;
		}

	    $('#next-section').on('click', function(){
	      $("html, body").animate({scrollTop: $posNext}, 700);
	      return false;
	    });


	    //Popup video
	    $("#acm-hero-<?php echo $mod; ?> .html5lightbox").html5lightbox({
	      autoslide: true,
	      showplaybutton: false,
	      jsfolder: "<?php echo JUri::base(true).'/templates/ja_morgan/js/html5lightbox/' ?>"
	    });
	});
})(jQuery);
</script>
