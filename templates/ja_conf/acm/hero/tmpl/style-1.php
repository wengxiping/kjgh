<?php
  $heroHeading    = $helper->get('hero-heading');
  $heroTitle      = $helper->get('hero-title');
  $heroIntro      = $helper->get('hero-intro');
  $btnFirstText   = $helper->get('hero-btn1-text');
  $btnFirstLink   = $helper->get('hero-btn1-link');
  $btnFirstClass  = $helper->get('hero-btn1-class');
  $btnSecondText  = $helper->get('hero-btn2-text');
  $btnSecondLink  = $helper->get('hero-btn2-link');
  $heroBg         = $helper->get('hero-bg');
  $heroImg        = $helper->get('hero-img');
  $heroImgMask    = $helper->get('hero-bg-mask');
  $heroIcon       = $helper->get('hero-icon-mouse');
  $heroTitleMask  = $helper->get('hero-mask-title');
?>

<div id="acm-hero-<?php echo $module->id ;?>" class="acm-hero style-1" style="background: url(<?php echo $heroBg; ?>) no-repeat center top;">
	<?php if($heroImgMask) :?>
		<div class="mask" style="background-image: url(<?php echo $heroImgMask; ?>);"></div>
	<?php endif ;?>

	<?php if($heroIcon) : ?>
  	<div class="hero-icon">
  		<img src="<?php echo $heroIcon ;?>" alt="icon" />
  	</div>
  <?php endif; ?>

  <div class="vertical-lines">
    <div class="container-wrap">
      <div class="line-wrap container">
        <div class="line-item line-1"></div>
        <div class="line-item line-2"></div>
        <div class="line-item line-3"></div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="hero-content-wrap" 
    <?php if($heroTitleMask) :?>
    	style="background-image: url(<?php echo $heroTitleMask; ?>);"
    <?php endif ;?> >
    
    	<?php if($heroImg) : ?>
        <div class="hero-img">
          <img src="<?php echo $heroImg; ?>" alt="<?php echo $heroHeading; ?>" />
        </div>
      <?php endif; ?>
    	
    	<?php if($heroHeading || $heroIntro || $btnFirstText || $btnSecondText) : ?>
			<div class="hero-content">
	      <?php if($heroHeading) : ?>
	      <div class="title-intro">
	        <?php echo $heroHeading; ?>
	      </div>
	      <?php endif; ?>
	      
	      <?php if($heroTitle) : ?>
	      <div class="hero-title">
	        <?php echo $heroTitle; ?>
	      </div>
	      <?php endif; ?>
	      
	      <?php if($btnFirstText || $btnSecondText ) : ?>
	      <div class="hero-btn-actions">
	      	<?php if($btnFirstText): ?>
	        <a href="<?php echo $btnFirstLink; ?>" title="<?php echo $btnFirstText; ?>" class="btn <?php echo $btnFirstClass; ?>"><?php echo $btnFirstText; ?></a>
	        <?php endif; ?>
	        <?php if($btnSecondText): ?>
	        <a href="<?php echo $btnSecondLink; ?>" title="<?php echo $btnSecondText; ?>" class="btn"><?php echo $btnSecondText; ?><span class="fa fa-angle-right"></span></a>
	        <?php endif; ?>
	      </div>
	      <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  (function($){
    $(document).ready(function(){
      
      $heightContent = $('#acm-hero-<?php echo $module->id ;?> .hero-content-wrap').outerHeight();
      $heightWindow = $(window).outerHeight();
      if($heightContent > $heightWindow) {
        $('#acm-hero-<?php echo $module->id ;?>').addClass('normal');

        $heightBlock = $('#acm-hero-<?php echo $module->id ;?>').outerHeight();
        $('#acm-hero-<?php echo $module->id ;?> .line-item').css('min-height',$heightBlock);
      };

      $(window).resize(function() {
        $heightContent = $('#acm-hero-<?php echo $module->id ;?> .hero-content-wrap').outerHeight();
        $heightWindow = $(window).outerHeight();

        if($heightContent > $heightWindow) {
          $('#acm-hero-<?php echo $module->id ;?>').addClass('normal');
          $heightBlock = $('#acm-hero-<?php echo $module->id ;?>').outerHeight();
          $('#acm-hero-<?php echo $module->id ;?> .line-item').css('min-height',$heightBlock);
        };
      })
    });
  })(jQuery);
</script>