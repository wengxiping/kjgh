<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
?>
<div class="sharing clearfix">
	<!-- FB -->
	<div style="float:left;" id="rsep_fb_like">
		<div id="fb-root"></div>
		<script src="https://connect.facebook.net/en_US/all.js" type="text/javascript"></script>
		<script type="text/javascript">
			FB.init({status: true, cookie: true, xfbml: true});
		</script>
		<fb:like href="<?php echo JUri::getInstance()->toString(); ?>" send="true" layout="button_count" width="150"
		         show_faces="false"></fb:like>
	</div>

	<!-- Twitter -->
	<div style="float:left;" id="rsep_twitter">
		<a href="https://twitter.com/share" class="twitter-share-button"
		   data-text="<?php echo $this->item->title . " " . $socialUrl; ?>">Tweet</a>
		<script>!function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (!d.getElementById(id)) {
					js = d.createElement(s);
					js.id = id;
					js.src = "//platform.twitter.com/widgets.js";
					fjs.parentNode.insertBefore(js, fjs);
				}
			}(document, "script", "twitter-wjs");</script>
	</div>
</div>