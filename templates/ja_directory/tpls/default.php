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

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>"
      class='<jdoc:include type="pageclass" />'>

<head>
    <jdoc:include type="head"/>
    <?php $this->loadBlock('head') ?>
    <?php $this->addCss('layouts/footer') ?>
    <?php $this->addCss('layouts/docs') ?>
    <?php $this->addCss('layouts/new-menu') ?>
    <?php $this->addCss('layouts/new-nav') ?>
    <?php $this->addCss('layouts/hslm-style') ?>
    <script type="text/javascript" src="/templates/ja_directory/js/header.js"></script>
</head>

<body>

<div class="t3-wrapper"> <!-- Need this wrapper for off-canvas menu. Remove if you don't use of-canvas -->

    <?php $this->loadBlock('header') ?>
    <!-- MASSHEAD -->
    <?php $this->loadBlock('slideshow') ?>
    <?php $this->loadBlock('newmenu') ?>
    <?php $this->loadBlock('masthead') ?>
    <!-- 新增 首页背景图 和 统计 start -->
    <?php $this->loadBlock('banner-show')?>
    <!-- 新增 首页背景图 和 统计 end -->

    <?php $this->loadBlock('section-1') ?>

    <?php $this->loadBlock('spotlight-1') ?>

    <?php $this->loadBlock('mainbody') ?>

    <?php $this->loadBlock('spotlight-2') ?>

    <?php $this->loadBlock('section-2') ?>

    <?php $this->loadBlock('navhelper') ?>
    <?php $this->loadBlock('new-hand') ?>

    <?php $this->loadBlock('footer') ?>

</div>

</body>

</html>
