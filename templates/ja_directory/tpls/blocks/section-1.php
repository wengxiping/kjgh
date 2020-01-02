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

<?php if ($this->countModules('section-1')) : ?>
  <!-- SECTION 1 -->
  <section class="wrap <?php if ($this->countModules('slideshow') || $this->countModules('masthead')) : ?> has-slideshow <?php endif; ?> t3-section t3-section-1 <?php $this->_c('section-1') ?>">
      <jdoc:include type="modules" name="<?php $this->_p('section-1') ?>" style="raw"/>
  </section>
  <!-- //SECTION 1 -->
<?php endif ?>