<?php
  $heroStyle      = $helper->get('hero-style');
  $heroTextPos    = $helper->get('hero-content-position');
  $heroTextAlign  = $helper->get('hero-text-align');
  $heroHeading    = $helper->get('hero-heading');
  $heroIntro      = $helper->get('hero-intro');
  $btnFirstText   = $helper->get('hero-btn1-text');
  $btnFirstLink   = $helper->get('hero-btn1-link');
  $btnSecondText  = $helper->get('hero-btn2-text');
  $btnSecondLink  = $helper->get('hero-btn2-link');
  $heroBg         = $helper->get('hero-bg');
	$heroScreen			= $helper->get('hero-screen');
?>

<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>">
  <div class="acm-hero <?php echo ($heroStyle .' '. $heroTextPos. ' '. $heroTextAlign.' '. $heroScreen); ?> <?php if( trim($heroHeading) ) echo ' show-intro'; ?>" style="background-image: url(<?php echo trim($heroBg); ?>);">
    <div class="container">
      <div class="hero-content<?php echo $helper->get('hero-effect'); ?>">

        <?php if( trim($heroHeading)) : ?>
        <div class="hero-heading">
          <?php echo $heroHeading; ?>
        </div>
        <?php endif; ?>

        <?php if( trim($heroIntro)) : ?>
        <div class="hero-intro">
          <?php echo $heroIntro; ?>
        </div>
        <?php endif; ?>

        <?php if( trim($btnFirstText) || trim($btnSecondText) ) : ?>
        <div class="hero-btn-actions">
  			<?php if( trim($btnFirstText)): ?>
          <a href="<?php echo trim($btnFirstLink); ?>" title="<?php echo trim($btnFirstText); ?>" class="btn btn-primary btn-rounded"><?php echo trim($btnFirstText); ?></a>
  				<?php endif; ?>

  				<?php if( trim($btnSecondLink)) :?>
          <a href="<?php echo trim($btnSecondLink); ?>" title="<?php echo trim($btnSecondText); ?>" class="btn btn-rounded btn-border"><?php echo trim($btnSecondText); ?></a>
  				<?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
<style>
    .zs-plan{
       width: 100% !important;height: 100% !important;
    }
    .zs-plan .acm-hero{padding: 0!important;}
    .zs-plan .acm-hero .container{
        padding: 14% 0 14% 0!important;
    }
    .zs-plan .acm-hero .hero-heading{
        height: 69px;
        opacity: 1;
        font-size: 52px;
        font-family: MicrosoftYaHei;
        font-weight: 700;
        color: rgba(255,255,255,1) !important;
        line-height: 69px;
        letter-spacing: 2.08px;
        margin: 0!important;padding: 0!important;
    }
    .zs-plan .acm-hero .hero-intro{
        height: 26px;
        opacity: 1;
        font-size: 20px;
        font-family: MicrosoftYaHei;
        color: rgba(255,255,255,1) !important;
        line-height: 26px;
        letter-spacing: 0px;
        margin: 16px 0 100px 0!important;padding: 0!important;
    }
    .zs-plan .acm-hero .hero-btn-actions{
        margin: 0!important;padding: 0!important;
    }
    .zs-plan .acm-hero .hero-btn-actions .btn{
        padding: 0!important;margin: 0!important;
        width: 200px!important;
        height: 42px!important;
        line-height: 42px;
        opacity: 1;
        font-size: 16px!important;
        font-family: 微软雅黑!important;
        font-weight: lighter!important;
        color: rgba(255,255,255,1);
        letter-spacing: 2px;
        text-align: center!important;
        background: none!important;
        border: 1px solid #FFFFFF!important;
        box-shadow: none!important;
        border-radius: 0!important;

    }
    .zs-plan .acm-hero .hero-btn-actions .btn:hover{
        opacity: 0.8;
    }
</style>
