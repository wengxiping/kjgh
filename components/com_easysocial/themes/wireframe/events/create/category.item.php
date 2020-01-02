<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="btn-wrap"
	data-category-item
	data-parent-id="<?php echo $category->parent_id; ?>"
	data-id="<?php echo $category->id; ?>"
	data-back-id="<?php echo $backId; ?>">
	<?php if ($category->container) { ?>
	<span class="btn btn-es is-container">
		<img class="avatar" src="<?php echo $category->getAvatar(SOCIAL_AVATAR_SQUARE);?>" alt="<?php echo $this->html('string.escape', $category->getTitle());?>" />

		<div class="es-title"><?php echo $category->getTitle();?></div>
	</span>
	<?php } else { ?>
	<a class="btn btn-es" href="<?php echo ESR::events(array_merge($categoryRouteBaseOptions, array('category_id' => $category->id)));?>">
		<img class="avatar" src="<?php echo $category->getAvatar(SOCIAL_AVATAR_SQUARE);?>" alt="<?php echo $this->html('string.escape', $category->getTitle());?>" />

		<div class="es-title"><?php echo $category->getTitle();?></div>
	</a>
	<?php } ?>
	<?php if ($category->hasImmediateCategories($profileId)) { ?>
		<div class="btn-wrap__toggle" data-toggle-subcategories="show">
			<?php echo JText::_('COM_ES_SHOW_SUBCATEGORIES') ?>
		</div>
	<?php } ?>
</div>
