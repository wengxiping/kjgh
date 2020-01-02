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

// get params
$sitename  = $this->params->get('sitename');
$slogan    = $this->params->get('slogan', '');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}

$headright = (($this->getParam('navigation_collapse_enable', 1) && $this->getParam('responsive', 1)) || $this->getParam('addon_offcanvas_enable'));
?>

<header id="t3-header" class="t3-header">
	<div class="container container-hd">
		<div class="row">
			<div class="col-xs-4 col-sm-1 col-md-2">
				<!-- LOGO -->
				<div class="logo logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
					<a href="<?php echo JUri::base() ?>" title="<?php echo strip_tags($sitename) ?>">
						<?php if($logotype == 'image'): ?>
							<img class="logo-img" src="<?php echo JUri::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
						<?php endif ?>

						<?php if($logoimgsm) : ?>
							<img class="logo-img-sm" src="<?php echo JUri::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
						<?php endif ?>
						<span><?php echo $sitename ?></span>
					</a>
					<small class="site-slogan"><?php echo $slogan ?></small>
				</div>
				<!-- // LOGO -->
			</div>

			<div class="col-xs-8 col-sm-11 col-md-10">
				<?php if($headright) :?>
				<!-- HEADRIGHT -->
				<div class="headright pull-right">
					<!-- Brand and toggle get grouped for better mobile display -->
					<div class="navbar-header">
						<?php if ($this->getParam('navigation_collapse_enable', 1) && $this->getParam('responsive', 1)) : ?>
							<?php $this->addScript(T3_URL.'/js/nav-collapse.js'); ?>
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar-collapse">
								<span class="fa fa-bars"></span>
							</button>
						<?php endif ?>

						<?php if ($this->getParam('addon_offcanvas_enable')) : ?>
							<?php $this->loadBlock ('off-canvas') ?>
						<?php endif ?>
					</div>
				</div>
				<!-- // HEADRIGHT -->
				<?php endif ?>

				<!-- MAIN NAVIGATION -->
				<nav id="t3-mainnav" class="navbar navbar-default t3-mainnav pull-right">
						<?php if ($this->getParam('navigation_collapse_enable')) : ?>
							<div class="t3-navbar-collapse navbar-collapse collapse"></div>
						<?php endif ?>

						<div class="t3-navbar navbar-collapse collapse">
							<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
						</div>
				</nav>
				<!-- //MAIN NAVIGATION -->
			</div>
		</div>
	</div>
</header>
<!-- //HEADER -->

