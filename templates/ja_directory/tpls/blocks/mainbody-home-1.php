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
			<div class="container">
				<jdoc:include type="modules" name="<?php $this->_p('home-1') ?>" style="raw" />
			</div>
		</div>
		<!-- //HOME SL 1 -->
	<?php endif ?>

	<?php if ($this->countModules('home-2')) : ?>
		<!-- HOME SL 2 -->
		<div class="wrap t3-sl t3-sl-2 <?php $this->_c('home-2') ?>">
			<div class="container">
				<jdoc:include type="modules" name="<?php $this->_p('home-2') ?>" style="raw" />
			</div>
		</div>
		<!-- //HOME SL 2 -->
	<?php endif ?>

	<?php if ($this->countModules('home-3')) : ?>
		<!-- HOME SL 3 -->
		<div class="wrap t3-sl t3-sl-3 <?php $this->_c('home-3') ?>">
			<div class="container">
				<jdoc:include type="modules" name="<?php $this->_p('home-3') ?>" style="raw" />
			</div>
		</div>
		<!-- //HOME SL 3 -->
	<?php endif ?>

	<?php if ($this->countModules('home-4')) : ?>
		<!-- HOME SL 4 -->
		<div class="wrap t3-sl t3-sl-4 <?php $this->_c('home-4') ?>">
			<div class="container">
				<jdoc:include type="modules" name="<?php $this->_p('home-4') ?>" style="raw" />
			</div>
		</div>
		<!-- //HOME SL 4 -->
	<?php endif ?>

	<?php if ($this->countModules('home-5')) : ?>
		<!-- HOME SL 5 -->
		<div class="wrap t3-sl t3-sl-5 <?php $this->_c('home-5') ?>">
			<div class="container">
				<jdoc:include type="modules" name="<?php $this->_p('home-5') ?>" style="raw" />
			</div>
		</div>
		<!-- //HOME SL 5 -->
	<?php endif ?>

</div>
