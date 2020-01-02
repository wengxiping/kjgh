<?php
/**
 * ------------------------------------------------------------------------
 * JA Conf Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
  $videoHeading    = $helper->get('video-heading');
  $videoIntro      = $helper->get('video-intro');
  $videoLink 			 = $helper->get('video-link');
  $buttonValue     = $helper->get('button-value');
  $buttonLink      = $helper->get('button-link');
  
  $video_src    = '';
  $video_link    = '';
  $video_lang = '';

  $currentLanguage = JFactory::getLanguage();
  $isRTL = $currentLanguage->isRtl();

  if($isRTL) {
  	$video_lang = 'rtl';
  }

	if ($videoLink) {
	  $arr = preg_split ('/=/', $videoLink, 2);
	  if (count($arr) == 2) {    
	    switch (trim($arr[0])) {
	      case 'vimeo':
	        $video_src = '//player.vimeo.com/video/' . trim($arr[1]) . '?title=0&amp;byline=0&amp;portrait=0&amp;&amp;loop=1';
	        $video_link = trim($arr[1]);
	        break;
	      case 'youtube':
	        $video_src = '//www.youtube.com/embed/' . trim($arr[1]) . '?rel=0&amp;controls=0&amp;showinfo=0&amp;loop=1&amp;html5=1';
	         $video_link = trim($arr[1]);
	        break;
	      case 'youku':
	        $video_src = '//player.youku.com/embed/' . trim($arr[1]) . '?rel=0&amp;controls=0&amp;showinfo=0&amp;loop=1&amp;html5=1';
	         $video_link = trim($arr[1]);
	         $videoStyle = $videoStyle.' youku-video';
	        break;
	      default:
	        break;
	    }
	  }
	}
?>

<div id="acm-video-<?php echo $module->id; ?>" class="acm-video row <?php echo $video_lang ;?>">
  <div class="col-md-6 video-content">
  	<?php if($videoHeading || $module->showtitle) : ?>
  	<div class="section-title">
	    <?php if($videoHeading) : ?>
	    <div class="title-intro">
	      <?php echo $videoHeading; ?>
	    </div>
	    <?php endif; ?>

	    <?php if($module->showtitle): ?>
				<h3 class="title-lead h1"><?php echo $module->title ?></h3>
			<?php endif; ?>
		</div>
		<?php endif; ?>
    
    <?php if( trim($videoIntro)) : ?>
    <div class="video-intro">
      <?php echo $videoIntro; ?>
    </div>
    <?php endif; ?>

    <?php if(trim($buttonValue)): ?>
    	<div class="more-link">
	      <a href="<?php echo $buttonLink; ?>" ><?php echo $buttonValue; ?> <span class="fas fa-arrow-right"></span></a>
	    </div>
    <?php endif; ?>
  </div>
	<div class="col-md-6 video-player">
		<div class="video-wrapper video-wrapper<?php echo $module->id;?>">
			<div class="video">
				<?php if ($videoLink) { ?>
					<div id="videoplayer<?php echo $module->id; ?>">
					<span></span>
					<?php
						if (count($arr) == 2) :
						switch (trim($arr[0])) {
						  case 'vimeo':
							?>
							<iframe id="player1" src="https://player.vimeo.com/video/<?php echo $video_link; ?>?api=1&amp;player_id=player1" width="630" height="354" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
							<?php
							break;
						  case 'youtube':
							?>
							<img alt="Thumbnail" class="img-thumb" src="https://i.ytimg.com/vi/<?php echo $video_link; ?>/sddefault.jpg" />
							<?php
							break;
							case 'youku':
							?>
							<iframe height=498 width=510 src='https://player.youku.com/embed/<?php echo $video_link; ?>==' frameborder=0 'allowfullscreen'></iframe>
							<?php
							break;
						  default:
							break;
						}
					  endif;
					?>
					</div>
					<a <?php if (count($arr) == 2) {if (trim($arr[0]) == 'youtube') {echo ' onclick="javideoPlay'.$module->id.'();" ';}} ?> title="Play" class="btn-play"><span class="sr-only">Watch the video</span><span class="fa fa-play"></span></a>
				<?php } ?>
		  </div>
		</div>
	</div>
</div>
<script>
(function($){
	
  $(document).ready(function(){
  	$widthBody = $('.container-wrap').width();
  	$widthContainer = $('.t3-section-wrap .container').width();
  	$positionRight = -(($widthBody - $widthContainer) / 2) + 32;

  	if($('.acm-video').hasClass('rtl')) {
  		$('.video-wrapper').css('left',$positionRight);
  	} else {
  		$('.video-wrapper').css('right',$positionRight);
  	};
  	

  	setTimeout(function(){
  		$heightVideo = $('.video-wrapper .video').width()*(9/16);
  		$('.video-content').css('min-height',$heightVideo);
  	},200);

  	$(window).resize(function() {
  		$widthBody = $('.container-wrap').width();
	  	$widthContainer = $('.t3-section-wrap .container').width();
	  	$positionRight = -(($widthBody - $widthContainer) / 2) + 32;
	  	$('.video-wrapper').css('right',$positionRight);
	  	$heightVideo = $('.video-wrapper .video').width()*(9/16);
  		$('.video-content').css('min-height',$heightVideo);
  	})



    // for vimeo js api player. need jquery so we set inside.
    <?php
    	if (count($arr) == 2) {
	    switch (trim($arr[0])) {
	      case 'vimeo':
			?>
			var player = $('iframe#player1');
			var playerOrigin = '*';
			var status = $('.status');

			// Listen for messages from the player
			if (window.addEventListener) {
				window.addEventListener('message', onMessageReceived, false);
			}
			else {
				window.attachEvent('onmessage', onMessageReceived, false);
			}

			// Handle messages received from the player
			function onMessageReceived(event) {
				// Handle messages from the vimeo player only
				if (!(/^https?:\/\/player.vimeo.com/).test(event.origin)) {
					return false;
				}
		
				if (playerOrigin === '*') {
					playerOrigin = event.origin;
				}
		
				var data = JSON.parse(event.data);
		
				switch (data.event) {
					case 'ready':
						onReady();
						break;
			   
					case 'playProgress':
						onPlayProgress(data.data);
						break;
				
					case 'pause':
						onPause();
						break;
			   
					case 'finish':
						onFinish();
						break;
				}
			}

			// Call the API when a button is pressed
			$('.btn-play').on('click', function() {
				jQuery('div.video-wrapper').addClass('playing');
				post($(this).attr('title').toLowerCase());
			});

			// Helper function for sending a message to the player
			function post(action, value) {
				var data = {
				  method: action
				};
		
				if (value) {
					data.value = value;
				}
		
				var message = JSON.stringify(data);
				player[0].contentWindow.postMessage(data, playerOrigin);
			}

			function onReady() {
				status.text('ready');
		
				post('addEventListener', 'pause');
				post('addEventListener', 'finish');
				post('addEventListener', 'playProgress');
			}

			function onPause() {
				status.text('paused');
			}

			function onFinish() {
				status.text('finished');
			}

			function onPlayProgress(data) {
				status.text(data.seconds + 's played');
			}
			<?php
	        break;
	      default:
	        break;
	    }
	  }
    ?>
  });

})(jQuery);
<?php
	// for youtube js api player. don't need jquery so we set outside.
	if (count($arr) == 2) :
	switch (trim($arr[0])) {
	  case 'youtube':
		?>
		  var tag = document.createElement('script');

		  tag.src = "https://www.youtube.com/iframe_api";
		  var firstScriptTag = document.getElementsByTagName('script')[0];
		  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		  var player;
		  function onYouTubeIframeAPIReady() {
		  	
		  }
		  function onPlayerReady(event) {
		  	jQuery('iframe#videoplayer<?php echo $module->id;?>').css('width', '100%').css('height', '100%');
			event.target.playVideo();
		  }

		  var done = false;
		  function onPlayerStateChange(event) {}
		  function stopVideo() {}

		  function javideoPlay<?php echo $module->id;?>() {
				jQuery('div.video-wrapper<?php echo $module->id;?>').addClass('playing');
			 	player = new YT.Player('videoplayer<?php echo $module->id;?>', {
			  height: '390',
			  width: '640',
			  videoId: '<?php echo $video_link; ?>',
			  events: {
				'onReady': onPlayerReady,
				'onStateChange': onPlayerStateChange
			  }
			});
		  }
		<?php
		break;
	  default:
		break;
	}
  endif;
?>
</script>