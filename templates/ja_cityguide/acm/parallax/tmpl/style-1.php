<?php
	/**
	 * ------------------------------------------------------------------------
 * JA City Guide Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
	*/
	defined('_JEXEC') or die;
	$bgSource = $helper->get('bg-source');

	$bgImage = '';

	if($helper->get('bg-image')) {
		$bgImage = 'data-paroller-factor="-0.1" style="background-image: url('.$helper->get('bg-image').')"';
	}

	$bgMask = $helper->get('bg-mask');
?>

<div class="acm-parallax">
	<div class="parallax-showcase-item <?php if($bgImage) echo "ja-paroller" ;?>" <?php echo $bgImage ;?>>
		<!-- Video Background -->
		<?php if($bgSource) :?>
			<div id="video-bg" class="ja-paroller" data-paroller-type="foreground" data-paroller-factor="0.3" data-paroller-direction="vertical">
				<div class="video-wrap">
					<iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo $helper->get('link-video'); ?>?version=3&autoplay=1&mute=1&loop=1&rel=0&controls=0&showinfo=0&playlist=<?php echo $helper->get('link-video'); ?>" allowfullscreen></iframe>
				</div>
			</div>
		<?php endif ;?>
		<!-- // Video Background -->

		<!-- Mask Background -->
		<div class="mask-bg <?php if($bgMask) echo 'has-bg' ;?>"></div>
	</div>
</div>
