<?php
  $heroStyle      = $helper->get('hero-style');
  $heroTextPos    = $helper->get('hero-content-position');
  $heroTextAlign  = $helper->get('hero-text-align');
  $heroHeading    = $helper->get('hero-heading');
  $heroIntro      = $helper->get('hero-intro');
  $heroVideo      = $helper->get('hero-video');
  $heroScreen     = $helper->get('hero-screen');
  
  $video_src    = '';
  $video_link    = '';
  $video_type = 'embed';
  if ($heroVideo) {
    $arr = preg_split ('/=/', $heroVideo, 2);
    if (count($arr) == 2) {    
      switch (trim($arr[0])) {
        case 'vimeo':
          $video_src = '//player.vimeo.com/video/' . trim($arr[1]) . '?title=0&amp;byline=0&amp;portrait=0&amp;autoplay=1&amp;loop=1';
          $video_link = trim($arr[1]);
          break;
        case 'youtube':
          $video_src = '//www.youtube.com/embed/' . trim($arr[1]) . '?playlist=' . trim($arr[1]) . '&amp;autoplay=1&amp;loop=1&amp;html5=1';
           $video_link = trim($arr[1]);
          break;
        default:
          break;
      }
    } else {
      $video_type = 'url';
      $video_src = $heroVideo;
    }
  }
?>

<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>">
  <div class="acm-hero style-4 <?php echo ($heroStyle .' '. $heroTextPos. ' '. $heroTextAlign.' '. $heroScreen); ?> <?php if( trim($heroHeading) ) echo ' show-intro'; ?>">
    <div class="video-wrapper">
      <?php if ($heroVideo) : ?>
        <?php if($video_type == 'embed'): ?>
        <iframe frameborder="0" width="100%" height="100%" allowfullscreen="" mozallowfullscreen="" webkitallowfullscreen="" src="<?php echo $video_src ?>"></iframe>
        <?php elseif ($video_type == 'url'): ?>
        <video loop autoplay="autoplay" style=" height: 100%;left: 0;position: absolute;top: 0;z-index: 1;width: 100%;">
          <source src="<?php echo $video_src ?>" type="video/mp4" />
        </video> 
        <?php endif; ?>
      <?php endif; ?>
      <div class="container">
        <div class="hero-content">
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
          
          <?php if ($heroVideo): ?>
          <div class="hero-btn-actions">
            <a href="http://vimeo.com/<?php echo $video_link; ?>" title="<?php if( trim($heroHeading)) : ?> <?php echo $heroHeading; ?> <?php endif; ?>" class="btn btn-border btn-border-inverse btn-rounded">
              <span class="sr-only">Watch the video</span><i class="fa fa-chevron-right"></i>
            </a>
          </div>
          <?php endif; ?>
        </div>
    
      </div>
    </div>
  </div>
</div>
<script>
(function($){
  
  $(document).ready(function(){
    $('.hero-btn-actions .btn-border-inverse', '.style-4').unbind('click').click(function(e) {
      $(this).parents('.style-4:first').addClass('mask-off');
      e.preventDefault();
    });
    
    $( window ).scroll(function() {
      $('.style-4.mask-off').removeClass('mask-off');
    });
  });

})(jQuery);
</script>
