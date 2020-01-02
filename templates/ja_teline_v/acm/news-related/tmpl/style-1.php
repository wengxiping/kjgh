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

$input = JFactory::getApplication()->input;
if ($input->get ('option') != 'com_content' || $input->get ('view') != 'article') {
	return ;
}
$item_id = $input->get ('id');

$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
$model->setState('params', $aparams);
$item = $model->getItem ($item_id);
$items = JATemplateHelper::getRelatedItems($item, $aparams);
?>

<?php foreach ($items as $item) : ?>
<?php echo JLayoutHelper::render('joomla.content.item_link', array('item' => $item, 'params' => $aparams)); ?>
<?php endforeach ?>