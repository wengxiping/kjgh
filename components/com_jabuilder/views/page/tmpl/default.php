<?php 
/**
 * ------------------------------------------------------------------------
 * JA Builder Package
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

$item = $this->item;

JHtml::_('jquery.framework');

$doc = JFactory::getDocument();
// store this item to process later on plugin
$doc->jubitem = $item;

?>

<?php if ($item->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1> <?php echo $this->escape($item->params->get('page_heading')); ?> </h1>
	</div>
<?php endif; ?>

<?php if ( !empty($item) ) : ?>
	<div class="content">
		<?php echo $item->content ?>
	</div>
<?php endif;