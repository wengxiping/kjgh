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
$duration = $aparams->get('duration', 1);

$type = JFactory::getApplication()->input->get('t3action') ? 'content' : 'count';


?>

<?php if ($type == 'count'): ?>
	<?php
	$count = JATemplateHelper::countItemsByDate($catids, $duration . ' days');
	$itemid = JFactory::getApplication()->input->get('ItemId');
	?>
	<div class="whatsnew-alert" data-url="<?php echo JUri::base() ?>?t3action=module&amp;mid=<?php echo $module->id ?>&amp;style=raw">
		<span class="number"><?php echo $count ?></span>
		<span class="text"><?php echo JText::_('TPL_WN_NEW_ARTICLES'); ?></span>
		<span class="text indicator"><i class="fa fa-chevron-down"></i></span>
	</div>

<?php else: ?>
	<?php
	$aparams->set('date_filtering', 'relative');
	$aparams->set('date_field', 'a.publish_up');
	$aparams->set('relative_date', $duration);
	$items = JATemplateHelper::getArticles($aparams, $catids, $aparams->get('count', 99));
	?>

	<div class="magazine-links container">
		<?php foreach ($items as $item): ?>
			<?php echo JLayoutHelper::render('joomla.content.link.clean', array('item' => $item, 'params' => $aparams)); ?>
		<?php endforeach ?>
	</div>

<?php endif ?>