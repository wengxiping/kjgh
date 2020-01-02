<?php
/**
 * ------------------------------------------------------------------------
 * JA Morgan Template
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

<!-- TOPBAR -->
<?php if ($this->countModules('topbar-left') || $this->countModules('topbar-right')) : ?>
<div class="t3-topbar">
	<div class="container container-hd">
		<div class="container-inner clearfix">
		  <?php if ($this->countModules('topbar-left')) : ?>
		  <div class="topbar-left pull-left <?php $this->_c('topbar-left') ?>">
		    <jdoc:include type="modules" name="<?php $this->_p('topbar-left') ?>" style="raw" />
		  </div>
		  <?php endif ?>

		  <?php if ($this->countModules('topbar-right')) : ?>
		  <div class="topbar-right pull-right <?php $this->_c('topbar-right') ?>">
		    <jdoc:include type="modules" name="<?php $this->_p('topbar-right') ?>" style="raw" />
		  </div>
		  <?php endif ?>
		</div>
	</div>
</div>
<?php endif ?>
