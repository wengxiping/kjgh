<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
$item = $displayData['item'];
$title = $this->escape($item->category_title);
if (!isset($item->catslug)) {
	$item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
}
// Template helper
JLoader::register('JATemplateHelper', T3_TEMPLATE_PATH . '/helper.php');

// Custom field
$color = '';
$customs 		= JATemplateHelper::getCustomFields($item->catid, 'category');

	if(empty($customs)) :
		$color = "default";
	else: 
		$color = $customs['colors'];
	endif;
?>

<span class="category-name category <?php echo $color ;?> hasTooltip" title="<?php echo JText::sprintf('COM_CONTENT_CATEGORY', ''); ?>">
	<?php if ($displayData['params']->get('link_category') && $item->catslug) : ?>
		<?php echo JHtml::_('link', JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug)), '<span itemprop="genre">'.$title.'</span>'); ?>
	<?php else : ?>
		<span itemprop="genre"><?php echo $title ?></span>
	<?php endif; ?>
</span>