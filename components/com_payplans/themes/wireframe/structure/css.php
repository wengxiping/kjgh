<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<style type="text/css">
#pp .pp-toolbar { border-color: <?php echo $this->config->get('layout_toolbar_bordercolor', '#333333');?>; }
#pp .pp-toolbar { background-color: <?php echo $this->config->get('layout_toolbar_color', '#333333');?>;}
#pp .pp-toolbar,
#pp .pp-toolbar .o-nav__item .pp-toolbar__link { color: <?php echo $this->config->get('layout_toolbar_textcolor', '#FFFFFF')?>; }
#pp .pp-toolbar .o-nav__item.is-active .pp-toolbar__link,
#pp .pp-toolbar .o-nav__item .pp-toolbar__link:hover, 
#pp .pp-toolbar .o-nav__item .pp-toolbar__link:focus,
#pp .pp-toolbar .o-nav__item .pp-toolbar__link:active { background-color: <?php echo $this->config->get('layout_toolbar_activecolor', '#5c5c5c')?>; }
</style>