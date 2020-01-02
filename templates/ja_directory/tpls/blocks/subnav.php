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
<?php if ($this->countModules('subnav')) : ?>
  <!-- SUB NAV -->
  <nav class="wrap t3-subnav <?php $this->_c('subnav') ?>">
    <div class="container">
      <jdoc:include type="modules" name="<?php $this->_p('subnav') ?>" style="raw"/>
    </div>
  </nav>
  <!-- //SUB NAV -->
<?php endif ?>