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

<?php if ($this->countModules('newmenu')) : ?>
    <!-- 新建菜单位置 ------------------------------------------ start  -->
    <div class="s-hs-menu-container">
        <div class="menu-container hs-menu">
            <jdoc:include type="modules" name="<?php $this->_p('newmenu') ?>" />
        </div>
    </div>
    <!-- 新建菜单位置 ------------------------------------------ end  -->
<?php endif ?>
