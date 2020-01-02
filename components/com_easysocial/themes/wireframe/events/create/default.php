<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container" data-es-select-category
	<?php echo isset($page) ? 'data-page-id="' . $page->id . '"' : '' ?>
	<?php echo isset($group) ? 'data-group-id="' . $group->id . '"' : '' ?>
	>

	<div class="es-content">
		<?php echo $this->html('html.snackbar', 'COM_ES_CLUSTERS_SELECT_CATEGORY', 'h1'); ?>

		<p><?php echo JText::_('COM_ES_CLUSTERS_SELECT_CATEGORY_INFO_EVENTS'); ?></p>

		<div class="o-col-sm">
			<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm t-hidden" data-select-category-back>
				<?php echo JText::_('COM_ES_BACK'); ?>
			</a>
		</div>

		<div class="es-create-category-select" data-es-items-container>
			<?php foreach ($categories as $category) { ?>
				<?php echo $this->loadTemplate('site/events/create/category.item', array('category' => $category, 'categoryRouteBaseOptions' => $categoryRouteBaseOptions, 'backId' => $backId, 'profileId' => $profileId)) ?>
			<?php } ?>
		</div>
	</div>
</div>
