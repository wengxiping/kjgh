
EasySocial.require()
.script('site/videos/form')
.done(function($) {

	$('[data-videos-form]').implement(EasySocial.Controller.Videos.Form, {
		"type": "<?php echo $type; ?>",
		"uid": "<?php echo $video->uid; ?>",
		"isPrivateCluster": "<?php echo $isPrivateCluster; ?>",
		"uploadingText": "<?php echo JText::_('COM_ES_UPLOADING');?>",
		<?php if ($userTagItemList) { ?>
		"tagsExclusion": <?php echo FD::json()->encode($userTagItemList); ?>
		<?php } ?>
	});

});
