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

<?php if ($this->countModules('new-hand')) : ?>
    <!-- SECTION 2 -->
<div class="new-clientbg">
    <section class="wrap t3-section t3-section-2 <?php $this->_c('new-hand') ?>">
        <jdoc:include type="modules" name="<?php $this->_p('new-hand') ?>" style="raw"/>
    </section>
</div>

    <!-- //SECTION 2 -->
<?php endif ?>
