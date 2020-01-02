<?php
/*
 * ------------------------------------------------------------------------
 * JA Mono Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$params 	= $displayData['params'];
$title 		= $displayData['title'];
$item 		= $displayData['item'];
$imageBg = $displayData['imageBg'];
$canEdit = $params->get('access-edit');
$print = $displayData['print'];

$info    	= $params->get('info_block_position', 0);
$icons = !empty($this->print) || $canEdit || $params->get('show_print_icon') || $params->get('show_email_icon');

// Check if associations are implemented. If they are, define the parameter.
$assocParam = (JLanguageAssociations::isEnabled() && $params->get('show_associations'));

// Todo Not that elegant would be nice to group the params
$useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam);
?>

<div class="ja-masthead<?php echo $params->get('moduleclass_sfx','')?>" <?php if ($imageBg != '') : ?>style="background-image: url(<?php echo $imageBg; ?>)"<?php endif; ?>>
	<div class="container">
		<div class="ja-masthead-detail">
			<?php if ($params->get('show_category')) : ?>
				<div class="category-name">
					<?php $title_cat = $this->escape($item->category_title); ?>
					<?php if ($params->get('link_category') && $item->catslug) : ?>
						<?php $url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug)) . '" itemprop="genre">' . $title_cat . '</a>'; ?>
						<?php echo JText::sprintf($url); ?>
					<?php else : ?>
						<?php echo JText::sprintf('<span itemprop="genre">' . $title_cat . '</span>'); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ($params->get('show_title')) : ?>
				<h3 class="ja-masthead-title h1"><?php echo $title; ?></h3>
			<?php endif; ?>

			<!-- Aside -->
			<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
			<aside class="article-aside clearfix">
				<?php if ($icons): ?>
			  		<?php echo JLayoutHelper::render('joomla.content.icons', array('item' => $item, 'params' => $params, 'print' => $print)); ?>
			  <?php endif; ?>

				<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
			</aside>
			<?php endif; ?>
			<!-- // Aside -->
		</div>
	</div>
</div>