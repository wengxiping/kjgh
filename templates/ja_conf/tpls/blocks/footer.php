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

$lgFooter  = $this->params->get('lg-footer');
?>

<!-- BACK TOP TOP BUTTON -->
<div id="back-to-top" data-spy="affix" data-offset-top="200" class="back-to-top hidden-xs hidden-sm affix-top">
  <button class="btn btn-linear" title="Back to Top" aria-label="back-to-top"><span class="fas fa-angle-double-up"></span></button>
</div>

<script type="text/javascript">
(function($) {
  // Back to top
  $('#back-to-top').on('click', function(){
    $("html, body").animate({scrollTop: 0}, 500);
    return false;
  });
})(jQuery);
</script>
<!-- BACK TO TOP BUTTON -->

<!-- FOOTER -->
<footer id="t3-footer" class="wrap t3-footer">

	<?php if ($this->checkSpotlight('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6')) : ?>
		<!-- FOOT NAVIGATION -->
		<div class="container">
			<?php $this->spotlight('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6') ?>
		</div>
		<!-- //FOOT NAVIGATION -->
	<?php endif ?>

	<section class="t3-copyright text-center">
		<div class="container">
			<div class="row">
				<div class="copyright <?php $this->_c('footer') ?>">
					<jdoc:include type="modules" name="<?php $this->_p('footer') ?>" />
				</div>
				
				<?php if ($this->getParam('t3-rmvlogo', 1)): ?>
					<div class="poweredby text-hide">
						<a class="t3-logo t3-logo-small t3-logo-light" href="http://t3-framework.org" title="<?php echo JText::_('T3_POWER_BY_TEXT') ?>"
						   target="_blank" <?php echo method_exists('T3', 'isHome') && T3::isHome() ? '' : 'rel="nofollow"' ?>><?php echo JText::_('T3_POWER_BY_HTML') ?></a>
					</div>
				<?php endif; ?>

				<?php if($lgFooter) :?>
					<div class="logo-footer">
						<img src="<?php echo $lgFooter ;?>" alt="Logo Footer" />
					</div>
				<?php endif ;?>
			</div>
		</div>
	</section>

</footer>
<!-- //FOOTER -->