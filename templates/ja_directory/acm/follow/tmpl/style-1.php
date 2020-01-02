<?php
/**
 * ------------------------------------------------------------------------
 * JA Directory Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
?>

<?php if ( $helper->get('facebook') || $helper->get('google-plus') || $helper->get('twitter') || $helper->get('pinterest') || $helper->get('linkedin') ): ?>
<div class="uber-social">
	<div class="addthis_toolbox">
		<?php if($helper->get('facebook')): ?>
			<a class="addthis_button_facebook_follow" addthis:userid="<?php echo $helper->get('facebook')?>"><i class="fa fa-facebook"></i></a>
		<?php endif; ?>
		
		<?php if($helper->get('twitter')): ?>
		<a class="addthis_button_twitter_follow" addthis:userid="<?php echo $helper->get('twitter')?>"><i class="fa fa-twitter"></i></a>
		<?php endif; ?>
		
		<?php if($helper->get('google-plus')): ?>
		<a class="addthis_button_google_follow" addthis:userid="+<?php echo $helper->get('google-plus')?>"><i class="fa fa-google-plus"></i></a>
		<?php endif; ?>
		
		<?php if($helper->get('pinterest')): ?>
		<a class="addthis_button_pinterest_follow" addthis:userid="<?php echo $helper->get('pinterest')?>"><i class="fa fa-pinterest"></i></a>
		<?php endif; ?>
		
		<?php if($helper->get('linkedin')): ?>
		<a class="addthis_button_linkedin_follow" addthis:usertype="company" addthis:userid="<?php echo $helper->get('linkedin')?>"><i class="fa fa-linkedin"></i></a>
		<?php endif; ?>
	</div>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52c4eb2a034cad83"></script>
	<!-- AddThis Follow END -->
</div>
<?php endif; ?>	