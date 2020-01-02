
EasySocial.require()
.script('site/vendors/videojs', 'site/vendors/videojs/brand', 'site/vendors/videojs/watermark')
.done(function($) {

	var player = videojs('video-<?php echo $uid;?>', {
		"controls": true,
		"poster": "<?php echo $video->getThumbnail();?>"
	}, function() {
	
	});

	<?php if ($this->config->get('video.layout.player.logo')) { ?>
	player.brand({
		image: "<?php echo $logo;?>",
		title: "<?php echo $this->jConfig->getValue('sitename');?>",
		destination: "<?php echo JURI::root();?>",
		destinationTarget: "_top"
	});
	<?php } ?>

	<?php if ($this->config->get('video.layout.player.watermark')) { ?>
	player.watermark({
		image: "<?php echo $watermark;?>",
		position: "<?php echo $this->config->get('video.layout.player.watermarkposition');?>",
		url: "<?php echo JURI::root();?>"
	});
	<?php } ?>
});