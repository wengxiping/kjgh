<?php
	$helper = $displayData['helper'];
	$root_url = JUri::root(true);
	$isBuilderTemplate = JFactory::getApplication()->getTemplate() == 'ja_builder';
	$site = defined('JUB_SITE_KEY') ? JUB_SITE_KEY : 'default';
	$key = substr(md5(JFactory::getConfig()->get('secret')), 0, 13);
	// 
	$osite = '';
	$row = $helper->getKey('page', true);
	if (is_object($row) && $row->data) {
		$data = json_decode($row->data, true);
		$osite = isset($data['settings']) && isset($data['settings']['site']) ? $data['settings']['site'] : '';
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Page</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<!-- jQuery, using core -->		
</head>

<body class="jub jub-loading">

	<div id="jub-mainbody">
	</div>

	<!-- <script src="<?php echo $root_url ?>/media/jui/js/jquery.min.js"></script> -->
	<!-- <script src="<?php echo $root_url ?>/media/jub/assets/lib/bootstrap/js/bootstrap.min.js"></script> -->

	<script>

	    JUB = {
	    	provider: '<?php echo JUB_PROVIDER ?>',
	        siteUrl: '<?php echo JUri::root(true) ?>',
	        mediaUrl: '<?php echo JUri::root(true) ?>/media/jub/dev/<?php echo $site ?>',
	        assetUrl: '<?php echo JUri::root(true) ?>/media/jub/assets',
	        ckeditorUrl: '<?php echo JUri::root(true) ?>/plugins/system/jabuilder/assets/ckeditor',
	        siteUrlHelper: location.href,
	        toolUrl: '<?php echo JUB_BUILDER_URL ?>',
	        site: 'j<?php echo $helper->getSiteid() ?>-<?php echo $key ?>',
	        osite: '<?php echo $osite ?>',
	        page: '<?php echo $helper->getKey('page') ?>',
	        layout: '<?php echo $helper->getKey('layout') ?>',
	        container: '#jub-mainbody',
	        contents: {
	            <?php if ($isBuilderTemplate): ?>
	            header: 'blocks',
	            <?php endif ?>
	            <?php if ($helper->isJUBPage()): ?>
	            content: 'blocks',
	            <?php else: ?>
	            top: 'blocks',
	            content: 'mainbody',
	            bottom: 'blocks',
	            <?php endif ?>
	            <?php if ($isBuilderTemplate): ?>
	            footer: 'blocks'
	            <?php endif ?>
	        },
	        encodeData: <?php echo function_exists('gzinflate') ? 1 : 0 ?>,
	        version: '<?php echo $helper->getVersion() ?>',
	        upload_max_size: <?php echo $helper->getUploadSize() ?>
	    }

	    // jQuery.getScript(JUB.toolUrl + '/js/main.js');
	</script>

	<script src="<?php echo JUB_BUILDER_URL ?>/js/loader.min.js?<?php echo $helper->getVersion() ?>"></script>
</body>

</html>