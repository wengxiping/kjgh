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
$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/new_featured.css");
?>

<?php if ($this->countModules('section-2')) : ?>
  <!-- SECTION 2 -->
  <section class="wrap t3-section t3-section-2 <?php $this->_c('section-2') ?>">
<!--     <p class='fist-merchant'>首批拟邀入驻合作企业、机构与品牌 </p>-->
      <jdoc:include type="modules" name="<?php $this->_p('section-2') ?>" style="raw"/>
  </section>
  <!-- //SECTION 2 -->
<?php endif ?>
