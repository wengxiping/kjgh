
EasySocial.require()
.script('site/stream/stream')
.done(function($){

	$('[data-es-streams]').implement('EasySocial.Controller.Stream', {
		checknew: <?php echo $this->config->get('stream.updates.enabled') ? 'true' : 'false'; ?>,
		source: "<?php echo $view; ?>",
		sourceId: "<?php echo JRequest::getInt('id', ''); ?>",
		clusterId: "<?php echo $clusterId; ?>",
		clusterType: "<?php echo $clusterType; ?>",
		autoload: <?php echo $autoload ? 'true' : 'false'; ?>,
		commentOptions: {
				'attachments': <?php echo $this->config->get('comments.attachments.enabled') ? 'true' : 'false';?>,
				'errorMessage': "<?php echo JText::_('COM_ES_COMMENT_ERROR_MESSAGE'); ?>"
		}
	});
});
