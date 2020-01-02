<?php
/**
 * ------------------------------------------------------------------------
 * JA Teline V Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$aparams = JATemplateHelper::getParams();

$aparams->loadArray($helper->toArray(true));

// get news
$catids = $aparams->get('list_categories');

$items = JATemplateHelper::getArticles($aparams, $catids, $aparams->get('count', 4));
?>

<div class="magazine-links">
	<?php foreach ($items as $item): ?>
	<?php echo JATemplateHelper::render($item, 'joomla.content.link', array('item' => $item, 'params' => $aparams)); ?>
	<?php endforeach ?>
</div>