<?php
/*
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

<div class="home">

	<?php if ($this->countModules('home-1')) : ?>
		<!-- HOME SL 1 -->
		<div class="wrap t3-sl t3-sl-1 <?php $this->_c('home-1') ?>">
			<jdoc:include type="modules" name="<?php $this->_p('home-1') ?>" style="raw" />
		</div>
		<!-- //HOME SL 1 -->
	<?php endif ?>

	<?php $this->loadBlock('mainbody') ?>

	<?php if ($this->countModules('home-5')) : ?>
		<!-- HOME SL 5 -->
		<div class="wrap t3-sl t3-sl-5 <?php $this->_c('home-5') ?>">
			<jdoc:include type="modules" name="<?php $this->_p('home-5') ?>" style="raw" />
		</div>
		<!-- //HOME SL 5 -->
	<?php endif ?>

</div>
